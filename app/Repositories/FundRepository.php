<?php

namespace App\Repositories;

use App\Models\Fund;

class FundRepository
{
    protected $fund;

    public function __construct(Fund $fund)
    {
        $this->fund = $fund;
    }

    public function index()
    {
        $funds = $this->fund::all();
        return $funds;
    }

    public function store($data)
    {
        $fund = $this->fund::create($data);
        return $fund;
    }

    public function show($id)
    {
        $fund = $this->fund::findOrFail($id);
        return $fund;
    }

    public function update($data, $id)
    {
        $fund = $this->fund::findOrFail($id);
        $fund->fill($data);
        $fund->save();
        return $fund;
    }

    public function destroy($id)
    {
        $fund = $this->fund::findOrFail($id);
        $fund->delete();
        return $fund;
    }
}
