<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

trait HasPaginatedList
{
    /**
     * Apply pagination parameters to a query
     */
    protected function paginateQuery(Request $request, Builder $query): LengthAwarePaginator
    {
        // Get pagination parameters with defaults
        $perPage = min($request->input('per_page', 15), 100);
        $page = $request->input('page', 1);

        // Handle sorting
        $sortField = $request->input('sort_by');
        $sortDirection = strtolower($request->input('sort_order', 'asc'));
        
        if ($sortField) {
            if (!in_array($sortDirection, ['asc', 'desc'])) {
                $sortDirection = 'asc';
            }
            $query->orderBy($sortField, $sortDirection);
        }

        // Handle search
        $search = $request->input('search');
        if ($search && method_exists($query->getModel(), 'getSearchableFields')) {
            $fields = $query->getModel()->getSearchableFields();
            $query->where(function($q) use ($fields, $search) {
                foreach ($fields as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}
