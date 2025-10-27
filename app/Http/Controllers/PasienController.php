<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Datapasien;
use App\Models\Antrian;
use App\Models\jadwalpoliklinik;
use App\Models\RiwayatKunjungan;
use App\Models\Dokter;
use App\Models\Poliklinik;
use App\Models\Rating; // Add this import
use Carbon\Carbon;

class PasienController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        // Get patient data for the current user if it exists
        $dataPasien = null;
        if ($user) {
            $dataPasien = Datapasien::where('user_id', $user->id)->first();
        }
        
        // Initialize dashboard data
        $dashboardData = [
            'totalKunjungan' => 0,
            'totalResep' => 0,
            'jadwalBerikutnya' => null,
            'upcomingAppointments' => [],
        ];
        
        // If patient data exists, fetch related information
        if ($dataPasien) {
            // Count total visits (completed appointments)
            $dashboardData['totalKunjungan'] = Antrian::where('id_pasien', $dataPasien->id)
                ->where('status', 'dilayani')
                ->count();
            
            // Count total prescriptions (this is an example - adjust based on your database structure)
            // Assuming prescriptions are related to completed appointments
            $dashboardData['totalResep'] = Antrian::where('id_pasien', $dataPasien->id)
                ->where('status', 'dilayani')
                ->count();
                
            // Get next upcoming appointment
            $nextAppointment = Antrian::where('id_pasien', $dataPasien->id)
                ->whereIn('status', ['menunggu', 'diproses'])
                ->orderBy('tanggal_berobat', 'asc')
                ->first();
                
            $dashboardData['jadwalBerikutnya'] = $nextAppointment;
            
            // Get all upcoming appointments
            $dashboardData['upcomingAppointments'] = Antrian::where('id_pasien', $dataPasien->id)
                ->whereIn('status', ['menunggu', 'diproses'])
                ->orderBy('tanggal_berobat', 'asc')
                ->get();
        }
        
        // Get available jadwal poliklinik for the next 7 days
        $today = Carbon::today();
        $nextWeek = Carbon::today()->addDays(7);
        
        $availableSchedules = jadwalpoliklinik::whereBetween('tanggal_praktek', [$today, $nextWeek])
            ->where('jumlah', '>', 0)
            ->with(['dokter', 'poliklinik'])
            ->orderBy('tanggal_praktek', 'asc')
            ->get();
            
        $dashboardData['availableSchedules'] = $availableSchedules;

        return view('dashboardpasien', compact('dataPasien', 'dashboardData'));
    }

    public function jadwalPeriksa()
    {
        $user = Auth::user();
        $datapasien = Datapasien::where('user_id', $user->id)->first();
        
        // Get available schedule for the next 7 days
        $today = Carbon::today();
        $nextWeek = Carbon::today()->addDays(7);
        
        $jadwalPoliklinik = jadwalpoliklinik::whereBetween('tanggal_praktek', [$today, $nextWeek])
            ->where('jumlah', '>', 0)
            ->with(['dokter', 'poliklinik'])
            ->orderBy('tanggal_praktek', 'asc')
            ->get();
            
        $dokters = Dokter::with('poliklinik')->get();
        $polikliniks = Poliklinik::all();
        
        return view('pasien.jadwal-periksa', compact('jadwalPoliklinik', 'dokters', 'polikliniks'));
    }

    public function riwayatPeriksa()
    {
        $user = Auth::user();
        $datapasien = Datapasien::where('user_id', $user->id)->first();
        
        if (!$datapasien) {
            return redirect()->route('pasien.create')->with('error', 'Harap lengkapi data pasien terlebih dahulu');
        }
        
        // Get all completed appointments for this patient
        $riwayat = Antrian::where('id_pasien', $datapasien->id)
            ->where('status', 'dilayani')
            ->orderBy('tanggal_berobat', 'desc')
            ->get();
        
        // Check for ratings and attach to riwayat
        foreach ($riwayat as $item) {
            $item->rating = Rating::where('dokter_id', $item->dokter_id)
                ->where('user_id', $user->id)
                ->first();
        }
        
        // Get associated medical records if any
        $riwayatKunjungan = RiwayatKunjungan::where('pasien_id', $datapasien->id)
            ->orderBy('tanggal_kunjungan', 'desc')
            ->get();
            
        return view('pasien.riwayat-periksa', compact('riwayat', 'riwayatKunjungan'));
    }

    public function profil()
    {
        $profil = [
            'nama' => 'John Doe',
            'no_rm' => 'RM-2023-001',
            'tanggal_lahir' => '1990-01-01',
            'alamat' => 'Jl. Contoh No. 123',
            'no_telp' => '08123456789'
        ];

        return view('pasien.profil', compact('profil'));
    }

    public function riwayatAntrian()
    {
        $userId = Auth::id();
        
        // Get current active appointments for this patient
        $antrianAktif = Antrian::where('user_id', $userId)
            ->whereIn('status', ['menunggu', 'diproses'])
            ->orderBy('tanggal_berobat', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'no_antrian' => $item->no_antrian,
                    'poli' => $item->poliklinik,
                    'dokter' => $item->nama_dokter,
                    'tanggal' => $item->tanggal_berobat->format('d/m/Y'),
                    'status' => $item->status
                ];
            });
        
        // Get appointment history for this patient
        $riwayatAntrian = Antrian::where('user_id', $userId)
            ->where('status', 'dilayani')
            ->orderBy('tanggal_berobat', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'no_antrian' => $item->no_antrian,
                    'poli' => $item->poliklinik,
                    'dokter' => $item->nama_dokter,
                    'tanggal' => $item->tanggal_berobat->format('d/m/Y'),
                    'waktu_selesai' => $item->updated_at->format('H:i:s'),
                    'status' => $item->status
                ];
            });
        
        return view('pasien.riwayat-antrian', compact('antrianAktif', 'riwayatAntrian'));
    }
}
