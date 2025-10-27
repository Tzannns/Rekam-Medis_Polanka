<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Datapasien;
use App\Models\Antrian;
use App\Models\Jadwalpoliklinik;
use App\Models\RiwayatKunjungan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PetugasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:petugas');
    }

    public function index()
    {
        // Get today's date
        $today = Carbon::today()->format('Y-m-d');
        
        // Get real data for dashboard
        $totalAntrian = Antrian::whereDate('tanggal_berobat', $today)->count();
        
        // Count processed and served patients based on status
        $dilayani = Antrian::whereDate('tanggal_berobat', $today)
            ->where('status', 'dilayani')
            ->count();
            
        $diproses = Antrian::whereDate('tanggal_berobat', $today)
            ->where('status', 'diproses')
            ->count();
            
        $menunggu = Antrian::whereDate('tanggal_berobat', $today)
            ->where('status', 'menunggu')
            ->count();
        
        // Total registered patients
        $totalPasien = Datapasien::count();

        // Get today's queue data - ONLY SHOW WAITING AND PROCESSING PATIENTS
        $antrianToday = Antrian::whereDate('tanggal_berobat', $today)
            ->whereIn('status', ['menunggu', 'diproses'])
            ->orderBy('no_antrian', 'asc')
            ->limit(10)
            ->get();

        // Format the data for the view
        $antrian = [];
        foreach ($antrianToday as $item) {
            $status = $item->status;
            $statusLabel = ucfirst($status);
            
            $antrian[] = [
                'id' => $item->id,
                'no_antrian' => $item->no_antrian,
                'nama_pasien' => $item->nama_pasien,
                'poli' => $item->poliklinik,
                'dokter' => $item->nama_dokter,
                'status' => $statusLabel
            ];
        }

        // Get today's COMPLETED appointments for display in a separate section
        $antrianSelesai = Antrian::whereDate('tanggal_berobat', $today)
            ->where('status', 'dilayani')
            ->orderBy('waktu_selesai', 'desc') // Order by waktu_selesai instead of updated_at
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'no_antrian' => $item->no_antrian,
                    'nama_pasien' => $item->nama_pasien,
                    'poli' => $item->poliklinik,
                    'dokter' => $item->nama_dokter,
                    'waktu_selesai' => $item->waktu_selesai ? Carbon::parse($item->waktu_selesai)->format('H:i:s') : $item->updated_at->format('H:i:s'),
                    'status' => ucfirst($item->status)
                ];
            });

        return view('dashboardpetugas', compact(
            'totalAntrian', 'dilayani', 'diproses', 'menunggu', 
            'totalPasien', 'antrian', 'antrianSelesai'
        ));
    }

    public function daftarPasienBaru()
    {
        return view('petugas.daftar-pasien');
    }

    public function antrianHariIni()
    {
        $today = Carbon::today()->format('Y-m-d');
        
        // Only get active patients (menunggu or diproses)
        $antrianData = Antrian::whereDate('tanggal_berobat', $today)
            ->whereIn('status', ['menunggu', 'diproses'])
            ->orderBy('no_antrian', 'asc')
            ->get();

        // Convert to array format for compatibility with array_filter
        $antrian = $antrianData->map(function($item) {
            return [
                'id' => $item->id,
                'no_antrian' => $item->no_antrian,
                'nama_pasien' => $item->nama_pasien,
                'poli' => $item->poliklinik,
                'dokter' => $item->nama_dokter,
                'status' => ucfirst($item->status)
            ];
        })->toArray(); // Convert to array for array_filter compatibility

        // Get recently completed patients to display
        $antrianSelesaiData = Antrian::whereDate('tanggal_berobat', $today)
            ->where('status', 'dilayani')
            ->orderBy('waktu_selesai', 'desc') // Order by waktu_selesai instead of updated_at
            ->limit(5)
            ->get();

        $antrianSelesai = $antrianSelesaiData->map(function($item) {
            return [
                'id' => $item->id,
                'no_antrian' => $item->no_antrian,
                'nama_pasien' => $item->nama_pasien,
                'poli' => $item->poliklinik,
                'dokter' => $item->nama_dokter,
                'waktu_selesai' => $item->waktu_selesai ? Carbon::parse($item->waktu_selesai)->format('H:i:s') : $item->updated_at->format('H:i:s'),
                'status' => ucfirst($item->status)
            ];
        })->toArray(); // Convert to array

        return view('petugas.antrian', compact('antrian', 'antrianSelesai'));
    }

    public function riwayatAntrian(Request $request)
    {
        // Get date from request or use today's date
        $date = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : Carbon::today()->format('Y-m-d');
        
        // Get completed patients for the selected date
        $riwayat = Antrian::whereDate('tanggal_berobat', $date)
            ->where('status', 'dilayani')
            ->orderBy('no_antrian', 'asc')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'no_antrian' => $item->no_antrian,
                    'nama_pasien' => $item->nama_pasien,
                    'poli' => $item->poliklinik,
                    'dokter' => $item->nama_dokter,
                    'waktu_selesai' => $item->waktu_selesai ? Carbon::parse($item->waktu_selesai)->format('H:i:s') : $item->updated_at->format('H:i:s'),
                    'status' => $item->status
                ];
            });

        return view('petugas.riwayat-antrian', compact('riwayat', 'date'));
    }

    public function rekamMedis($id = null)
    {
        if ($id) {
            // Show specific medical record
            return view('petugas.rekam-medis-detail');
        }

        // Get search parameters
        $search = request('search');
        $date = request('date', date('Y-m-d'));
        $poliklinikId = request('poliklinik');

        // Base query
        $query = Antrian::query()
            ->join('datapasien', 'antrian.id_pasien', '=', 'datapasien.id')
            ->join('jadwalpoliklinik', 'antrian.jadwalpoliklinik_id', '=', 'jadwalpoliklinik.id')
            ->join('poliklinik', 'jadwalpoliklinik.poliklinik_id', '=', 'poliklinik.id')
            ->join('dokter', 'jadwalpoliklinik.dokter_id', '=', 'dokter.id')
            ->select([
                'antrian.id',
                'antrian.tanggal_berobat',
                'datapasien.no_kberobat as no_berobat',
                'datapasien.nama_pasien',
                'poliklinik.nama_poliklinik as poliklinik',
                'dokter.nama_dokter as dokter',
                'antrian.status'
            ]);

        // Apply filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('datapasien.nama_pasien', 'like', "%{$search}%")
                  ->orWhere('datapasien.no_berobat', 'like', "%{$search}%");
            });
        }

        if ($date) {
            $query->whereDate('antrian.tanggal_berobat', $date);
        }

        if ($poliklinikId) {
            $query->where('poliklinik.id', $poliklinikId);
        }

        // Get results
        $rekamMedis = $query->orderBy('antrian.tanggal_berobat', 'desc')
                           ->orderBy('antrian.id', 'desc')
                           ->paginate(25);

        // Show list of medical records
        return view('petugas.rekam-medis-list', compact('rekamMedis'));
    }

    public function prosesAntrian($id)
    {
        try {
            DB::beginTransaction();
            
            $antrian = Antrian::findOrFail($id);
            $antrian->status = 'diproses';
            $antrian->waktu_mulai = now(); // Record the time when processing starts
            $antrian->save();
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Pasien sedang diproses');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses antrian: ' . $e->getMessage());
        }
    }
    
    public function selesaiAntrian($id)
    {
        try {
            DB::beginTransaction();
            
            $antrian = Antrian::findOrFail($id);
            $antrian->status = 'dilayani';
            $antrian->waktu_selesai = now(); // Record time when processing ends
            $antrian->save();
            
            // Create a visit history record
            $waktuMulai = $antrian->waktu_mulai ?? $antrian->created_at; // Use recorded start time or fallback
            $waktuSelesai = $antrian->waktu_selesai;
            $durasiPelayanan = $waktuMulai->diffInMinutes($waktuSelesai);
            
            // Make sure we have values for the poliklinik_id and dokter_id
            $jadwal = $antrian->jadwalpoliklinik;
            $poliklinik_id = null;
            if ($jadwal && $jadwal->poliklinik) {
                $poliklinik_id = $jadwal->poliklinik_id;
            } elseif ($antrian->dokter && $antrian->dokter->poliklinik) {
                $poliklinik_id = $antrian->dokter->poliklinik_id;
            }
            
            // Ensure dokter_id is properly set
            $dokter_id = $antrian->dokter_id;
            
            // Log debugging information
            Log::info('Completing appointment', [
                'antrian_id' => $antrian->id,
                'pasien_id' => $antrian->id_pasien,
                'dokter_id' => $dokter_id,
                'poliklinik_id' => $poliklinik_id
            ]);
            
            // Create the visit record with all necessary data
            RiwayatKunjungan::create([
                'antrian_id' => $antrian->id,
                'pasien_id' => $antrian->id_pasien,
                'dokter_id' => $dokter_id,
                'poliklinik_id' => $poliklinik_id,
                'kode_kunjungan' => RiwayatKunjungan::generateKodeKunjungan(),
                'no_antrian' => $antrian->no_antrian,
                'nama_pasien' => $antrian->nama_pasien,
                'nama_dokter' => $antrian->nama_dokter,
                'poliklinik' => $antrian->poliklinik,
                'tanggal_kunjungan' => $antrian->tanggal_berobat,
                'waktu_mulai' => $waktuMulai,
                'waktu_selesai' => $waktuSelesai,
                'durasi_pelayanan' => $durasiPelayanan,
                'status' => 'dilayani',
                'penjamin' => $antrian->penjamin,
                'catatan' => null // Add a default empty value for catatan
            ]);
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Pasien telah selesai dilayani dan riwayat kunjungan telah dicatat');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete appointment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Gagal menyelesaikan antrian: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

