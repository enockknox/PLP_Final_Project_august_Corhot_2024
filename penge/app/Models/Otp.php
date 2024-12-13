<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HassFactory


     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id'
        'type',
        'code',
        'active',
    ];
}
