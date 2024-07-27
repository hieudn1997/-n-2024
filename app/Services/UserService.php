<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\FundTransaction;
use App\Models\User;
use App\Models\UserAsset;
use App\Models\UserPackage;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserService extends BaseService
{
    protected $store;
    protected $vendor;


    public function __construct(FirebaseService $store, VendorService $vendor)
    {
        $this->store = $store;
        $this->vendor = $vendor;
    }

    public function changeAvatar($image)
    {
        try {
            $filePath = Config('app.asset_url') . $this->store->upload($image);
            $user = User::find(Auth::id());
            $user->avatar = $filePath;
            $user->save();

            return $this->ok('Success');
        } catch (Exception $e) {
            return $this->error($e);
        }
    }

    public function getAssetInfo()
    {
        $userPackages = UserPackage::with(['package', 'transactions'])->where('user_id', '=', Auth::id())->get();
        $userPackageIds = [];

        try {
            $userAssets = [];

            $total = 0;

            $balance = 0;

            $profit = 0;

            $started_at = false;

            foreach ($userPackages as $key => $userPackage) {
                array_push($userPackageIds, $userPackage->id);

                $investmentAmount = 0;
                //đã thanh toán
                foreach ($userPackage->transactions as $key => $transaction) {
                    if (
                        $transaction->status === BankService::STATUS_PAID
                    ) {
                        $investmentAmount += $transaction->amount;
                    }
                }

                $userPackage->investment_amount = $investmentAmount;
                $userPackage->profit = -$investmentAmount;
                $userPackage->balance = 0;

                $total += $investmentAmount;
                $profit -= $investmentAmount;

                $userAssets[$userPackage->id] = $userPackage;
            }

            $assets = UserAsset::with(['fund', 'fundTransactions'])->whereIn('user_package_id', $userPackageIds)->get();


            foreach ($assets as $key => $asset) {
                $amount = $asset->amount * $asset->fund->current_value;

                $balance += $amount;

                $userAssets[$asset->user_package_id]->balance += $amount;
                $userAssets[$asset->user_package_id]->profit += $amount;
                $profit += $amount;
            }

            $trans = FundTransaction::with('userAsset')->where('purchaser', '=', Auth::id())->orderBy('created_at')->get();

            foreach ($trans as $key => $transaction) {
                if (!$started_at) {
                    $started_at = $transaction->created_at;
                }
                // chờ thanh toán
                if ($transaction->status === BankService::STATUS_NEW && $transaction->type === BankService::TYPE_BUY) {
                    $balance += $transaction->amount;
                    $userAssets[$transaction->userAsset->user_package_id]->balance += $transaction->amount;
                    $userAssets[$transaction->userAsset->user_package_id]->profit += $transaction->amount;
                    $profit += $transaction->amount;
                }
                //bán
                if ($transaction->status === BankService::STATUS_SOLD && $transaction->type === BankService::TYPE_SELL) {
                    $amount = $transaction->volume * $transaction->price;
                    $userAssets[$transaction->userAsset->user_package_id]->profit += $amount;
                    $profit += $amount;
                }
            }

            $packages = [];
            foreach ($userAssets as $asset) {
                array_push($packages, $asset);
            }
Log::info('1aaaa212');
            return $this->ok([
                'total_invest' => $total,
                'started_at' => $started_at,
                'profit' => $profit,
                'balance' =>  $balance,
                'packages' =>  $packages
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function uploadEKycImage($file)
    {
        $fileName = $file->getClientOriginalName();

        $filePath = Config('app.asset_url') . $this->store->upload($file);

        $options = [
            'headers' => config('ekyc'),
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => file_get_contents($file),
                    'filename' => $fileName,
                    'headers'  => ['Content-Type' => '<Content-type header>']
                ],
                ['name' => 'title', 'contents' => $fileName],
                ['name' => 'description', 'contents' => $fileName]
            ]
        ];

        $res = $this->vendor->post(env('VNPT_EKYC_DOMAIN') . '/file-service/v1/addFile', $options);

        return [
            'path' => $filePath,
            'hash' => $res->object->hash
        ];
    }

    public function checkIdentityCardImage($user)
    {
        if($user->is_verify) {
            return $this->ok(new UserResource($user));
        }

        try {
            $time = (int) floor(microtime(true) * 1000);

            if ($user->identity_image_front && $user->identity_image_back) {
                $imageFront = $user->identity_image_front_hash;
                $imageBack = $user->identity_image_back_hash;
                $headers =  array_merge(config('ekyc'), [
                    'content-type' => 'Application/json',
                    'mac-address' => 'd8:5e:d3:d2:94:f5 brd ff:ff:ff:ff:ff:ff'
                ]);

                $options = [
                    'headers' => $headers,
                    'body' => json_encode(
                        [
                            "img_front" => $imageFront,
                            "img_back" => $imageBack,
                            "client_session" => "ANDROID_nokia7.2_28_Simulator_2.4.2_08d2d8686ee5fa0e_" . $time,
                            "type" => -1,
                            "validate_postcode" => true,
                            "token" => getRandomString(16, $imageFront)
                        ]
                    )
                ];

                $resFront = $this->vendor->post(env('VNPT_EKYC_DOMAIN') . '/ai/v1/ocr/id/front', $options);
                $resBack = $this->vendor->post(env('VNPT_EKYC_DOMAIN') . '/ai/v1/ocr/id/back', $options);

                $user->update([
                    "name" => $resFront->object?->name,
                    "address" => $resFront->object?->recent_location,
                    "identity_number" => $resFront->object?->id,
                    "dob" => formatDate($resFront->object?->birth_day),
                    "gender" => $resFront->object?->gender,
                    "valid_date" => $resFront->object?->valid_date,
                    "issue_place" => formatDate($resBack->object?->issue_place),
                    "issue_date" => formatDate($resBack->object?->issue_date),
                    "is_verify" => true
                ]);

                return $this->ok(new UserResource($user));
            }
        } catch (\Throwable $th) {
            return $this->error($th, self::HTTP_BAD_REQUEST, 'Invalid Image');
        }
    }
}
