<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public $incrementing = false;

    public $timestamps = true;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'amount',
        'status',
        'type',
        'purchaser',
        'bank_id',
        'bank_account_id'
    ];

    public function userPackage()
    {
        return $this->belongsTo(UserPackage::class);
    }
}
