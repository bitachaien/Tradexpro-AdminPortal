<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface WebsocketDataSenderInterface
{
    public function sendData(Model $order);
}
