<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookAttempt extends Model
{
    protected $fillable = [
        'webhook_id',
        'status',
        'http_code',
        'response_time',
        'error',
    ];
}
