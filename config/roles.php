<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Roles
    |--------------------------------------------------------------------------
    |
    | This file contains all the role definitions and related functionality
    | for the application.
    |
    */

    'roles' => [
        'owner' => 'owner',
        'admin' => 'admin',
        'user' => 'user',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Hierarchies
    |--------------------------------------------------------------------------
    |
    | Define which roles have access to other roles' permissions.
    | For example, owner has access to all admin permissions.
    |
    */
    'hierarchies' => [
        'owner' => ['admin', 'user'],
        'admin' => ['user'],
        'user' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Permissions
    |--------------------------------------------------------------------------
    |
    | Define the permissions for each role.
    |
    */
    'permissions' => [
        'owner' => [
            'manage_users',
            'manage_settings',
            'view_reports',
            'manage_roles',
        ],
        'admin' => [
            'manage_users',
            'view_reports',
        ],
        'user' => [
            'view_reports',
        ],
    ],
]; 