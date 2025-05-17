<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Models\Organization;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class OrganizationController extends Controller
{
    /**
     * List all organizations.
     * 
     * 
     * @return JsonResponse Paginated list of organizations with their root users
     */
    public function index(): JsonResponse
    {
        if (!Gate::allows('viewAny', Organization::class)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $organizations = Auth::user()->role === config('roles.roles.super_admin')
            ? Organization::with('rootUser')->paginate()
            : Organization::where('root_user_id', Auth::id())->with('rootUser')->paginate();

        return response()->json($organizations);
    }

    /**
     * Create a new organization.
     * 
     * @param StoreOrganizationRequest $request The validated organization creation request
     * @return JsonResponse The created organization resource with 201 status
     */
    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        if (!Gate::allows('create', Organization::class)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $organization = Organization::create([
            ...$request->validated(),
            'root_user_id' => Auth::id()
        ]);

        // Update the creating user's organization
        User::where('id', Auth::id())->update(['organization_id' => $organization->organization_id]);

        return response()->json($organization, Response::HTTP_CREATED);
    }

    /**
     * Display the specified organization.
     * 
     * 
     * @param Organization $organization The organization to display
     * @return JsonResponse The organization resource with its root user
     */
    public function show(Organization $organization): JsonResponse
    {
        if (!Gate::allows('view', $organization)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        return response()->json($organization->load('rootUser'));
    }

    /**
     * Update the specified organization.
     * 
     * @param UpdateOrganizationRequest $request The validated organization update request
     * @param Organization $organization The organization to update
     * @return JsonResponse The updated organization resource
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization): JsonResponse
    {
        if (!Gate::allows('update', $organization)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $organization->update($request->validated());
        return response()->json($organization);
    }

    /**
     * Remove the specified organization.
     *
     * @param Organization $organization The organization to delete
     * @return JsonResponse Empty response with 204 status code
     */
    public function destroy(Organization $organization): JsonResponse
    {
        if (!Gate::allows('delete', $organization)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $organization->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}