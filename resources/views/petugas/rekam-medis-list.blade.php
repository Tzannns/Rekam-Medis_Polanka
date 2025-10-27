@extends('layout.petugas')

@section('title', 'Daftar Rekam Medis')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Rekam Medis</h1>
    </div>

    <!-- Search Card -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('petugas.rekam-medis') }}" method="GET" class="row align-items-center">
                <div class="col-md-4">
                    <label for="search">Cari Pasien</label>
                    <input type="text" class="form-control" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Nama / No. Berobat">
                </div>
                <div class="col-md-3">
                    <label for="date">Tanggal</label>
                    <input type="date" class="form-control" name="date" id="date"
                        value="{{ request('date', date('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label for="poliklinik">Poliklinik</label>
                    <select class="form-control" name="poliklinik" id="poliklinik">
                        <option value="">Semua Poliklinik</option>
                        @foreach (App\Models\Poliklinik::all() as $poli)
                            <option value="{{ $poli->id }}" {{ request('poliklinik') == $poli->id ? 'selected' : '' }}>
                                {{ $poli->nama_poliklinik }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="d-none d-md-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search fa-sm"></i> Cari
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Records Card -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>No. Berobat</th>
                            <th>Nama Pasien</th>
                            <th>Poliklinik</th>
                            <th>Dokter</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekamMedis ?? [] as $index => $record)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $record->tanggal_berobat }}</td>
                                <td>{{ $record->no_berobat }}</td>
                                <td>{{ $record->nama_pasien }}</td>
                                <td>{{ $record->poliklinik }}</td>
                                <td>{{ $record->dokter }}</td>
                                <td>
                                    @if ($record->status === 'selesai')
                                        <span class="badge badge-success">Selesai</span>
                                    @elseif($record->status === 'diproses')
                                        <span class="badge badge-warning">Diproses</span>
                                    @else
                                        <span class="badge badge-primary">Menunggu</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('petugas.rekam-medis', $record->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data rekam medis</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (isset($rekamMedis) && method_exists($rekamMedis, 'links'))
                <div class="mt-3">
                    {{ $rekamMedis->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <link href="{{ asset('template/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('template/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('template/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "order": [
                    [1, "desc"]
                ], // Sort by date column descending
                "pageLength": 25,
                "language": {
                    "emptyTable": "Tidak ada data rekam medis",
                    "info": "Menampilkan _START_ hingga _END_ dari _TOTAL_ rekam medis",
                    "infoEmpty": "Menampilkan 0 hingga 0 dari 0 rekam medis",
                    "infoFiltered": "(difilter dari _MAX_ total rekam medis)",
                    "lengthMenu": "Tampilkan _MENU_ rekam medis",
                    "search": "Cari:",
                    "zeroRecords": "Tidak ditemukan rekam medis yang sesuai"
                }
            });
        });
    </script>
@endpush
