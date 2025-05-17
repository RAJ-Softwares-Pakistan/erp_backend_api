<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class VendorController extends Controller
{
    /**
     * List of vendors.
     * 
     * @throws AuthorizationException If user doesn't have permission to view vendors
     * @return JsonResponse Paginated list of vendors with their organization details
     */
    public function index(): JsonResponse
    {
        if (!Gate::allows('viewAny', Vendor::class)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $vendors = Auth::user()->role === config('roles.roles.super_admin')
            ? Vendor::paginate()
            : Vendor::where('organization_id', Auth::user()->organization_id)->paginate();

        return response()->json($vendors);
    }

    /**
     * Create a new vendor
     * 
     * @param StoreVendorRequest $request The validated vendor creation request
     * @throws AuthorizationException If user doesn't have permission to create vendors
     * @return JsonResponse The created vendor resource
     */
    public function store(StoreVendorRequest $request): JsonResponse
    {
        if (!Gate::allows('create', Vendor::class)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
        
        $vendor = Vendor::create([
            ...$request->validated(),
            'organization_id' => Auth::user()->organization_id
        ]);

        return response()->json($vendor, Response::HTTP_CREATED);
    }

    /**
     * Display vendor.
     * 
     * @param Vendor $vendor The vendor to display
     * @throws AuthorizationException If user doesn't have permission to view the vendor
     * @return JsonResponse The vendor resource with its organization
     */
    public function show(Vendor $vendor): JsonResponse
    {
        if (!Gate::allows('view', $vendor)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        return response()->json($vendor->load('organization'));
    }

    /**
     * Update vendor's information.
     * 
     * @param UpdateVendorRequest $request The validated vendor update request
     * @param Vendor $vendor The vendor to update
     * @throws AuthorizationException If user doesn't have permission to update the vendor
     * @return JsonResponse The updated vendor resource
     */
    public function update(UpdateVendorRequest $request, Vendor $vendor): JsonResponse
    {
        if (!Gate::allows('update', $vendor)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $vendor->update($request->validated());
        return response()->json($vendor);
    }

    /**
     * Remove vendor.
     * 
     * @param Vendor $vendor The vendor to delete
     * @throws AuthorizationException If user doesn't have permission to delete the vendor
     * @return JsonResponse Empty response with 204 status code
     */
    public function destroy(Vendor $vendor): JsonResponse
    {
        if (!Gate::allows('delete', $vendor)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $vendor->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}