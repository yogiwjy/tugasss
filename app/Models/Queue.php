<?php
// File: app/Models/Queue.php
// FIX: Remove undefined doctor relationship

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Queue extends Model
{
    protected $fillable = [
        'counter_id',
        'service_id',
        'user_id', // ✅ CORRECT: user_id bukan patient_id atau doctor_id
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

    // ✅ CORRECT: Relationship yang sesuai dengan struktur database
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }

    // ✅ CORRECT: Relationship ke user (pasien)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ✅ REMOVED: Hapus relationship doctor karena tidak ada doctor_id di queues table
    // public function doctor(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'doctor_id');
    // }

    // ✅ CORRECT: Relationship ke medical record
    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }

    // ✅ ADDED: Helper method untuk mendapatkan dokter dari medical record (jika ada)
    public function getDoctorAttribute()
    {
        // Jika ada medical record, ambil dokter dari sana
        if ($this->medicalRecord && $this->medicalRecord->doctor) {
            return $this->medicalRecord->doctor;
        }
        
        // Jika tidak ada medical record, return null
        return null;
    }

    // ✅ ADDED: Helper method untuk mendapatkan nama dokter
    public function getDoctorNameAttribute(): ?string
    {
        if ($this->doctor) {
            return $this->doctor->name;
        }
        
        return null;
    }

    // Helper methods untuk status
    public function canEdit(): bool
    {
        return in_array($this->status, ['waiting']);
    }

    public function canCancel(): bool
    {
        return in_array($this->status, ['waiting']);
    }

    public function canPrint(): bool
    {
        return in_array($this->status, ['waiting', 'serving', 'finished']);
    }

    // Accessor untuk format tanggal
    public function getFormattedTanggalAttribute(): string
    {
        return $this->created_at->format('d F Y');
    }

    // Accessor untuk status badge
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
}