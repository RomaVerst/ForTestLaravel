<?php

namespace App\Http\Controllers\Api;

use App\Models\Car;
use App\Http\Resources\CarResource;

class CarController extends Controller
{
    /**
     * @OA\Get(
     *     path="/cars",
     *     operationId="getCars",
     *     tags={"Cars"},
     *     summary="Show cars",
     *      @OA\Response(
     *         response="200",
     *         description="Active cars list",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "data": {
     *                      {
     *                          "car_id": 123,
     *                          "brand": "Some Brand",
     *                          "model": "Model car",
     *                          "number": "G123GG12"
     *                      }
     *                  },
     *                },
     *               summary="Success response."
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
     *                "message": "Unauthenticated.",
     *                },
     *               summary="Unauthenticated."
     *             )
     *         )
     *     )
     * )
     *
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */

    public function index()
    {
        return CarResource::collection(Car::activeCars()->get());
    }
}
