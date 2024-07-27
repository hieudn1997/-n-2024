<?php

namespace App\Console\Commands;

use App\Events\SendPersonalNotification;
use App\Services\BankService;
use App\Services\FundService;
use Illuminate\Console\Command;

class DailyPrice extends Command
{
    private $fund;
    private $bank;

    public function __construct(FundService $fund, BankService $bank)
    {
        parent::__construct();
        $this->fund = $fund;
        $this->bank = $bank;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'price:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The fund credential price is updated daily.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->fund->updateDailyPrice();
    }
}
