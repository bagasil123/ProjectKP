<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            @if($company && $company->logo)
                <img src="{{ $company->logo_url }}" height="40" alt="{{ $company->company_name }}" class="img-fluid">
            @else
                <span class="fw-bold">{{ $company->company_name ?? 'Company' }}</span>
            @endif
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                @foreach($menus as $menu)
                    @if($menu->submenus->count() > 0)
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                {{ $menu->nama_menu }}
                            </a>
                            <ul class="dropdown-menu">
                                @foreach($menu->submenus as $submenu)
                                    <li>
                                        <a class="dropdown-item"
                                           href="{{ $submenu->websiteContent ? route('page.show', $submenu->websiteContent->id) : ($submenu->tautan ?? '#') }}"
                                           target="{{ !empty($submenu->tautan) && str_starts_with($submenu->tautan, 'http') ? '_blank' : '_self' }}">
                                            {{ $submenu->nama_submenu }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link"
                               href="{{ $menu->final_link }}"
                               target="{{ $menu->link_target }}">
                                {{ $menu->nama_menu }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>

            <!-- Login/Auth Button -->
            <ul class="navbar-nav">
                @guest
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="btn btn-dark ms-2">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    </li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
                            <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i> Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

@push('styles')
<style>
    /* Auth Button Styles */
    .btn-dark {
        background-color: #000;
        color: #fff;
        border: 1px solid #000;
        transition: all 0.3s ease;
    }

    .btn-dark:hover {
        background-color: #333;
        border-color: #333;
        color: #fff;
    }

    /* Modal Styles */
    .modal-content {
        border-radius: 10px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        border-bottom: none;
        padding-bottom: 0;
    }

    .modal-title {
        font-weight: 600;
        color: #000;
    }

    /* Form Styles */
    .form-label {
        font-weight: 500;
        color: #000;
    }

    .form-control {
        border-radius: 5px;
        padding: 0.5rem 0.75rem;
    }

    .form-control:focus {
        border-color: #000;
        box-shadow: 0 0 0 0.25rem rgba(0, 0, 0, 0.1);
    }

    /* Navbar Positioning */
    .navbar-nav {
        align-items: center;
    }

    @media (max-width: 991.98px) {
        .navbar-collapse {
            padding-top: 1rem;
        }
        .navbar-nav.me-auto {
            margin-bottom: 1rem;
        }
        .navbar-nav .btn {
            margin-left: 0 !important;
            margin-top: 0.5rem;
            width: 100%;
        }
    }
</style>
@endpush
