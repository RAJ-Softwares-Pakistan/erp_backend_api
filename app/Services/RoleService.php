<?php

namespace App\Services;

class RoleService
{
    /**
     * Get all available roles
     */
    public static function getRoles(): array
    {
        return config('roles.roles');
    }

    /**
     * Get role hierarchies
     */
    public static function getHierarchies(): array
    {
        return config('roles.hierarchies');
    }

    /**
     * Get role permissions
     */
    public static function getPermissions(): array
    {
        return config('roles.permissions');
    }

    /**
     * Check if a role exists
     */
    public static function isValidRole(string $role): bool
    {
        return in_array($role, array_values(self::getRoles()));
    }

    /**
     * Get permissions for a specific role
     */
    public static function getRolePermissions(string $role): array
    {
        return config('roles.permissions.' . $role, []);
    }

    /**
     * Get all permissions a role has access to (including inherited permissions)
     */
    public static function getAllPermissions(string $role): array
    {
        $permissions = config('roles.permissions.' . $role, []);
        
        // Include permissions from subordinate roles
        foreach (config('roles.hierarchies.' . $role, []) as $subordinateRole) {
            $permissions = array_merge($permissions, config('roles.permissions.' . $subordinateRole, []));
        }

        return array_unique($permissions);
    }

    /**
     * Check if a role has a specific permission
     */
    public static function hasPermission(string $role, string $permission): bool
    {
        return in_array($permission, self::getAllPermissions($role));
    }

    /**
     * Check if a role can access another role's permissions
     */
    public static function canAccessRole(string $userRole, string $targetRole): bool
    {
        if ($userRole === $targetRole) {
            return true;
        }

        return in_array($targetRole, config('roles.hierarchies.' . $userRole, []));
    }
}