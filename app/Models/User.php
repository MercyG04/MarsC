<?php

namespace App\Models;
use App\Enums\UserRole;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role','phone_number'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'role' => UserRole::class,
        ];
    }

    
    public function valuations(): HasMany
    {
    return $this->hasMany(Valuation::class, 'created_by');
    }

    

    public function hasRole(UserRole ...$roles): bool
    {
    return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
    return $this->role === UserRole::Admin;
    }

    public function isAgent(): bool
    {
    return $this->role === UserRole::Agent;
    }

    public function isUnderwriter(): bool
    {
    return $this->role === UserRole::Underwriter;
    }

    public function isClaimsOfficer(): bool
    {
    return $this->role === UserRole::ClaimsOfficer;
    }
    
}
