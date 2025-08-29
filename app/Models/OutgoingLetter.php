<?php

namespace App\Models;

use App\Models\LetterRequest;
use App\Models\User;
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
        // Tambahkan 'outgoing_letters_id' sebagai argumen kedua
        return $this->hasOne(LetterRequest::class, 'outgoing_letters_id');
    }
}
