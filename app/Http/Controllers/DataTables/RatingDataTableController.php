<?php

namespace App\Http\Controllers\DataTables;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class RatingDataTableController extends Controller
{
    /**
     * Display a listing of ratings with DataTables.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $ratings = Rating::with(['dokter', 'user', 'antrian'])
                ->select(['id', 'dokter_id', 'user_id', 'antrian_id', 'rating', 'review', 'created_at']);
            
            return DataTables::of($ratings)
                ->addIndexColumn()
                ->addColumn('action', function ($rating) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<button type="button" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</button>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteRating(' . $rating->id . ')"><i class="fas fa-trash"></i> Delete</button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('nama_dokter', function ($rating) {
                    return $rating->dokter ? $rating->dokter->nama_dokter : '-';
                })
                ->addColumn('nama_user', function ($rating) {
                    return $rating->user ? $rating->user->nama_user : '-';
                })
                ->addColumn('no_antrian', function ($rating) {
                    return $rating->antrian ? $rating->antrian->no_antrian : '-';
                })
                ->addColumn('rating_stars', function ($rating) {
                    $stars = '';
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $rating->rating) {
                            $stars .= '<i class="fas fa-star text-warning"></i>';
                        } else {
                            $stars .= '<i class="far fa-star text-muted"></i>';
                        }
                    }
                    return $stars . ' (' . $rating->rating . '/5)';
                })
                ->editColumn('created_at', function ($rating) {
                    return $rating->created_at->format('d/m/Y H:i');
                })
                ->rawColumns(['action', 'rating_stars'])
                ->make(true);
        }

        return view('rating.datatable');
    }
}