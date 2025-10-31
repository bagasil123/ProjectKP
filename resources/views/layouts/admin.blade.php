<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Laravel SB Admin 2">
    <meta name="author" content="CV.PRIMA BELLA PANEN REJEKI">
    <meta name="author" content="CV.PRIMA BELLA PANEN REJEKI">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables/dataTables.min.css') }}" rel="stylesheet"> {{-- CSS DataTables --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css"
        rel="stylesheet"> {{-- Select2 --}}

    <!-- Favicon -->
    <link href="{{ asset('img/favicon.png') }}" rel="icon" type="image/png">

    <!-- Cropper.js untuk Crop Gambar -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">




</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

        <!-- Sidebar - Brand -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/home') }}">
            <div class="sidebar-brand-icon">
                <img src="{{ asset('img/LOGOPBPR.png') }}" alt="Logo" style="height: 70px;">
            </div>
        </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

        <!-- Nav Item - Dashboard (Umum, bisa diakses semua yang login) -->
        <li class="nav-item {{ Nav::isRoute('home') }}">
            <a class="nav-link" href="{{ route('home') }}">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>{{ __('Dashboard') }}</span></a>
        </li>

        {{-- LOGIKA PEMBANGUNAN MENU DINAMIS BERDASARKAN ROLE PENGGUNA --}}
        @php
            $userMenus = collect(); // Inisialisasi koleksi kosong
            // Pastikan user login dan memiliki role
            if (Auth::check() && Auth::user()->role) {
                // Ambil menu-menu yang terkait dengan role pengguna, diurutkan berdasarkan 'order'
                // Relasi 'menus' dari model Role akan mengambil data dari tabel 'role_menu' dan 'menus'
                $userMenus = Auth::user()->role->menus->sortBy('order');
            }
            // Filter hanya menu utama (yang tidak punya parent_id)
            $mainMenus = $userMenus->whereNull('parent_id');
        @endphp

        @foreach ($mainMenus as $mainMenu)
            {{-- Ambil semua submenu dari userMenus yang memiliki parent_id ini --}}
            @php
                $childrenMenus = $userMenus->where('parent_id', $mainMenu->id)->sortBy('order');
            @endphp

            @if ($childrenMenus->isNotEmpty())
                {{-- Ini adalah menu utama dengan submenu (collapse menu) --}}
                <li class="nav-item {{ Request::is(Str::after(trim($mainMenu->url, '/'), '/') . '*') ? 'active' : '' }}">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapse{{ Str::slug($mainMenu->name) }}"
                        aria-expanded="true" aria-controls="collapse{{ Str::slug($mainMenu->name) }}">
                        <i class="{{ $mainMenu->icon ?? 'fas fa-fw fa-folder' }}"></i> {{-- Gunakan ikon dari DB --}}
                        <span>{{ $mainMenu->name }}</span>
                    </a>
                    <div id="collapse{{ Str::slug($mainMenu->name) }}" class="collapse {{ Request::is(Str::after(trim($mainMenu->url, '/'), '/') . '*') ? 'show' : '' }}" aria-labelledby="heading{{ Str::slug($mainMenu->name) }}"
                        data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header">{{ $mainMenu->name }} Menu:</h6>
                            @foreach ($childrenMenus as $subMenuItem)
                                <a class="collapse-item {{ Request::is(trim($subMenuItem->url, '/') . '*') ? 'active' : '' }}" href="{{ $subMenuItem->url ? url($subMenuItem->url) : '#' }}">
                                    {{ $subMenuItem->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </li>
            @else
                {{-- Ini adalah menu utama tanpa submenu (single link) --}}
                <li class="nav-item {{ Request::is(trim($mainMenu->url, '/') . '*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ $mainMenu->url ? url($mainMenu->url) : '#' }}">
                        <i class="{{ $mainMenu->icon ?? 'fas fa-fw fa-file' }}"></i> {{-- Gunakan ikon dari DB --}}
                        <span>{{ $mainMenu->name }}</span>
                    </a>
                </li>
            @endif
        @endforeach

                        <!-- SIDEBAR LAMA -->
                        <!-- Nav Item - Dashboard -->
                        {{-- <li class="nav-item {{ Nav::isRoute('home') }}">
                            <a class="nav-link" href="{{ route('home') }}">
                                <i class="fas fa-fw fa-tachometer-alt"></i>
                                <span>{{ __('Dashboard') }}</span></a>
                        </li>
                        <!-- Nav Item - Data Master Collapse Menu -->
                        <li class="nav-item">
                            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseDatamaster"
                                aria-expanded="true" aria-controls="collapseDatamaster">
                                <i class="fas fa-fw fa-wrench"></i>
                                <span>Data Master</span>
                            </a>
                            <div id="collapseDatamaster" class="collapse" aria-labelledby="headingDatamaster"
                                data-parent="#accordionSidebar">
                                <div class="bg-white py-2 collapse-inner rounded">
                                    <h6 class="collapse-header">Data Master Menu:</h6>
                                    <a class="collapse-item" href="route('#')">test</a>
                                    <a class="collapse-item" href="route('#')">test</a>
                                    <a class="collapse-item" href="route('#')">test</a>
                                    <a class="collapse-item" href="route('#')">test</a>
                                    <a class="collapse-item" href="route('#')">test</a>
                                </div>
                            </div>
                        </li>
                        <!-- Nav Item - Pembelian Collapse Menu -->
                        <li class="nav-item">
                            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePembelian"
                                aria-expanded="true" aria-controls="collapsePembelian">
                                <i class="fas fa-fw fa-wrench"></i>
                                <span>Pembelian</span>
                            </a>
                            <div id="collapsePembelian" class="collapse" aria-labelledby="headingPembelian"
                                data-parent="#accordionSidebar">
                                <div class="bg-white py-2 collapse-inner rounded">
                                    <h6 class="collapse-header">Pembelian Menu:</h6>
                                    <a class="collapse-item" href="route('#')">test</a>
                                    <a class="collapse-item" href="route('#')">test</a>
                                    <a class="collapse-item" href="route('#')">test</a>
                                    <a class="collapse-item" href="route('#')">test</a>
                                    <a class="collapse-item" href="route('#')">test</a>
                                </div>
                            </div>
                        </li>
                        <!-- Nav Item - Penjualan Collapse Menu -->
                        <li class="nav-item">
                            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePenjualan"
                                aria-expanded="true" aria-controls="collapsePenjualan">
                                <i class="fas fa-fw fa-wrench"></i>
                                <span>Penjualan</span>
                            </a>
                            <div id="collapsePenjualan" class="collapse" aria-labelledby="headingPenjualan"
                                data-parent="#accordionSidebar">
                                <div class="bg-white py-2 collapse-inner rounded">
                                    <h6 class="collapse-header">Penjualan Menu:</h6>
                                    <a class="collapse-item" href="route('pelanggan.index')">test</a>
                                    <a class="collapse-item" href="route('customer-orders.index')">test</a>
                                    <a class="collapse-item" href="route('penjualan.index')">test</a>
                                    <a class="collapse-item" href="route('#')">test</a>
                                    <a class="collapse-item" href="{{ route('retur.penjualan.index') }}">Retur Penjualan</a>
                                </div>
                            </div>
                        </li>
                        <!-- Nav Item - Akunting Collapse Menu -->
                        <li class="nav-item">
                            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAkunting"
                                aria-expanded="true" aria-controls="collapseAkunting">
                                <i class="fas fa-fw fa-wrench"></i>
                                <span>Akunting</span>
                            </a>
                            <div id="collapseAkunting" class="collapse" aria-labelledby="headingAkunting"
                                data-parent="#accordionSidebar">
                                <div class="bg-white py-2 collapse-inner rounded">
                                    <h6 class="collapse-header">Akunting Menu:</h6>
                                    <a class="collapse-item" href="{{ route('kodeakunting.index') }}">Kode Akunting</a>
                                    <a class="collapse-item" href="{{ route('jurnalumum.index') }}">Jurnal Umum</a>
                                    <a class="collapse-item" href="{{ route('bukubesar.index') }}">Daftar Penjurnalan</a>
                                    <a class="collapse-item" href="{{ route('kas-masuk.index') }}">Kas Masuk</a>
                                    <a class="collapse-item" href="{{ route('kas-keluar.index') }}">Kas Keluar</a>
                                </div>
                            </div>
                        </li>
            <!-- Nav Item - Inventory Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseInventory"
                    aria-expanded="true" aria-controls="collapseInventory">
                    <i class="fas fa-fw fa-wrench"></i>
                    <span>Inventory</span>
                </a>
                <div id="collapseInventory" class="collapse" aria-labelledby="headingInventory"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Inventory Menu:</h6>
                        <a class="collapse-item" href="route('#')">Supplier</a>
                        <a class="collapse-item" href="route('#')">Data Produk</a>
                        <a class="collapse-item" href="route('#')">Kelompok Produk</a>
                        <a class="collapse-item" href="route('#')">Satuan Produk</a>
                        <a class="collapse-item" href="route('#')">Purchase Order</a>
                        <a class="collapse-item" href="route('#')">Penerimaan</a>
                    </div>
                </div>
            </li><!-- Nav Item - Mutasi Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMutasi"
                    aria-expanded="true" aria-controls="collapseMutasi">
                    <i class="fas fa-fw fa-wrench"></i>
                    <span>Mutasi Gudang</span>
                </a>
                <div id="collapseMutasi" class="collapse" aria-labelledby="headingMutasi"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Mutasi Gudang:</h6>
                        <a class="collapse-item" href="{{ route('warehouse.index') }}">Gudang</a>
                        <a class="collapse-item" href="{{ route('gudangorder.index')}}">Permintaan</a>
                        <a class="collapse-item" href="{{ route('transfergudang.index')}}">Transfer Gudang</a>
                        <a class="collapse-item" href="{{ route('terimagudang.index')}}">Terima Gudang</a>
                    </div>
                </div>
            </li> --}}
<!-- SIDEBAR LAMA -->

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                {{ __('Settings') }}
            </div>

        <!-- Nav Item - Profile -->
        <li class="nav-item {{ Nav::isRoute('profile') }}">
            <a class="nav-link" href="{{ route('profile') }}">
                <i class="fas fa-fw fa-user"></i>
                <span>{{ __('Profile') }}</span>
            </a>
        </li>

<!-- SIDEBAR LAMA -->

        <!-- Nav Item - Keamanan Collapse Menu -->
        {{-- <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseKeamanan"
                aria-expanded="true" aria-controls="collapseKeamanan">
                <i class="fas fa-fw fa-wrench"></i>
                <span>Keamanan</span>
            </a>
            <div id="collapseKeamanan" class="collapse" aria-labelledby="headingKeamanan"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Keamanan Menu:</h6>
                    <a class="collapse-item" href="route('#')">Role</a>
                    <a class="collapse-item" href="route('#')">Permissions</a>
                    <a class="collapse-item" href="route('#')">User</a>
                </div>
            </div>
        </li> --}}

<!-- Nav Item - Data Karyawan Collapse Menu -->
{{-- <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseDatakaryawan"
        aria-expanded="true" aria-controls="collapseDatakaryawan">
        <i class="fas fa-fw fa-wrench"></i>
        <span>Data Karyawan</span>
    </a>
    <div id="collapseDatakaryawan" class="collapse" aria-labelledby="headingDatakaryawan"
        data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Presensi Menu:</h6>
            <a class="collapse-item" href="{{ route('employee.index') }}">Data Karyawan</a>
            <a class="collapse-item" href="{{ route('divisi.index') }}">Divisi</a>
            <a class="collapse-item" href="{{ route('subdivisi.index') }}">Sub-Divisi</a>
            <a class="collapse-item" href="{{ route('posisi.index') }}">Posisi</a>
            <a class="collapse-item" href="route('jadwal.index')">Shifting</a>
            <a class="collapse-item" href="route('absensi.index')">Absensi</a>
            <a class="collapse-item" href="route('leave.approvals.index')">Approval</a>
        </div>
    </div>
</li> --}}
<!-- Nav Item - Company Profile Collapse Menu -->
{{-- <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseComprof"
        aria-expanded="true" aria-controls="collapseComprof">
        <i class="fas fa-fw fa-wrench"></i>
        <span>Company Profile</span>
    </a>
    <div id="collapseComprof" class="collapse" aria-labelledby="headingCompanyProfile"
        data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Company Profile Menu:</h6>
            <a class="collapse-item" href="{{ route('comprof.settingmenu.index') }}">Setting Menu</a>
            <a class="collapse-item" href="{{ route('comprof.settingsubmenu.index') }}">Setting Sub Menu</a>
            <a class="collapse-item" href="{{ route('comprof.slider.index') }}">Setting Slider</a>
            <a class="collapse-item" href="{{ route('comprof.setperusahaan.index') }}">Setting Perusahaan</a>
            <a class="collapse-item" href="{{ route('comprof.datastaf.index') }}">Data Staf</a>
            <a class="collapse-item" href="{{ route('comprof.kategoriberita.index') }}">Kategori Berita</a>
            <a class="collapse-item" href="{{ route('comprof.kategorialbum.index') }}">Kategori Album</a>
            <a class="collapse-item" href="{{ route('comprof.websitecontent.index') }}">Kelola Website</a>
        </div>
    </div>
</li> --}}
<!-- SIDEBAR LAMA -->

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                {{-- <!-- Topbar Search -->
                <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form> --}}

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                            placeholder="Search for..." aria-label="Search"
                                            aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                    {{-- <!-- Nav Item - Alerts -->
                    <li class="nav-item dropdown no-arrow mx-1">
                        <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bell fa-fw"></i>
                            <!-- Counter - Alerts -->
                            <span class="badge badge-danger badge-counter">3+</span>
                        </a> --}}
                        <!-- Dropdown - Alerts -->
                        {{-- <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                            <h6 class="dropdown-header">
                                Alerts Center
                            </h6>
                            <a class="dropdown-item d-flex align-items-center" href="#">
                                <div class="mr-3">
                                    <div class="icon-circle bg-primary">
                                        <i class="fas fa-file-alt text-white"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="small text-gray-500">December 12, 2019</div>
                                    <span class="font-weight-bold">A new monthly report is ready to download!</span>
                                </div>
                            </a>
                            <a class="dropdown-item d-flex align-items-center" href="#">
                                <div class="mr-3">
                                    <div class="icon-circle bg-success">
                                        <i class="fas fa-donate text-white"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="small text-gray-500">December 7, 2019</div>
                                    $290.29 has been deposited into your account!
                                </div>
                            </a>
                            <a class="dropdown-item d-flex align-items-center" href="#">
                                <div class="mr-3">
                                    <div class="icon-circle bg-warning">
                                        <i class="fas fa-exclamation-triangle text-white"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="small text-gray-500">December 2, 2019</div>
                                    Spending Alert: We've noticed unusually high spending for your account.
                                </div>
                            </a>
                            <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                        </div>
                    </li> --}}

                    {{-- <!-- Nav Item - Messages -->
                    <li class="nav-item dropdown no-arrow mx-1">
                        <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-envelope fa-fw"></i>
                            <!-- Counter - Messages -->
                            <span class="badge badge-danger badge-counter">7</span>
                        </a> --}}
                        <!-- Dropdown - Messages -->
                        {{-- <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="messagesDropdown">
                            <h6 class="dropdown-header">
                                Message Center
                            </h6>
                            <a class="dropdown-item d-flex align-items-center" href="#">
                                <div class="dropdown-list-image mr-3">
                                    <img class="rounded-circle" src="https://source.unsplash.com/fn_BT9fwg_E/60x60" alt="">
                                    <div class="status-indicator bg-success"></div>
                                </div>
                                <div class="font-weight-bold">
                                    <div class="text-truncate">Hi there! I am wondering if you can help me with a problem I've been having.</div>
                                    <div class="small text-gray-500">Emily Fowler · 58m</div>
                                </div>
                            </a>
                            <a class="dropdown-item d-flex align-items-center" href="#">
                                <div class="dropdown-list-image mr-3">
                                    <img class="rounded-circle" src="https://source.unsplash.com/AU4VPcFN4LE/60x60" alt="">
                                    <div class="status-indicator"></div>
                                </div>
                                <div>
                                    <div class="text-truncate">I have the photos that you ordered last month, how would you like them sent to you?</div>
                                    <div class="small text-gray-500">Jae Chun · 1d</div>
                                </div>
                            </a>
                            <a class="dropdown-item d-flex align-items-center" href="#">
                                <div class="dropdown-list-image mr-3">
                                    <img class="rounded-circle" src="https://source.unsplash.com/CS2uCrpNzJY/60x60" alt="">
                                    <div class="status-indicator bg-warning"></div>
                                </div>
                                <div>
                                    <div class="text-truncate">Last month's report looks great, I am very happy with the progress so far, keep up the good work!</div>
                                    <div class="small text-gray-500">Morgan Alvarez · 2d</div>
                                </div>
                            </a>
                            <a class="dropdown-item d-flex align-items-center" href="#">
                                <div class="dropdown-list-image mr-3">
                                    <img class="rounded-circle" src="https://source.unsplash.com/Mv9hjnEUHR4/60x60" alt="">
                                    <div class="status-indicator bg-success"></div>
                                </div>
                                <div>
                                    <div class="text-truncate">Am I a good boy? The reason I ask is because someone told me that people say this to all dogs, even if they aren't good...</div>
                                    <div class="small text-gray-500">Chicken the Dog · 2w</div>
                                </div>
                            </a>
                            <a class="dropdown-item text-center small text-gray-500" href="#">Read More Messages</a>
                        </div>
                    </li> --}}

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{-- BARIS INI YANG HARUS DIUBAH --}}
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->Mem_UserName ?? 'Guest' }}</span>
                                {{-- BARIS INI JUGA --}}
                                <figure class="img-profile rounded-circle avatar font-weight-bold" data-initial="{{ Auth::user()->Mem_UserName[0] ?? '?' }}"></figure>
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="{{ route('profile') }}">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    {{ __('Profile') }}
                                </a>
                                <a class="dropdown-item" href="javascript:void(0)">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    {{ __('Settings') }}
                                </a>
                                <a class="dropdown-item" href="javascript:void(0)">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    {{ __('Activity Log') }}
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal"
                                    data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    {{ __('Logout') }}
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    @yield('main-content')

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

        <!-- Footer -->
        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>© CV.PRIMA BELLA PANEN REJEKI {{ now()->year }}</span>
                </div>
            </div>
        </footer>
        <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ __('Ready to Leave?') }}</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-link" type="button" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <a class="btn btn-danger" href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Scripts -->
    {{-- !!! Tambahkan jQuery jika belum ada dan diperlukan oleh plugin lain --}}
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> {{-- Contoh SweetAlert --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script> {{-- Contoh InputMask --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> {{-- Select2 --}}

    <!-- Bootstrap 5 JS Bundle (sudah termasuk Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <!-- Script Cropper.js untuk Crop Gambar -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script>
        let idleTime = 0;
        const idleLimit = 7200;
        let intervalId;

        function resetTimer() {
            idleTime = 0;
        }

        function startIdleTimer() {
            intervalId = setInterval(() => {
                idleTime++;
                if (idleTime >= idleLimit) {
                    clearInterval(intervalId);
                    logoutUser();
                }
            }, 1000);
        }

        function logoutUser() {
            fetch('/logout', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            }).then(() => {
                window.location.href = '/login';
            }).catch(() => {
                window.location.href = '/login';
            });
        }

        ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, resetTimer, true);
        });

        document.addEventListener('DOMContentLoaded', startIdleTimer);
        window.addEventListener('focus', resetTimer);
</script>

@stack('scripts') {{-- Script custom akan dimuat setelah jQuery & BS5 --}}
@stack('css')
</body>
</html>
