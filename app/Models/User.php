<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use App\Models\LetterRequest;
use App\Models\OutgoingLetter;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function LetterRequest()
    {
        return $this->hasMany(LetterRequest::class);
    }

    public function IncomingLetter()
    {
        return $this->hasMany(IncomingLetter::class);
    }

    public function OutgoingLetter()
    {
        return $this->hasMany(OutgoingLetter::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        $user = Auth::user();  // Ambil user yang sedang login
        // if (!$user) {
        //     return false;  // Jika tidak ada user yang login, tidak bisa akses
        // }

        $userType = $user->user_type;  // Ambil user_type dari user yang login

        // Cek akses berdasarkan panel dan user_type
        if ($panel->getId() === 'e-suratadmin' && $userType === 'admin') {
            return true;  // Akses untuk admin
        } elseif ($panel->getId() === 'e-suratuser' && $userType === 'member') {
            return true;  // Akses untuk user
        }

        return false;  // Tidak ada akses
    }
}
