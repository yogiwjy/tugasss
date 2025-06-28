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
        'doctor_id',  // ✅ TAMBAH doctor_id
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

    // ✅ TAMBAH: Relationship ke DoctorSchedule
    public function doctorSchedule(): BelongsTo
    {
        return $this->belongsTo(DoctorSchedule::class, 'doctor_id');
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }

    // ✅ ACCESSOR METHODS YANG BENAR

    /**
     * Get nama pasien dari user yang terkait
     */
    public function getNameAttribute(): ?string
    {
        return $this->user ? $this->user->name : null;
    }

    /**
     * Get phone pasien dari user yang terkait  
     */
    public function getPhoneAttribute(): ?string
    {
        return $this->user ? $this->user->phone : null;
    }

    /**
     * Get gender pasien dari user yang terkait
     */
    public function getGenderAttribute(): ?string
    {
        return $this->user ? $this->user->gender : null;
    }

    /**
     * Get alamat pasien dari user yang terkait
     */
    public function getAddressAttribute(): ?string
    {
        return $this->user ? $this->user->address : null;
    }

    /**
     * Get nomor KTP pasien dari user yang terkait
     */
    public function getNomorKtpAttribute(): ?string
    {
        return $this->user ? $this->user->nomor_ktp : null;
    }

    /**
     * Get nama layanan/poli dari service yang terkait
     */
    public function getPoliAttribute(): ?string
    {
        return $this->service ? $this->service->name : null;
    }

    /**
     * ✅ PERBAIKAN UTAMA: Get dokter dari doctor_id (prioritas utama) atau medical record
     */
    public function getDoctorAttribute()
    {
        // Prioritas 1: Dari doctor_id yang dipilih saat ambil antrian
        if ($this->doctor_id && $this->doctorSchedule) {
            return (object) [
                'id' => $this->doctorSchedule->id,
                'name' => $this->doctorSchedule->doctor_name,
                'service' => $this->doctorSchedule->service,
            ];
        }
        
        // Prioritas 2: Dari medical record (jika sudah ada rekam medis)
        if ($this->medicalRecord && $this->medicalRecord->doctor) {
            return $this->medicalRecord->doctor;
        }
        
        return null;
    }

    /**
     * ✅ PERBAIKAN UTAMA: Get nama dokter yang dipilih saat antrian atau dari rekam medis
     */
    public function getDoctorNameAttribute(): ?string
    {
        // Prioritas 1: Dari doctor_schedule yang dipilih saat ambil antrian
        if ($this->doctor_id && $this->doctorSchedule) {
            return $this->doctorSchedule->doctor_name;
        }
        
        // Prioritas 2: Dari medical record (jika sudah ada rekam medis)
        if ($this->medicalRecord && $this->medicalRecord->doctor) {
            return $this->medicalRecord->doctor->name;
        }
        
        // Fallback: Belum ditentukan
        return null;
    }

    /**
     * Get tanggal antrian dalam format yang mudah dibaca
     */
    public function getFormattedTanggalAttribute(): string
    {
        return $this->created_at->format('d F Y');
    }

    /**
     * Get tanggal antrian lengkap dengan hari
     */
    public function getTanggalAttribute()
    {
        return $this->created_at;
    }

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