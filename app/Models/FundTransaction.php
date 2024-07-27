<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundTransaction extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'id',
        'amount',
        'status',
        'type',
        'ref',
        'purchaser',
        'transaction_id',
        'volume',
        'price'
    ];

    public function userAsset(): BelongsTo
    {
        return $this->belongsTo(UserAsset::class);
    }
}
