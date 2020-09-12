<?php

namespace App\Jobs;

use File;
use Image;
use App\Models\Design;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UploadImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $design;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Design $design)
    {
        $this->design = $design;
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        $disk = $this->design->disk;
        $filename = $this->design->image;
        $original_file = storage_path() . '/uploads/original/'. $filename;
        \Log::error("coucou upload image");
        try{
             // create the Extra Large Image and save to tmp disk
             Image::make($original_file)
             ->resize(2560, null, function($constraint){
                 $constraint->aspectRatio();
             })
             ->save($extralarge = storage_path('uploads/extralarge/'. $filename));

            // create the Large Image and save to tmp disk
            Image::make($original_file)
                ->resize(1920, null, function($constraint){
                    $constraint->aspectRatio();
                })
                ->save($large = storage_path('uploads/large/'. $filename));

            // Create the thumbnail image
            Image::make($original_file)
                ->resize(250, null, function($constraint){
                    $constraint->aspectRatio();
                })
                ->save($thumbnail = storage_path('uploads/thumbnail/'. $filename));

            // Create the mini thumbnail image
            Image::make($original_file)
                ->resize(100, null, function($constraint){
                    $constraint->aspectRatio();
                })
                ->save($minithumbnail = storage_path('uploads/minithumbnail/'. $filename));
                
            // store images to permanent disk
            // original image
            if(Storage::disk($disk)
                ->put('uploads/original/'.$filename, fopen($original_file, 'r+'))){
                    File::delete($original_file);
                }

                // extralarge images
            if(Storage::disk($disk)
                ->put('uploads/extralarge/'.$filename, fopen($extralarge, 'r+'))){
                File::delete($extralarge);
            }
            // large images
            if(Storage::disk($disk)
                ->put('uploads/large/'.$filename, fopen($large, 'r+'))){
                    File::delete($large);
                }

            // thumbnail images
            if(Storage::disk($disk)
                ->put('uploads/thumbnail/'.$filename, fopen($thumbnail, 'r+'))){
                    File::delete($thumbnail);
                 
                }
            
            // minithumbnail images
            if(Storage::disk($disk)
                ->put('uploads/minithumbnail/'.$filename, fopen($minithumbnail, 'r+'))){
                    File::delete($minithumbnail);
                 
                }
            // Update the database record with success flag
            $this->design->update([
                'upload_successful' => true
            ]);

        } catch(\Exception $e){
            \Log::error($e->getMessage());
        }

    }
}
