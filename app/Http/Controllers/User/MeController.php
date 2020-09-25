<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class MeController extends Controller
{
    
    public function getMe()
    {
        if(auth()->check()){
            $user = auth()->user();
            return new UserResource($user);
            //$user->created_at_human = $user->created_at->diffForHumans();
            //return response()->json(["user" => auth()->user()], 200);
        }

        return response()->json(null, 401);
    }
    public function setLang($locale)
    {
        App::setLocale($locale);
    }
}
