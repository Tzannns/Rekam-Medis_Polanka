<?php

namespace App\Http\Controllers\DataTables;

use App\Http\Controllers\Controller;
use App\Models\Antrian;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class AntrianDataTableController extends Controller
{
    /**
     * Display a listing of queues with DataTables.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $antrians = Antrian::with(['datapasien', 'dokter', 'poliklinik'])
                ->select(['id', 'datapasien_id', 'dokter_id', 'poliklinik_id', 'no_antrian', 'tanggal_antrian', 'status', 'created_at']);
            
            return DataTables::of($antrians)
                ->addIndexColumn()
                ->addColumn('action', function ($antrian) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('antrian.show', $antrian->id) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</a>';
                    $btn .= '<a href="' . route('antrian.edit', $antrian->id) . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteAntrian(' . $antrian->id . ')"><i class="fas fa-trash"></i> Delete</button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('nama_pasien', function ($antrian) {
                    return $antrian->datapasien ? $antrian->datapasien->nama_pasien : '-';
                })
                ->addColumn('nama_dokter', function ($antrian) {
                    return $antrian->dokter ? $antrian->dokter->nama_dokter : '-';
                })
                ->addColumn('nama_poliklinik', function ($antrian) {
                    return $antrian->poliklinik ? $antrian->poliklinik->nama_poliklinik : '-';
                })
                ->addColumn('status_badge', function ($antrian) {
                    $badgeClass = match($antrian->status) {
                        'menunggu' => 'badge-warning',
                        'sedang_dilayani' => 'badge-info',
                        'selesai' => 'badge-success',
                        'batal' => 'badge-danger',
                        default => 'badge-secondary'
                    };
                    return '<span class="badge ' . $badgeClass . '">' . ucfirst(str_replace('_', ' ', $antrian->status)) . '</span>';
                })
                ->editColumn('tanggal_antrian', function ($antrian) {
                    return $antrian->tanggal_antrian ? \Carbon\Carbon::parse($antrian->tanggal_antrian)->format('d/m/Y') : '-';
                })
                ->editColumn('created_at', function ($antrian) {
                    return $antrian->created_at->format('d/m/Y H:i');
                })
                ->rawColumns(['action', 'status_badge'])
                ->make(true);
        }

        return view('antrian.datatable');
    }
}