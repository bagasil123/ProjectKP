@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    </div>

    <!-- ===== ANALISIS PENJUALAN ===== -->
    <h4 class="mb-3 text-gray-700">Analisis Penjualan</h4>
    <div class="row">
        <!-- Grafik Penjualan per Bulan -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Penjualan per Bulan (Tahun Ini)</h6></div>
                <div class="card-body">
                    <div class="chart-bar" style="height: 320px;"><canvas id="penjualanBulananChart"></canvas></div>
                </div>
            </div>
        </div>
        <!-- Top 10 Produk -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Top Produk (Berdasarkan QTY)</h6></div>
                <div class="card-body" style="max-height: 150px; overflow-y: auto;">
                    <ul class="list-group list-group-flush">
                        @forelse($topProductsQty as $product)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $product->nama_produk }}
                                <span class="badge bg-primary text-white rounded">{{ $product->total_qty }}</span>
                            </li>
                        @empty
                            <li class="list-group-item">Data tidak ditemukan.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
             <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Top Produk (Berdasarkan Nominal)</h6></div>
                 <div class="card-body" style="max-height: 150px; overflow-y: auto;">
                    <ul class="list-group list-group-flush">
                        @forelse($topProductsNominal as $product)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $product->nama_produk }}</span>
                                <span class="text-success font-weight-bold">Rp {{ number_format($product->total_nominal, 0, ',', '.') }}</span>
                            </li>
                        @empty
                            <li class="list-group-item">Data tidak ditemukan.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Anda bisa duplikat blok "Analisis Penjualan" ini untuk "Analisis Pembelian" menggunakan variabel $purchaseLabels & $purchaseData --}}
    <hr class="my-4">

    <!-- ===== ANALISIS KARYAWAN ===== -->
    <h4 class="mb-3 text-gray-700">Analisis Karyawan</h4>
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Komposisi Gender</h6></div>
                <div class="card-body"><div class="chart-pie pt-4"><canvas id="genderPieChart"></canvas></div></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100">
                 <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Karyawan per Departemen</h6></div>
                <div class="card-body"><div class="chart-pie pt-4"><canvas id="departmentPieChart"></canvas></div></div>
            </div>
        </div>
        <div class="col-xl-6 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Presensi Hari Ini</h6></div>
                <div class="card-body"><div class="chart-bar"><canvas id="attendanceChart"></canvas></div></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {

    // --- FUNGSI HELPER UNTUK FORMAT RUPIAH ---
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(number);
    }


    // --- GRAFIK PENJUALAN PER BULAN ---
    // 1. Ambil data penjualan dari controller (Blade)
    // Variabelnya harus sama dengan yang Anda kirim dari controller, yaitu 'penjualanBulananData'
    const penjualanData = @json($penjualanBulananData ?? []); // '?? []' untuk keamanan jika variabel tidak ada

    // 2. Dapatkan elemen canvas
    const ctxPenjualan = document.getElementById('penjualanBulananChart');

    // 3. Buat chart baru jika elemennya ada
    if (ctxPenjualan) {
        new Chart(ctxPenjualan, {
            type: 'bar',
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
                datasets: [{
                    label: 'Total Penjualan',
                    data: penjualanData, // <-- Gunakan variabel yang sudah kita ambil
                    backgroundColor: 'rgba(78, 115, 223, 0.9)', // Warna standar template
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1,
                    hoverBackgroundColor: 'rgba(46, 90, 217, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            // Gunakan fungsi helper untuk format label sumbu Y
                            callback: function(value) {
                                return formatRupiah(value);
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            // Gunakan fungsi helper untuk format tooltip
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += formatRupiah(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }


    // --- Kode untuk grafik lainnya (Gender, Departemen, dll.) bisa diletakkan di sini ---
    // Contoh untuk grafik gender (pastikan variabel $genderLabels dan $genderValues ada dari controller)
    const ctxGender = document.getElementById('genderPieChart');
    if (ctxGender) {
        new Chart(ctxGender, {
            type: 'pie',
            data: {
                labels: @json($genderLabels ?? []),
                datasets: [{
                    data: @json($genderValues ?? []),
                    backgroundColor: ['#4e73df', '#e74a3b']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
    // ====================================================================
    // === TAMBAHKAN BLOK KODE DI BAWAH INI UNTUK GRAFIK DEPARTEMEN ===
    // ====================================================================
    const ctxDepartment = document.getElementById('departmentPieChart');
    if (ctxDepartment) {
        new Chart(ctxDepartment, {
            type: 'pie', // Atau 'doughnut' jika Anda lebih suka
            data: {
                // Variabel ini harus sama dengan yang Anda kirim dari controller
                labels: @json($departmentLabels ?? []),
                datasets: [{
                    data: @json($departmentValues ?? []),
                    // Sediakan lebih banyak warna jika departemen Anda lebih dari 5
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#858796', '#e74a3b', '#5a5c69'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#60616f', '#c0392b', '#3d3e48']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true, // Tampilkan legenda agar tahu warna departemen
                        position: 'bottom',
                    }
                }
            }
        });
    }

    const ctxAttendance = document.getElementById('attendanceChart');
    if (ctxAttendance) {
        new Chart(ctxAttendance, {
            type: 'bar', // Tipe grafik adalah bar
            data: {
                // Label sumbu-X diambil dari controller
                labels: @json($attendanceLabels),
                datasets: [{
                    label: 'Jumlah Karyawan',
                    // Data (nilai) diambil dari controller
                    data: @json($attendanceValues),
                    // Tentukan warna untuk setiap bar
                    backgroundColor: [
                        'rgba(28, 200, 138, 0.8)', // Hijau untuk HADIR
                        'rgba(246, 194, 62, 0.8)', // Kuning untuk SAKIT
                        'rgba(78, 115, 223, 0.8)', // Biru untuk IZIN
                        'rgba(231, 74, 59, 0.8)'  // Merah untuk ALFA
                    ],
                    borderColor: [
                        'rgba(28, 200, 138, 1)',
                        'rgba(246, 194, 62, 1)',
                        'rgba(78, 115, 223, 1)',
                        'rgba(231, 74, 59, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            // Pastikan sumbu Y hanya menampilkan bilangan bulat
                            stepSize: 1,
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        // Sembunyikan legenda dataset karena sudah jelas dari warna
                        display: false
                    }
                }
            }
        });
    }

    // ... dan seterusnya untuk grafik lainnya

});
</script>
@endpush
