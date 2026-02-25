<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAudit extends Model
{
    protected $fillable = ['guard', 'email', 'user_id', 'status', 'ip_address', 'user_agent'];
}
