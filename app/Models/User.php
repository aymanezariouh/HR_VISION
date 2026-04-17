<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_HR = 'hr';
    public const ROLE_EMPLOYEE = 'employee';
    public const SUPER_ADMIN_ID = 1;
    public const ROOT_ADMIN_ID = self::SUPER_ADMIN_ID;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'is_active',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function hasRole(string $role): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->role === $role;
    }

    /**
     * Determine whether the user has any of the given roles.
     *
     * @param  array<int, string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($this->role, $roles, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->id === self::SUPER_ADMIN_ID && $this->role === self::ROLE_ADMIN;
    }

    public function isRootAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if ($user->id !== self::SUPER_ADMIN_ID) {
                return;
            }

            $wasSuperAdmin = $user->exists && $user->getOriginal('role') === self::ROLE_ADMIN;
            $isBeingCreatedAsSuperAdmin = ! $user->exists && $user->role === self::ROLE_ADMIN;

            if (! ($wasSuperAdmin || $isBeingCreatedAsSuperAdmin)) {
                return;
            }

            $user->role = self::ROLE_ADMIN;
            $user->is_active = true;
        });
    }
}
