<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'amount',
        'user_package_id',
        'fund_id'
    ];

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }

    public function package()
    {
        return $this->belongsTo(UserPackage::class);
    }

    public function fundTransactions(): HasMany
    {
        return $this->hasMany(FundTransaction::class);
    }
}
