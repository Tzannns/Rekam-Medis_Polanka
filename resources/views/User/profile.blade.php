@extends(Auth::user()->roles == 'admin' ? 'layout.admin' : (Auth::user()->roles == 'pasien' ? 'layout.pasien' : (Auth::user()->roles == 'petugas' ? 'layout.petugas' : 'layout.petugas')))

@section('title', 'Edit Profile')

@section('content')
    <!-- Hidden inputs for SweetAlert messages -->
    <input type="hidden" id="success-message" value="{{ session('success') }}">
    <input type="hidden" id="error-message" value="{{ session('error') }}">

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Profile</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('profile.update', Auth::id()) }}" method="POST" enctype="multipart/form-data"
                class="user-form">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-4 text-center">
                        <label for="foto_user" class="form-label d-block"></label>
                        <img id="preview-image"
                            src="{{ Auth::user()->foto_user ? asset('storage/foto_user/' . Auth::user()->foto_user) : asset('img/default.jpg') }}"
                            alt="Foto Profil" class="img-profile rounded-circle mb-3"
                            style="width: 150px; height: 150px; object-fit: cover;">
                        <input type="file" name="foto_user" id="foto_user" class="form-control"
                            onchange="loadfile(event)" accept="image/*">
                    </div>
                    <div class="col-md-8">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nama_user">Nama User</label>
                                <input type="text" name="nama_user" id="nama_user" class="form-control"
                                    value="{{ Auth::user()->nama_user }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="username">Email/Username</label>
                                <input type="text" name="username" id="username" class="form-control"
                                    value="{{ Auth::user()->username }}" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password">Password</label>
                                <input type="password" name="password" id="password" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="no_telepon">No Telepon</label>
                                <input type="text" name="no_telepon" id="no_telepon" class="form-control"
                                    value="{{ Auth::user()->no_telepon }}" required>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('dashboard-' . Auth::user()->roles) }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Script untuk menampilkan preview gambar
        var loadfile = function(event) {
            var output = document.getElementById('preview-image');
            output.src = URL.createObjectURL(event.target.files[0]);
            output.onload = function() {
                URL.revokeObjectURL(output.src) // free memory
            }
            // Validate file size
            var fileSize = event.target.files[0].size / 1024 / 1024; // in MB
            if (fileSize > 2) {
                Swal.fire({
                    icon: 'error',
                    title: 'File terlalu besar',
                    text: 'Ukuran file maksimal adalah 2MB'
                });
                event.target.value = '';
                output.src =
                    "{{ Auth::user()->foto_user ? asset('storage/foto_user/' . Auth::user()->foto_user) : asset('img/default.jpg') }}";
            }
        };
    </script>
@endsection

@push('scripts')
    <script src="{{ asset('js/sweetalert.js') }}"></script>
@endpush

@include('sweetalert::alert')
