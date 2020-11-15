<?php

namespace App\Repositories\Eloquent;

use App\Models\Design;
use Illuminate\Http\Request;
use App\Repositories\Contracts\IDesign;
use App\Repositories\Eloquent\BaseRepository;

class DesignRepository extends BaseRepository implements IDesign
{
    public function model()
    {
        return Design::class;
    }

    public function applyTags($id, array $data)
    {
        $design = $this->find($id);
        $design->retag($data);
    }

    public function addComment($designId, array $data)
    {
        // Get the design for which we want to create a comment
        $design = $this->find($designId);

        // Create the comment for the design
        $comment = $design->comments()->create($data);

        return $comment;
    }

    public function like($id)
    {
        $design = $this->model->findOrFail($id);
        if($design->isLikedByUser(auth()->id())){
            $design->unlike();
        } else {
            $design->like();
        }

        return $design->likes()->count();
    }
    public function totalLikes($id)
    {
        $design = $this->model->findOrFail($id);
        return $design->likes()->count();
    }
    public function isLikedByUser($id)
    {
        $design = $this->model->findOrFail($id);
        return $design->isLikedByUser(auth()->id());
    }

    public function uploadIsSuccessful($id)
    {
        $design = $this->model->find($id);
        return $design->upload_successful;
    }
 
    public function search(Request $request)
    {
        $query = (new $this->model)->newQuery();

        $query->where('is_live', true);

        // Returns only designs with comments
        if($request->has_comments){
            $query->has('comments');
        }

        // Returns only designs assigned to teams
        if($request->has_team){
            $query->has('team');
        }

        // Search title and description for provided string
        if($request->q){
            $query->where(function($q) use ($request){
                $q->where('title', 'like', '%'.$request->q.'%')
                    ->orWhere('description', 'like', '%'.$request->q.'%');
            });
        }

         // Returns only designs assigned to tag name
         if($request->tag){
            $query->withAllTags($request->tag);
        }

        if($request->tags){
            $query->withAnyTags($request->tags);
        }

         // Returns only designs assigned to user id
         if($request->userId){
            $query->where('user_id', $request->userId);
        }
 // Returns only designs whereNotIn id
 if($request->whereNotIn){
    $query->whereNotIn('id', $request->whereNotIn);
}
        // Order the query by likes or latest first
        if($request->orderBy == 'likes'){
            $query->withCount('likes')   // likes_count
                ->orderByDesc('likes_count');
        } else 
        {
            $query->latest();
        }

        return $query->with('user')->paginate($request->nbResults, ['*'], 'page', $request->page);
    }

    public function fetchByTagName($tag) 
    { 
        // You can use the scope that comes with the EloquentTaggable package:
        $designs = $this->model->withAllTags($tag)->with('user')->get();

        // Then return the results of the search
        return $designs;
    }

    public function fetchByTags(array $tags)
    {
         /* $designs = $this->model->whereHas('tags', function($q) use ($tags){
                         $q->whereIn('name', $tags);
                    }); */

                    $designs = $this->model->withAnyTags($tags)->with('user')->get();
    }
    public function getTags() 
    { 
        // You can use the scope that comes with the EloquentTaggable package:
        $tags = $this->model->getAllTags()->get();

        // Then return the results of the search
        return $tags;
    }

}