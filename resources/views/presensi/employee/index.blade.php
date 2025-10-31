@extends('layouts.admin')


@section('main-content')
<!-- Run di terminal untuk menghubungkan folder public untuk menyimpan foto karyawan : php artisan storage:link -->
<!-- Begin Page Content -->
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Daftar Data Karyawan</h1>
    <p class="mb-4">Manajemen Data Karyawan untuk aplikasi.</p>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Tambah Data Karyawan Button -->
    <div class="mb-3">
        @php
            $currentRouteName = Route::currentRouteName();
            $currentMenuSlug = Str::beforeLast($currentRouteName, '.'); 
        @endphp

        @can('tambah', $currentMenuSlug) 
        <button type="button" class="btn btn-primary" data-toggle="modal" id="addEmployeeButton">
            <i class="fas fa-plus"></i> Tambah Karyawan
        </button>
        @endcan
    </div>


    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Data Karyawan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered display my-4" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="10%">Kode Karyawan</th>
                            <th width="15%">Nama Karyawan</th>
                            <th width="5%">Aktif (Y/N)</th>
                            <th width="5%">Foto Karyawan</th>
                            <th width="5%">Entry ID</th>
                            <th width="15%">First Entry</th>
                            <th width="5%">Update ID</th>
                            <th width="15%">Last Update</th>
                            <th width="12%">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($Employees as $index => $Employee)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $Employee->emp_Code }} </td>
                            <td>{{ $Employee->emp_Name }} </td>
                            <td>{{ $Employee->emp_ActiveYN }} </td>
                            <td>
                                @if ($Employee->EMP_PICT && file_exists(public_path('storage/employee_pictures/' . $Employee->EMP_PICT)))
                                    <img src="{{ asset('storage/employee_pictures/' . $Employee->EMP_PICT) }}" alt="Employee Picture" width="100" style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%;">
                                @else
                                    <span>No Image</span>
                                @endif
                            </td>
                            <td>{{ $Employee->emp_ENTRYID }} </td>
                            <td>{{ \Carbon\Carbon::parse($Employee->emp_FirstEntry)->format('d-m-Y H:i') }}</td>
                            <td>{{ $Employee->emp_UpdateID }} </td>
                            <td>{{ $Employee->emp_LastUpdate }} </td>
                            <td>
                                <button class="btn btn-info btn-sm btn-view" data-id="{{ $Employee->emp_Auto }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @can('ubah', $currentMenuSlug) 
                                <button class="btn btn-warning btn-sm btn-edit" data-id="{{ $Employee->emp_Auto }}" title="Edit Employee">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcan

                                @can('hapus', $currentMenuSlug) 
                                <button class="btn btn-danger btn-sm btn-delete" data-id="{{ $Employee->emp_Auto }}" data-nama="{{ $Employee->emp_Name }}" title="Hapus Employee">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(count($Employees) > 0)
            <!-- Tampilkan tabel -->
            @else
                <div class="alert alert-info">Tidak ada Data Karyawan tersedia.</div>
            @endif
        </div>
    </div>
</div>

