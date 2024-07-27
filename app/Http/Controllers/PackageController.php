<?php

namespace App\Http\Controllers;

use App\Events\SendPersonalNotification;
use App\Http\Requests\ChangePackageAvatarRequest;
use App\Http\Requests\CreatePackageRequest;
use App\Http\Requests\CreateTransactionRequest;
use App\Http\Requests\PackageIdNeededRequest;
use App\Models\Notification;
use App\Models\Package;
use App\Services\BankService;
use App\Services\PackageService;
use App\Services\TransactionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PackageController extends Controller
{
    protected $package;
    protected $transaction;
    protected $bank;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PackageService $package, TransactionService $transaction, BankService $bank)
    {
        $this->package = $package;
        $this->transaction = $transaction;
        $this->bank = $bank;
    }

    /**
     * @QAparam page nullable [0-9]+
     * @QAparam per_page nullable [0-9]+
     * @QAparam order_by string nullable "id", "created_at", "updated_at", "name", "is_default"
     * @QAparam sort_by string nullable 'desc'|'asc'
     * @QAparam fund_code string nullable
     * @QAparam fund_name string nullable
     * @QAparam package_name string nullable
     * @QAparam owner_id number nullable
     */
    public function getAllPackages(Request $request)
    {
        $perPage = 15;
        $orderBy = 'created_at'; // $fields
        $sortBy = 'desc'; // $orders
        $orders = ['desc', 'asc'];
        $fields = ["id", "created_at", "updated_at", "name", "is_default"];

        if ($request->has('per_page') && is_numeric($request->input('per_page'))) {
            $perPage = $request->input('per_page');
        }

        if ($request->has('order_by') && in_array($request->input('order_by'), $fields)) {
            $orderBy = 'fund_transactions' . $request->input('order_by');
        }

        if ($request->has('sort_by') && in_array($request->input('sort_by'), $orders)) {
            $sortBy = $request->input('sort_by');
        }

        $query = Package::with('funds')->with('userPackages')->orderBy($orderBy, $sortBy);

        if ($request->has('package_name') && $request->input('package_name')) {
            $query = $query->whereRaw("name ILIKE '%" . $request->input('package_name') . "%' ");
        }

        if ($request->has('fund_name') && $request->input('fund_name')) {
            $query = $query->whereHas('funds', function ($q) use ($request) {
                $q->whereRaw("name ILIKE '%" . $request->input('fund_name') . "%' ");
            });
        }

        if ($request->has('fund_code') && $request->input('fund_code')) {
            $query = $query->whereHas('funds', function ($q) use ($request) {
                $q->whereRaw("code ILIKE '%" . $request->input('fund_code') . "%' ");
            });
        }

        if ($request->has('owner_id') && $request->input('owner_id')) {
            $query = $query->whereHas('userPackages', function ($q) use ($request) {
                $q->whereRaw("user_id = '" . $request->input('owner_id') . "' ");
            });
        }


        return $query->paginate($perPage);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDefaultPackages()
    {
        return $this->package->getDefaultPackages();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCustomizedPackages()
    {
        return $this->package->getCustomizedPackages();
    }

    public function getPackageDetail(PackageIdNeededRequest $request)
    {
        return $this->package->getPackageDetail($request->id);
    }

    /**
     * @QAparam month integer nullable
     */
    public function getHistory(PackageIdNeededRequest $request)
    {
        return $this->package->getHistory($request->id, $request->month);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function clone(PackageIdNeededRequest $request)
    {
        return $this->package->clone($request->id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(CreatePackageRequest $request)
    {
        return $this->package->create($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->package->show($id);
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

    public function changeAvatar(ChangePackageAvatarRequest $request)
    {
        return $this->package->changeAvatar($request->id, $request->avatar);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CreatePackageRequest $request, $id)
    {
        return $this->package->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(PackageIdNeededRequest $request)
    {
        return $this->package->destroy($request->id);
    }
}
