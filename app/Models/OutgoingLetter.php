<?php

namespace App\Models;

use App\Models\User;
use App\Models\LetterRequest;
use Illuminate\Database\Eloquent\Model;

class OutgoingLetter extends Model
{
    protected $table = 'outgoing_letters';

    protected $fillable = [
        'user_id',
        'letter_number',
        'subject',
        'recipient',
        'outgoing_date',
        'file_path'
    ];

    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function LetterRequest()
    {
        return $this->hasOne(LetterRequest::class);
    }
}
