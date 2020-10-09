<?php

namespace App\Jobs;

use File;
use Image;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UploadAvatar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        $disk = $this->user->disk;
        $filename = $this->user->avatar;
        $original_file = storage_path() . '/uploads/avatars/original/'. $filename;
        \Log::error("coucou upload image");
        try{
             // create the Large Image and save to tmp disk
             Image::make($original_file)
             ->resize(240, null, function($constraint){
                 $constraint->aspectRatio();
             })
             ->save($large = storage_path('uploads/avatars/large/'. $filename));

            // create the medium Image and save to tmp disk
            Image::make($original_file)
                ->resize(100, null, function($constraint){
                    $constraint->aspectRatio();
                })
                ->save($medium = storage_path('uploads/avatars/medium/'. $filename));

            // Create the small image
            Image::make($original_file)
                ->resize(50, null, function($constraint){
                    $constraint->aspectRatio();
                })
                ->save($small = storage_path('uploads/avatars/small/'. $filename));

                
            // store images to permanent disk
            // original image
            if(Storage::disk($disk)
                ->put('uploads/avatars/original/'.$filename, fopen($original_file, 'r+'))){
                    File::delete($original_file);
                }

                // extralarge images
            if(Storage::disk($disk)
                ->put('uploads/avatars/large/'.$filename, fopen($large, 'r+'))){
                File::delete($large);
            }
            // large images
            if(Storage::disk($disk)
                ->put('uploads/avatars/medium/'.$filename, fopen($medium, 'r+'))){
                    File::delete($medium);
                }

            // thumbnail images
            if(Storage::disk($disk)
                ->put('uploads/avatars/small/'.$filename, fopen($small, 'r+'))){
                    File::delete($small);
                 
                }
            
            
            // Update the database record with success flag
            $this->user->update([
                'upload_successful' => true
            ]);

        } catch(\Exception $e){
            \Log::error($e->getMessage());
        }

    }
}
