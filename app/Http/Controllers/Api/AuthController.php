<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/register",
     *     operationId="registerUser",
     *     tags={"Authorization"},
     *     summary="Register new user",
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="User name",
     *          required=true,
     *          @OA\Schema(type="string"),
     *          @OA\Examples(example="string", value="John", summary="John")
     *     ),
     *     @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="User email",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="password",
     *          in="query",
     *          description="Password",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *      @OA\Response(
     *         response="200",
     *         description="Succesfull added new user",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "message": "User successfull added",
     *                 "errors": {}
     *                },
     *               summary="Success response."
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *         response="422",
     *         description="Unprocessable Content",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "message": "The given data was invalid.",
     *                 "errors": {
     *                      "email":"The email must be a valid email address."
     *                  }
     *                },
     *               summary="Unprocessable Content."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Dublicate user",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *                 example="array",
     *                 value={
     *                      "message": "The given data was invalid",
     *                      "errors": {
     *                          "email":{"User with the same email is already exists"}
     *                       }
     *                 },
     *                 summary="Dublicate user."
     *             ),
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);
        $issetUser = User::where('email', '=', $request->email)->exists();
        if (!$issetUser) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);
//            $user->roles()->attach(2);
            return response()->json([
                'message' => 'User successful added',
                'errors' => []
            ]);
        } else {
            return response()->json([
                'message' => 'The given data was invalid',
                'errors' => [
                    'email' => ['User with the same email is already exists']
                ]
            ], 403);
        }
    }

    /**
     *
     * @OA\Post(
     *     path="/login",
     *     operationId="loginUser",
     *     tags={"Authorization"},
     *     summary="Log in user",
     *     @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="User email",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="password",
     *          in="query",
     *          description="Password",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *      @OA\Response(
     *         response="200",
     *         description="Succes authorization",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "access_token": "3biHV254xugVE9UkJRuI6fdjG3Bz3YVopb2ZnbnB",
     *                },
     *               summary="Success response."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="User not exists",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *                 example="array",
     *                 value={
     *                      "message": "The given data was invalid",
     *                      "errors": {
     *                          "password":{"Invalid credentials"}
     *                       }
     *                 },
     *                 summary="User not exists."
     *             ),
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $credentials = $request->only('email', 'password');
        if (!auth()->attempt($credentials)) {
            return response()->json([
                'message' => 'The given data was invalid',
                'errors' => [
                    'password' => ['Invalid credentials']
                ]
            ], 422);
        }
        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'access_token' => $token
        ]);
    }

    /**
     *
     * @OA\Post(
     *     path="/logout",
     *     operationId="logoutUser",
     *     tags={"Authorization"},
     *     summary="Log out user",
     *      @OA\Response(
     *         response="200",
     *         description="Succesfull added new user",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *               example="array",
     *               value={
     *                 "message": "Logout Success",
     *                 "errors": {}
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
     *                 "message": "Unauthenticated"
     *                },
     *               summary="Unauthenticated"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Logout Fail",
     *         @OA\JsonContent(
     *             @OA\Schema(type="array"),
     *             @OA\Examples(
     *                 example="array",
     *                 value={
     *                      "message": "Logout fail",
     *                      "errors": {
     *                          "email":{"Something went wrong"}
     *                       }
     *                 },
     *                 summary="Logout fail."
     *             ),
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        if (\Auth::check()) {
            $user = \Auth::user();
            $user->tokens()->delete();
            session()->flush();
            return response()->json([
                'message' =>'Logout Success',
                'errors' => []
            ],200);
        }else{
            return response()->json([
                'message' =>'Logout fail',
                'errors' =>['You are not authorized']
            ], 500);
        }
    }
}
