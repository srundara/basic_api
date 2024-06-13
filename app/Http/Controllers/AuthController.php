<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }


    public function index(){
        return "Welcome!";
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {

        $credentials = request(['email', 'password']);
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('email', request('email'))->first();
        $userProfile = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'profile_url' => $user->profile_url,
        ];
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $userProfile,
            'expires_in' => auth()->factory()->getTTL() * 10080,
        ]);
        return $this->respondWithToken($token);
    }
    // function register
    public function register(Request $request)
    {
            $userRegister = $request->validate([
                'name' => 'required|string|max:55',
                'email' => 'email|required|unique:users',
                'password' => 'required',
            ]);
            $userRegister['password'] = bcrypt($request->password);

            if($request->hasFile('profile')){
                $image = $request->file('profile');
                $name = time().'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('/user');
                $image->move($destinationPath,$name);
                // $imaneName = $name;
                $userRegister['profile_url'] = $name;
            }

            $user = User::create($userRegister);
            $accessToken = $user->createToken('authToken')->accessToken;
            return response(['user' => $user,'access_token' => $accessToken]);
    }

    public function update(Request $request,$id){
        $user = User::find($id);
        $user->name = $request->name;
        /// check if have profile image
        if($request->hasFile('profile')){
            $image = $request->file('profile');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/user');
            $image->move($destinationPath, $name);
            // $user->profile_url = $name;
            $data['profile_url'] = $name;
            /// delete old image
            $oldImage = public_path('/user/').$user->profile_url;
            if(file_exists($oldImage)){
                unlink($oldImage);
            }
        }
        $user->update($data);
        $baseUrlImage = request()->getSchemeAndHttpHost() . '/user';
        $user->profile_url = $baseUrlImage.'/'.$data['profile_url'];
        return response()->json([
            'message' =>  'User update successful',
            'user' => $user
        ]);

    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
