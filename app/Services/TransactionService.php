<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\UserPackage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TransactionService extends BaseService
{
    public function create($userId, $packageId, $amount, $type = BankService::TYPE_BUY)
    {
        try {
            $userId = Auth::user()->id;
            $userPackage = UserPackage::where([
                'user_id' => $userId,
                'package_id' => $packageId
            ])->firstOrFail();

            $time = Carbon::now()->timestamp;
            $transactionId = getRandomString(16, $time . $userId);

            $transaction = Transaction::create([
                'id' =>  $transactionId,
                'status' => BankService::STATUS_NEW,
                'amount' => $amount,
                'purchaser' => $userId,
                'type' => $type
            ]);

            $userPackage->transactions()->save($transaction);

            if( $type === BankService::TYPE_WITHDRAW) {
                return $transaction;
            }

            return $transactionId;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
