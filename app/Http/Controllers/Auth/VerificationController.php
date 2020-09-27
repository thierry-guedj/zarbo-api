<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use App\Repositories\Contracts\IUser;
use App\Providers\RouteServiceProvider;

//use Illuminate\Foundation\Auth\VerifiesEmails;

class VerificationController extends Controller
{
    protected $users;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(IUser $users)
    {
        $this->middleware('throttle:60,1')->only('verify', 'resend');
        $this->users = $users;
    }


    public function verify(Request $request, User $user)
    {

        // Check if the URL is a valid signed URL
        if(! URL::hasValidSignature($request)){
            return response()->json(["errors" => [
                "message" => trans('messages.invalidVerificationLink')
            ]], 422);
        }

        // Check if the user has already verified account
        if($user->hasVerifiedEmail()){
            return response()->json(["errors" => [
                "message" => trans('messages.emailAlreadyVerified')
            ]], 422);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json(["message" => trans('messages.emailSuccessVerified')], 200);
    }

    public function resend(Request $request)
    {
        $this->validate($request, [
            'email' => ['email', 'required']
        ]);
        
        //$user = $this->users->findWhereFirst('email', $request->email);
        $user = User::where('email', $request->email)->first();
        
        if(! $user){
            return response()->json(["errors" => [
                "message" => trans('messages.noUser')
            ]], 422);
        }

        if($user->hasVerifiedEmail()){
            return response()->json(["errors" => [
                "message" => trans('messages.emailAlreadyVerified')
            ]], 422);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['status' => trans('messages.verificationLnkResent')]);

    }

    
}
