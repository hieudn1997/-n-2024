<?php

namespace App\Services;

use App\Http\Resources\FundCollection;
use Exception;
use App\Http\Resources\PackageCollection;
use App\Http\Resources\PackageResource;
use App\Models\FundTransaction;
use App\Models\Package;
use App\Models\UserAsset;
use App\Repositories\PackageRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PackageService extends BaseService
{
    protected $package;
    protected $fund;
    protected $store;

    public function __construct(PackageRepository $package, FundService $fund, FirebaseService $store)
    {
        $this->store = $store;
        $this->fund = $fund;
        $this->package = $package;
    }

    public function index()
    {
        try {
            $packages = $this->package->index();

            return $this->ok($packages);
        } catch (Exception $e) {
            return $this->error($e);
        }
    }

    public function create($data)
    {
        DB::beginTransaction();
        try {
            $package = $this->package->store(['name' => $data->name]);

            if (!$package) {
                throw new Exception('An error has occurred');
            }

            $package->owners()->attach([
                Auth::id() => [
                    'investment_amount' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);

            $funds = [];

            foreach ($data->allocation as $fund) {
                $funds += [
                    $fund['fund_id'] => [
                        'allocation_percentage' => $fund['percentage'],
                        'created_at' => now(),
                        'updated_at' => now()

                    ]
                ];
            }

            $package->funds()->attach($funds);

            DB::commit();

            return $this->ok("Created", self::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error($e);
        }
    }

    public function show($id)
    {
        try {
            $package = $this->package->show($id);

            return $this->ok($package);
        } catch (Exception $e) {
            return $this->error($e, self::HTTP_NOT_FOUND, 'The package ID does not exist');
        }
    }

    public function clone($id)
    {
        try {
            $package = Package::findOrFail($id);

            if ($package->owners()->wherePivot('user_id', '=', Auth::id())
                ->wherePivot('deleted_at', null)->first()
            ) {
                return $this->error(new Exception('You have already cloned this package.'), self::HTTP_FORBIDDEN);
            }

            $package->owners()->attach([
                Auth::id() => [
                    'investment_amount' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);

            return $this->ok("Created", self::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->error($e, self::HTTP_INTERNAL_SERVER_ERROR, 'An error has occurred');
        }
    }

    public function update($data, $id)
    {
        try {
            $package = Package::join('user_packages', 'packages.id', '=', 'user_packages.package_id')
                ->where([
                    'user_packages.id' => $id,
                    'user_id' => Auth::id()
                ])->firstOrFail();

            DB::beginTransaction();
            try {
                $package = $this->package->store(['name' => $data->name]);

                if (!$package) {
                    throw new Exception('An error has occurred');
                }

                $funds = [];

                foreach ($data->allocation as $fund) {
                    $funds += [
                        $fund['fund_id'] => [
                            'allocation_percentage' => $fund['percentage'],
                            'created_at' => now(),
                            'updated_at' => now()

                        ]
                    ];
                }

                $package->funds()->attach($funds);

                DB::table('user_packages')
                    ->where('id', $id)
                    ->update(['package_id' => $package->id]);

                DB::commit();

                return $this->ok("Created", self::HTTP_CREATED);
            } catch (Exception $e) {
                DB::rollBack();

                return $this->error($e, self::HTTP_INTERNAL_SERVER_ERROR, 'An error has occurred');
            }
        } catch (Exception $e) {
            return $this->error($e, self::HTTP_NOT_FOUND, 'The package ID is invalid');
        }
    }

    public function destroy($id)
    {
        try {
            $package = Package::find($id);
            $package->owners()->wherePivot('user_id', '=', Auth::id())->detach();

            return $this->ok(null, self::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return $this->error($e, self::HTTP_INTERNAL_SERVER_ERROR, 'An error has occurred');
        }
    }

    public function changeAvatar($userPackageId, $avatar)
    {
        try {
            $userPackage = DB::table('user_packages')->where(['user_id' => Auth::id(), 'id' => $userPackageId])->first();

            if (!$userPackage) {
                throw new Exception('Permission denied', self::HTTP_FORBIDDEN);
            }

            $filePath = Config('app.asset_url') . $this->store->upload($avatar);

            $isSuccess = DB::table('user_packages')->where(['user_id' => Auth::id(), 'id' => $userPackageId])->update(['avatar' => $filePath]);

            if ($isSuccess) {
                return $this->ok("Success");
            }

            throw new Exception('An error has occurred', self::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            return $this->error($e, self::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDefaultPackages()
    {
        try {
            $packages = Package::default()->orderBy('id')->get();

            return $this->ok(new PackageCollection($packages));
        } catch (Exception $e) {
            return $this->error($e);
        }
    }

    /**
     * @return PackageResource
     */
    public function getPackageDetail($id)
    {
        try {
            $package = Package::findOrFail($id);

            $user = $package->owners()
                ->wherePivot('user_id', '=', Auth::id())
                ->firstOrFail();

            $package->owner = $user?->pivot;

            $userPackage = $package->userPackages()
                ->where('user_id', '=', Auth::id())
                ->firstOrFail();

            $trans = FundTransaction::join('user_assets', 'fund_transactions.user_asset_id', '=', 'user_assets.id')
                ->where([
                    'fund_transactions.purchaser' => Auth::id(),
                    'user_assets.user_package_id' => $userPackage->id
                ])
                ->select('fund_transactions.*')
                ->get();

            $balance = 0;
            $profit = 0;
            $investmentAmount = 0;

            foreach ($userPackage->transactions as $key => $transaction) {
                if ($transaction->status === BankService::STATUS_PAID && $transaction->type === BankService::TYPE_BUY) {
                    $investmentAmount += $transaction->amount;
                }
            }

            $profit -= $investmentAmount;

            foreach ($trans as $key => $transaction) {
                if ($transaction->status === BankService::STATUS_NEW && $transaction->type === BankService::TYPE_BUY) {
                    $balance += $transaction->amount;
                    $profit += $transaction->amount;
                }

                if ($transaction->status === BankService::STATUS_SOLD && $transaction->type === BankService::TYPE_SELL) {
                    $profit += $transaction->volume * $transaction->price;
                }
            }

            $assets = UserAsset::with(['fund', 'fundTransactions'])->where('user_package_id', '=', $userPackage->id)->get();

            foreach ($assets as $key => $asset) {
                $amount = $asset->amount * $asset->fund->current_value;

                $balance += $amount;

                $profit += $amount;
            }

            return $this->ok([
                'id' => $package->id,
                'avatar' => $package->owner?->avatar ?? Config('package.default_avatar'),
                'is_default' => $package->is_default ?? false,
                'name' => $package->name,
                'allocation' => $package->funds ? new FundCollection($package->funds) : null,
                // 'investment_amount' => $package->owner?->investment_amount ?? "0.000",
                'profit' => $profit,
                'investment_amount' =>  $investmentAmount,
                'balance' => $balance,
                'transactions' => $trans
            ]);
        } catch (Exception $e) {
            try {
                $package = Package::findOrFail($id);
                $user = $package->owners()
                    ->wherePivot('user_id', '=', Auth::id())
                    ->first();
                $package->owner = $user?->pivot;
                return $this->ok(new PackageResource($package));
            } catch (\Throwable $th) {
                return $this->error($e, self::HTTP_INTERNAL_SERVER_ERROR, 'An error has occurred');
            }
        }
    }

    public function getHistory($id, $month)
    {
        try {
            $package = Package::findOrFail($id);
            $allocation = $package->funds;
            $maxLength = 0;
            foreach ($allocation as $fund) {
                $fund->historical_data = $this->fund->getHistory($fund->id, $this->fund->getPeriod($month), true);
                $maxLength = max($maxLength, sizeof($fund->historical_data ?? []));
            }
            $historicalData = [];

            for ($i = $maxLength; $i > 0; $i--) {
                $data = [
                    'navCurrent' => 0,
                    'fundNumber' => 0,
                ];
                foreach ($allocation as $fund) {
                    $index = sizeof($fund->historical_data) - $i;
                    if (isset($fund->historical_data[$index])) {
                        $data['matchedDate'] = $fund->historical_data[$index]->matchedDate;
                        $data['navCurrent'] += $fund->historical_data[$index]->navCurrent * $fund->pivot->allocation_percentage / 100;
                        $data['fundNumber']++;
                    }
                }
                array_push($historicalData, $data);
            }

            return $this->ok($historicalData);
        } catch (Exception $e) {
            return $this->error($e, self::HTTP_INTERNAL_SERVER_ERROR, 'An error has occurred');
        }
    }

    public function getCustomizedPackages()
    {
        try {
            $packages = Package::join(
                'user_packages',
                'user_packages.package_id',
                '=',
                'packages.id'
            )
                ->select('*', 'packages.id as id')
                ->where('user_packages.user_id', Auth::id())
                ->where('user_packages.deleted_at', null)
                ->orderBy('user_packages.id')
                ->get();

            return $this->ok(new PackageCollection($packages));
        } catch (Exception $e) {
            return $this->error($e);
        }
    }
}
