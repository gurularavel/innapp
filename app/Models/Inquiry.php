<?php

namespace App\Models;

use App\Services\Concerns\NormalizesPhone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A lead left through one of the public site forms.
 */
class Inquiry extends Model
{
    use NormalizesPhone;

    public const TYPE_CONTACT = 'contact';
    public const TYPE_DEMO = 'demo';

    public const TYPES = [
        self::TYPE_CONTACT => 'Təqdimat sorğusu',
        self::TYPE_DEMO    => 'Demo (e-poçt)',
    ];

    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';

    public const STATUSES = [
        self::STATUS_NEW         => ['label' => 'Yeni',       'badge' => 'primary'],
        self::STATUS_IN_PROGRESS => ['label' => 'İcrada',     'badge' => 'warning text-dark'],
        self::STATUS_DONE        => ['label' => 'Tamamlanıb', 'badge' => 'success'],
    ];

    protected $fillable = [
        'type',
        'name',
        'email',
        'phone',
        'message',
        'status',
        'admin_note',
        'ip',
        'user_agent',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? $this->status;
    }

    public function getStatusBadgeAttribute(): string
    {
        return self::STATUSES[$this->status]['badge'] ?? 'secondary';
    }

    /** wa.me link for the sender's number, null when there is no phone. */
    public function getWhatsappUrlAttribute(): ?string
    {
        return $this->phone ? 'https://wa.me/' . $this->normalizePhone($this->phone) : null;
    }

    /** Whatever identifies the sender best — the demo form only collects an e-mail. */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: ($this->email ?: ($this->phone ?: '—'));
    }
}
