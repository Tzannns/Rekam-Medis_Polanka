<?php

namespace App\Http\Controllers\DataTables;

use App\Http\Controllers\Controller;
use App\Models\RiwayatKunjungan;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class RiwayatKunjunganDataTableController extends Controller
{
    /**
     * Display a listing of visit history with DataTables.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $riwayatKunjungans = RiwayatKunjungan::with(['pasien', 'dokter', 'poliklinik'])
                ->select(['id', 'kode_kunjungan', 'no_antrian', 'nama_pasien', 'nama_dokter', 'poliklinik', 'tanggal_kunjungan', 'waktu_mulai', 'waktu_selesai', 'durasi_pelayanan', 'status', 'penjamin', 'created_at']);
            
            return DataTables::of($riwayatKunjungans)
                ->addIndexColumn()
                ->addColumn('action', function ($riwayat) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="#" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</a>';
                    $btn .= '<a href="#" class="btn btn-sm btn-primary"><i class="fas fa-print"></i> Print</a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('status_badge', function ($riwayat) {
                    $badgeClass = match($riwayat->status) {
                        'selesai' => 'badge-success',
                        'berlangsung' => 'badge-info',
                        'batal' => 'badge-danger',
                        default => 'badge-secondary'
                    };
                    return '<span class="badge ' . $badgeClass . '">' . ucfirst($riwayat->status) . '</span>';
                })
                ->addColumn('durasi_formatted', function ($riwayat) {
                    if ($riwayat->durasi_pelayanan) {
                        $hours = floor($riwayat->durasi_pelayanan / 60);
                        $minutes = $riwayat->durasi_pelayanan % 60;
                        return $hours . 'h ' . $minutes . 'm';
                    }
                    return '-';
                })
                ->editColumn('tanggal_kunjungan', function ($riwayat) {
                    return $riwayat->tanggal_kunjungan ? \Carbon\Carbon::parse($riwayat->tanggal_kunjungan)->format('d/m/Y') : '-';
                })
                ->editColumn('waktu_mulai', function ($riwayat) {
                    return $riwayat->waktu_mulai ? \Carbon\Carbon::parse($riwayat->waktu_mulai)->format('H:i') : '-';
                })
                ->editColumn('waktu_selesai', function ($riwayat) {
                    return $riwayat->waktu_selesai ? \Carbon\Carbon::parse($riwayat->waktu_selesai)->format('H:i') : '-';
                })
                ->editColumn('created_at', function ($riwayat) {
                    return $riwayat->created_at->format('d/m/Y H:i');
                })
                ->rawColumns(['action', 'status_badge'])
                ->make(true);
        }

        return view('riwayat.datatable');
    }
}