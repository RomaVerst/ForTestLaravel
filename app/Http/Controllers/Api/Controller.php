<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Test Laravel API",
 *     version="1.0",
 *      @OA\Contact(
 *         email="admin@example.com"
 *     ),
 *     @OA\License(
 *         name="GNU GPL v3",
 *         url="https://fsf.org/"
 *     )
 * ),
 * @OA\Tag(
 *    name="Authorization",
 *    description="Register, login and get api token, logout"
 * ),
 * @OA\Tag(
 *     name="Cars",
 *     description="Show all active cars"
 * ),
 * @OA\Tag(
 *    name="Rented Car",
 *    description="Add, show, delete rented cars",
 * ),
 * @OA\Server(
 *    description="Laravel Test Server",
 *    url="http://127.0.0.1:8000/api"
 * )
 */

class Controller extends BaseController
{
    //
}
