@extends('frontend.layouts.app')

@section('title', $album->nama_album . ' - Gallery')

@section('content')
<section class="hero-section" style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('{{ $album->gambarAlbums->first()->path_gambar ? asset('storage/'.$album->gambarAlbums->first()->path_gambar) : '' }}')">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4">{{ $album->nama_album }}</h1>
                <p class="lead mb-4">{{ $album->deskripsi ?? 'Album photos' }}</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container py-4">
        <a href="{{ route('gallery.show', $kategori->id) }}" class="btn btn-outline-secondary mb-4">
            <i class="fas fa-arrow-left me-2"></i> Back to {{ $kategori->kategori_album }}
        </a>

        <div class="row">
            @foreach($images as $image)
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="gallery-item">
                    <a href="{{ asset('storage/' . $image->path_gambar) }}" data-lightbox="album-{{ $album->id }}" data-title="{{ $image->judul_gambar }}">
                        <img src="{{ asset('storage/' . $image->path_gambar) }}" 
                             class="img-fluid rounded shadow-sm" 
                             alt="{{ $image->judul_gambar }}"
                             style="height: 200px; width: 100%; object-fit: cover;">
                    </a>
                    @if($image->judul_gambar || $image->deskripsi)
                    <div class="gallery-caption mt-2">
                        @if($image->judul_gambar)
                            <h6>{{ $image->judul_gambar }}</h6>
                        @endif
                        @if($image->deskripsi)
                            <p class="small">{{ \Illuminate\Support\Str::limit($image->deskripsi, 50) }}</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
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
