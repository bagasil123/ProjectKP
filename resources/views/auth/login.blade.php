@extends('layouts.auth')

@section('main-content')
<style>
    body {
        background-color: #f4f4f4;
    }
    .bg-login-image {
        background: url('/img/cover-login.png');
        background-position: center;
        background-size: cover;
    }
    .btn-custom-primary {
        background-color: #5a2a83;
        border-color: #5a2a83;
        color: white;
    }
    .btn-custom-primary:hover {
        background-color: #4c1d6b;
        border-color: #4c1d6b;
    }
    .card {
        border-radius: 1rem;
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <!-- Gambar ilustrasi -->
                        <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                        <!-- Form Login -->
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center mb-4">
                                    <h1 class="h4 text-dark">{{ __('Selamat Datang') }}</h1>
                                    <p class="text-muted mb-0">Silakan masuk ke akun Anda</p>
                                </div>

                                {{-- Tampilkan error validasi --}}
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('login') }}" class="user mt-3">
                                    @csrf

                                    <div class="form-group">
                                        <label for="Mem_UserName" class="text-muted small">Nama Pengguna</label>
                                        <input type="text"
                                               class="form-control form-control-user @error('Mem_UserName') is-invalid @enderror"
                                               name="Mem_UserName"
                                               id="Mem_UserName"
                                               placeholder="Masukkan nama pengguna"
                                               value="{{ old('Mem_UserName') }}"
                                               required autofocus>
                                        @error('Mem_UserName')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="mem_password" class="text-muted small">Password</label>
                                        <input type="password"
                                               class="form-control form-control-user @error('password') is-invalid @enderror"
                                               name="mem_password"
                                               id="mem_password"
                                               placeholder="Masukkan password"
                                               required>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group form-check mt-3">
                                        <input type="checkbox" class="form-check-input" name="remember" id="remember"
                                            {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label small text-muted" for="remember">Ingat saya</label>
                                    </div>

                                    <button type="submit" class="btn btn-custom-primary btn-user btn-block mt-4">
                                        {{ __('Login') }}
                                    </button>
                                </form>

                                @if (Route::has('password.request'))
                                    <div class="text-center mt-3">
                                        <a class="small text-muted" href="{{ route('password.request') }}">
                                            {{ __('Lupa password?') }}
                                        </a>
                                    </div>
                                @endif

                            </div> <!-- /.p-5 -->
                        </div> <!-- /.col-lg-6 -->
                    </div> <!-- /.row -->
                </div> <!-- /.card-body -->
            </div> <!-- /.card -->
        </div>
    </div>
</div>
@endsection
