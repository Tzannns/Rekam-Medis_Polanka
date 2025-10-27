<?php

namespace App\Http\Controllers\DataTables;

use App\Http\Controllers\Controller;
use App\Models\Poliklinik;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class PoliklinikDataTableController extends Controller
{
    /**
     * Display a listing of polikliniks with DataTables.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $polikliniks = Poliklinik::select(['id', 'nama_poliklinik', 'created_at']);
            
            return DataTables::of($polikliniks)
                ->addIndexColumn()
                ->addColumn('action', function ($poliklinik) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('poliklinik.edit', $poliklinik->id) . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deletePoliklinik(' . $poliklinik->id . ')"><i class="fas fa-trash"></i> Delete</button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('dokter_count', function ($poliklinik) {
                    return $poliklinik->dokter()->count() . ' Dokter';
                })
                ->editColumn('created_at', function ($poliklinik) {
                    return $poliklinik->created_at->format('d/m/Y H:i');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('poliklinik.datatable');
    }
}