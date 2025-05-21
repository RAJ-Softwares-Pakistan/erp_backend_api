<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'organization_id',
        'name',
        'location'
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
            'location'
        ];
    }

    /**
     * Get the organization that owns the warehouse.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }
}
