<?php

namespace App\Models;

use App\Services\RoleService;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'organization_id'
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the fields that should be searchable.
     *
     * @return array
     */
    public function getSearchableFields(): array
    {
        return [
            'name',
            'email',
            'role'
        ];
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(config('roles.roles.super_admin'));
    }

    /**
     * Check if user is an organization owner
     */
    public function isOrgOwner(): bool
    {
        return $this->hasRole(config('roles.roles.org_owner'));
    }

    /**
     * Check if user is a regular organization user
     */
    public function isOrgUser(): bool
    {
        return $this->hasRole(config('roles.roles.org_user'));
    }

    /**
     * Get all permissions for the user's role
     */
    public function getPermissions(): array
    {
        return RoleService::getAllPermissions($this->role);
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return RoleService::hasPermission($this->role, $permission);
    }

    /**
     * Check if user can access another role's permissions
     */
    public function canAccessRole(string $targetRole): bool
    {
        return RoleService::canAccessRole($this->role, $targetRole);
    }

    /**
     * Check if user is the owner of a specific organization
     */
    public function isOwnerOf(Organization $organization): bool 
    {
        return $this->id === $organization->root_user_id;
    }

    /**
     * Check if user is a member of a specific organization
     */
    public function isMemberOf(Organization $organization): bool 
    {
        return $this->organization_id === $organization->organization_id;
    }

    /**
     * Get the organization the user belongs to
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * Get the organizations owned by the user
     */
    public function ownedOrganizations(): HasOne
    {
        return $this->hasOne(Organization::class, 'root_user_id');
    }
}
