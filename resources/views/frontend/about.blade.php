@extends('frontend.layouts.app')

@section('title', 'About Us')

@section('content')
    <!-- Hero Section -->
    <section class="hero-section" style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('{{ $company->about_banner_url ?? '' }}')">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="display-3 fw-bold mb-4">About Our Company</h1>
                    <p class="lead mb-4">Learn more about our history, mission and values</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-5">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="section-title">Our Story</h2>
                    <p class="lead">{{ $company->description ?? 'No company description available.' }}</p>
                    
                    @if($company->mission)
                    <div class="mt-5">
                        <h3 class="h4">Our Mission</h3>
                        <p>{{ $company->mission }}</p>
                    </div>
                    @endif
                    
                    @if($company->vision)
                    <div class="mt-4">
                        <h3 class="h4">Our Vision</h3>
                        <p>{{ $company->vision }}</p>
                    </div>
                    @endif
                </div>
                <div class="col-lg-6">
                    <div class="ratio ratio-16x9 mb-4">
                        <iframe class="rounded shadow" src="https://www.youtube.com/embed/{{ $company->youtube_embed ?? 'your-video-id' }}" title="About Our Company" allowfullscreen></iframe>
                    </div>
                    @if($company->values)
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h3 class="h4">Our Core Values</h3>
                            <ul class="list-unstyled">
                                @foreach(explode("\n", $company->values) as $value)
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> {{ $value }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Milestones Section -->
    @if($company->milestones)
    <section class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="section-title d-inline-block">Our Milestones</h2>
            </div>
            <div class="row">
                @foreach(json_decode($company->milestones, true) as $milestone)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="display-4 text-primary mb-3">{{ $milestone['year'] }}</div>
                            <h4>{{ $milestone['title'] }}</h4>
                            <p class="text-muted">{{ $milestone['description'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
@endsection