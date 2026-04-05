<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationRequest extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}