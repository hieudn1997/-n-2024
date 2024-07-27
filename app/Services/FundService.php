<?php

namespace App\Services;

use App\Http\Resources\FundCollection;
use App\Http\Resources\FundResource;
use App\Repositories\FundRepository;
use Exception;
use Illuminate\Support\Facades\Log;

class FundService extends BaseService
{
    protected $fund;
    protected $vendor;

    public function __construct(FundRepository $fund, VendorService $vendor)
    {
        $this->fund = $fund;
        $this->vendor = $vendor;
    }

    public function index()
    {
        try {
            $funds = $this->fund->index();

            return $this->ok(new FundCollection($funds));
        } catch (Exception $e) {
            return $this->error($e);
        }
    }

    public function store($data)
    {
        $fund = $this->fund->store($data);
        return $fund;
    }

    public function show($id)
    {
        try {
            $fund = $this->fund->show($id);

            return $this->ok(new FundResource($fund));
        } catch (Exception $e) {
            return $this->error($e, BaseService::HTTP_NOT_FOUND, 'The fund ID does not exist');
        }
    }

    public function update($data, $id)
    {
        $fund = $this->fund->update($data, $id);
        return $fund;
    }

    public function destroy($id)
    {
        $fund = $this->fund->destroy($id);
        return $fund;
    }

    /**
     * @param number $month
     * @return string
     */
    public function getPeriod($month)
    {
        if ($month >= 0) {
            return '&month=' . $month;
        }
        return '';
    }

    /**
     * @param integer $id
     * @param string $query
     * @param boolean $raw
     */
    public function getHistory($id, $query = '', $raw = false)
    {
        try {
            $fund = $this->fund->show($id);
            $credential = $fund->credential;
            $token = $this->vendor->getCredential($credential->id);
            $history = $this->vendor->get($fund->historical_data_url . $query, [
                'headers' => ['Authorization' => 'Bearer ' . $token],
            ]);

            if ($raw) {
                return $history->value;
            }

            return $this->ok($history->value);
        } catch (Exception $e) {
            return $this->error($e);
        }
    }

    public function updateDailyPrice()
    {
        $funds = $this->fund->index();

        foreach ($funds as $fund) {
            $history = $this->getHistory($fund->id, '&month=1', true);
            $lastPrice = array_pop($history)->navCurrent;
            
            $fund->current_value = $lastPrice;
            $fund->save();
        }
    }
}
