<?php

namespace App\Http\Controllers\User;

use App\Jobs\UploadAvatar;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\IUser;

class AvatarController extends Controller
{
    protected $users;

    public function __construct(IUser $users)
    {
        $this->users = $users;
    }

    public function upload(Request $request)
    {
        // validate the request
        \Log::error($request->get('slim_output_0'));
        $request->replace($request->all()); 
        $this->validate($request, [
            'slim_output_0' => ['required', 'mimes:jpg,jpeg,gif,bmp,png', 'max:2000']
        ]); 

        // get the image
        $image = $request->file('slim_output_0');
        $image_path = $image->getPathName();
        

        // get the original file name and replace any spaces with _
        // Business Cards.png = timestamp()_business_cards.png
        $filename = time()."_". preg_replace('/\s+/', '_', strtolower($image->getClientOriginalName()));
        
        // move the image to the temporary location (tmp)
        $tmp = $image->storeAs('uploads/avatars/original', $filename, 'tmp');

        // create the database record for the design
        // $design = auth()->user()->designs()->create([
        //     'image' => $filename,
        //     'disk' => config('site.upload_disk')
        // ]);

        $user = $this->users->update(auth()->id(), [
            'image' => $filename,
            'name' => $request->name,
            'tagline' => $request->tagline,
            'about' => $request->about,
            'disk' => config('site.upload_disk')
        ]);
        \Log::error($user);
        // dispatch a job to handle the image manipulation
        $this->dispatch(new UploadAvatar($user));
        
        return response()->json($user, 200);

    }
}