<footer class="py-5 bg-dark text-white">
    <div class="container">
        <div class="row">
            <!-- Company Information Column -->
            <div class="col-lg-4 mb-4">
                <h5 class="mb-3">{{ $company->company_name ?? 'CV Prima Bella Panen Rejeki' }}</h5>
                
                @if(isset($company->address))
                    <div class="address-text">
                        @php
                            // Clean up address formatting
                            $address = strip_tags($company->address);
                            $address = str_replace(['&nbsp;', '  '], [' ', ' '], $address);
                            $address = trim(preg_replace('/\s+/', ' ', $address));
                        @endphp
                        {{ $address }}
                    </div>
                @else
                    <p>Jl. KH. Hasyim Ashari No. 148 RT.002/RW.002, Poris Plawad Indah, Kec. Cipondoh, Kota Tangerang, Banten 15141, Indonesia</p>
                @endif
                
                <p><i class="fas fa-phone me-2"></i> {{ $company->phone ?? '08179827384' }}</p>
                <p><i class="fas fa-envelope me-2"></i> {{ $company->email ?? 'pbpr@gmail.com' }}</p>
            </div>

            <!-- Quick Links Column -->
            <div class="col-lg-4 mb-4">
                <h5 class="mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    @if(isset($menus) && count($menus) > 0)
                        @foreach($menus as $menu)
                            <li class="mb-2">
                                <a href="{{ $menu->final_link }}" 
                                   class="text-white text-decoration-none"
                                   target="{{ $menu->link_target ?? '_self' }}">
                                    {{ $menu->nama_menu }}
                                </a>
                            </li>
                        @endforeach
                    @else
                        <!-- Default links if no menus available -->
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Akunting</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Inventory</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Retur</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Penjualan</a></li>
                    @endif
                </ul>
            </div>

            <!-- Social Media Column -->
            <div class="col-lg-4 mb-4">
            <h5 class="mb-3">Connect With Us</h5>
            <div class="social-links">
                @if($company)
                    @if($company->facebook)
                        <a href="{{ $company->facebook }}" class="social-icon me-2" target="_blank">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    @endif
                    @if($company->twitter)
                        <a href="{{ $company->twitter }}" class="social-icon me-2" target="_blank">
                            <i class="fab fa-twitter"></i>
                        </a>
                    @endif
                    @if($company->instagram)
                        <a href="{{ $company->instagram }}" class="social-icon me-2" target="_blank">
                            <i class="fab fa-instagram"></i>
                        </a>
                    @endif
                    @if($company->linkedin)
                        <a href="{{ $company->linkedin }}" class="social-icon me-2" target="_blank">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    @endif

                    @unless($company->facebook || $company->twitter || $company->instagram || $company->linkedin)
                        <p class="text-muted">Follow us on social media</p>
                    @endunless
                @else
                    <p class="text-muted">Follow us on social media</p>
                @endif
            </div>
        </div>

        
        <hr class="my-4 bg-secondary">
        
        <div class="text-center">
            <p class="mb-0">
                &copy; {{ date('Y') }} {{ $company->company_name ?? 'CV Prima Bella Panen Rejeki' }}. All rights reserved.
            </p>
        </div>
    </div>
</footer>

<style>
    .address-text {
        white-space: pre-line;
        margin-bottom: 1rem;
        line-height: 1.6;
    }
    
    .social-links {
        font-size: 1.25rem;
    }
    
    .social-icon {
        color: #fff;
        transition: color 0.3s ease;
    }
    
    .social-icon:hover {
        color: #0d6efd;
        text-decoration: none;
    }
    
    footer a {
        transition: color 0.3s ease;
    }
    
    footer a:hover {
        color: #0d6efd !important;
    }
</style>