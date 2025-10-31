@extends('frontend.layouts.app')

@section('title', $title ?? $content->judul)

@section('content')
<section class="hero-section" style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('{{ $content->gambar ? asset('storage/' . $content->gambar) : $default_banner }}')">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold mb-4">{{ $content->judul }}</h1>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container py-4">
        <div class="row">
            <div class="col-lg-12">
                <div class="content-body">
                    {!! $content->isi !!}
                </div>
                
                @if($relatedAlbums && $relatedAlbums->albums->isNotEmpty())
                <div class="mt-5">
                    <h3 class="mb-4">Gallery Terkait</h3>
                    <div class="row">
                        @foreach($relatedAlbums->albums as $album)
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    @if($album->gambarAlbums->isNotEmpty())
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
                                        <a href="{{ route('gallery.show', $relatedAlbums->id) }}" class="btn btn-outline-primary">
                                            Lihat Gallery
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                @if($relatedNews && $relatedNews->beritas->isNotEmpty())
                    <div class="mt-5">
                        <h3 class="mb-4">Berita Terkait</h3>
                        <div class="row">
                            @foreach($relatedNews->beritas as $berita)
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        @if($berita->gambar_berita)
                                            <img src="{{ asset('storage/' . $berita->gambar_berita) }}" 
                                                 class="card-img-top" 
                                                 alt="{{ $berita->judul_berita }}"
                                                 style="height: 200px; object-fit: cover;">
                                        @endif
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $berita->judul_berita }}</h5>
                                            <p class="card-text">{{ \Illuminate\Support\Str::limit(strip_tags($berita->isi_berita), 100) }}</p>
                                            <a href="{{ route('news.show', $berita->slug) }}" class="btn btn-outline-primary">
                                                Baca Selengkapnya
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
