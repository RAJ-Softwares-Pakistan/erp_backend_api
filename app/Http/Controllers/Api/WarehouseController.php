<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Models\Warehouse;
use App\Traits\HasPaginatedList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class WarehouseController extends Controller
{
    use HasPaginatedList;

    /**
     * List of warehouses.
     * 
     * 
     * @param Request $request The request with pagination parameters
     * @throws AuthorizationException If user doesn't have permission to view warehouses
     * @return JsonResponse Paginated list of warehouses
     */
    public function index(Request $request): JsonResponse
    {
        if (!Gate::allows('viewAny', Warehouse::class)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $query = Auth::user()->role === config('roles.roles.super_admin')
            ? Warehouse::with('organization')
            : Warehouse::with('organization')->where('organization_id', Auth::user()->organization_id);

        $warehouses = $this->paginateQuery($request, $query);

        return response()->json($warehouses);
    }

    /**
     * Create a new warehouse
     * 
     * @param StoreWarehouseRequest $request The validated warehouse creation request
     * @throws AuthorizationException If user doesn't have permission to create warehouses
     * @return JsonResponse The created warehouse resource
     */
    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        if (!Gate::allows('create', Warehouse::class)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
        
        $warehouse = Warehouse::create([
            ...$request->validated(),
            'organization_id' => Auth::user()->organization_id
        ]);

        return response()->json($warehouse, Response::HTTP_CREATED);
    }

    /**
     * Display warehouse details.
     * 
     * @param Warehouse $warehouse The warehouse to display
     * @throws AuthorizationException If user doesn't have permission to view the warehouse
     * @return JsonResponse The warehouse resource with its organization
     */
    public function show(Warehouse $warehouse): JsonResponse
    {
        if (!Gate::allows('view', $warehouse)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        return response()->json($warehouse->load('organization'));
    }

    /**
     * Update warehouse information.
     * 
     * @param UpdateWarehouseRequest $request The validated warehouse update request
     * @param Warehouse $warehouse The warehouse to update
     * @throws AuthorizationException If user doesn't have permission to update the warehouse
     * @return JsonResponse The updated warehouse resource
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): JsonResponse
    {
        if (!Gate::allows('update', $warehouse)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $warehouse->update($request->validated());
        return response()->json($warehouse);
    }

    /**
     * Soft delete warehouse.
     * 
     * @param Warehouse $warehouse The warehouse to delete
     * @throws AuthorizationException If user doesn't have permission to delete the warehouse
     * @return JsonResponse Empty response with 204 status code
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        if (!Gate::allows('delete', $warehouse)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $warehouse->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * List soft-deleted warehouses.
     * 
     * @param Request $request The request with pagination parameters
     * @throws AuthorizationException If user doesn't have permission to view warehouses
     * @return JsonResponse Paginated list of soft-deleted warehouses
     */
    public function trashed(Request $request): JsonResponse
    {
        if (!Gate::allows('viewAny', Warehouse::class)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $query = Auth::user()->role === config('roles.roles.super_admin')
            ? Warehouse::onlyTrashed()->with('organization')
            : Warehouse::onlyTrashed()->with('organization')
                ->where('organization_id', Auth::user()->organization_id);

        $warehouses = $this->paginateQuery($request, $query);

        return response()->json($warehouses);
    }

    /**
     * Restore a soft-deleted warehouse.
     * 
     * @param string $id The ID of the warehouse to restore
     * @throws AuthorizationException If user doesn't have permission to restore the warehouse
     * @return JsonResponse The restored warehouse resource
     */
    public function restore(string $id): JsonResponse
    {
        $warehouse = Warehouse::onlyTrashed()->findOrFail($id);

        if (!Gate::allows('restore', $warehouse)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $warehouse->restore();
        return response()->json($warehouse);
    }

    /**
     * Permanently delete a soft-deleted warehouse.
     * 
     * @param string $id The ID of the warehouse to permanently delete
     * @throws AuthorizationException If user doesn't have permission to force delete the warehouse
     * @return JsonResponse Empty response with 204 status code
     */
    public function forceDelete(string $id): JsonResponse
    {
        $warehouse = Warehouse::onlyTrashed()->findOrFail($id);

        if (!Gate::allows('forceDelete', $warehouse)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $warehouse->forceDelete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
