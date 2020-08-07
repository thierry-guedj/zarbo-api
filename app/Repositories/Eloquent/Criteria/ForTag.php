<?php

namespace App\Repositories\Eloquent\Criteria;

use App\Repositories\Criteria\ICriterion;

class ForTag implements ICriterion
{
    protected $tag;

    public function __construct($tag)
    {
        $this->tag = $tag;
    }
    public function apply($model)
    {
        // return $model->whereHas('tags', function($q) use ($this->tag){$q->where('name', $this->tag) });
        // $repository->fetchByTagName($this->tag);
        return $model->withAllTags($this->tag);
        
    }
}