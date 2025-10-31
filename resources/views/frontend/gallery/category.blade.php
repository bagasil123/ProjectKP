@extends('frontend.layouts.app')

@section('title', $kategori->kategori_album . ' - Gallery')

@section('content')
<section class="hero-section" style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('{{ $content->gambar ? asset('storage/'.$content->gambar) : ($company->gallery_banner_url ?? '') }}')">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4">{{ $content->judul ?? $kategori->kategori_album }}</h1>
                <p class="lead mb-4">{{ $content->excerpt ?? 'Gallery in this category' }}</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container py-4">
        <a href="{{ route('gallery.index') }}" class="btn btn-outline-secondary mb-4">
            <i class="fas fa-arrow-left me-2"></i> Back to Gallery
        </a>

        <!-- Konten deskripsi kategori -->
        @if($content->isi)
            <div class="mb-5 content-description">
                {!! $content->isi !!}
            </div>
        @endif

        <div class="row">
            @foreach($albums as $album)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    @if($album->gambarAlbums->count() > 0)
                        <img src="{{ asset('storage/' . $album->gambarAlbums->first()->path_gambar) }}" 
                             class="card-img-top" 
                             alt="{{ $album->nama_album }}"
                             style="height: 200px; object-fit: cover;">
                    @else
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-image fa-3x text-muted"></i>
                        </div>
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $album->nama_album }}</h5>
                        <p class="card-text">{{ \Illuminate\Support\Str::limit($album->deskripsi, 100) }}</p>
                        <a href="{{ route('gallery.show', $kategori->id) }}?album={{ $album->id }}" class="btn btn-outline-primary">
                            View Album
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{ $albums->links() }}
    </div>
</section>
@endsection
