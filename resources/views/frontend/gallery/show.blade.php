@extends('frontend.layouts.app')

@section('title', $kategori->kategori_album . ' - Gallery')

@section('content')
<section class="hero-section" style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('{{ $coverImage ? asset('storage/' . $coverImage) : '' }}')">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4">{{ $kategori->kategori_album }}</h1>
                <p class="lead mb-4">Photo collection in this category</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container py-4">
        <a href="{{ route('gallery.index') }}" class="btn btn-outline-secondary mb-4">
            <i class="fas fa-arrow-left me-2"></i> Back to Gallery
        </a>

        @if($albums->isEmpty())
            <div class="alert alert-info">No albums available in this category</div>
        @else
            @foreach($albums as $album)
            <div class="mb-5">
                <h3 class="h4 mb-3">{{ $album->nama_album }}</h3>
                @if($album->deskripsi)
                    <p class="text-muted mb-3">{{ $album->deskripsi }}</p>
                @endif
                
                @if($album->gambarAlbums->isEmpty())
                    <div class="alert alert-warning">No images in this album</div>
                @else
                    <div class="row g-3">
                        @foreach($album->gambarAlbums as $gambar)
                        <div class="col-6 col-md-4 col-lg-3">
                            <a href="{{ asset('storage/' . $gambar->path_gambar) }}" data-lightbox="album-{{ $album->id }}" data-title="{{ $gambar->judul_gambar ?? $album->nama_album }}">
                                <img src="{{ asset('storage/' . $gambar->path_gambar) }}" class="img-fluid rounded shadow-sm" alt="{{ $gambar->judul_gambar }}" style="height: 200px; width: 100%; object-fit: cover;">
                            </a>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @endforeach
        @endif
    </div>
</section>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<script>
    lightbox.option({
        'resizeDuration': 200,
        'wrapAround': true,
        'albumLabel': 'Image %1 of %2'
    });
</script>
@endpush
@endsection
