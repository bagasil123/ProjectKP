@extends('frontend.layouts.app')

@section('title', 'Home')

@section('content')

<!-- Hero Section -->
<section class="hero-banner bg-secondary">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="hero-content text-white py-5">
                    <h1 class="display-4 fw-bold">CV PRIMA BELLA PANEN REJEKI</h1>
                    <p class="lead fs-5">We provide the best services for your business needs</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="py-5 bg-light">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="section-title">About Us</h2>
                <p class="lead">
                    {{ Str::limit($company->description ?? 'We are a professional company dedicated to providing high-quality services to our clients. Our team consists of experienced professionals committed to excellence.', 200) }}
                </p>
                <a href="{{ route('about') }}" class="btn btn-outline-primary">Read More About Us</a>
            </div>
            <div class="col-lg-6">
                <div class="ratio ratio-16x9">
                    <iframe class="rounded shadow" src="https://www.youtube.com/embed/{{ $company->youtube_embed ?? 'your-video-id' }}" title="About Our Company" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Slider Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title d-inline-block">Featured Projects</h2>
            <p class="text-muted">Explore our latest works and achievements</p>
        </div>

        <div class="slider-container mx-auto">
            <div id="featuredSlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
                <div class="carousel-indicators">
                    @foreach($sliders as $key => $slider)
                        <button type="button" data-bs-target="#featuredSlider" data-bs-slide-to="{{ $key }}" class="{{ $key === 0 ? 'active' : '' }}"></button>
                    @endforeach
                </div>
                <div class="carousel-inner rounded shadow">
                    @foreach($sliders as $key => $slider)
                        <div class="carousel-item {{ $key === 0 ? 'active' : '' }}">
                            <a href="{{ $slider->link }}" target="_blank">
                                <img src="{{ $slider->image_url }}" class="d-block w-100 slider-image" alt="{{ $slider->title }}">
                            </a>
                            <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 p-3 rounded">
                                <h5>{{ $slider->title }}</h5>
                                <p>{{ $slider->description }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button class="carousel-control-prev" type="button" data-bs-target="#featuredSlider" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#featuredSlider" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="section-title d-inline-block">Our Team</h2>
            <p class="text-muted">Meet our professional and dedicated team members</p>
        </div>

        <div class="row">
            @foreach($staffs as $staff)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="{{ $staff->profile_image_url }}" class="card-img-top" alt="{{ $staff->name }}">
                        <div class="card-body text-center">
                            <h5 class="card-title">{{ $staff->name }}</h5>
                            <p class="text-muted">{{ $staff->jabatan }}</p>
                            <div class="card-text text-muted text-start mb-3">
                                @if(!empty($staff->clean_description))
                                    {!! \Illuminate\Support\Str::limit($staff->clean_description, 100) !!}
                                @else
                                    <span class="text-muted">No description available</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-center">
                                @if($staff->social_facebook)
                                    <a href="{{ $staff->social_facebook }}" class="text-primary mx-2"><i class="fab fa-facebook-f"></i></a>
                                @endif
                                @if($staff->social_twitter)
                                    <a href="{{ $staff->social_twitter }}" class="text-primary mx-2"><i class="fab fa-twitter"></i></a>
                                @endif
                                @if($staff->social_linkedin)
                                    <a href="{{ $staff->social_linkedin }}" class="text-primary mx-2"><i class="fab fa-linkedin-in"></i></a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('team') }}" class="btn btn-primary">View All Team Members</a>
        </div>
    </div>
</section>

<!-- Gallery Categories Section -->
<section class="py-5 bg-light">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="section-title d-inline-block">Gallery Categories</h2>
            <p class="text-muted">Explore our collection of galleries</p>
        </div>
        <div class="row">
            @foreach($kategoriAlbums as $kategori)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title">{{ $kategori->kategori_album }}</h5>
                            <div class="mt-3">
                                <a href="{{ route('gallery.show', $kategori->id) }}" class="btn btn-outline-primary">View Gallery</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- News Categories Section -->
<section class="py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="section-title d-inline-block">News Categories</h2>
            <p class="text-muted">Stay updated with our latest news</p>
        </div>
        <div class="row">
            @foreach($kategoriBeritas as $kategori)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title">{{ $kategori->kategori_berita }}</h5>
                            <div class="mt-3">
                                <a href="{{ route('news.category', $kategori->id) }}" class="btn btn-outline-primary">View News</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection

@push('styles')
<style>
    .hero-banner {
        background: linear-gradient(135deg, #5a6268 0%, #6c757d 100%);
        padding-top: 8rem;
        padding-bottom: 4rem;
        margin-top: -1px;
    }

    .hero-content {
        padding: 3rem 0;
    }

    .slider-container {
        max-width: 900px;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    .slider-container:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    }

    .carousel-item img {
        height: 500px;
        object-fit: cover;
        transition: all 0.4s ease;
    }

    .slider-image:hover {
        transform: scale(1.03);
        box-shadow: inset 0 0 15px rgba(0, 0, 0, 0.3), 0 0 25px rgba(0, 0, 0, 0.4);
        filter: brightness(0.95);
    }

    .section-title {
        position: relative;
        padding-bottom: 15px;
        margin-bottom: 25px;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: #0d6efd;
    }

    .carousel-caption {
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: 80%;
        transition: all 0.3s ease;
    }

    .carousel-item:hover .carousel-caption {
        background-color: rgba(0, 0, 0, 0.7);
        bottom: 25px;
    }

    .card-text {
        line-height: 1.6;
    }

    .card-text strong,
    .card-text b {
        font-weight: bold;
    }

    .card-text em,
    .card-text i {
        font-style: italic;
    }

    .card-text u {
        text-decoration: underline;
    }

    .card-text a {
        color: var(--primary);
        text-decoration: none;
    }

    .card-text a:hover {
        text-decoration: underline;
    }

    .card-text ul,
    .card-text ol {
        padding-left: 20px;
        margin-bottom: 10px;
    }

    .card-text li {
        margin-bottom: 5px;
    }

    @media (max-width: 992px) {
        .hero-content h1 {
            font-size: 2.5rem;
        }

        .hero-content p {
            font-size: 1.2rem;
        }

        .hero-banner {
            padding-top: 6rem;
            padding-bottom: 3rem;
        }
    }

    @media (max-width: 768px) {
        .carousel-item img {
            height: 350px;
        }

        .hero-content {
            text-align: center;
            padding: 2rem 0;
        }

        .hero-content h1 {
            font-size: 2.2rem !important;
        }

        .hero-content p {
            font-size: 1.1rem !important;
        }

        .section-title {
            font-size: 1.8rem;
        }

        .hero-banner {
            padding-top: 5rem;
            padding-bottom: 2.5rem;
        }
    }

    @media (max-width: 576px) {
        .carousel-item img {
            height: 250px;
        }

        .hero-content h1 {
            font-size: 1.8rem !important;
        }

        .hero-content p {
            font-size: 1rem !important;
        }

        .hero-banner {
            padding-top: 4rem;
            padding-bottom: 2rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const myCarousel = document.querySelector('#featuredSlider');
        new bootstrap.Carousel(myCarousel, {
            interval: 5000,
            wrap: true
        });
    });
</script>
@endpush
