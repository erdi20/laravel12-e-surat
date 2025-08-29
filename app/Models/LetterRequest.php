<?php

namespace App\Models;

use App\Models\OutgoingLetter;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LetterRequest extends Model
{
    protected $table = 'letter_requests';

    protected $fillable = [
        'user_id',
        'outgoing_letters_id',
        'subject',
        'purpose',
        'description',
        'status'
    ];

    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function OutgoingLetter()
    {
        // Tambahkan 'outgoing_letters_id' sebagai argumen kedua
        return $this->belongsTo(OutgoingLetter::class, 'outgoing_letters_id');
    }
}
