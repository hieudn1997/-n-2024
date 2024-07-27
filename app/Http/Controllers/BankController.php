<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\BankService;
use Illuminate\Http\Request;

class BankController extends Controller
{
    protected $bank;

    public function __construct(BankService $bank)
    {
        $this->bank = $bank;
    }

    public function getBankAccountInfo($bankId, $accountId)
    {
        return $this->bank->getBankAccountInfo($bankId, $accountId);
    }

    public function getBankAccountUsed(Request $request)
    {
        $transaction = Transaction::where([
            ['purchaser', '=', $request->user()->id],
            'type' => BankService::TYPE_WITHDRAW,
        ])->first();

        $bankAccount = null;

        if ($transaction?->bank_id && $transaction?->bank_account_id) {
            $bankAccount = $this->bank->getBankAccountInfo($transaction->bank_id, $transaction->bank_account_id, false, true);
        }

        return $this->bank->ok($bankAccount);
    }
}
