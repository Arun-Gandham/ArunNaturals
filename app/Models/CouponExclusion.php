<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponExclusion extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id',
        'customer_phone',
        'customer_email',
        'customer_name',
    ];
}

