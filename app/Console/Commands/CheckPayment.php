<?php

namespace App\Console\Commands;

use App\Events\SendPersonalNotification;
use App\Models\Notification;
use App\Models\Transaction;
use App\Services\BankService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckPayment extends Command
{
    private $bank;

    public function __construct(BankService $bank)
    {
        parent::__construct();
        $this->bank = $bank;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check payment status.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $checkNeededTime = Cache::get('payment-check-needed');
        echo "asdasdas";
        Log::info($checkNeededTime);

        $now = now()->toString();
        $matches = array();

        $transactionList = [];
        $transactionData = [];

        // if ($checkNeededTime === $now || date('i') % 10 === 0) {
        if (true) {
            $bankHistory = $this->bank->getTransactionHistory();

            foreach ($bankHistory as $key => $tran) {
                preg_match("/[a-zA-Z0-9]{16}/", $tran->description, $matches);
                $ref = $matches[0] ?? null;

                if ($ref) {
                    array_push($transactionList, $ref);
                    $transactionData[$ref] = $tran->amount;
                }
            }

            $transactions = Transaction::whereIn('id', $transactionList)->where(
                [
                    'status' => $this->bank::STATUS_NEW,
                    'type' => $this->bank::TYPE_BUY,
                ]
            )->get();

            foreach ($transactions as $transaction) {

                $this->bank->buyFundCertificate($transaction);

                $transaction->status = BankService::STATUS_PAID;
                $transaction->save();
                $msg = 'Bạn đã nạp thành công số tiền ' . $transaction->amount;
                $related_url = '/transactions/' . $transaction->id;

                Notification::create([
                    'user_id' => $transaction->purchaser,
                    'message' => $msg,
                    'related_url' => $related_url,
                    'status' => Notification::STATUS_UNREAD
                ]);

                broadcast(new SendPersonalNotification($transaction->purchaser, $msg, $related_url));

                Log::info($transaction->id . '::::' . $transaction->amount . '::::' . 'PAID');
            }
        }

        $expiredTransactions = Transaction::where('status', '=', $this->bank::STATUS_NEW)->whereTime('created_at', '<=', now()->subMinutes(15))->get();

        foreach ($expiredTransactions as $key => $transaction) {
            $msg = 'Giao dịch của bạn đã bị hủy do chưa thanh toán';
            $related_url = '/transactions/' . $transaction->id;

            Notification::create([
                'user_id' => $transaction->purchaser,
                'message' => $msg,
                'related_url' => $related_url,
                'status' => Notification::STATUS_UNREAD
            ]);

            broadcast(new SendPersonalNotification($transaction->purchaser, $msg, $related_url));

            Log::info($transaction->id . '::::' . $transaction->amount . '::::' . 'CANCELED');
        }

        if (sizeof($expiredTransactions) > 0) {
            Transaction::where('status', '=', $this->bank::STATUS_NEW)->whereTime('created_at', '<=', now()->subMinutes(15))->update([
                'status' => BankService::STATUS_CANCEL
            ]);
        }
    }
}
