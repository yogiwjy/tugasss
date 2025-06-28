<?php
// File: app/Models/Queue.php
// PERBAIKAN LENGKAP untuk Queue Model

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Queue extends Model
{
    protected $fillable = [
        'counter_id',
        'service_id',
        'user_id',
        'number',
        'status',
        'called_at',
        'served_at',
        'canceled_at',
        'finished_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'served_at' => 'datetime', 
        'canceled_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    // ✅ RELATIONSHIP YANG BENAR
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }

    // ✅ ACCESSOR METHODS YANG BENAR

    /**
     * Get status badge color untuk UI
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'waiting' => 'warning',
            'serving' => 'info', 
            'finished' => 'success',
            'canceled' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get status dalam bahasa Indonesia
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'waiting' => 'Menunggu',
            'serving' => 'Sedang Dilayani',
            'finished' => 'Selesai',
            'canceled' => 'Dibatalkan',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get tanggal antrian dalam format yang mudah dibaca
     */
    public function getFormattedTanggalAttribute(): string
    {
        return $this->created_at->format('d F Y');
    }

    // ✅ HELPER METHODS

    /**
     * Check apakah antrian bisa diedit
     */
    public function canEdit(): bool
    {
        return in_array($this->status, ['waiting']);
    }

    /**
     * Check apakah antrian bisa dibatalkan
     */
    public function canCancel(): bool
    {
        return in_array($this->status, ['waiting']);
    }

    /**
     * Check apakah antrian bisa diprint
     */
    public function canPrint(): bool
    {
        return in_array($this->status, ['waiting', 'serving', 'finished']);
    }

    /**
     * Check apakah antrian sudah selesai atau dibatalkan
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['finished', 'canceled']);
    }

    // ✅ SCOPE METHODS

    /**
     * Scope untuk antrian hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope untuk antrian berdasarkan user tertentu
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk antrian berdasarkan service/poli
     */
    public function scopeForService($query, $serviceName)
    {
        return $query->whereHas('service', function($q) use ($serviceName) {
            $q->where('name', $serviceName);
        });
    }

    /**
     * Scope untuk antrian berdasarkan status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}