<?php

namespace App\Console\Commands;

use App\Models\FundTransaction;
use App\Services\BankService;
use Illuminate\Console\Command;
use App\Console\Commands\Log;

class DailyBuy extends Command
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
    protected $signature = 'buy:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily buy';

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
            'fund_transactions.type' => BankService::TYPE_BUY,
            'fund_transactions.status' => BankService::STATUS_NEW,
            'fund_transactions.ref' => null,  
            ])
            ->whereTime('fund_transactions.created_at', '>', date("Y-m-d H:i:s", strtotime("yesterday +1 Hours")))
            ->whereTime('fund_transactions.created_at', '<=', date("Y-m-d H:i:s", strtotime("today +21 Hours")))
            ->groupBy('funds.id')
            ->get();
            logger($transactions);
        foreach ($transactions as $key => $value) {
            if($value->sum_amount >= 10000 ){
                logger("bvbvbvbvbv");
                $ref = $this->bank->transFundCertificate($value->code, $value->sum_amount);
                FundTransaction
                    ::join('user_assets', 'user_assets.id', '=', 'fund_transactions.user_asset_id')
                    ->join('funds', 'user_assets.fund_id', '=', 'funds.id')
                    ->where([
                        'fund_transactions.type' => BankService::TYPE_BUY,
                        'fund_transactions.status' => BankService::STATUS_NEW,
                        'funds.code' => $value->code,
                 'fund_transactions.ref' => null,   ])
                    ->whereTime('fund_transactions.created_at', '>', date("Y-m-d H:i:s", strtotime("yesterday +1 Hours")))
                    ->whereTime('fund_transactions.created_at', '<=', date("Y-m-d H:i:s", strtotime("today +21 Hours")))
                    ->update([
                        'fund_transactions.ref' => $ref
                    ]);
            }
          
        }
       

       
        return 0;
    }
}
