<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'organization_id';

    protected $fillable = [
        'root_user_id',
        'name',
        'address',
        'phone',
        'email',
        'logo',
        'website',
        'enable_gst',
        'enable_witholding',
        'ntn_no',
        'currency',
        'industry_type'
    ];

    protected $casts = [
        'enable_gst' => 'boolean',
        'enable_witholding' => 'boolean',
    ];

    public function rootUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'root_user_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'organization_id');
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class, 'organization_id');
    }
}