<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, TwoFactorAuthenticatable, SoftDeletes;

    public $timestamps = true;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'identity_number',
        'dob',
        'gender',
        'avatar',
        // 'phone_number',
        // 'otp',
        'email_verified_at',
        'created_at',
        'updated_at',
        'is_verify',
        'role',
        'identity_image_front',
        'identity_image_front_hash',
        'identity_image_back',
        'identity_image_back_hash',
        'issue_place',
        'issue_date',
        'valid_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'otp',
        'password',
        'remember_token',
        'identity_image_front_hash',
        'identity_image_back_hash',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'user_packages')->withPivot('investment_amount',  'avatar');
    }

    public function userPackages(): HasMany
    {
        return $this->hasMany(UserPackage::class, 'user_id');
    }
}