<!-- Modal untuk Crop Gambar -->
<div class="modal fade" id="cropImageModal" tabindex="-1" aria-labelledby="cropImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cropImageModalLabel">Potong Gambar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Kontainer untuk gambar yang akan di-crop --}}
                <div style="width: 100%; height: 400px;">
                    <img id="image-to-crop" src="" style="max-width: 100%;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="crop-and-save">Potong & Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal View -->
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-labelledby="viewEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewEmployeeModalLabel">Data Karyawan</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- Informasi Karyawan --}}
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Foto Karyawan:</strong></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-9"><span id="viewEmpPict"></span></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"><strong>ID Karyawan:</strong> <span id="viewEmpAuto"></span></div>
                    <div class="col-md-3"><strong>Kode Karyawan:</strong> <span id="viewEmpCode"></span></div>
                    <div class="col-md-3"><strong>Nama Karyawan:</strong> <span id="viewEmpName"></span></div>
                    <div class="col-md-3"><strong>NID :</strong> <span id="viewEmpNID"></span></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"><strong>Aktif (Y/N):</strong> <span id="viewEmpActiveYN"></span></div>
                    <div class="col-md-3"><strong>Kode Divisi:</strong> <span id="viewEmpDivCode"></span></div>
                    <div class="col-md-3"><strong>Kode Sub-divisi:</strong> <span id="viewEmpSubDivCode"></span></div>
                    <div class="col-md-3"><strong>Kode Posisi:</strong> <span id="viewEmpPosCode"></span></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"><strong>Email 1:</strong> <span id="viewEmpEmail1"></span></div>
                    <div class="col-md-3"><strong>Email 2:</strong> <span id="viewEmpEmail2"></span></div>
                    <div class="col-md-3"><strong>Website:</strong> <span id="viewEmpWeb"></span></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"><strong>Jenis Kelamin:</strong> <span id="viewEmpSex"></span></div>
                    <div class="col-md-3"><strong>Status Pernikahan:</strong> <span id="viewEmpMarital"></span></div>
                    <div class="col-md-3"><strong>Agama:</strong> <span id="viewEmpReligion"></span></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"><strong>Tempat Lahir:</strong> <span id="viewEmpPlaceBorn"></span></div>
                    <div class="col-md-3"><strong>Tanggal Lahir:</strong> <span id="viewEmpDateBorn"></span></div>
                    <div class="col-md-3"><strong>Golongan Darah:</strong> <span id="viewEmpBlood"></span></div>
                    <div class="col-md-3"><strong>Pendidikan Terakhir:</strong> <span id="viewEmpEducation"></span></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"><strong>Tanggal Enroll:</strong> <span id="viewEmpEnroll"></span></div>
                    <div class="col-md-3"><strong>Tanggal Mulai Contract:</strong> <span id="viewEmpStartContract"></span></div>
                    <div class="col-md-3"><strong>Tanggal Contract Expired:</strong> <span id="viewEmpExpired"></span></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"><strong>Tanggal Permanent:</strong> <span id="viewEmpPermanent"></span></div>
                    <div class="col-md-3"><strong>Tanggal Quit:</strong> <span id="viewEmpQuit"></span></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"><strong>Kode Alasan:</strong> <span id="viewEmpReason"></span></div>
                    <div class="col-md-3"><strong>Kantor:</strong> <span id="viewEmpOffice"></span></div>
                    <div class="col-md-3"><strong>Kode Shift:</strong> <span id="viewEmpShif"></span></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"><strong>Status:</strong> <span id="viewEmpStatus"></span></div>
                    <div class="col-md-3"><strong>Status PTKP:</strong> <span id="viewEmpPtkp"></span></div>
                    <div class="col-md-3"><strong>Pajak:</strong> <span id="viewEmpPajak"></span></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"><strong>Pembayaran:</strong> <span id="viewEmpBayar"></span></div>
                    <div class="col-md-3"><strong>Kode Bank:</strong> <span id="viewEmpBank"></span></div>
                    <div class="col-md-3"><strong>Nomor Rekening:</strong> <span id="viewEmpNorek"></span></div>
                    <div class="col-md-3"><strong>Pemilik:</strong> <span id="viewEmpPemilik"></span></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"><strong>NPWP:</strong> <span id="viewEmpNpwp"></span></div>
                    <div class="col-md-3"><strong>JAMSOSTEK:</strong> <span id="viewEmpJamsostek"></span></div>
                    <div class="col-md-3"><strong>Terdaftar JAMSOSTEK:</strong> <span id="viewEmpDateJamsostek"></span></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"><strong>KTP:</strong> <span id="viewEmpKtp"></span></div>
                    <div class="col-md-3"><strong>Nomor KTP:</strong> <span id="viewEmpNoKtp"></span></div>
                </div>
                <hr>
                {{-- Data Address --}}
                <h5 class="mb-3">Data Address</h5>
                <ul class="nav nav-tabs" id="viewEmployeeTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="view_address_tab1" data-bs-toggle="tab" data-bs-target="#view_address1" type="button" role="tab" aria-controls="view_address1" aria-selected="true">Data Alamat 1</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="view_address_tab2" data-bs-toggle="tab" data-bs-target="#view_address2" type="button" role="tab" aria-controls="view_address2" aria-selected="false">Data Alamat 2</button>
                    </li>
                </ul>
                <div class="tab-content" id="viewEmployeeTabContent">
                    <div class="tab-pane fade show active" id="view_address1" role="tabpanel" aria-labelledby="view_address_tab1">
                        <div class="row my-4 ml-2">
                            <div class="col-md-3"><strong>Alamat 1:</strong> <span id="viewEmpAddress1"></span></div>
                            <div class="col-md-3"><strong>Kode Kota 1:</strong> <span id="viewEmpCityCode1"></span></div>
                            <div class="col-md-3"><strong>Kode Provinsi 1:</strong> <span id="viewEmpProvinceCode1"></span></div>
                            <div class="col-md-3"><strong>Kode Pos 1:</strong> <span id="viewEmpZipCode1"></span></div>
                        </div>
                        <div class="row my-4 ml-2">
                            <div class="col-md-3"><strong>Nomor Telpon 1:</strong> <span id="viewEmpPhone1"></span></div>
                            <div class="col-md-3"><strong>Nomor Telpon 2:</strong> <span id="viewEmpPhone2"></span></div>
                            <div class="col-md-3"><strong>Nomor Handphone 1:</strong> <span id="viewEmpHp1"></span></div>
                            <div class="col-md-3"><strong>Nomor Handphone 2:</strong> <span id="viewEmpHp2"></span></div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="view_address2" role="tabpanel" aria-labelledby="view_address_tab2">
                        <div class="row my-4 ml-2">
                            <div class="col-md-3"><strong>Alamat 2:</strong> <span id="viewEmpAddress2"></span></div>
                            <div class="col-md-3"><strong>Kode Kota 2:</strong> <span id="viewEmpCityCode2"></span></div>
                            <div class="col-md-3"><strong>Kode Provinsi 2:</strong> <span id="viewEmpProvinceCode2"></span></div>
                            <div class="col-md-3"><strong>Kode Pos 2:</strong> <span id="viewEmpZipCode2"></span></div>
                        </div>
                        <div class="row my-4 ml-2">
                            <div class="col-md-3"><strong>Nomor Telpon 3:</strong> <span id="viewEmpPhone3"></span></div>
                            <div class="col-md-3"><strong>Nomor Telpon 4:</strong> <span id="viewEmpPhone4"></span></div>
                            <div class="col-md-3"><strong>Nomor Handphone 3:</strong> <span id="viewEmpHp3"></span></div>
                            <div class="col-md-3"><strong>Nomor Handphone 4:</strong> <span id="viewEmpHp4"></span></div>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Data Karyawan -->
