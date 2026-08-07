<?php

namespace App\Models;

use Database\Factories\LinkVisitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'link_id',
    'ip_address',
    'user_agent',
    'device_type',
    'browser',
    'os',
])]
class LinkVisit extends Model
{
    /** @use HasFactory<LinkVisitFactory> */
    use HasFactory;

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }
}
