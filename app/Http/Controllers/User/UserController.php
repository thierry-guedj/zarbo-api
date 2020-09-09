<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Repositories\Contracts\IUser;
use App\Repositories\Eloquent\Criteria\EagerLoad;

class UserController extends Controller
{
    protected $users;
    
    public function __construct(IUser $users)
    {
        $this->users = $users;
    }

    public function index()
    {
        $users = $this->users->withCriteria([
            new EagerLoad(['designs'])
        ])->all();

        return UserResource::collection($users);
    }

    public function search(Request $request)
    {
        $designers = $this->users->search($request);
        return UserResource::collection($designers);
    }

    public function findByUserName($username)
    {
        $user = $this->users->findWhereFirst('username', $username);
        return new UserResource($user);
    }
    public function findById($id)
    {
        $user = $this->users->findWhereFirst('id', $id);
        return new UserResource($user);
    }
}
