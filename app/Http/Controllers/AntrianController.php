<?php
// File: app/Http/Controllers/AntrianController.php
// PERBAIKAN: Simpan doctor_id saat buat antrian

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Service; 
use App\Models\User;
use App\Models\DoctorSchedule;  // ✅ TAMBAH IMPORT
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AntrianController extends Controller
{
    /**
     * Dashboard antrian user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // ✅ TAMBAH: Load doctor schedule relationship
        $antrianTerbaru = Queue::with(['service', 'counter', 'user', 'doctorSchedule'])
                              ->where('user_id', $user->id)
                              ->latest()
                              ->first();

        return view('antrian.index', compact('antrianTerbaru'));
    }

    /**
     * Form buat antrian baru
     */
    public function create()
    {
        $user = Auth::user();
        
        // ✅ SUDAH BENAR
        $existingQueue = Queue::where('user_id', $user->id)
                             ->whereIn('status', ['waiting', 'serving'])
                             ->whereDate('created_at', today())
                             ->first();

        if ($existingQueue) {
            return redirect()->route('antrian.index')->withErrors([
                'error' => 'Anda masih memiliki antrian aktif. Harap selesaikan antrian tersebut terlebih dahulu.'
            ]);
        }

        $services = Service::where('is_active', true)->get();
        
        // ✅ PERBAIKAN: Ambil dari DoctorSchedule yang aktif
        $doctors = DoctorSchedule::where('is_active', true)
                                ->with('service')
                                ->get();
        
        return view('antrian.ambil', compact('services', 'doctors'));
    }

    /**
     * Simpan antrian baru
     */
    public function store(Request $request)
    {        
        // ✅ PERBAIKAN: Tambah validasi doctor_id
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'doctor_id' => 'nullable|exists:doctor_schedules,id',
        ], [
            'service_id.required' => 'Layanan harus dipilih',
            'doctor_id.exists' => 'Dokter yang dipilih tidak valid',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // ✅ SUDAH BENAR
            $existingQueue = Queue::where('user_id', $user->id)
                                 ->whereIn('status', ['waiting', 'serving'])
                                 ->whereDate('created_at', today())
                                 ->first();

            if ($existingQueue) {
                DB::rollBack();
                return back()->withErrors([
                    'error' => 'Anda sudah memiliki antrian aktif hari ini.'
                ])->withInput();
            }

            // ✅ SUDAH BENAR
            $queueNumber = $this->generateQueueNumber($request->service_id);

            // ✅ PERBAIKAN: Tambah doctor_id ke data yang disimpan
            $queueData = [
                'service_id' => $request->service_id,
                'user_id' => $user->id,
                'doctor_id' => $request->doctor_id, // ✅ TAMBAH INI
                'number' => $queueNumber,
                'status' => 'waiting',
            ];

            $queue = Queue::create($queueData);

            DB::commit();

            return redirect()->route('antrian.index')->with('success', 
                'Antrian berhasil dibuat! Nomor antrian Anda: ' . $queueNumber
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat membuat antrian: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Lihat detail antrian
     */
    public function show($id)
    {
        // ✅ TAMBAH: Load doctor schedule relationship
        $queue = Queue::with(['service', 'counter', 'user', 'doctorSchedule'])->findOrFail($id);
        
        if ($queue->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('antrian.show', compact('queue'));
    }

    /**
     * Edit antrian
     */
    public function edit($id)
    {
        // ✅ TAMBAH: Load doctor schedule relationship
        $queue = Queue::with(['service', 'counter', 'user', 'doctorSchedule'])->findOrFail($id);
        
        if ($queue->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!in_array($queue->status, ['waiting'])) {
            return redirect()->route('antrian.index')
                           ->withErrors(['error' => 'Antrian tidak dapat diedit karena sudah dipanggil atau selesai.']);
        }

        $services = Service::where('is_active', true)->get();
        
        // ✅ PERBAIKAN: Ambil dari DoctorSchedule yang aktif
        $doctors = DoctorSchedule::where('is_active', true)
                                ->with('service')
                                ->get();
        
        return view('antrian.edit', compact('queue', 'services', 'doctors'));
    }

    /**
     * Update antrian
     */
    public function update(Request $request, $id)
    {
        $queue = Queue::findOrFail($id);
        
        if ($queue->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!in_array($queue->status, ['waiting'])) {
            return redirect()->route('antrian.index')
                           ->withErrors(['error' => 'Antrian tidak dapat diubah karena sudah dipanggil atau selesai.']);
        }

        $request->validate([
            'service_id' => 'required|exists:services,id',
            'doctor_id' => 'nullable|exists:doctor_schedules,id',
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'service_id' => $request->service_id,
                'doctor_id' => $request->doctor_id,  // ✅ TAMBAH INI
            ];
            
            if ($queue->service_id != $request->service_id) {
                $updateData['number'] = $this->generateQueueNumber($request->service_id);
            }

            $queue->update($updateData);

            DB::commit();

            return redirect()->route('antrian.index')->with('success', 'Antrian berhasil diubah!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat mengubah antrian.'])->withInput();
        }
    }

    /**
     * Batalkan antrian - SESUAI ROUTE YANG ADA
     */
    public function cancel($id) // ✅ Route: DELETE /cancel/{queue}
    {
        $queue = Queue::findOrFail($id);
        
        if ($queue->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!in_array($queue->status, ['waiting'])) {
            return redirect()->route('antrian.index')
                           ->withErrors(['error' => 'Antrian tidak dapat dibatalkan karena sudah dipanggil atau selesai.']);
        }

        try {
            $queue->update([
                'status' => 'canceled',
                'canceled_at' => now()
            ]);
            
            return redirect()->route('antrian.index')->with('success', 'Antrian berhasil dibatalkan!');
            
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat membatalkan antrian.'
            ]);
        }
    }

    /**
     * Print ticket - SESUAI ROUTE DAN VIEW YANG ADA
     */
    public function ticket($id) // ✅ Route: GET /ticket/{queue}
    {
        // ✅ TAMBAH: Load doctor schedule relationship
        $queue = Queue::with(['service', 'counter', 'user', 'doctorSchedule'])->findOrFail($id);
        
        if ($queue->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // ✅ BENAR - menggunakan view print yang sudah ada
        return view('antrian.print', compact('queue'));
    }

    /**
     * ⚠️ TAMBAHAN - Method untuk destroy jika diperlukan
     */
    public function destroy($id)
    {
        // Redirect ke cancel method
        return $this->cancel($id);
    }

    /**
     * Generate Queue Number
     */
    private function generateQueueNumber($serviceId)
    {
        $service = Service::findOrFail($serviceId);
        
        $lastQueue = Queue::where('service_id', $serviceId)
                         ->whereDate('created_at', today())
                         ->orderBy('id', 'desc')
                         ->first();
        
        $sequence = $lastQueue ? 
                   (int) substr($lastQueue->number, strlen($service->prefix)) + 1 : 1;
        
        return $service->prefix . sprintf('%0' . $service->padding . 'd', $sequence);
    }
}