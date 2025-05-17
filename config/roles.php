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
        'super_admin' => 'super_admin',  // System-wide admin with all permissions
        'org_owner' => 'org_owner',      // Organization owner
        'org_user' => 'org_user',        // Regular organization user
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Hierarchies
    |--------------------------------------------------------------------------
    |
    | Define which roles have access to other roles' permissions.
    |
    */
    'hierarchies' => [
        'super_admin' => ['org_owner', 'org_user'],
        'org_owner' => ['org_user'],
        'org_user' => [],
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
        'super_admin' => [
            'manage_organizations',
            'manage_all_users',
            'view_all_data',
            'manage_system_settings',
        ],
        'org_owner' => [
            'manage_org_settings',
            'manage_org_users',
            'manage_vendors',
            'view_org_reports',
            'delete_vendors',
        ],
        'org_user' => [
            'view_vendors',
            'create_vendors',
            'edit_vendors',
            'view_org_data',
        ],
    ],
];