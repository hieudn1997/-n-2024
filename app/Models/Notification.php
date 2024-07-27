<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    const STATUS_UNREAD = 0;
    const STATUS_READ = 1;

    public $timestamps = true;

    public $fillable = [
        'user_id',
        'message',
        'related_url',
        'status'
    ];
}
