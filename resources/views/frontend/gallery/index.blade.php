@extends('frontend.layouts.app')

@section('title', 'Gallery Categories')

@section('content')
<section class="hero-section" style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('{{ $company->gallery_banner_url ?? '' }}')">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4">Our Gallery</h1>
                <p class="lead mb-4">Explore our collection of memorable moments and events</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="section-title d-inline-block">Gallery Categories</h2>
            <p class="text-muted">Browse through our photo collections</p>
        </div>

        @if($kategoriAlbums->isEmpty())
            <div class="alert alert-info">No gallery categories available</div>
        @else
            <div class="row g-4">
                @foreach($kategoriAlbums as $kategori)
                <div class="col-md-4 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        @php
                            $coverImage = null;
                            foreach ($kategori->albums as $album) {
                                if ($album->gambarAlbums->isNotEmpty()) {
                                    $coverImage = $album->gambarAlbums->first()->path_gambar;
                                    break;
                                }
                            }
                        @endphp

                        @if($coverImage)
                            <img src="{{ asset('storage/' . $coverImage) }}" class="card-img-top" alt="{{ $kategori->kategori_album }}" style="height: 200px; object-fit: cover;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        @endif
                        <div class="card-body text-center">
                            <h5 class="card-title">{{ $kategori->kategori_album }}</h5>
                            <p class="text-muted small">{{ $kategori->albums_count }} albums</p>
                            <a href="{{ route('gallery.show', $kategori->id) }}" class="btn btn-outline-primary btn-sm mt-2">View Gallery</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
