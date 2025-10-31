@extends('layouts.admin')

@section('main-content')
    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">{{ __('Profil Pengguna') }}</h1>

    @if (session('success'))
        <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-left-danger" role="alert">
            <ul class="pl-4 my-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">

        <div class="col-lg-4 order-lg-2">

            <div class="card shadow mb-4">
                <div class="card-profile-image mt-4">
                    {{-- Menggunakan Mem_UserName[0] untuk inisial --}}
                    <figure class="rounded-circle avatar avatar font-weight-bold" style="font-size: 60px; height: 180px; width: 180px;" data-initial="{{ Auth::user()->Mem_UserName[0] ?? '?' }}"></figure>
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center">
                                {{-- Menampilkan Nama Pengguna dari Mem_UserName --}}
                                <h5 class="font-weight-bold">{{ Auth::user()->Mem_UserName ?? 'N/A' }}</h5>
                                {{-- Anda bisa menampilkan role di sini jika diinginkan --}}
                                <p>{{ Auth::user()->role->name ?? 'Role Tidak Diketahui' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Bagian statistik profil (opsional, sesuaikan atau hapus jika tidak relevan) --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card-profile-stats">
                                <span class="heading">22</span>
                                <span class="description">Friends</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card-profile-stats">
                                <span class="heading">10</span>
                                <span class="description">Photos</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card-profile-stats">
                                <span class="heading">89</span>
                                <span class="description">Comments</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-lg-8 order-lg-1">

            <div class="card shadow mb-4">

                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Akun Saya</h6>
                </div>

                <div class="card-body">

                    {{-- Form untuk mengupdate informasi pengguna --}}
                    <form method="POST" action="{{ route('profile.update') }}" autocomplete="off">
                        @csrf
                        @method('PUT') {{-- Menggunakan PUT method untuk update --}}

                        <h6 class="heading-small text-muted mb-4">Informasi Pengguna</h6>

                        <div class="pl-lg-4">
                            <div class="row">
                                <div class="col-lg-12"> {{-- Menggunakan col-lg-12 karena hanya ada satu field nama --}}
                                    <div class="form-group focused">
                                        <label class="form-control-label" for="Mem_UserName">Nama Pengguna<span class="small text-danger">*</span></label>
                                        {{-- Mengambil nilai dari Mem_UserName --}}
                                        <input type="text" id="Mem_UserName" class="form-control" name="Mem_UserName" placeholder="Nama Pengguna" value="{{ old('Mem_UserName', Auth::user()->Mem_UserName ?? '') }}">
                                    </div>
                                </div>
                                {{-- Hapus kolom Last Name jika tidak ada padanannya --}}
                                {{-- <div class="col-lg-6">
                                    <div class="form-group focused">
                                        <label class="form-control-label" for="last_name">Last name</label>
                                        <input type="text" id="last_name" class="form-control" name="last_name" placeholder="Last name" value="{{ old('last_name', Auth::user()->last_name) }}">
                                    </div>
                                </div> --}}
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        {{-- Jika tidak ada kolom email di m_members, Anda bisa hapus ini atau ganti dengan Mem_ID atau lainnya --}}
                                        <label class="form-control-label" for="Mem_ID">ID Member</label>
                                        <input type="text" id="Mem_ID" class="form-control" name="Mem_ID" placeholder="ID Member" value="{{ old('Mem_ID', Auth::user()->Mem_ID ?? '') }}" readonly>
                                        {{-- Contoh: Jika ingin menampilkan email, dan ada kolom email di m_members --}}
                                        {{-- <label class="form-control-label" for="email">Email address<span class="small text-danger">*</span></label> --}}
                                        {{-- <input type="email" id="email" class="form-control" name="email" placeholder="example@example.com" value="{{ old('email', Auth::user()->email) }}"> --}}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group focused">
                                        <label class="form-control-label" for="current_password">Password Saat Ini</label>
                                        <input type="password" id="current_password" class="form-control" name="current_password" placeholder="Password Saat Ini">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group focused">
                                        <label class="form-control-label" for="new_password">Password Baru</label>
                                        <input type="password" id="new_password" class="form-control" name="new_password" placeholder="Password Baru">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group focused">
                                        <label class="form-control-label" for="confirm_password">Konfirmasi Password Baru</label>
                                        {{-- Name harus 'new_password_confirmation' agar validasi 'confirmed' di Laravel bekerja --}}
                                        <input type="password" id="confirm_password" class="form-control" name="new_password_confirmation" placeholder="Konfirmasi Password Baru">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Button -->
                        <div class="pl-lg-4">
                            <div class="row">
                                <div class="col text-center">
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

            </div>

        </div>

    </div>

@endsection