<?php

namespace App\Http\Controllers\DataTables;

use App\Http\Controllers\Controller;
use App\Models\poliklinik;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PoliklinikDataTableController extends Controller
{
    /**
     * Display a listing of polikliniks with DataTables.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $polikliniks = poliklinik::select(['id', 'nama_poliklinik']);
                
                $datatable = DataTables::of($polikliniks)
                    ->addIndexColumn()
                    ->addColumn('action', function ($poliklinik) {
                        $btn = '<div class="btn-group" role="group">';
                        $btn .= '<a href="' . route('poliklinik.edit', $poliklinik->id) . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>';
                        $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deletePoliklinik(' . $poliklinik->id . ')"><i class="fas fa-trash"></i> Delete</button>';
                        $btn .= '</div>';
                        return $btn;
                    })
                    ->rawColumns(['action']);
                
                return $datatable->make(true);
            } catch (\Exception $e) {
                Log::error('PoliklinikDataTable Error: ' . $e->getMessage());
                Log::error('PoliklinikDataTable Trace: ' . $e->getTraceAsString());
                
                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Terjadi kesalahan saat memuat data. Silakan refresh halaman.'
                ], 500);
            }
        }

        return view('poliklinik.index');
    }
}