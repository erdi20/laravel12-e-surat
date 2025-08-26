<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class IncomingLetter extends Model
{
    protected $table = 'incoming_letters';

    protected $fillable = [
        'user_id',
        'letter_number',
        'subject',
        'sender',
        'incoming_date',
        'file_path'
    ];

    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
