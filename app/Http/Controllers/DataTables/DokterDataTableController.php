<?php

namespace App\Http\Controllers\DataTables;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class DokterDataTableController extends Controller
{
    /**
     * Display a listing of doctors with DataTables.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dokters = Dokter::with('poliklinik')->select(['id', 'nama_dokter', 'poliklinik_id', 'foto_dokter', 'created_at']);
            
            return DataTables::of($dokters)
                ->addIndexColumn()
                ->addColumn('action', function ($dokter) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('dokter.edit', $dokter->id) . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteDokter(' . $dokter->id . ')"><i class="fas fa-trash"></i> Delete</button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('poliklinik_name', function ($dokter) {
                    return $dokter->poliklinik ? $dokter->poliklinik->nama_poliklinik : '-';
                })
                ->addColumn('foto', function ($dokter) {
                    if ($dokter->foto_dokter) {
                        return '<img src="' . asset('storage/foto_dokter/' . $dokter->foto_dokter) . '" alt="Foto Dokter" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">';
                    }
                    return '<img src="' . asset('img/default.jpg') . '" alt="Default" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">';
                })
                ->editColumn('created_at', function ($dokter) {
                    return $dokter->created_at->format('d/m/Y H:i');
                })
                ->rawColumns(['action', 'foto'])
                ->make(true);
        }

        return view('dokter.datatable');
    }
}