@extends('frontend.layouts.app')

@section('title', $kategori->kategori_berita . ' - News')

@section('content')
@php
    $bannerUrl = '';
    $title = $kategori->kategori_berita;
    $description = 'News in this category';

    // Jika ada konten terkait, gunakan data dari konten
    if(isset($content) && $content) {
        $bannerUrl = $content->gambar ? asset('storage/'.$content->gambar) : ($company->news_banner_url ?? '');
        $title = $content->judul ?? $kategori->kategori_berita;
        $description = $content->excerpt ?? 'News in this category';
    } else {
        $bannerUrl = $company->news_banner_url ?? '';
    }
@endphp

<section class="hero-section" style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('{{ $bannerUrl }}')">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4">{{ $title }}</h1>
                <p class="lead mb-4">{{ $description }}</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container py-4">
        <a href="{{ route('news.index') }}" class="btn btn-outline-secondary mb-4">
            <i class="fas fa-arrow-left me-2"></i> Back to News
        </a>

        <!-- Konten deskripsi kategori -->
        @if(isset($content) && $content->isi)
            <div class="mb-5 content-description">
                {!! $content->isi !!}
            </div>
        @endif

        <div class="row">
            @foreach($beritas as $berita)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    @if($berita->gambar_berita)
                        <img src="{{ asset('storage/' . $berita->gambar_berita) }}" 
                             class="card-img-top" 
                             alt="{{ $berita->judul_berita }}"
                             style="height: 200px; object-fit: cover;">
                    @endif
                    <div class="card-body">
                        <h3 class="h5">{{ $berita->judul_berita }}</h3>
                        <p class="text-muted small">{{ $berita->created_at->format('F j, Y') }}</p>
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($berita->isi_berita), 100) }}</p>
                        <a href="{{ route('news.show', $berita->slug) }}" class="btn btn-sm btn-outline-primary">
                            Read More
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{ $beritas->links() }}
    </div>
</section>
@endsection