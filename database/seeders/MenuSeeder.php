<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\keamanan\Menu; // Pastikan namespace ini sesuai dengan lokasi model Menu Anda

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $keamanan = Menu::firstOrCreate(
            ['slug' => 'keamanan'],
            ['name' => 'Keamanan', 'url' => null, 'icon' => 'fas fa-shield-alt', 'order' => 20, 'parent_id' => null]
        );
        // Submenu Keamanan
        if ($keamanan) {
            Menu::firstOrCreate(['slug' => 'keamanan.roles'], ['name' => 'Roles', 'url' => '/keamanan/roles', 'icon' => 'fas fa-user-tag', 'order' => 1, 'parent_id' => $keamanan->id]);
            Menu::firstOrCreate(['slug' => 'keamanan.permission'], ['name' => 'Permissions', 'url' => '/keamanan/permission', 'icon' => 'fas fa-lock', 'order' => 2, 'parent_id' => $keamanan->id]);
            Menu::firstOrCreate(['slug' => 'keamanan.member'], ['name' => 'User', 'url' => '/keamanan/member', 'icon' => 'fas fa-users', 'order' => 3, 'parent_id' => $keamanan->id]); // Icon fas fa-users
        }


        // === Menu Induk: Presensi ===
        $presensi = Menu::firstOrCreate(
            ['slug' => 'presensi'],
            ['name' => 'Presensi', 'url' => null, 'icon' => 'fas fa-clock', 'order' => 30, 'parent_id' => null]
        );

        // Submenu Presensi
        if ($presensi) {
            Menu::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'url' => '/presensi/employee', 'icon' => 'fas fa-id-badge', 'order' => 1, 'parent_id' => $presensi->id]);
            Menu::firstOrCreate(['slug' => 'divisi'], ['name' => 'Divisi', 'url' => '/presensi/divisi', 'icon' => 'fas fa-sitemap', 'order' => 2, 'parent_id' => $presensi->id]);
            Menu::firstOrCreate(['slug' => 'subdivisi'], ['name' => 'Sub Divisi', 'url' => '/presensi/subdivisi', 'icon' => 'fas fa-network-wired', 'order' => 3, 'parent_id' => $presensi->id]);
            Menu::firstOrCreate(['slug' => 'posisi'], ['name' => 'Posisi', 'url' => '/presensi/posisi', 'icon' => 'fas fa-briefcase', 'order' => 4, 'parent_id' => $presensi->id]);

            // === Menu Absensi (akan mencakup fitur rekap) ===
            Menu::firstOrCreate(
                ['slug' => 'absensi'],
                ['name' => 'Absensi', 'url' => '/presensi/absensi', 'icon' => 'fas fa-fingerprint', 'order' => 5, 'parent_id' => $presensi->id]
            );

            // Jadwal (induk dari Shift, Libur, Lokasi) - Asumsi ini sudah ada dari pembahasan sebelumnya
            $jadwal = Menu::firstOrCreate(
                ['slug' => 'jadwal'],
                ['name' => 'Jadwal', 'url' => '/presensi/jadwal', 'icon' => 'fas fa-calendar-alt', 'order' => 6, 'parent_id' => $presensi->id]
            );

            if ($jadwal) {
                Menu::firstOrCreate(['slug' => 'shift'], ['name' => 'Shift', 'url' => '/shift', 'icon' => 'fas fa-exchange-alt', 'order' => 1, 'parent_id' => $jadwal->id]);
                Menu::firstOrCreate(['slug' => 'holiday'], ['name' => 'Libur Nasional', 'url' => '/holiday', 'icon' => 'fas fa-calendar-day', 'order' => 2, 'parent_id' => $jadwal->id]);
                Menu::firstOrCreate(['slug' => 'officelocation'], ['name' => 'Lokasi Kantor', 'url' => '/officelocation', 'icon' => 'fas fa-building', 'order' => 3, 'parent_id' => $jadwal->id]);
            }

            // Persetujuan Cuti (Leave Approvals)
            Menu::firstOrCreate(
                ['slug' => 'leave.approvals'],
                ['name' => 'Persetujuan Cuti', 'url' => '/presensi/leave-approvals', 'icon' => 'fas fa-user-check', 'order' => 7, 'parent_id' => $presensi->id]
            );
        }
    


        $akunting = Menu::firstOrCreate(
            ['slug' => 'akunting'],
            ['name' => 'Akunting', 'url' => null, 'icon' => 'fas fa-calculator', 'order' => 40, 'parent_id' => null]
        );
          // Submenu Akunting
        if ($akunting) {
            Menu::firstOrCreate(['slug' => 'kodeakunting'], ['name' => 'Kode Akunting', 'url' => '/akunting/kodeakunting', 'icon' => 'fas fa-code', 'order' => 1, 'parent_id' => $akunting->id]); // Icon dummy
            Menu::firstOrCreate(['slug' => 'jurnalumum'], ['name' => 'Jurnal Umum', 'url' => '/akunting/jurnal-umum', 'icon' => 'fas fa-book', 'order' => 2, 'parent_id' => $akunting->id]); // Icon dummy
            Menu::firstOrCreate(['slug' => 'bukubesar'], ['name' => 'Buku Besar', 'url' => '/akunting/buku-besar', 'icon' => 'fas fa-book-open', 'order' => 3, 'parent_id' => $akunting->id]); // Icon dummy
            Menu::firstOrCreate(['slug' => 'kas-masuk'], ['name' => 'Kas Masuk', 'url' => '/akunting/kas-masuk', 'icon' => 'fas fa-money-bill-alt', 'order' => 4, 'parent_id' => $akunting->id]); // Icon dummy
            Menu::firstOrCreate(['slug' => 'kas-keluar'], ['name' => 'Kas Keluar', 'url' => '/akunting/kas-keluar', 'icon' => 'fas fa-money-bill-wave', 'order' => 5, 'parent_id' => $akunting->id]); // Icon dummy
        }


        $inventory = Menu::firstOrCreate(
            ['slug' => 'inventory'],
            ['name' => 'Inventory', 'url' => null, 'icon' => 'fas fa-boxes', 'order' => 50, 'parent_id' => null]
        );
        // Submenu Inventory
        if ($inventory) {
            Menu::firstOrCreate(['slug' => 'supplier'], ['name' => 'Supplier', 'url' => '/inventory/supplier', 'icon' => 'fas fa-truck', 'order' => 1, 'parent_id' => $inventory->id]);
            Menu::firstOrCreate(['slug' => 'kelompokproduk'], ['name' => 'Kelompok Produk', 'url' => '/inventory/kelompokproduk', 'icon' => 'fas fa-cubes', 'order' => 2, 'parent_id' => $inventory->id]);
            Menu::firstOrCreate(['slug' => 'satuanproduk'], ['name' => 'Satuan Produk', 'url' => '/inventory/satuanproduk', 'icon' => 'fas fa-balance-scale', 'order' => 3, 'parent_id' => $inventory->id]);
            Menu::firstOrCreate(['slug' => 'dataproduk'], ['name' => 'Data Produk', 'url' => '/inventory/dataproduk', 'icon' => 'fas fa-box', 'order' => 4, 'parent_id' => $inventory->id]);
            Menu::firstOrCreate(['slug' => 'penerimaan'], ['name' => 'Penerimaan', 'url' => '/inventory/penerimaan', 'icon' => 'fas fa-loading', 'order' => 5, 'parent_id' => $inventory->id]);
            Menu::firstOrCreate(['slug' => 'purchase-orders'], ['name' => 'Purchase Orders', 'url' => '/inventory/purchase-orders', 'icon' => 'fas fa-shopping-cart', 'order' => 6, 'parent_id' => $inventory->id]);
            Menu::firstOrCreate(['slug' => 'retur.pembelian'], ['name' => 'Retur Pembelian', 'url' => '/retur/pembelian', 'icon' => 'fas fa-cash-undo-alt', 'order' => 7, 'parent_id' => $inventory->id]);
        }


        $penjualan = Menu::firstOrCreate(
            ['slug' => 'penjualan'],
            ['name' => 'penjualan', 'url' => null, 'icon' => 'fas fa-shopping-cart', 'order' => 50, 'parent_id' => null]
        );
        // Submenu Penjualan
        if ($penjualan) {
            Menu::firstOrCreate(['slug' => 'pelanggan'], ['name' => 'Pelanggan', 'url' => '/pelanggan', 'icon' => 'fas fa-users', 'order' => 1, 'parent_id' => $penjualan->id]);
            Menu::firstOrCreate(['slug' => 'customer-orders'], ['name' => 'Customer Orders', 'url' => '/customer-orders', 'icon' => 'fas fa-receipt', 'order' => 2, 'parent_id' => $penjualan->id]);
            Menu::firstOrCreate(['slug' => 'penjualan.index'], ['name' => 'Daftar Penjualan', 'url' => '/penjualan', 'icon' => 'fas fa-file-invoice-dollar', 'order' => 3, 'parent_id' => $penjualan->id]);
            Menu::firstOrCreate(['slug' => 'retur.penjualan'], ['name' => 'Retur Penjualan', 'url' => '/retur/penjualan', 'icon' => 'fas fa-cash-register', 'order' => 4, 'parent_id' => $penjualan->id]);
        }


        $mutasiGudang = Menu::firstOrCreate(
            ['slug' => 'mutasi-gudang'],
            ['name' => 'Mutasi Gudang', 'url' => null, 'icon' => 'fas fa-truck-moving', 'order' => 60, 'parent_id' => null] 
        );
        // Submenu Mutasi Gudang
        if ($mutasiGudang) {
            Menu::firstOrCreate(['slug' => 'warehouse'], ['name' => 'Warehouse', 'url' => '/mutasigudang/warehouse', 'icon' => 'fas fa-warehouse', 'order' => 1, 'parent_id' => $mutasiGudang->id]);
            Menu::firstOrCreate(['slug' => 'gudangorder'], ['name' => 'Gudang Order', 'url' => '/mutasigudang/gudangorder', 'icon' => 'fas fa-clipboard-list', 'order' => 2, 'parent_id' => $mutasiGudang->id]);
            Menu::firstOrCreate(['slug' => 'transfergudang'], ['name' => 'Transfer Gudang', 'url' => '/mutasigudang/transfergudang', 'icon' => 'fas fa-exchange-alt', 'order' => 3, 'parent_id' => $mutasiGudang->id]);
            Menu::firstOrCreate(['slug' => 'terimagudang'], ['name' => 'Terima Gudang', 'url' => '/mutasigudang/terimagudang', 'icon' => 'fas fa-exchange-alt', 'order' => 4, 'parent_id' => $mutasiGudang->id]);
        }


        $companyProfile = Menu::firstOrCreate(
            ['slug' => 'company-profile'],
            ['name' => 'Company Profile', 'url' => null, 'icon' => 'fas fa-building', 'order' => 80, 'parent_id' => null] 
        );
        // Submenu Company Profile
        if ($companyProfile) {
            Menu::firstOrCreate(['slug' => 'comprof.settingmenu'], ['name' => 'Setting Menu', 'url' => '/comprof/settingmenu', 'icon' => 'fas fa-bars', 'order' => 1, 'parent_id' => $companyProfile->id]);
            Menu::firstOrCreate(['slug' => 'comprof.settingsubmenu'], ['name' => 'Setting Sub Menu', 'url' => '/comprof/settingsubmenu', 'icon' => 'fas fa-indent', 'order' => 2, 'parent_id' => $companyProfile->id]);
            Menu::firstOrCreate(['slug' => 'comprof.datastaf'], ['name' => 'Data Staf', 'url' => '/comprof/datastaf', 'icon' => 'fas fa-users', 'order' => 3, 'parent_id' => $companyProfile->id]);
            Menu::firstOrCreate(['slug' => 'comprof.kategorialbum'], ['name' => 'Kategori Album', 'url' => '/comprof/kategorialbum', 'icon' => 'fas fa-images', 'order' => 4, 'parent_id' => $companyProfile->id]);
            Menu::firstOrCreate(['slug' => 'comprof.kategoriberita'], ['name' => 'Kategori Berita', 'url' => '/comprof/kategoriberita', 'icon' => 'fas fa-newspaper', 'order' => 5, 'parent_id' => $companyProfile->id]);
            Menu::firstOrCreate(['slug' => 'comprof.setperusahaan'], ['name' => 'Setting Perusahaan', 'url' => '/comprof/setperusahaan', 'icon' => 'fas fa-building', 'order' => 6, 'parent_id' => $companyProfile->id]);
            Menu::firstOrCreate(['slug' => 'comprof.slider'], ['name' => 'Setting Slider', 'url' => '/comprof/slider', 'icon' => 'fas fa-sliders-h', 'order' => 7, 'parent_id' => $companyProfile->id]);
        }
    }
}
