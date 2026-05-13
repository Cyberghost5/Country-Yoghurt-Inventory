<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'state', 'staff_states', 'lga', 'staff_lgas', 'shop_name', 'address', 'password', 'role'])]
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
            'password'          => 'hashed',
            'staff_states'      => 'array',
            'staff_lgas'        => 'array',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin'], true);
    }

    public function isAdminOrStaff(): bool
    {
        return in_array($this->role, ['admin', 'super_admin', 'staff'], true);
    }

    /**
     * Returns the list of states this staff member covers.
     */
    public function staffStates(): array
    {
        if ($this->role === 'staff' && !empty($this->staff_states)) {
            return $this->staff_states;
        }
        return $this->state ? [$this->state] : [];
    }

    /**
     * Returns the list of LGAs this staff member covers.
     */
    public function staffLgas(): array
    {
        if ($this->role === 'staff' && !empty($this->staff_lgas)) {
            return $this->staff_lgas;
        }
        return $this->lga ? [$this->lga] : [];
    }
}
