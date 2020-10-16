<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Repositories\Contracts\IUser;
use App\Repositories\Eloquent\Criteria\{
    EagerLoad,
    LatestFirst,
};

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
    public function update(Request $request, $id )
    {
        $user = $this->users->find($id);
        $this->authorize('update', $user);

        $this->validate($request, [
            'name' => ['required', 'unique:users,name,'. $id],
            /* 'title' => ['required', 'unique:users,title,'. $id],
            'description' => ['required', 'string', 'min:6', 'max:450'],
            'tags' => ['required'],
            'title' => ['required'],
            'description' => ['string', 'min:6', 'max:450'],            
            'team' => ['required_if:assign_to_team,true'] */
        ]);

        $user = $this->users->update($id, [
            'avatar' => $request->avatar,
            'name' => $request->name,
            'tagline' => $request->tagline,
            'about' => $request->about,
        ]);

        return new UserResource($user);
    }
    public function findById($id)
    {
        $user = $this->users->withCriteria([
            new EagerLoad(['designs'])
        ])->findWhereFirst('id', $id);
        return new UserResource($user);
    }
    public function uploadIsSuccessful($userId)
    {       
        $user = $this->users->findWhereFirst('id', $userId);
        $time=0;
        while($user->upload_successful == false && $time < 50000) {
            $user = $this->users->findWhereFirst('id', $userId);
            $time++;
        }
        return true;
    }
    public function lastUsers()
    {
        $users = $this->users->withCriteria([
            new LatestFirst(),
        ])->paginate(15);
        return UserResource::collection($users);

    }
}
