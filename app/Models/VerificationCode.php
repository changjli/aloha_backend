<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'code',
    ];

    public function User()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
