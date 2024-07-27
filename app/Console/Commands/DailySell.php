<?php

namespace App\Console\Commands;

use App\Models\FundTransaction;
use App\Services\BankService;
use Illuminate\Console\Command;

class DailySell extends Command
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
    protected $signature = 'sell:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily sell';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $transactions = FundTransaction
            ::join('user_assets', 'user_assets.id', '=', 'fund_transactions.user_asset_id')
            ->join('funds', 'user_assets.fund_id', '=', 'funds.id')
            ->selectRaw('funds.code, sum(fund_transactions.amount) as sum_amount')
            ->where([
                'fund_transactions.type' => BankService::TYPE_SELL,
                'fund_transactions.status' => BankService::STATUS_NEW,
            ])
            ->whereTime('fund_transactions.created_at', '>', date("Y-m-d H:i:s", strtotime("yesterday +1 Hours")))
            ->whereTime('fund_transactions.created_at', '<=', date("Y-m-d H:i:s", strtotime("today +21 Hours")))
            ->groupBy('funds.id')
            ->get();
                logger($transactions);
        foreach ($transactions as $key => $value) {
            $ref = $this->bank->transFundCertificate($value->code, $value->sum_amount, 1, "SELL");
            FundTransaction
                ::join('user_assets', 'user_assets.id', '=', 'fund_transactions.user_asset_id')
                ->join('funds', 'user_assets.fund_id', '=', 'funds.id')
                ->where([
                    'fund_transactions.type' => BankService::TYPE_SELL,
                    'fund_transactions.status' => BankService::STATUS_NEW,
                    'funds.code' => $value->code
                ])
                ->whereTime('fund_transactions.created_at', '>', date("Y-m-d H:i:s", strtotime("yesterday +1 Hours")))
                ->whereTime('fund_transactions.created_at', '<=', date("Y-m-d H:i:s", strtotime("today +21 Hours")))
                ->update([
                    'fund_transactions.ref' => $ref
                ]);
        }

        return 0;
    }
}
