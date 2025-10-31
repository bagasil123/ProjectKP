@extends('frontend.layouts.app')

@section('title', 'Our Team')

@section('content')
    <!-- Hero Section -->
    <section class="hero-section" style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('{{ $company->team_banner_url ?? asset('images/default-team-banner.jpg') }}')">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="display-3 fw-bold mb-4">Meet Our Team</h1>
                    <p class="lead mb-4">The talented people behind our success</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="section-title d-inline-block">Our Professional Team</h2>
                <p class="text-muted">Meet our dedicated team members who work hard to deliver the best results</p>
            </div>
            <div class="row">
                @forelse($staffs as $staff)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="{{ $staff->profile_image_url }}" class="card-img-top team-member-img" alt="{{ $staff->name }}">
                        <div class="card-body text-center">
                            <h5 class="card-title mb-1">{{ $staff->name }}</h5>
                            <p class="text-muted mb-3">{{ $staff->jabatan }}</p>
                            <div class="card-text text-muted text-start mb-3">
                                {!! $staff->clean_description !!}
                            </div>
                            <div class="social-links">
                                @if($staff->social_facebook)
                                    <a href="{{ $staff->social_facebook }}" target="_blank" class="text-primary mx-1"><i class="fab fa-facebook-f"></i></a>
                                @endif
                                @if($staff->social_twitter)
                                    <a href="{{ $staff->social_twitter }}" target="_blank" class="text-primary mx-1"><i class="fab fa-twitter"></i></a>
                                @endif
                                @if($staff->social_linkedin)
                                    <a href="{{ $staff->social_linkedin }}" target="_blank" class="text-primary mx-1"><i class="fab fa-linkedin-in"></i></a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        No team members found.
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Join Team Section -->
    <section class="py-5 bg-light">
        <div class="container py-5 text-center">
            <h2 class="section-title d-inline-block">Want to Join Our Team?</h2>
            <p class="lead mb-4">We're always looking for talented individuals to join our growing team</p>
            <a href="{{ route('careers') }}" class="btn btn-primary btn-lg px-4">View Open Positions</a>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .hero-section {
        padding: 5rem 0;
        color: white;
        background-size: cover;
        background-position: center;
    }
    
    .section-title {
        position: relative;
        padding-bottom: 0.5rem;
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: var(--primary);
    }
    
    .team-member-img {
        height: 250px;
        object-fit: cover;
        object-position: top;
        border-radius: 0;
    }
    
    .card {
        transition: all 0.3s ease;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .card-text {
        line-height: 1.6;
        font-size: 0.9rem;
    }
    
    .card-text br {
        display: block;
        content: "";
        margin-bottom: 0.75rem;
    }
    
    .social-links a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(0, 123, 255, 0.1);
        color: var(--primary);
        transition: all 0.3s ease;
    }
    
    .social-links a:hover {
        background: var(--primary);
        color: white;
        transform: scale(1.1);
    }
</style>
@endpush