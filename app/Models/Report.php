<?php

namespace App\Models;

use App\Enums\ReportStatusEnum;
use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'link_id',
    'tracking_code',
    'reason',
    'status',
    'reporter_ip',
    'admin_note',
    'reviewed_by',
    'reviewed_at',
])]
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ReportStatusEnum::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class)->withTrashed();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function generateTrackingCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (static::query()->where('tracking_code', $code)->exists());

        return $code;
    }
}
