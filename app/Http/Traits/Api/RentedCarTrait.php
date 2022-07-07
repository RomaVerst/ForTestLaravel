<?php

namespace App\Http\Traits\Api;

use App\Models\RentedCar;
use Carbon\Carbon;
use Illuminate\Http\Request;

trait RentedCarTrait
{
    public function getUser($param = '') {
        $user = auth()->user();
        if (!$user) return response()->json([
            'message' => 'fail',
            'errors' => ['not found user']
        ], 401);
        return $param === '' ? $user : $user[$param];
    }

    public function getEndRentInFormat($hours) {
        return Carbon::createFromTimestamp(time() + ($hours * 3600))->format('Y.m.d H:i:s');
    }

    public function saveNewRent(Request $request, $endRent) {
        $rentCar = new RentedCar();

        $rentCar->user_id = $request->user()->id;
        $rentCar->car_id = $request->car_id;
        $rentCar->end_rent = $endRent;
        $rentCar->save();
        return $rentCar;
    }

    public function isSameRow(RentedCar $rentedCar) {
        $userId = $this->getUser('id');
        return ($userId == $rentedCar->user_id) ? true : false;
    }
}