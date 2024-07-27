<?php

namespace App\Http\Controllers;

use App\Http\Resources\FundCollection;
use App\Models\Fund;
use App\Services\FundService;
use Illuminate\Http\Request;

class FundController extends Controller
{
    protected $fund;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FundService $fund)
    {
        // $this->middleware('auth:sanctum');
        $this->fund = $fund;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->fund->index();
    }

    /**
     * @QAparam page nullable [0-9]+
     * @QAparam per_page nullable [0-9]+
     * @QAparam order_by string nullable 'code', 'name', 'current_value', 'created_at', 'updated_at'
     * @QAparam sort_by string nullable 'desc'|'asc'
     * @QAparam name string nullable
     * @QAparam code string nullable
     */
    public function get(Request $request)
    {
        try {
            $perPage = 2;
            $orderBy = 'created_at'; // $fields
            $sortBy = 'desc'; // $orders
            $orders = ['desc', 'asc'];
            $fields = ['code', 'name', 'current_value', 'created_at', 'updated_at'];

            if ($request->has('per_page') && is_numeric($request->input('per_page'))) {
                $perPage = $request->input('per_page');
            }

            if ($request->has('order_by') && in_array($request->input('order_by'), $fields)) {
                $orderBy = $request->input('order_by');
            }

            if ($request->has('sort_by') && in_array($request->input('sort_by'), $orders)) {
                $sortBy = $request->input('sort_by');
            }

            $query = Fund::orderBy($orderBy, $sortBy);

            if ($request->has('name') && $request->input('name')) {
                $query = $query->whereRaw("name ILIKE '%" . $request->input('name') . "%' ");
            }

            if ($request->has('code') && $request->input('code')) {
                $query = $query->whereRaw("code ILIKE '%" . $request->input('code') . "%' ");
            }

            $data = $query->paginate($perPage);

            return response()->json($data);
        } catch (\Throwable $th) {
            return ($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->fund->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->fund->show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return $this->fund->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->fund->destroy($id);
    }

    /**
     * @QAparam month integer nullable
     */
    public function getHistory(Request $request)
    {
        $query = $this->fund->getPeriod($request->month);

        return $this->fund->getHistory($request->id, $query);
    }
}
