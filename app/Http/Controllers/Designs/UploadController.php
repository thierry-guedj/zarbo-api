<?php

namespace App\Http\Controllers\Designs;

use App\Jobs\UploadImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\IDesign;

class UploadController extends Controller
{
    protected $designs;

    public function __construct(IDesign $designs)
    {
        $this->designs = $designs;
    }

    public function upload(Request $request)
    {
        // validate the request
        \Log::error("coucou upload controller");
        $this->validate($request, [
            'slim_output_0' => ['required', 'mimes:jpg,jpeg,gif,bmp,png', 'max:10000']
        ]); 

        // get the image
        $image = $request->file('slim_output_0');
        $image_path = $image->getPathName();
        

        // get the original file name and replace any spaces with _
        // Business Cards.png = timestamp()_business_cards.png
        $filename = time()."_". preg_replace('/\s+/', '_', strtolower($image->getClientOriginalName()));
        
        // move the image to the temporary location (tmp)
        $tmp = $image->storeAs('uploads/designs/original', $filename, 'tmp');

        // create the database record for the design
        // $design = auth()->user()->designs()->create([
        //     'image' => $filename,
        //     'disk' => config('site.upload_disk')
        // ]);

        $design = $this->designs->create([
            'user_id' => auth()->id(),
            'image' => $filename,
            'title' =>$request->title,
            'description' =>$request->description,
            // 'tags' =>$request->tags,
            'is_live' =>$request->is_live,
            'slug' =>$request->slug,
            'disk' => config('site.upload_disk')
        ]);
        \Log::error($design);
        // dispatch a job to handle the image manipulation
        $this->dispatch(new UploadImage($design));
        
        return response()->json($design, 200);

    }
}
