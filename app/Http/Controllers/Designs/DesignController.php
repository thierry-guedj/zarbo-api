<?php

namespace App\Http\Controllers\Designs;

use App\Models\Design;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DesignResource;
use App\Repositories\Contracts\IDesign;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Repositories\Eloquent\Criteria\{
    EagerLoad,
    IsLive,
    LatestFirst,
    ForUser,
};

class DesignController extends Controller
{
    protected $designs;
    
    public function __construct(IDesign $designs)
    {
        $this->designs = $designs;

    }

    public function index()
    {
        $designs = $this->designs->withCriteria([
            new LatestFirst(),
            new IsLive(),
            new ForUser(3),
            new EagerLoad(['user', 'comments'])
        ])->all();
        return DesignResource::collection($designs);

    }

    public function findDesign($id)
    {
        $design = $this->designs->withCriteria([
            new IsLive(), 
            new EagerLoad(['user', 'comments'])
            ])->find($id);
        return new DesignResource($design);
    }

    public function update(Request $request, $id)
    {
        $design = $this->designs->find($id);
        $this->authorize('update', $design);

      /*   $this->validate($request, [
            'title' => ['required', 'unique:designs,title,'. $id],
            'description' => ['required', 'string', 'min:6', 'max:450'],
            'tags' => ['required'],
            'title' => ['required'],
            'description' => ['string', 'min:6', 'max:450'],            
            'team' => ['required_if:assign_to_team,true']
        ]); */

        $design = $this->designs->update($id, [
            'team_id' => $request->team,
            'title' => $request->title,
            'description' => $request->description,
            'slug' => Str::slug($request->title),
            'is_live' => $request->is_live
        ]);

        // Apply tags
        $this->designs->applyTags($id, $request->tags);

        return new DesignResource($design);
    }
    public function updateIsLive(Request $request, $id)
    {
        $design = $this->designs->find($id);
        $this->authorize('update', $design);

        $design = $this->designs->update($id, [
            'is_live' => $request->isLive
        ]);

        return new DesignResource($design);
    }

    public function destroy($id)
    {
        $design = $this->designs->find($id);
        $this->authorize('delete', $design);

        // Delete the files associated to the record
        foreach(['original', 'large', 'thumbnail', 'extralarge', 'minithumbnail'] as $size){
            // Check if the file exists in the database
            if(Storage::disk($design->disk)->exists("uploads/designs/{$size}/".$design->image)){
                Storage::disk($design->disk)->delete("uploads/designs/{$size}/".$design->image);
            }
        }

        $this->designs->delete($id);

        return response()->json(['message' => trans('messages.recordDeleted')], 200);

    }

    public function like($id)
    {
        $total = $this->designs->like($id);
        return response()->json([
            'message' => trans('messages.successful'),
            'total' => $total
        ], 200);
    }
    public function totalLikes($id)
    {
        $total = $this->designs->totalLikes($id);
        return response()->json([
            'total' => $total
        ], 200);
    }
    public function checkIfUserHasLiked($designId)
    {
        $isLiked = $this->designs->isLikedByUser($designId);
        return response()->json(['liked' => $isLiked], 200);
    }
 

    public function search(Request $request)
    {
        $designs = $this->designs->search($request);
        return DesignResource::collection($designs);
    }

    public function findBySlug($slug)
    {
        $design = $this->designs->withCriteria([
            new IsLive(), 
            new EagerLoad(['user', 'comments'])
            ])->findWhereFirst('slug', $slug);
        return new DesignResource($design);        
    }

    public function getForTeam($teamId)
    {
        $designs = $this->designs
                        ->withCriteria([new IsLive()])
                        ->findWhere('team_id', $teamId);
        return DesignResource::collection($designs);
    }

    public function getForUser($userId)
    {
        $designs = $this->designs
                        ->withCriteria([new LatestFirst()])
                        ->findWhere('user_id', $userId);
        return DesignResource::collection($designs);
    }
    public function getForUserFront($userId)
    {
        $designs = $this->designs
                        ->withCriteria([new isLive()])
                        ->findWhere('user_id', $userId);
        return DesignResource::collection($designs);
    }
    public function getForUserWhereNotIn($userId, $designId)
    {
        $designs = $this->designs
                        ->withCriteria([
                            new isLive(), 
                            new LatestFirst()
                            ])
                        ->findWhere('user_id', $userId)
                        ->whereNotIn('id', $designId)->paginate(6);
        return DesignResource::collection($designs);
    }
 
    public function userOwnsDesign($id)
    {
        $design = $this->designs->withCriteria(
            [ new ForUser(auth()->id())]
        )->findWhereFirst('id', $id);

        return new DesignResource($design);
    }

    public function uploadIsSuccessful($designId)
    {       
        $design = $this->designs->find($designId);
        $time=0;
        while($design->upload_successful == false && $time < 50000) {
            $design = $this->designs->find($designId);
            $time++;
        }
        return new DesignResource($design);
    }

    public function searchByTagName($tag)
    {
        $designs = $this->designs->fetchByTagName($tag);
        return DesignResource::collection($designs);
       /*  $designs = $this->designs->withCriteria([
            new IsLive(), 
            new EagerLoad(['user', 'comments']),
            new ForTag($tag),
            ])->fetchByTagName('tag', $tag);
        return DesignResource::collection($designs);   */      
    }
    public function searchByTags(array $tags)
    {
        $designs = $this->designs->fetchByTags($tags);
        return DesignResource::collection($designs);
       /*  $designs = $this->designs->withCriteria([
            new IsLive(), 
            new EagerLoad(['user', 'comments']),
            new ForTag($tag),
            ])->fetchByTagName('tag', $tag);
        return DesignResource::collection($designs);   */      
    }
    public function getTags() 
    { 
        // You can use the scope that comes with the EloquentTaggable package:
        $tags = $this->designs->allTagModels();

        // Then return the results of the search
        return $tags;
    }
    public function lastDesigns()
    {
        $designs = $this->designs->withCriteria([
            new LatestFirst(),
            new IsLive(),
            new EagerLoad(['user', 'comments'])
        ])->paginate(12);
        return DesignResource::collection($designs);

    }
}
