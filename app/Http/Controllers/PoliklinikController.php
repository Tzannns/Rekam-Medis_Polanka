<?php
// poliklinikcontroller.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\poliklinik;

class PoliklinikController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('poliklinik.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('poliklinik.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_poliklinik' => 'required|max:255',
        ]);

        try {
            poliklinik::create($validatedData);
            return redirect()
                ->route('poliklinik.index')
                ->with('success', 'Data berhasil disimpan!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Gagal menyimpan data. Silakan coba lagi.']);
        }
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
        $poliklinik = poliklinik::findOrFail($id);
        return view('poliklinik.update', ['poliklinik' => $poliklinik]);
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
        // Validasi data
        $validatedData = $request->validate([
            'nama_poliklinik' => 'required|max:255',
        ]);

        // Cari poliklinik berdasarkan ID
        $poliklinik = poliklinik::findOrFail($id);

        // Update data poliklinik
        $poliklinik->nama_poliklinik = $validatedData['nama_poliklinik'];
        $poliklinik->save();

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('poliklinik.index')->with('success', 'Data berhasil diperbarui!');
        return redirect()->back()->withInput()->withErrors(['error' => 'Gagal memperbarui data. Silakan coba lagi.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $poliklinik = poliklinik::findOrFail($id);
            $poliklinik->delete();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data poliklinik berhasil dihapus!'
                ]);
            }
            
            return redirect()->route('poliklinik.index')
                ->with('success', 'Data poliklinik berhasil dihapus!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus data poliklinik.'
                ], 500);
            }
            
            return redirect()->route('poliklinik.index')
                ->with('error', 'Gagal menghapus data poliklinik.');
        }
    }
}
