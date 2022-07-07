<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CarResource;
use App\Http\Traits\Api\RentedCarTrait;
use App\Models\Car;
use App\Models\RentedCar;
use Illuminate\Http\Request;

class RentedCarController extends Controller
{
    use RentedCarTrait;
    /**
     * @OA\Get(
     *     path="/rented-cars",
     *     operationId="rentedCarShow",
     *     tags={"Rented Car"},
     *     summary="Show rented car",
     *     @OA\Response(
     *         response="200",
     *         description="Rented car",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "rented_id": 1,
     *                 "user": "John",
     *                 "end_rent": "2099-01-01 00:00:00",
     *                 "rentedCar": {{
     *                      "car_id": 1,
     *                      "brand": "Brand",
     *                      "model": "Model",
     *                      "number": "G123GG123"
     *                  }},
     *                  "errors": {}
     *                },
     *               summary="Rented car."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "message": "Unauthenticated"
     *                },
     *               summary="Unauthenticated"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "message": "no data info",
     *                 "errors": {"no any car rented"}
     *                },
     *               summary="Not found."
     *             )
     *         )
     *     )
     * )
     *
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
            ], 404);
        }
    }

    /**
     *
     * @OA\Post(
     *     path="/rented-cars",
     *     summary="Add new rent",
     *     description="Add new rent",
     *     operationId="storeRentedCar",
     *     tags={"Rented Car"},
     *     @OA\Parameter(
     *         description="Time for rent",
     *         in="query",
     *         name="time_rent",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="An int value."),
     *     ),
     *     @OA\Parameter(
     *         description="Car id",
     *         in="query",
     *         name="car_id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="An int value."),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "rented_id":1,
     *                 "message": "Car successfully rented",
     *                 "errors": {}
     *                },
     *               summary="Success response."
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent,
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "message": "Unauthenticated"
     *                },
     *               summary="Unauthenticated"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=503,
     *         description="Service Unavailable",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "message": "Update time rent fail",
     *                 "errors": {"Problems with update time rent"}
     *                },
     *               summary="Service Unavailable."
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "message": "update error",
     *                 "errors": {"You do not have permission to update this row"}
     *                },
     *               summary="Forbidden."
     *             )
     *          )
     *     )
     * )
     *
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
        $car = new CarResource(Car::findOrFail($request->car_id));

        $rowIssetUser = RentedCar::where('user_id', $request->user()->id)->exists();
        $rowIssetCar = RentedCar::where('car_id', $car->id)->exists();

        $dateEndRent = $this->getEndRentInFormat((int)$request->time_rent);
        $switcherCase = ($rowIssetUser && $rowIssetCar) ? 11 : (($rowIssetUser || $rowIssetCar) ? 1 : 0);
        $result = [];
        $status = 200;
        switch ($switcherCase) {
            case 11:
                $result = [
                    'message' => 'rent fail',
                    'errors' => [ 'You have already rented this car' ]
                ];
                $status = 403;
                break;
            case 1:
                $result = [
                    'message' => 'rent fail',
                    'errors' => [ 'You have already rented another car or this car used' ]
                ];
                $status = 403;
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
                    $status = 503;
                };
        }
        return response()->json($result, $status);
    }

    /**
     * @OA\Put(
     *     path="/rented-cars/{id}",
     *     summary="Updates a rente time",
     *     description="Updates a rente time",
     *     operationId="updateRentedCar",
     *     tags={"Rented Car"},
     *     @OA\Parameter(
     *         description="Rented_id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="An int value."),
     *     ),
     *     @OA\Parameter(
     *         description="Time for rent",
     *         in="query",
     *         name="time_rent",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="An int value."),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "message": "Time for rent successfully updated",
     *                 "errors": {}
     *                },
     *               summary="Success response."
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent,
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "message": "Unauthenticated"
     *                },
     *               summary="Unauthenticated"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=503,
     *         description="Service Unavailable",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "message": "Update time rent fail",
     *                 "errors": {"Problems with update time rent"}
     *                },
     *               summary="Service Unavailable."
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "message": "update error",
     *                 "errors": {"You do not have permission to update this row"}
     *                },
     *               summary="Forbidden."
     *             )
     *          )
     *     )
     * )
     *
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
                return $rentedCar->save()
                    ?
                     response()->json([
                        'message' => 'Time for rent successfully updated',
                        'errors' => []
                    ])
                    :
                    response()->json([
                        'message' => 'Update time rent fail',
                        'errors' => ['Problems with update time rent']
                    ], 503);

            } else {
                return response()->json([
                    'message' => 'update error',
                    'errors' => ['Rental time has not ended yet']
                ], 403);
            }
        } else {
            return response()->json([
                'message' => 'update error',
                'errors' => ['You do not have permission to update this row']
            ], 403);
        }
    }

    /**
     * @OA\Delete(
     *     path="/rented-cars/{id}",
     *     summary="Delete rented car",
     *     description="Delete rented car",
     *     operationId="deleteRentedCar",
     *     tags={"Rented Car"},
     *     @OA\Parameter(
     *         description="Rented_id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="An int value."),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "message": "Row success deleted",
     *                 "errors": {}
     *                },
     *               summary="Success response."
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent,
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "message": "Unauthenticated"
     *                },
     *               summary="Unauthenticated"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=503,
     *         description="Service Unavailable",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "message": "Delete fail",
     *                 "errors": {"Problems with delete this row"}
     *                },
     *               summary="Service Unavailable."
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "message": "delete error",
     *                 "errors": {"You do not have permission to delete this row"}
     *                },
     *               summary="Forbidden."
     *             )
     *          )
     *     )
     * )
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RentedCar  $rentedCar
     * @return \Illuminate\Http\Response
     */
    public function destroy(RentedCar $rentedCar)
    {
        if ($this->isSameRow($rentedCar)) {
            ;
            return $rentedCar->delete()
                ?
                response()->json([
                    'message' => 'row success deleted',
                    'errors' => []
                ])
                :
                response()->json([
                    'message' => 'Delete fail',
                    'errors' => ['Problems with delete this row']
                ], 503)
                ;
        } else {
            return response()->json([
                'message' => 'delete error',
                'errors' => ['You do not have permission to delete this row']
            ], 403);
        }
    }
}
