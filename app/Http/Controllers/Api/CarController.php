<?php

namespace App\Http\Controllers\Api;

use App\Models\Car;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CarResource;

class CarController extends Controller
{
    public function index()
    {
        return CarResource::collection(Car::activeCars()->get());
    }
}
