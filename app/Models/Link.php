<?php

namespace App\Models;

use App\Enums\LinkTypeEnum;
use Database\Factories\LinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'destination',
    'short_code',
    'type',
    'creator_ip',
    'is_public_stats',
])]
class Link extends Model
{
    /** @use HasFactory<LinkFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'type' => 'link',
        'is_public_stats' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => LinkTypeEnum::class,
            'is_public_stats' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'short_code';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(LinkVisit::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
