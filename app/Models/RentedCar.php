<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentedCar extends Model
{
    public $timestamps = false;
    public $table = 'rented_cars';
    protected $fillable = ['user_id', 'car_id', 'end_rent'];
}
