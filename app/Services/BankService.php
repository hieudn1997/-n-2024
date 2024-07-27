<?php

namespace App\Services;

use App\Models\Fund;
use App\Models\FundTransaction;
use App\Models\Transaction;
use App\Models\UserAsset;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BankService extends VendorService
{
    // hủy giao dịch
    const STATUS_CANCEL = -1;
    // đang chờ thanh toán
    const STATUS_NEW = 0;
    //dã thanh toán
    const STATUS_PAID = 1;
    // đã mua đc quỹ bên tcinvest 
    const STATUS_BOUGHT = 2;
    // đã bán ...
    const STATUS_SOLD = 3;
    // 
    const STATUS_ADVANCED_MONEY = 4;
    // đã rút
    const STATUS_WITHDRAWN = 5;
    // lỗi giao dịch
    const STATUS_FAILURE = 6;

    const TYPE_BUY = 0;
    const TYPE_SELL = 1;
    const TYPE_WITHDRAW = 2;


    /**
     * @override
     */
    public function getCredential($credentialId = 0, $forceUpdate = false)
    {
        try {
            $credential = Cache::get('bank:::credential', false);

            if (!$credential) {
                $bankConfig = Config('bank');

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://ebank.tpb.vn/gateway/api/auth/login',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => '{"username":"' . base64_decode($bankConfig['username']) . '","password":"' . base64_decode($bankConfig['password']) . '","step_2FA":"VERIFY"}',
                    CURLOPT_HTTPHEADER => array(
                        'Accept: application/json, text/plain, */*',
                        'Accept-Language: en-US,en;q=0.7',
                        'Authorization: Bearer',
                        'Connection: keep-alive',
                        'Content-Type: application/json',
                        'Sec-Fetch-Dest: empty',
                        'Sec-Fetch-Mode: cors',
                        'Sec-Fetch-Site: same-origin',
                        'Sec-GPC: 1',
                    ),
                ));

                $response = curl_exec($curl);


                curl_close($curl);

                Log::info($response);

                $credential = json_decode($response)->access_token;

                Cache::put('bank:::credential', $credential, now()->addMinutes(5));
            }

            return $credential;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getBankInfo()
    {
        $bankConfig = Config('bank');
        return [
            'bank_code' => $bankConfig['code'],
            'bank_name' => $bankConfig['name'],
            'account_number' => $bankConfig['account_number'],
            'beneficiary_name' => $bankConfig['accountant_holder'],
        ];
    }

    public function getTransactionHistory($keyword = "")
    {
        try {
            $credential = $this->getCredential();
            $bankConfig = Config('bank');

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $bankConfig['get_history_url'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode([
                    'accountNo' => $bankConfig['account_number'],
                    'currency' => "VND",
                    'fromDate' => date("Ymd", strtotime("-1 days")),
                    'keyword' => $keyword,
                    'toDate' => date("Ymd")
                ]),
                CURLOPT_HTTPHEADER => array(
                    'APP_VERSION: 2024.08.17',
                    'Accept: application/json, text/plain, */*',
                    'Accept-Language: en-US,en;q=0.9',
                    "Authorization: Bearer $credential",
                    'Connection: keep-alive',
                    'Content-Type: application/json',
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $response = json_decode($response);

            return $response->transactionInfos;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function checkPayment($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            if ($transaction->status) {
                return $this->ok([
                    'payment_status' => (bool) $transaction->status
                ]);
            }

            $bankHistory = $this->getTransactionHistory();

            $matches = array();

            foreach ($bankHistory as $key => $value) {
                preg_match("/[a-zA-Z0-9]{16}/", $value->description, $matches);
                $ref = $matches[0] ?? null;

                if ($ref && $ref === $id && $value->amount == $transaction->amount) {
                    $transaction->status = 1;
                    $transaction->save();

                    $this->buyFundCertificate($transaction);

                    break;
                }
            }

            return $this->ok([
                'payment_status' => (bool) $transaction->status
            ]);
        } catch (Exception $e) {
            return $this->ok([
                'payment_status' => false
            ]);
        }
    }

    public function getBankAccountInfo($bankId, $accountId, $isCheck = false, $raw = false)
    {
        try {
            $credential = $this->getCredential();
            $bankConfig = Config('bank');

            $getInfoUrl = $bankId === $bankConfig['id']
                ? $bankConfig['internal_account_info_url']
                : $bankConfig['external_account_info_url'];


            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $getInfoUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode([
                    'debtorAccountNumber' => $bankConfig['account_number'],
                    'creditorAccountNumber' => $accountId,
                    'creditorBankId' =>  $bankId
                ]),
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json, text/plain, */*',
                    'Accept-Language: en-US,en;q=0.9',
                    "Authorization: Bearer  $credential",
                    'Connection: keep-alive',
                    'Content-Type: application/json',
                    'Sec-Fetch-Dest: empty',
                    'Sec-Fetch-Mode: cors',
                    'Sec-Fetch-Site: same-origin',
                    'Sec-GPC: 1',
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $response = json_decode($response);

            if ($isCheck) return true;

            $bankInfo = [
                'name' => $response->creditorInfo->name,
                'account_number' => $response->creditorInfo->accountNumber,
                'bank_id' => $response?->creditorInfo?->extBankId ?? $bankId,
                'bank_code' => $response->creditorInfo->extBankCode ?? $bankConfig['code'],
                'bank_name_en' => $response->creditorInfo->extBankNameEn ?? $bankConfig['name'],
                'bank_name_vn' => $response->creditorInfo->extBankNameVn ?? $bankConfig['name'],
                'currency' => $response->creditorInfo->currency,
            ];

            if ($raw) return $bankInfo;

            return $this->ok($bankInfo);
        } catch (\Throwable $th) {
            if ($raw) return false;

            Log::info($th);
            $response = json_decode($th->getMessage());

            return $this->error(new Exception("Tài khoản ngân hàng không tồn tại"));
        }
    }

    public function buyFundCertificate($transaction, $matchedDate = 1)
    {
        $userPackage = $transaction->userPackage;

        foreach ($userPackage->package->funds as $index => $fund) {
            $vendorConfig = Config('vendor');
            $accountId = $vendorConfig['account_id'];
            $percentage = $fund->pivot->allocation_percentage;
            $amount = $transaction->amount * $percentage / 100;

            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $fund->credential->token
                ],
                'body' => json_encode([
                    "action" => "BUY",
                    "status" => "BOOKING",
                    "transactionValue" => $amount,
                    "productCode" => $fund->code,
                    "autoPurchase" => 1,
                    "type" => [
                        "value" => "NORMAL",
                        "period" => "ST"
                    ],
                    "matchedDate" => date("Y-m-d", strtotime("$matchedDate days")),
                    "periodicOrder" => true,
                    "purchaserId" => $accountId,
                    "accountId" => $accountId,
                    "referralCode" => null
                ])
            ];


            $userAsset = UserAsset::firstOrCreate([
                'user_package_id' => $userPackage->id,
                'fund_id' => $fund->id,
            ]);

            try {
                // $tradeResponse = $this->post($tradeUrl, $options);
                $userAsset->fundTransactions()->save(new FundTransaction([
                    'amount' => $amount,
                    'status' => self::STATUS_NEW,
                    'type' => self::TYPE_BUY,
                    // 'ref' => $tradeResponse->id,
                    'transaction_id' => $transaction->id,
                    'purchaser' => $transaction->purchaser

                ]));
            } catch (\Throwable $th) {
                Log::error($th);
                $userAsset->fundTransactions()->save(new FundTransaction([
                    'amount' => $amount,
                    'status' => self::STATUS_FAILURE,
                    'type' => self::TYPE_BUY,
                    'ref' => '',
                    'transaction_id' => $transaction->id,
                    'purchaser' => $transaction->purchaser
                ]));
                throw $th;
            }
        }
    }

    public function cancelFundTransaction($orderList, $accessToken)
    {
        $vendorConfig = Config('vendor');
        $tradeCancelUrl = $vendorConfig['trade_cancel_url'];

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken
            ],
            'body' => json_encode([
                'orderList' => $orderList
            ])
        ];

        $this->put($tradeCancelUrl, $options);
    }

    public function transFundCertificate($fundCode, $amount, $matchedDate = 0, $action = "BUY")
    {
        try {
            logger('1212');
            $fund = Fund::where('code', '=', $fundCode)->first();
            
            $vendorConfig = Config('vendor');
            $tradeUrl = $vendorConfig['trade_url'];
            $accountId = $vendorConfig['account_id'];

            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $fund->credential->token
                ],
                'body' => json_encode([
                    "accountId" => $accountId,
                    "action" => $action,
                    "matchedDate" => date("Y-m-d", strtotime("$matchedDate days")),
                    "productCode" => $fund->code,
                    "purchaserId" => $accountId,
                    "referralCode"=> "",
                    "status"=> "BOOKING",
                    "transactionValue" => $amount,
                    "type" => [
                        "value" => "NORMAL",
                        "period" => ""
                    ],
                ])
            ];
            logger($options);
            $tradeResponse = $this->post($tradeUrl, $options);
   
            return $tradeResponse->id;
        } catch (\Throwable $th) {
            if ($matchedDate < 6) {
                return $this->transFundCertificate($fundCode, $amount,  $matchedDate + 1, $action);
            } else {
                Log::error($matchedDate);
                throw $th;
            }
        }
    }
}
