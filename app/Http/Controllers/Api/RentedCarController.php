<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CarResource;
use App\Http\Traits\Api\RentedCarTrait;
use App\Models\Car;
use App\Models\RentedCar;
use Illuminate\Http\Request;

class RentedCarController extends Controller
{
    use RentedCarTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = $this->getUser();
        $row = RentedCar::where('user_id', $user->id)->first();
        if ($row) {
            $carInfo = CarResource::collection(Car::where('id', $row->car_id)->get());
            return response()->json([
               'rented_id' => $row->id,
               'user' => $user->name,
               'end_rent' => $row->end_rent,
               'rentedCar' => $carInfo,
               'errors' => []
            ]);
        } else {
            return response()->json([
                'message' => 'no data info',
                'errors' => ['no any car rented']
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'time_rent' => ['required', 'integer'],
            'car_id' => ['required', 'integer'],
        ]);
        $rowIssetUser = RentedCar::where('user_id', $request->user()->id)->first();
        $rowIssetCar = RentedCar::where('car_id', $request->car_id)->first();
        $dateEndRent = $this->getEndRentInFormat((int)$request->time_rent);
        $switcherCase = ($rowIssetUser && $rowIssetCar) ? 11 : (($rowIssetUser || $rowIssetCar) ? 1 : 0);
        $result = [];
        switch ($switcherCase) {
            case 11:
                $result = [
                    'message' => 'rent fail',
                    'errors' => [ 'You have already rented this car' ]
                ];
                break;
            case 1:
                $result = [
                    'message' => 'rent fail',
                    'errors' => [ 'You have already rented another car or this car used' ]
                ];
                break;
            case 0:
                if ($rented = $this->saveNewRent($request, $dateEndRent)) {
                    $result = [
                        'rented_id' => $rented->id,
                        'message' => 'Car successfully rented',
                        'errors' => []
                    ];
                } else {
                    $result = [
                        'message' => 'rent fail',
                        'errors' => [ 'Problems with add new rent' ]
                    ];
                };
        }
        return response()->json($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RentedCar  $rentedCar
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RentedCar $rentedCar)
    {
        if ($this->isSameRow($rentedCar)) {
            if ( now() > $rentedCar->end_rent ) {
                $rentedCar->end_rent = $this->getEndRentInFormat((int)$request->time_rent);
                $rentedCar->save();
                return response()->json([
                    'message' => 'Time for rent succesfully updated',
                    'errors' => []
                ]);
            } else {
                return response()->json([
                    'message' => 'update error',
                    'errors' => ['Rental time has not ended yet']
                ]);
            }
        } else {
            return response()->json([
                'message' => 'update error',
                'errors' => ['You do not have permission to update this row']
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RentedCar  $rentedCar
     * @return \Illuminate\Http\Response
     */
    public function destroy(RentedCar $rentedCar)
    {
        if ($this->isSameRow($rentedCar)) {
            $rentedCar->delete();
            return response()->json([
                'message' => 'row succes deleted',
                'errors' => []
            ]);
        } else {
            return response()->json([
                'message' => 'delete error',
                'errors' => ['You do not have permission to delete this row']
            ]);
        }
    }
}
