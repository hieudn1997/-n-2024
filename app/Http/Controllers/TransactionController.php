<?php

namespace App\Http\Controllers;

use App\Events\SendPersonalNotification;
use App\Http\Requests\CreateTransactionRequest;
use App\Http\Requests\WithdrawRequest;
use App\Http\Resources\TransactionCollection;
use App\Models\FundTransaction;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\UserAsset;
use App\Models\UserPackage;
use App\Services\BankService;
use App\Services\TransactionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    protected $bank;
    protected $transaction;

    public function __construct(BankService $bank, TransactionService $transaction)
    {
        $this->bank = $bank;
        $this->transaction = $transaction;
    }

    /**
     * @QAparam page nullable [0-9]+
     * @QAparam per_page nullable [0-9]+
     * @QAparam order_by string nullable 'id', 'amount', 'status', 'user_package_id', 'created_at', 'updated_at', 'type', 'purchaser'
     * @QAparam sort_by string nullable 'desc'|'asc'
     * @QAparam ref string nullable
     * @QAparam purchaser_name string nullable
     * @QAparam purchaser_id string nullable
     */
    public function get(Request $request)
    {
        $perPage = 15;
        $orderBy = 'transactions.created_at'; // $fields
        $sortBy = 'desc'; // $orders
        $orders = ['desc', 'asc'];
        $fields = ['id', 'amount', 'status', 'user_package_id', 'created_at', 'updated_at', 'type', 'purchaser'];

        if ($request->has('per_page') && is_numeric($request->input('per_page'))) {
            $perPage = $request->input('per_page');
        }

        if ($request->has('order_by') && in_array($request->input('order_by'), $fields)) {
            $orderBy = 'transactions' . $request->input('order_by');
        }

        if ($request->has('sort_by') && in_array($request->input('sort_by'), $orders)) {
            $sortBy = $request->input('sort_by');
        }

        $query = Transaction
            ::select(['transactions.*', 'users.name as purchaser_name'])
            ->join('user_packages', 'user_packages.id', '=', 'transactions.user_package_id')
            ->join('users', 'user_packages.user_id', '=', 'users.id')
            ->orderBy($orderBy, $sortBy);

        if ($request->has('purchaser_name') && $request->input('purchaser_name')) {
            $query = $query->whereRaw("users.name ILIKE '%" . $request->input('purchaser_name') . "%' ");
        }

        if ($request->has('ref') && $request->input('ref')) {
            $query = $query->whereRaw("transactions.id ILIKE '%" . $request->input('ref') . "%' ");
        }

        if ($request->has('purchaser_id') && $request->input('purchaser_id')) {
            $query = $query->whereRaw("transactions.purchaser = '" . $request->input('purchaser_id') . "' ");
        }

        $data = $query->paginate($perPage);

        return response()->json($data);
    }

    /**
     * @QAparam page nullable [0-9]+
     * @QAparam per_page nullable [0-9]+
     * @QAparam order_by string nullable "id", "created_at", "updated_at", "ref", "amount", "status", "type", "user_asset_id", "purchaser", "transaction_id", "volume", "price",
     * @QAparam sort_by string nullable 'desc'|'asc'
     * @QAparam fund_name string nullable
     * @QAparam fund_code string nullable
     * @QAparam purchaser_name string nullable
     * @QAparam package_name string nullable
     */
    public function getFundTransactions(Request $request)
    {
        $perPage = 15;
        $orderBy = 'fund_transactions.created_at'; // $fields
        $sortBy = 'desc'; // $orders
        $orders = ['desc', 'asc'];
        $fields = [
            "id",
            "created_at",
            "updated_at",
            "ref",
            "amount",
            "status",
            "type",
            "user_asset_id",
            "purchaser",
            "transaction_id",
            "volume",
            "price",
        ];

        if ($request->has('per_page') && is_numeric($request->input('per_page'))) {
            $perPage = $request->input('per_page');
        }

        if ($request->has('order_by') && in_array($request->input('order_by'), $fields)) {
            $orderBy = 'fund_transactions' . $request->input('order_by');
        }

        if ($request->has('sort_by') && in_array($request->input('sort_by'), $orders)) {
            $sortBy = $request->input('sort_by');
        }

        $query = FundTransaction
            ::select([
                'fund_transactions.*',
                'packages.name as package_name',
                'funds.name as fund_name',
                'funds.code as fund_code',
                'users.name as purchaser_name'
            ])
            ->join('user_assets', 'user_assets.id', '=', 'fund_transactions.user_asset_id')
            ->join('user_packages', 'user_assets.user_package_id', '=', 'user_packages.id')
            ->join('funds', 'user_assets.fund_id', '=', 'funds.id')
            ->join('packages', 'user_packages.package_id', '=', 'packages.id')
            ->join('users', 'user_packages.user_id', '=', 'users.id')
            ->orderBy($orderBy, $sortBy);

        if ($request->has('purchaser_name') && $request->input('purchaser_name')) {
            $query = $query->whereRaw("users.name ILIKE '%" . $request->input('purchaser_name') . "%' ");
        }

        if ($request->has('fund_name') && $request->input('fund_name')) {
            $query = $query->whereRaw("funds.name ILIKE '%" . $request->input('fund_name') . "%' ");
        }

        if ($request->has('fund_code') && $request->input('fund_code')) {
            $query = $query->whereRaw("funds.code ILIKE '%" . $request->input('fund_code') . "%' ");
        }

        if ($request->has('package_name') && $request->input('package_name')) {
            $query = $query->whereRaw("packages.name ILIKE '%" . $request->input('package_name') . "%' ");
        }

        $data = $query->paginate($perPage);

        return response()->json($data);
    }

    public function index()
    {
        $transactions = Transaction::where('purchaser', '=', Auth::id())->get();

        return $this->bank->ok(new TransactionCollection($transactions));
    }

    public function show($id)
    {
        $transaction = Transaction::find($id);

        $transactions = FundTransaction::where('transaction_id', '=', $id)->get();

        $transaction->detail = $transactions;

        return $this->bank->ok($transaction);
    }

    public function checkPayment($transactionId)
    {
        return $this->bank->checkPayment($transactionId);
    }

    public function createTransaction($packageId, CreateTransactionRequest $request)
    {
        $ref = $this->transaction->create(Auth::id(), $packageId, $request->amount);

        if (!$ref) {
            return $this->package->error(new Exception('Create transaction failed'));
        }

        Cache::put('payment-check-needed', now()->addMinutes(1)->toString());

        $bankInfo = $this->bank->getBankInfo();

        $msg = 'Vui lòng thanh toán số tiền ' . number_format($request->amount) . ' để tiến hành mua CCQ. Giao dịch sẽ tự động hủy sau 15 phút nếu không thanh toán.';
        $related_url = '/transactions/' . $ref;


        Notification::create([
            'user_id' => Auth::id(),
            'message' => $msg,
            'related_url' => $related_url,
            'status' => Notification::STATUS_UNREAD
        ]);

        broadcast(new SendPersonalNotification(Auth::id(), $msg, $related_url));

        return $this->transaction->ok(
            array_merge(
                $bankInfo,
                [
                    'reference_number' => $ref,
                    'transfer_amount' => number_format($request->amount),
                    'qr' => "https://img.vietqr.io/image/{$bankInfo['bank_code']}-{$bankInfo['account_number']}-qr_only.jpg?amount={$request->amount}&addInfo={$ref}"
                ]
            )
        );
    }

    public function withdraw(WithdrawRequest $request)
    {   
        try {
           
            DB::beginTransaction();

            $request->session()->remove('auth.password_confirmed_at');
            $userId = Auth::id();
            $userPackage = UserPackage::where([
                'user_id' => $userId,
                'package_id' => $request->id
                ])->firstOrFail();
                $isValid = $this->bank->getBankAccountInfo($request->bank_id, $request->bank_account_id, true);

            if ($isValid !== true) {
                return $isValid;
            }

            $balance = 0;

            $transaction = $this->transaction->create($userId, $request->id, $request->amount, BankService::TYPE_WITHDRAW);

            $transaction->bank_id = $request->bank_id;
            $transaction->bank_account_id = $request->bank_account_id;

            $transaction->save();

            $transactions = [];

            foreach ($userPackage->package->funds as $index => $fund) {
                $percentage = $fund->pivot->allocation_percentage;
                $amount = $request->amount * $percentage / 100;
                $userAsset = UserAsset::firstOrCreate([
                    'user_package_id' => $userPackage->id,
                    'fund_id' => $fund->id,
                ]);

                $bookingAmount = Transaction::where(
                    [
                        'status' => BankService::STATUS_NEW,
                        'type' => BankService::TYPE_WITHDRAW,
                        'purchaser' => $userId,
                        ['id', '<>', $transaction->id]
                    ]
                )->sum('amount');
                $balance += $userAsset->amount * $userAsset->fund->current_value;

                $newFundTransaction = [
                    'amount' => $amount,
                    'status' => BankService::STATUS_NEW,
                    'type' => BankService::TYPE_SELL,
                    'transaction_id' => $transaction->id,
                    'purchaser' => $userId
                ];

                try {
                    $userAsset->fundTransactions()->save(new FundTransaction([
                        'amount' => $amount,
                        'status' => BankService::STATUS_NEW,
                        'type' => BankService::TYPE_SELL,
                        'transaction_id' => $transaction->id,
                        'purchaser' => $userId
                    ]));

                    array_push($transactions, $newFundTransaction);
                } catch (\Throwable $th) {
                    Log::error($th);
                }
            }

            $packageName = $userPackage->package->name;
            $msg = "Đặt lệnh bán thành công $request->amount VNĐ tử gói $packageName";
            $related_url = '/transactions/';

            Notification::create([
                'user_id' => Auth::id(),
                'message' => $msg,
                'related_url' => $related_url,
                'status' => Notification::STATUS_UNREAD
            ]);

            if ($balance - $bookingAmount < $request->amount) {
                
                DB::rollBack();

                return $this->transaction->error(new Exception("Không được rút số tiền lớn hơn " . $balance - $bookingAmount . " VNĐ"));
            } else {
                DB::commit();
                broadcast(new SendPersonalNotification(Auth::id(), $msg, $related_url));
                $transaction->transactions = $transactions;

                return $this->transaction->ok($transaction);
            }
        } catch (\Throwable $th) {
            return $this->transaction->error($th);
        }
    }
}
