<?php

namespace App\Http\Controllers\DataTables;

use App\Http\Controllers\Controller;
use App\Models\Datapasien;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class DatapasienDataTableController extends Controller
{
    /**
     * Display a listing of patients with DataTables.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $datapasiens = Datapasien::with('user')->select(['id', 'nama_pasien', 'email', 'no_telp', 'nik', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'alamat', 'no_kberobat', 'no_kbpjs', 'created_at']);
            
            return DataTables::of($datapasiens)
                ->addIndexColumn()
                ->addColumn('action', function ($datapasien) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('pasien.show', $datapasien->id) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</a>';
                    $btn .= '<a href="' . route('pasien.edit', $datapasien->id) . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deletePasien(' . $datapasien->id . ')"><i class="fas fa-trash"></i> Delete</button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('foto', function ($datapasien) {
                    if ($datapasien->foto_pasien) {
                        return '<img src="' . asset('storage/foto_pasien/' . $datapasien->foto_pasien) . '" alt="Foto Pasien" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">';
                    }
                    return '<img src="' . asset('img/default.jpg') . '" alt="Default" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">';
                })
                ->editColumn('tanggal_lahir', function ($datapasien) {
                    return $datapasien->tanggal_lahir ? \Carbon\Carbon::parse($datapasien->tanggal_lahir)->format('d/m/Y') : '-';
                })
                ->editColumn('created_at', function ($datapasien) {
                    return $datapasien->created_at->format('d/m/Y H:i');
                })
                ->rawColumns(['action', 'foto'])
                ->make(true);
        }

        return view('pasien.datatable');
    }
}