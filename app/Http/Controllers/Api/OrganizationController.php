<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Http\Requests\RestoreOrganizationRequest;
use App\Models\Organization;
use App\Models\User;
use App\Services\RoleService;
use App\Traits\HasPaginatedList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class OrganizationController extends Controller
{
    use HasPaginatedList;

    /**
     * List all organizations.
     * 
     * @param Request $request The request with pagination parameters
     * @return JsonResponse Paginated list of organizations with their root users
     */
    public function index(Request $request): JsonResponse
    {
        if (!Gate::allows('viewAny', Organization::class)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $query = Auth::user()->role === config('roles.roles.super_admin')
            ? Organization::with('rootUser')
            : Organization::where('root_user_id', Auth::id())->with('rootUser');

        $organizations = $this->paginateQuery($request, $query);

        return response()->json($organizations);
    }

    /**
     * List soft-deleted organizations.
     * 
     * @param Request $request The request with pagination parameters
     * @return JsonResponse Paginated list of soft-deleted organizations
     */
    public function trashed(Request $request): JsonResponse
    {
        if (!Gate::allows('viewAny', Organization::class)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $query = Auth::user()->role === config('roles.roles.super_admin')
            ? Organization::onlyTrashed()->with('rootUser')
            : Organization::onlyTrashed()
                ->where('root_user_id', Auth::id())
                ->with('rootUser');

        $organizations = $this->paginateQuery($request, $query);

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

    /**
     * Restore a soft-deleted organization.
     * 
     * @param string $id The ID of the organization to restore
     * @param RestoreOrganizationRequest $request The validated restore request
     * @return JsonResponse The restored organization resource
     */
    public function restore(string $id, RestoreOrganizationRequest $request): JsonResponse
    {
        $organization = Organization::onlyTrashed()->findOrFail($id);

        if (!Gate::allows('restore', $organization)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $organization->restore();
        return response()->json($organization);
    }

    /**
     * Permanently delete a soft-deleted organization.
     * 
     * @param string $id The ID of the organization to permanently delete
     * @return JsonResponse Empty response with 204 status code
     */
    public function forceDelete(string $id): JsonResponse
    {
        $organization = Organization::onlyTrashed()->findOrFail($id);

        if (!Gate::allows('forceDelete', $organization)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $organization->forceDelete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}