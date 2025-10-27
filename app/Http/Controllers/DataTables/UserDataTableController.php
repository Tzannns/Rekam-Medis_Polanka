<?php

namespace App\Http\Controllers\DataTables;

use App\Http\Controllers\Controller;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class UserDataTableController extends Controller
{
    /**
     * Display a listing of users with DataTables.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = User::select(['id', 'nama_user', 'username', 'roles', 'no_telepon', 'created_at']);
            
            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('action', function ($user) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('user.edit', $user->id) . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteUser(' . $user->id . ')"><i class="fas fa-trash"></i> Delete</button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('role_badge', function ($user) {
                    $badgeClass = match($user->roles) {
                        'admin' => 'badge-danger',
                        'petugas' => 'badge-warning',
                        'pasien' => 'badge-info',
                        default => 'badge-secondary'
                    };
                    return '<span class="badge ' . $badgeClass . '">' . ucfirst($user->roles) . '</span>';
                })
                ->editColumn('created_at', function ($user) {
                    return $user->created_at->format('d/m/Y H:i');
                })
                ->rawColumns(['action', 'role_badge'])
                ->make(true);
        }

        return view('user.datatable');
    }
}