<div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl"> {{-- Ukuran modal besar --}}
        <div class="modal-content">
            <form id="employeeForm">
                @csrf {{-- CSRF Token --}}
                <input type="hidden" name="_method" id="formMethod" value="POST"> {{-- Untuk metode PUT saat edit --}}
                <input type="hidden" name="emp_Auto" id="Employee_Id" value="">
                <input type="hidden" name="delete_photo" id="delete_photo_input" value="0">

                <div class="modal-header">
                    <h5 class="modal-title" id="employeeModalLabel">Tambah Data Karyawan Baru</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                     {{-- Alert untuk error di dalam modal --}}
                    <div id="modal-alert" class="alert alert-danger" style="display: none;">
                         <ul id="modal-error-list"></ul>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                             <label class="form-label text-center d-block">Foto Karyawan (1:1)</label>
                             <div class="border rounded p-2 d-flex justify-content-center align-items-center mx-auto position-relative" style="width: 320px; height: 320px; background-color: #f8f9fa;">
                                 <img id="image-preview" src="#" alt="Pratinjau Gambar" class="img-fluid" style="display: none; max-height: 100%;"/>
                                 <span id="upload-label" class="text-muted">Pratinjau Foto</span>
                                 <button type="button" class="btn btn-danger btn-sm position-absolute" id="delete-image-btn" style="display: none; top: 5px; right: 5px;"><i class="fas fa-trash"></i></button>
                             </div>
                             <label for="EMP_PICT" class="form-label mt-4">Pilih/Ubah Foto...</label>
                             <input type="file" name="EMP_PICT_input" id="EMP_PICT" class="form-control" accept="image/png, image/jpeg, image/jpg">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="emp_Auto" class="form-label">ID Karyawan</label>
                            <input type="text" class="form-control" id="emp_Auto" name="emp_Auto">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="emp_Code" class="form-label">Kode Karyawan :</label>
                            <input type="text" class="form-control" id="emp_Code" name="emp_Code">
                        </div>
                        <div class="col-md-3">
                            <label for="emp_Name" class="form-label">Nama Karyawan :</label>
                            <input type="text" class="form-control" id="emp_Name" name="emp_Name">
                        </div>
                        <div class="col-md-3">
                            <label for="emp_NID" class="form-label">NID :</label>
                            <input type="text" class="form-control" id="emp_NID" name="emp_NID">
                        </div>
                        <div class="col-md-3">
                            <label for="emp_ActiveYN" class="form-label">Aktif (Y/T) :</label>
                            <select class="form-control bg-light small" id="emp_ActiveYN" name="emp_ActiveYN">
                                <option selected value="" >Pilih</option>
                                <option value="T">Tidak</option>
                                <option value="Y">Ya</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="emp_DivCode" class="form-label">Kode Divisi :</label>
                            <select class="form-control bg-light small" id="emp_DivCode" name="emp_DivCode">
                                <option selected value="" >Pilih</option>
                                @foreach($Divisis as $Divisi)
                                <option value="{{ $Divisi->div_auto }}">
                                    {{ $Divisi->Div_Name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="EMP_SUBDIVCODE" class="form-label">Kode Sub-Divisi :</label>
                            <select class="form-control bg-light small" id="EMP_SUBDIVCODE" name="EMP_SUBDIVCODE">
                                <option selected value="" >Pilih</option>
                                @foreach($SubDivisis as $SubDivisi)
                                <option value="{{ $SubDivisi->div_auto }}">
                                    {{ $SubDivisi->Div_Name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="emp_PosCode" class="form-label">Kode Posisi :</label>
                            <select class="form-control bg-light small" id="emp_PosCode" name="emp_PosCode">
                                <option selected value="" >Pilih</option>
                                @foreach($Posisis as $Posisi)
                                <option value="{{ $Posisi->pos_auto }}">
                                    {{ $Posisi->Pos_Name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="emp_Email" class="form-label">Email 1 :</label>
                            <input type="email" class="form-control" id="emp_Email" name="emp_Email">
                        </div>
                        <div class="col-md-3">
                            <label for="emp_Email2" class="form-label">Email 2 :</label>
                            <input type="email" class="form-control" id="emp_Email2" name="emp_Email2">
                        </div>
                        <div class="col-md-3">
                            <label for="emp_Web" class="form-label">Website :</label>
                            <input type="text" class="form-control" id="emp_Web" name="emp_Web">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="emp_Sex" class="form-label">Jenis Kelamin :</label>
                            <select class="form-control bg-light small" id="emp_Sex" name="emp_Sex">
                                <option selected value="" >Pilih</option>
                                <option value="M">Laki-laki</option>
                                <option value="F">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="emp_Marital" class="form-label">Status Pernikahan :</label>
                            <select class="form-control bg-light small" id="emp_Marital" name="emp_Marital">
                                <option selected value="" >Pilih</option>
                                <option value="S">Lajang</option>
                                <option value="M">Menikah</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="emp_Religion" class="form-label">Agama :</label>
                            <input type="text" class="form-control" id="emp_Religion" name="emp_Religion">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="emp_PlaceBorn" class="form-label">Tempat Lahir :</label>
                            <input type="text" class="form-control" id="emp_PlaceBorn" name="emp_PlaceBorn">
                        </div>
                        <div class="col-md-3">
                            <label for="emp_DateBorn" class="form-label">Tanggal Lahir :</label>
                            <input type="date" class="form-control" id="emp_DateBorn" name="emp_DateBorn">
                        </div>
                        <div class="col-md-3">
                            <label for="emp_blood" class="form-label">Golongan Darah :</label>
                            <select class="form-control bg-light small" id="emp_blood" name="emp_blood">
                                <option selected value="" >Pilih</option>
                                <option value="O">Golongan O</option>
                                <option value="A">Golongan A</option>
                                <option value="B">Golongan B</option>
                                <option value="AB">Golongan AB</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="emp_education" class="form-label">Pendidikan Terakhir :</label>
                            <select class="form-control bg-light small" id="emp_education" name="emp_education">
                                <option selected value="" >Pilih</option>
                                <option value="SMA">Sekolah Menengah Atas</option>
                                <option value="D3">Diploma Tiga</option>
                                <option value="S1">Strata 1</option>
                                <option value="S2">Strata 2</option>
                                <option value="S3">Strata 3</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="emp_Enroll" class="form-label">Tanggal Enroll :</label>
                            <input type="date" class="form-control" id="emp_Enroll" name="emp_Enroll">
                        </div>
                        <div class="col-md-3">
                            <label for="emp_startcontract" class="form-label">Tanggal Mulai Contract :</label>
                            <input type="date" class="form-control" id="emp_startcontract" name="emp_startcontract">
                        </div>
                        <div class="col-md-3">
                            <label for="emp_Expired" class="form-label">Tanggal Contract Expired :</label>
                            <input type="date" class="form-control" id="emp_Expired" name="emp_Expired">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="emp_permanent" class="form-label">Tanggal Permanent :</label>
                            <input type="date" class="form-control" id="emp_permanent" name="emp_permanent">
                        </div>
                        <div class="col-md-3">
                            <label for="emp_quit" class="form-label">Tanggal Quit :</label>
                            <input type="date" class="form-control" id="emp_quit" name="emp_quit">
                        </div>
                        <div class="col-md-3">
                            <label for="emp_reason" class="form-label">Kode Alasan :</label>
                            <select class="form-control bg-light small" id="emp_reason" name="emp_reason">
                                <option selected value="" >Pilih</option>
                                <option value="RES">Mengundurkan Diri</option>
                                <option value="PHK">Pemutusan Hubungan Kerja</option>
                                <option value="KON">Kontrak Berakhir</option>
                                <option value="PEN">Pensiun</option>
                                <option value="MDN">Meninggal Dunia</option>
                                <option value="DIS">Pelanggaran Disiplin</option>
                                <option value="MGK">Tidak Masuk</option>
                                <option value="MUT">Mutasi/Alih Kerja</option>
                                <option value="SAK">Sakit Berkepanjangan</option>
                                <option value="LNS">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="emp_office" class="form-label">Kode Kantor :</label>
                            <input type="text" class="form-control" id="emp_office" name="emp_office">
                        </div>
                        <div class="col-md-3">
                            <label for="EMP_SHIF" class="form-label">Kode Shift :</label>
                            <input type="text" class="form-control" id="EMP_SHIF" name="EMP_SHIF">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="EMP_status" class="form-label">Status Kerja :</label>
                            <select class="form-control bg-light small" id="EMP_status" name="EMP_status">
                                <option selected value="" >Pilih</option>
                                <option value="FT">Full Time</option>
                                <option value="PT">Part Time</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="emp_ptkp" class="form-label">Status PTKP :</label>
                            <input type="text" class="form-control" id="emp_ptkp" name="emp_ptkp">
                        </div>
                        <div class="col-md-3">
                            <label for="EMP_PAJAK" class="form-label">Status Pajak :</label>
                            <select class="form-control bg-light small" id="EMP_PAJAK" name="EMP_PAJAK">
                                <option selected value="" >Pilih</option>
                                <option value="01">Pajak Aktif</option>
                                <option value="02">Pajak Pasif</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="emp_bayar" class="form-label">Pembayaran :</label>
                            <select class="form-control bg-light small" id="emp_bayar" name="emp_bayar">
                                <option selected value="" >Pilih</option>
                                <option value="01">1</option>
                                <option value="02">2</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="emp_BANK" class="form-label">Kode Bank :</label>
                            <input type="text" class="form-control" id="emp_BANK" name="emp_BANK">
                        </div>
                        <div class="col-md-3">
                            <label for="emp_NOREK" class="form-label">Nomor Rekening :</label>
                            <input type="text" class="form-control" id="emp_NOREK" name="emp_NOREK">
                        </div>
                        <div class="col-md-3">
                            <label for="emp_PEMILIK" class="form-label">Pemilik :</label>
                            <input type="text" class="form-control" id="emp_PEMILIK" name="emp_PEMILIK">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="emp_NPWP" class="form-label">Nomor NPWP :</label>
                            <input type="text" class="form-control" id="emp_NPWP" name="emp_NPWP">
                        </div>
                        <div class="col-md-3">
                            <label for="EMP_JAMSOSTEK" class="form-label">JAMSOSTEK :</label>
                            <input type="text" class="form-control" id="EMP_JAMSOSTEK" name="EMP_JAMSOSTEK">
                        </div>
                        <div class="col-md-3">
                            <label for="emp_datejamsostek" class="form-label">Terdaftar JAMSOSTEK :</label>
                            <input type="date" class="form-control" id="emp_datejamsostek" name="emp_datejamsostek">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="emp_ktp" class="form-label">Status KTP :</label>
                            <select class="form-control bg-light small" id="emp_ktp" name="emp_ktp">
                                <option selected value="" >Pilih</option>
                                <option value="01">1</option>
                                <option value="02">2</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="emp_no_ktp" class="form-label">Nomor KTP :</label>
                            <input type="text" class="form-control" id="emp_no_ktp" name="emp_no_ktp">
                        </div>
                    </div>
                    <hr>

                    {{-- Detail Alamat --}}
                    <h5 class="mb-3">Detail Alamat</h5>
                    <ul class="nav nav-tabs" id="formEmployeeTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="form_address_tab1" data-bs-toggle="tab" data-bs-target="#form_address1" type="button" role="tab" aria-controls="form_address1" aria-selected="true">Data Alamat 1</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="form_address_tab2" data-bs-toggle="tab" data-bs-target="#form_address2" type="button" role="tab" aria-controls="form_address2" aria-selected="false">Data Alamat 2</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="formEmployeeTabContent">
                        <div class="tab-pane fade show active" id="form_address1" role="tabpanel" aria-labelledby="form_address_tab1">
                            <div class="row my-4 ml-2">
                                <div class="col-md-3">
                                    <label for="emp_Address" class="form-label">Alamat 1 :</label>
                                    <textarea type="text" name="emp_Address" id="emp_Address" placeholder="Alamat lengkap" class="form-control"></textarea>
                                </div>
                                <div class="col-md-3">
                                    <label for="emp_CityCode" class="form-label">Kode Kota 1 :</label>
                                    <input type="text" class="form-control" id="emp_CityCode" name="emp_CityCode">
                                </div>
                                <div class="col-md-3">
                                    <label for="emp_ProvinceCode" class="form-label">Kode Provinsi 1 :</label>
                                    <input type="text" class="form-control" id="emp_ProvinceCode" name="emp_ProvinceCode">
                                </div>
                                <div class="col-md-3">
                                    <label for="emp_ZipCode" class="form-label">Kode Pos 1 :</label>
                                    <input type="text" class="form-control" id="emp_ZipCode" name="emp_ZipCode">
                                </div>
                            </div>
                            <div class="row my-4 ml-2">
                                <div class="col-md-3">
                                    <label for="emp_Phone1" class="form-label">Nomor Telpon 1 :</label>
                                    <input type="text" class="form-control" id="emp_Phone1" name="emp_Phone1">
                                </div>
                                <div class="col-md-3">
                                    <label for="emp_Phone2" class="form-label">Nomor Telpon 2 :</label>
                                    <input type="text" class="form-control" id="emp_Phone2" name="emp_Phone2">
                                </div>
                                <div class="col-md-3">
                                    <label for="emp_hp1" class="form-label">Nomor Handphone 1 :</label>
                                    <input type="text" class="form-control" id="emp_hp1" name="emp_hp1">
                                </div>
                                <div class="col-md-3">
                                    <label for="emp_hp2" class="form-label">Nomor Handphone 2 :</label>
                                    <input type="text" class="form-control" id="emp_hp2" name="emp_hp2">
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="form_address2" role="tabpanel" aria-labelledby="form_address_tab2">
                            <div class="row my-4 ml-2">
                                <div class="col-md-3">
                                    <label for="emp_Address2" class="form-label">Alamat 2 :</label>
                                    <textarea type="text" name="emp_Address2" id="emp_Address2" placeholder="Alamat lengkap" class="form-control"></textarea>
                                </div>
                                <div class="col-md-3">
                                    <label for="emp_CityCode2" class="form-label">Kode Kota 2 :</label>
                                    <input type="text" class="form-control" id="emp_CityCode2" name="emp_CityCode2">
                                </div>
                                <div class="col-md-3">
                                    <label for="emp_ProvinceCode2" class="form-label">Kode Provinsi 2 :</label>
                                    <input type="text" class="form-control" id="emp_ProvinceCode2" name="emp_ProvinceCode2">
                                </div>
                                <div class="col-md-3">
                                    <label for="emp_ZipCode2" class="form-label">Kode Pos 2 :</label>
                                    <input type="text" class="form-control" id="emp_ZipCode2" name="emp_ZipCode2">
                                </div>
                            </div>
                            <div class="row my-4 ml-2">
                                <div class="col-md-3">
                                    <label for="emp_Phone3" class="form-label">Nomor Telpon 3 :</label>
                                    <input type="text" class="form-control" id="emp_Phone3" name="emp_Phone3">
                                </div>
                                <div class="col-md-3">
                                    <label for="emp_Phone4" class="form-label">Nomor Telpon 4 :</label>
                                    <input type="text" class="form-control" id="emp_Phone4" name="emp_Phone4">
                                </div>
                                <div class="col-md-3">
                                    <label for="emp_hp3" class="form-label">Nomor Handphone 3 :</label>
                                    <input type="text" class="form-control" id="emp_hp3" name="emp_hp3">
                                </div>
                                <div class="col-md-3">
                                    <label for="emp_hp4" class="form-label">Nomor Handphone 4 :</label>
                                    <input type="text" class="form-control" id="emp_hp4" name="emp_hp4">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    {{-- Tombol ini yang seharusnya menutup modal --}}
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanEmployee">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

 

@endsection


@push('scripts')
<script>
    $(document).ready(function() {
        // DataTable tetap pakai var
        var table = $('#dataTable').DataTable();

        // Sisanya pakai const
        const csrfToken       = $('meta[name="csrf-token"]').attr('content');
        const viewModalEl     = document.getElementById('viewEmployeeModal');
        const employeeModalEl = document.getElementById('employeeModal');
        const cropModalEl     = document.getElementById('cropImageModal');

        const viewModal       = new bootstrap.Modal(viewModalEl);
        const employeeModal   = new bootstrap.Modal(employeeModalEl);
        const cropModal       = new bootstrap.Modal(cropModalEl);
        
        const form            = $('#employeeForm');
        const modalTitle      = $('#employeeModalLabel');
        const btnSimpan       = $('#btnSimpanEmployee');
        
        let cropper;
        const imageToCrop     = document.getElementById('image-to-crop');
        const previewImage    = $('#image-preview');
        const deleteImageBtn  = $('#delete-image-btn');
        const fileInput       = $('#EMP_PICT');
        const uploadLabel     = $('#upload-label');
        let croppedImageBlob  = null;

        // Fungsi untuk mereset form dan state modal
        function resetForm() {
            form[0].reset();
            $('#Employee_Id').val('');
            $('#formMethod').val('POST');
            modalTitle.text('Tambah Data Karyawan Baru');
            btnSimpan.html('Simpan');
            $('#emp_Address').attr('placeholder', 'Alamat Lengkap');
            $('#emp_Address2').attr('placeholder', 'Alamat Lengkap');

            $('#emp_Auto').val('').prop('disabled', false);
            $('#emp_DivCode').val(''); // Reset divisi

            // Reset dan nonaktifkan dropdown sub-divisi
            $('#EMP_SUBDIVCODE').html('<option value="">Pilih Divisi Terlebih Dahulu</option>').prop('disabled', true);
            
            resetImageState();
            // Kembalikan Tab ke posisi awal
            // Gunakan selector ID yang baru dan unik
            var initialTab = new bootstrap.Tab(document.getElementById('form_address_tab1'));
            initialTab.show();
        }

        function resetImageState() {
            if (cropper) cropper.destroy();
            cropper = null;
            fileInput.val('');
            previewImage.attr('src', '#').hide();
            uploadLabel.show().text('Pratinjau Foto');
            deleteImageBtn.hide();
            croppedImageBlob = null;
            $('#delete_photo_input').val('0');
        }

        // Handler tombol “View”
        $(document).on('click', '.btn-view', function() {
            const id = $(this).data('id');

            $.ajax({
                url: `/presensi/employee/${id}`,
                method: 'GET',
                dataType: 'json',
                success: function(emp) {
                    // Isi data utama
                    $('#viewEmpAuto').text(emp.emp_Auto);
                    $('#viewEmpCode').text(emp.emp_Code);
                    $('#viewEmpName').text(emp.emp_Name);
                    $('#viewEmpNID').text(emp.emp_NID);

                    // Data Employment
                    $('#viewEmpActiveYN').text(emp.emp_ActiveYN);
                    $('#viewEmpDivCode').text(emp.emp_DivCode);
                    $('#viewEmpSubDivCode').text(emp.EMP_SUBDIVCODE);
                    $('#viewEmpPosCode').text(emp.emp_PosCode);

                    // Contact
                    $('#viewEmpEmail1').text(emp.emp_password);
                    $('#viewEmpEmail1').text(emp.emp_Email || '-');
                    $('#viewEmpEmail2').text(emp.emp_Email2 || '-');
                    $('#viewEmpWeb').text(emp.emp_Web || '-');

                    // Personal
                    $('#viewEmpSex').text(emp.emp_Sex);
                    $('#viewEmpMarital').text(emp.emp_Marital);
                    $('#viewEmpReligion').text(emp.emp_Religion);

                    $('#viewEmpPlaceBorn').text(emp.emp_PlaceBorn);
                    $('#viewEmpDateBorn').text(emp.emp_DateBorn);

                    $('#viewEmpBlood').text(emp.emp_blood);
                    $('#viewEmpEducation').text(emp.emp_education);

                    // Contract
                    $('#viewEmpEnroll').text(emp.emp_Enroll);
                    $('#viewEmpStartContract').text(emp.emp_startcontract);
                    $('#viewEmpExpired').text(emp.emp_Expired);
                    $('#viewEmpPermanent').text(emp.emp_permanent);
                    $('#viewEmpQuit').text(emp.emp_quit);

                    $('#viewEmpReason').text(emp.emp_reason);
                    $('#viewEmpOffice').text(emp.emp_office);
                    $('#viewEmpShif').text(emp.EMP_SHIF);

                    $('#viewEmpStatus').text(emp.EMP_status);
                    $('#viewEmpPtkp').text(emp.emp_ptkp);
                    $('#viewEmpPajak').text(emp.EMP_PAJAK);

                    $('#viewEmpBayar').text(emp.emp_bayar);
                    $('#viewEmpBank').text(emp.emp_BANK);
                    $('#viewEmpNorek').text(emp.emp_NOREK);
                    $('#viewEmpPemilik').text(emp.emp_PEMILIK);

                    $('#viewEmpNpwp').text(emp.emp_NPWP);
                    $('#viewEmpJamsostek').text(emp.EMP_JAMSOSTEK);
                    $('#viewEmpDateJamsostek').text(emp.emp_datejamsostek);

                    $('#viewEmpKtp').text(emp.emp_ktp);
                    $('#viewEmpNoKtp').text(emp.emp_no_ktp);

                    // Address 1
                    $('#viewEmpAddress1').text(emp.emp_Address);
                    $('#viewEmpCityCode1').text(emp.emp_CityCode);
                    $('#viewEmpProvinceCode1').text(emp.emp_ProvinceCode);
                    $('#viewEmpZipCode1').text(emp.emp_ZipCode);

                    $('#viewEmpPhone1').text(emp.emp_Phone1);
                    $('#viewEmpPhone2').text(emp.emp_Phone2);
                    $('#viewEmpHp1').text(emp.emp_hp1);
                    $('#viewEmpHp2').text(emp.emp_hp2);

                    // Address 2
                    $('#viewEmpAddress2').text(emp.emp_Address2 || '-');
                    $('#viewEmpCityCode2').text(emp.emp_CityCode2 || '-');
                    $('#viewEmpProvinceCode2').text(emp.emp_ProvinceCode2 || '-');
                    $('#viewEmpZipCode2').text(emp.emp_ZipCode2 || '-');

                    $('#viewEmpPhone3').text(emp.emp_Phone3 || '-');
                    $('#viewEmpPhone4').text(emp.emp_Phone4 || '-');
                    $('#viewEmpHp3').text(emp.emp_hp3 || '-');
                    $('#viewEmpHp4').text(emp.emp_hp4 || '-');

                    // Logika untuk menampilkan gambar di modal view
                    const viewImageContainer = $('#viewEmpPict');
                    viewImageContainer.empty(); // Selalu kosongkan kontainer dulu

                    if (emp.EMP_PICT) {
                        // Buat path gambar, tambahkan parameter acak untuk mencegah cache
                        const imageUrl = `/storage/employee_pictures/${emp.EMP_PICT}?t=${new Date().getTime()}`;
                        
                        // Buat elemen gambar baru
                        const imageTag = $('<img>', {
                            src: imageUrl,
                            alt: 'Foto Karyawan',
                            class: 'img-fluid rounded', // Class Bootstrap untuk styling
                            style: 'max-width: 200px; height: auto;' // Batasi ukuran agar tidak terlalu besar
                        });

                        // Tambahkan gambar ke dalam span
                        viewImageContainer.append(imageTag);
                    } else {
                        // Jika tidak ada gambar, tampilkan teks
                        viewImageContainer.text('Tidak ada gambar');
                    }

                    // Tampilkan modal
                    viewModal.show();
                },
                error: function() {
                    Swal.fire('Error', 'Gagal memuat data karyawan.', 'error');
                }
            });
        });

        // === Logika Upload & Crop Gambar ===
        fileInput.on('change', function(e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    imageToCrop.src = event.target.result;
                    cropModal.show();
                };
                reader.readAsDataURL(files[0]);
            }
            $(this).val('');
        });

        cropModalEl.addEventListener('shown.bs.modal', function () {
            $(this).css('z-index', 1060);
            $('.modal-backdrop').last().css('z-index', 1059);
            if (cropper) cropper.destroy();
            cropper = new Cropper(imageToCrop, {
                aspectRatio: 1 / 1, viewMode: 1,
            });
        });

        cropModalEl.addEventListener('hidden.bs.modal', function () {
            $('body').addClass('modal-open');
            if (cropper) cropper.destroy();
            cropper = null;
        });

        $('#crop-and-save').on('click', function() {
            const canvas = cropper.getCroppedCanvas({ width: 320, height: 320 });
            previewImage.attr('src', canvas.toDataURL()).show();
            uploadLabel.hide();
            deleteImageBtn.show();
            canvas.toBlob(blob => { croppedImageBlob = blob; }, 'image/png');
            cropModal.hide();
        });

        deleteImageBtn.on('click', function() {
            resetImageState();
            $('#delete_photo_input').val('1');
            uploadLabel.show().text('Foto telah dihapus');
        });

        //Tambah Karyawan Handler
        $('#addEmployeeButton').on('click', function() {
            resetForm();
            $('#emp_Auto').closest('.col-md-3').parent('.row').hide();
            employeeModal.show();
        });

        //Edit Karyawan Handler
        $(document).on('click', '.btn-edit', function() {
            const employeeId = $(this).data('id');
            resetForm();
            modalTitle.text('Edit Data Karyawan');
            btnSimpan.html('Update');

            $.ajax({
                url: `/presensi/employee/${employeeId}`,
                method: 'GET',
                success: function(data) {
                    $('#Employee_Id').val(data.emp_Auto);
                    $('#formMethod').val('PUT');
                    $('#emp_Address').val(data.emp_Address); // Akan mengisi area teks
                    $('#emp_Address2').val(data.emp_Address2); // Akan mengisi area teks
                    // Penting: Kosongkan nilai textarea agar placeholder terlihat
                    $('#emp_Address').val('');
                    $('#emp_Address2').val('');

                    // === PERBAIKAN UTAMA DI SINI ===
                    // Mengisi form sambil melewati field input file
                    $.each(data, function(key, value) {
                        // Lewati iterasi jika key-nya adalah EMP_PICT untuk mencegah error
                        if (key === 'EMP_PICT') {
                            return; // setara dengan 'continue' dalam loop biasa
                        }

                        let field = $(`#${key}`);
                        if (field.length) {
                             if (field.is('[type="date"]')) {
                                // Format tanggal Y-m-d untuk input date
                                field.val(value ? value.split(' ')[0] : null);
                            } else {
                                field.val(value);
                            }
                        }
                    });

                    // Logika untuk menampilkan pratinjau gambar yang sudah ada
                    if (data.EMP_PICT) {
                        // Tambahkan cache-busting query string
                        const imageUrl = `/storage/employee_pictures/${data.EMP_PICT}?t=` + new Date().getTime();
                        previewImage.attr('src', imageUrl).show();
                        uploadLabel.hide();
                        deleteImageBtn.show();
                    } else {
                        resetImageState();
                    }

                    $('#emp_Auto').prop('disabled', true);
                    $('#emp_DivCode').trigger('change', [data.EMP_SUBDIVCODE]);
                    employeeModal.show();
                },
                error: function() {
                    Swal.fire('Error', 'Gagal memuat data untuk diedit.', 'error');
                }
            });
        });



        //Submit Form Add & Edit Form
        form.on('submit', function(e) {
            e.preventDefault();
            const id = $('#Employee_Id').val();
            const method = $('#formMethod').val();
            const url = (method === 'POST') ? '{{ route("employee.store") }}' : `/presensi/employee/${id}`;
            const formData = new FormData(this);

            if (croppedImageBlob) {
                formData.append('EMP_PICT', croppedImageBlob, 'employee_photo.png');
            }
            if (method === 'PUT') {
                formData.append('_method', 'PUT');
            }

            $.ajax({
                url: url, method: 'POST', data: formData,
                processData: false, contentType: false, dataType: 'json',
                success: function(response) {
                    employeeModal.hide();
                    Swal.fire({
                        icon: 'success', title: 'Berhasil', text: response.message,
                        timer: 1500, showConfirmButton: false
                    }).then(() => location.reload());
                },
                error: function(xhr) {
                    let html = xhr.responseJSON?.message || 'Terjadi kesalahan server.';
                    if (xhr.status === 422 && xhr.responseJSON.errors) {
                        html = '<ul class="text-left p-0" style="list-style-type: none;">';
                        $.each(xhr.responseJSON.errors, (key, value) => {
                            html += `<li>${value[0]}</li>`;
                        });
                        html += '</ul>';
                    }
                    Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', html: html });
                }
            });
        });


        //CASCADING DROPDOWN
        $('#emp_DivCode').on('change', function(event, subDivToSelect) {
            const divisiId = $(this).val();
            const subDivDropdown = $('#EMP_SUBDIVCODE');

            // Kosongkan dan nonaktifkan jika tidak ada divisi yang dipilih
            if (!divisiId) {
                subDivDropdown.html('<option value="">Pilih Divisi Terlebih Dahulu</option>').prop('disabled', true);
                return;
            }

            // Ambil data sub-divisi dari server
            $.ajax({
                url: `/presensi/get-subdivisi/${divisiId}`,
                method: 'GET',
                success: function(subDivisions) {
                    subDivDropdown.empty().append('<option value="">Pilih Sub-Divisi</option>').prop('disabled', false);
                    
                    if (subDivisions.length > 0) {
                        $.each(subDivisions, function(index, subDiv) {
                            subDivDropdown.append($('<option>', {
                                value: subDiv.div_auto,
                                text: subDiv.Div_Name
                            }));
                        });
                    } else {
                        subDivDropdown.html('<option value="">Tidak ada Sub-Divisi</option>').prop('disabled', true);
                    }

                    // Jika ada parameter subDivToSelect (dari mode edit), pilih opsinya
                    if (subDivToSelect) {
                        subDivDropdown.val(subDivToSelect);
                    }
                },
                error: function() {
                    subDivDropdown.html('<option value="">Gagal memuat data</option>').prop('disabled', true);
                }
            });
        });

        // FUNGSI DELETE (BARU & DISESUAIKAN)
        $(document).on('click', '.btn-delete', function(){
            const id = $(this).data('id');
            const itemName = $(this).data('nama');
            const deleteUrl = `/presensi/employee/${id}`;

            Swal.fire({
                title: 'Apakah Anda yakin?',
                html: `Anda akan menghapus karyawan: <strong>${itemName}</strong>.<br><small>Tindakan ini tidak dapat dibatalkan.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteUrl,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: csrfToken
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: response.message || 'Data karyawan berhasil dihapus.',
                                timer: 1500,
                                showConfirmButton: false,
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(jqXHR) {
                            let errorMsg = jqXHR.responseJSON?.message || 'Gagal menghapus data.';
                            Swal.fire('Gagal!', errorMsg, 'error');
                        }
                    });
                }
            });
        });
    });
</script>

@endpush