<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Register new user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request){
        $validate = Validator::make($request->all(), [
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:4|confirmed',
        ]);
        if ($validate->fails()){
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors()
            ], 422);
        }
        $tenant = Tenant::find(Crypt::decrypt($request->tenant));
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->status = 'Active';
        $user->tenant_id = $tenant->id;
        $user->sub = $tenant->sub;
        $user->save();
        return response()->json(['status' => 'success'], 200);
    }

    
    //Subdomain enabled, remove credentials function for disable
    protected function credentials($sub)
    {
        return array_merge(request(['email', 'password']), ['sub' => $sub]);
    }



    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $domain = explode('.', $request->getHttpHost());
        unset($domain[0]);
        $domain = implode('.', $domain);

        $credentials = '';
        if($domain == config('app.default_domain_name')){
            $credentials = $this->credentials(explode('.', $request->getHttpHost())[0]);
        }else{
            $credentials = $this->credentials($request->getHttpHost());
        }
        // $credentials = request(['email', 'password']); //for disabled subdomain
        // $credentials = $this->credentials($sub); //for enabled subdomain

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token, $request->email);
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
    protected function respondWithToken($token, $email)
    {
        $user = User::select('menuroles as roles')->where('email', '=', $email)->first();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'roles' => $user->roles,
        ]);
    }
}
