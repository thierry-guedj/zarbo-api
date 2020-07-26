<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    
    public function participants()
    {
        return $this->belongsToMany(User::class, "participants");
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getLatestMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    public function isUnreadForUser($user_id)
    {
        return (bool)$this->messages()
                        ->whereNull('last_read')
                        ->where('user_id', '<>', $user_id)
                        ->count();
    }

    public function markAsReadForUser($user_id)
    {
        $this->messages()
            ->whereNull('last_read')
            ->where('user_id', '<>', $user_id)
            ->update([
                'last_read' => Carbon::now()
            ]);
    }
}
