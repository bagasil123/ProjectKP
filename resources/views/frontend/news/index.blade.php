@extends('frontend.layouts.app')

@section('title', 'News')

@section('content')
<section class="hero-section" style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('{{ $company->news_banner_url ?? '' }}')">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4">News</h1>
                <p class="lead mb-4">Stay updated with our latest news</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container py-4">
        <div class="row">
            <div class="col-lg-8">
                @foreach($beritas as $berita)
                    <div class="card mb-4">
                        @if($berita->gambar_berita)
                            <img src="{{ asset('storage/' . $berita->gambar_berita) }}" class="card-img-top" alt="{{ $berita->judul_berita }}">
                        @endif
                        <div class="card-body">
                            <h2 class="card-title">{{ $berita->judul_berita }}</h2>
                            <p class="text-muted">
                                Posted on {{ $berita->created_at->format('F j, Y') }} in 
                                <a href="{{ route('news.category', $berita->kategori_id) }}">{{ $berita->kategori->kategori_berita }}</a>
                            </p>
                            <p class="card-text">{{ Str::limit(strip_tags($berita->isi_berita), 200) }}</p>
                            <a href="{{ route('news.show', $berita->slug) }}" class="btn btn-outline-primary">Read More</a>
                        </div>
                    </div>
                @endforeach

                {{ $beritas->links() }}
            </div>
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Categories</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            @foreach($kategoriBeritas as $kategori)
                                <li class="mb-2">
                                    <a href="{{ route('news.category', $kategori->id) }}" class="text-decoration-none">
                                        {{ $kategori->kategori_berita }} <span class="badge bg-primary rounded-pill">{{ $kategori->beritas_count }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Latest News</h5>
                    </div>
                    <div class="card-body">
                        @foreach($latestNews as $news)
                            <div class="mb-3">
                                <h6><a href="{{ route('news.show', $news->slug) }}" class="text-decoration-none">{{ $news->judul_berita }}</a></h6>
                                <p class="small text-muted">{{ $news->created_at->format('F j, Y') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection