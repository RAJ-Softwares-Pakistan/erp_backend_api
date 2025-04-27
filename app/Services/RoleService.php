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
        return in_array($role, self::getRoles());
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
        $permissions = self::getRolePermissions($role);
        $hierarchies = self::getHierarchies();

        if (isset($hierarchies[$role])) {
            foreach ($hierarchies[$role] as $inheritedRole) {
                $permissions = array_merge($permissions, self::getRolePermissions($inheritedRole));
            }
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
    public static function canAccessRole(string $role, string $targetRole): bool
    {
        $hierarchies = self::getHierarchies();
        
        if (!isset($hierarchies[$role])) {
            return false;
        }

        return in_array($targetRole, $hierarchies[$role]);
    }
} 