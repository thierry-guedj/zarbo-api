<?php

namespace App\Repositories\Eloquent;

use App\Models\Message;
use App\Repositories\Contracts\IMessage;
use App\Repositories\Eloquent\BaseRepository;

class MessageRepository extends BaseRepository implements IMessage
{
    public function model()
    {
        return Message::class;  // App\Models\Message
    }
}