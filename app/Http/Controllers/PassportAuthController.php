<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PassportAuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Registro",
     *     description="A rota realiza o cadastro de um usuário no banco e retorna o usuário criado com seu token de acesso",
     *     operationId="register",
     *     @OA\RequestBody(
     *      required=true,
     *      description="User credentials register",
     *      @OA\JsonContent(
     *          required={"name", "email", "password"},
     *          @OA\Property(
     *              property="name", 
     *              type="string", 
     *              example="John Doe"
     *          ),
     *          @OA\Property(
     *              property="email", 
     *              type="string", 
     *              format="email",
     *              example="user@gmail.com"
     *          ),
     *          @OA\Property(
     *              property="password", 
     *              type="string", 
     *              format="password", 
     *              example="PassWord123456"
     *          ),
     *      ),
     *     ),
     *    @OA\Response(
     *         response=400,
     *         description="Error",
     *         @OA\JsonContent(
     *          @OA\Property(
     *              property="message", 
     *              type="string", 
     *              example="User already exists"
     *          )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="user",
     *                  ref="#/components/schemas/User"
     *              ),
     *              @OA\Property(
     *                  property="token",
     *                  type="string",
     *              )
     *          ),
     *     )
     * )
     */
    public function register(Request $request): JsonResponse
    {
        $this->validate($request, [
            'name' => 'required|min:4',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($user = User::where(['email' => $request->email])->first()) {
            return response()->json(['message' => 'User already exists'], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $token = $user->createToken('LaravelAuthApp')->accessToken;

        return response()->json(['user' => $user, 'token' => $token], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Login",
     *     description="A rota realiza o login de um usuário retornando os dados usuário juntamente com seu token de acesso",
     *     operationId="login",
     *     @OA\RequestBody(
     *          required=true,
     *          description="User credentials register",
     *          @OA\JsonContent(
     *              required={"email", "password"},
     *              @OA\Property(
     *                  property="email", 
     *                  type="string", 
     *                  format="email",
     *                  example="user@gmail.com"
     *              ),
     *              @OA\Property(
     *                  property="password", 
     *                  type="string", 
     *                  format="password", 
     *                  example="PassWord123456"
     *              ),
     *          ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Error",
     *         @OA\JsonContent(
     *          @OA\Property(
     *              property="message", 
     *              type="string", 
     *              example="Unauthorised"
     *          )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *           @OA\Property(
     *               property="user",
     *               ref="#/components/schemas/User"
     *           ),
     *           @OA\Property(
     *               property="token",
     *               type="string",
     *           )
     *         ),
     *     )
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
            return response()->json(['user' => auth()->user(), 'token' => $token], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Logout",
     *     description="A rota realiza o logout de um usuário previamente logado",
     *     operationId="logout",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Response(
     *         response=401,
     *         description="Error",
     *         @OA\JsonContent(
     *          @OA\Property(
     *              property="message", 
     *              type="string", 
     *              example="Unauthorised"
     *          )
     *         ),
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *           @OA\Property(
     *             property="message", 
     *             type="string", 
     *             example="You have been successfully logged out"
     *          )
     *         ),
     *     )
     * ) 
     * 
     */
    public function logout(Request $request)
    {
        if (empty(auth()->user())) {
            return response()->json(['message' => 'Unauthorised'], 401);
        }

        $token = $request->user()->token();
        $token->revoke();
        $response = [
            'message' => 'You have been successfully logged out'
        ];
        return response($response, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     tags={"Auth"},
     *     summary="Show User",
     *     description="A rota realiza a listagem do usuário baseado no token",
     *     operationId="show",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Response(
     *         response=401,
     *         description="Error",
     *         @OA\JsonContent(
     *          @OA\Property(
     *              property="message", 
     *              type="string", 
     *              example="Unauthorised"
     *          )
     *         ),
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *          @OA\Property(
     *               property="user",
     *               ref="#/components/schemas/User"
     *           ),
     *         ),
     *     )
     * ) 
     * 
     */

    public function show(Request $request)
    {
        if (empty(auth()->user())) {
            return response()->json(['message' => 'Unauthorised'], 401);
        }

        return response()->json(auth()->user(), 200);
    }

    /**
     * @OA\Put(
     *     path="/api/reset",
     *     tags={"Auth"},
     *     summary="Update password",
     *     description="A rota realiza a atualização da senha do usuário logado",
     *     operationId="resetPassword",
     *     security={ {"bearerAuth": {} }},
     *     @OA\RequestBody(
     *       required=true,
     *       description="User password updated",
     *       @OA\JsonContent(
     *         required={"password"},
     *         @OA\Property(
     *           property="password", 
     *           type="string", 
     *           format="password", 
     *           example="PassWord123456"
     *         ),
     *       ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Error",
     *         @OA\JsonContent(
     *          @OA\Property(
     *              property="message", 
     *              type="string", 
     *              example="Unauthorised"
     *          )
     *         ),
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *          @OA\Property(
     *            property="message",
     *            type="string", 
     *            example="Successed user updated"
     *          ),
     *        ),
     *     )
     * ) 
     * 
     */
    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|min:8',
        ]);

        if (empty(auth()->user())) {
            return response()->json(['message' => 'Unauthorised'], 401);
        }

        if (!$user = User::where(['id' => auth()->user()->id])->first()) {
            return response()->json(['message' => 'This user is not exists'], 400);
        }
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json(['message' => 'Successed user updated'], 200);
    }
}
