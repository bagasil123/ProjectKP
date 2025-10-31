@extends('layouts.admin')

@section('main-content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Hak Akses</h1>
    <p class="mb-4">Atur Hak Akses User</p>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Form Pengguna {{ isset($memberToEdit) ? 'Edit' : 'Baru' }}</h6>
        </div>
        <div class="card-body">
            <form id="memberForm" action="{{ isset($memberToEdit) ? route('keamanan.member.update', $memberToEdit->Mem_Auto) : route('keamanan.member.store') }}" method="POST">
                @csrf
                @if(isset($memberToEdit))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Mem_ID">Kode Karyawan</label>
                            <select name="Mem_ID" id="Mem_ID" class="form-control" {{ isset($memberToEdit) ? 'disabled' : 'required' }}>
                                @if(isset($memberToEdit))
                                    <option value="{{ $memberToEdit->Mem_ID }}" selected>
                                        {{ $memberToEdit->Mem_UserName }} ({{ $memberToEdit->Mem_ID }})
                                    </option>
                                @else
                                    <option value="">-- Pilih Karyawan --</option>
                                @endif
                            </select>
                            @if(isset($memberToEdit))
                                <input type="hidden" name="Mem_ID" value="{{ $memberToEdit->Mem_ID }}">
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Mem_UserName">Nama Pengguna</label>
                            <input type="text" name="Mem_UserName" id="Mem_UserName" class="form-control bg-light" 
                                value="{{ old('Mem_UserName', $memberToEdit->Mem_UserName ?? '') }}" readonly required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="mem_password" class="font-weight-bold">Password</label>
                            <div class="input-group">
                                <input type="password" name="mem_password" id="mem_password" class="form-control"
                                    placeholder="{{ isset($memberToEdit) ? 'Kosongkan jika tidak diubah' : '' }}"
                                    {{ isset($memberToEdit) ? '' : 'required' }}
                                    minlength="4">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Minimal 6 karakter</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="confirm_password" class="font-weight-bold">Konfirmasi Password</label>
                            <div class="input-group">
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                                    {{ isset($memberToEdit) ? '' : 'required' }}
                                    minlength="4"
                                    data-parsley-equalto="#mem_password"
                                    data-parsley-error-message="Password tidak sama">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="Mem_ActiveYN">Status</label>
                            <select name="Mem_ActiveYN" id="Mem_ActiveYN" class="form-control">
                                <option value="Y" {{ old('Mem_ActiveYN', $memberToEdit->Mem_ActiveYN ?? 'Y') == 'Y' ? 'selected' : '' }}>Aktif</option>
                                <option value="N" {{ old('Mem_ActiveYN', $memberToEdit->Mem_ActiveYN ?? '') == 'N' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="role_id">Role Utama</label>
                            <select name="role_id" id="role_id" class="form-control" required>
                                <option value="">-- Pilih Role --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" 
                                            data-role-name="{{ $role->name }}" {{-- Atribut ini penting untuk JS --}}
                                            {{ old('role_id', $memberToEdit->role_id ?? '') == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row" id="warehouse-access-section" style="display: none;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold"><i class="fas fa-warehouse"></i> Akses Gudang</h6>
                                <button type="button" id="add-warehouse-btn" class="btn btn-success btn-sm" title="Tambah Gudang">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                            <p class="text-muted" style="font-size: 0.9rem; margin-top: -10px;">
                                Pilih gudang yang dapat diakses oleh user ini.
                            </p>
                            <div id="warehouse-list-container">
                                </div>
                        </div>
                    </div>
                </div>
                <div id="userAccessSection" class="mt-4 d-none">
                    <h5 class="font-weight-bold mb-3">Hak Akses Per Role</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Role</th>
                                    <th class="text-center">Tambah</th>
                                    <th class="text-center">Ubah</th>
                                    <th class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="selectedRoleNameDisplay"></td>
                                    <td class="text-center">
                                        <input type="checkbox" name="akses_role[0][tambah]" id="akses_role_tambah" value="1" class="form-check-input">
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" name="akses_role[0][ubah]" id="akses_role_ubah" value="1" class="form-check-input">
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" name="akses_role[0][hapus]" id="akses_role_hapus" value="1" class="form-check-input">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-group mt-4">
                    @php
                        $currentRouteName = Route::currentRouteName();
                        $currentMenuSlug = Str::beforeLast($currentRouteName, '.'); 
                    @endphp

                    @unless(isset($memberToEdit))
                        @can('tambah', $currentMenuSlug)
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Pengguna Baru
                        </button>
                        @endcan
                    @endunless

                    @if(isset($memberToEdit))
                        @can('ubah', $currentMenuSlug)
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Perbarui Pengguna
                        </button>
                        @endcan
                    @endif

                    @if(isset($memberToEdit))
                    <a href="{{ route('keamanan.member.index') }}" class="btn btn-secondary">
                        <i class="fas fa-plus"></i> Buat Baru
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Pengguna</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($members as $member)
                        <tr>
                            <td>{{ $member->Mem_ID }}</td>
                            <td>{{ $member->Mem_UserName }}</td>
                            <td>{{ $member->role->name ?? '-' }}</td>
                            <td>
                                <span class="badge badge-{{ $member->Mem_ActiveYN == 'Y' ? 'success' : 'danger' }}">
                                    {{ $member->Mem_ActiveYN == 'Y' ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @can('ubah', $currentMenuSlug)
                                <a href="{{ route('keamanan.member.edit', $member->Mem_Auto) }}" class="btn btn-sm btn-warning edit-btn">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                @can('hapus', $currentMenuSlug)
                                <form action="{{ route('keamanan.member.destroy', $member->Mem_Auto) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger delete-btn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Link untuk Select2 --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" /> 
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> 

{{-- Link untuk moment.js (dipakai oleh select2) dan parsley.js (dipakai oleh form) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/parsleyjs@2.9.2/dist/parsley.min.js"></script> 


<script>
    // Data yang dilewatkan dari Laravel ke JavaScript
    const allRolesData = @json($roles);
    const simplifiedAccesses = @json($simplifiedAccesses); 
    const isEditMode = {{ isset($memberToEdit) ? 'true' : 'false' }};


    const allWarehouses = @json($data['warehouses'] ?? $warehouses ?? []);
    const memberWarehouses = @json(old('warehouses', $memberWarehouses ?? [])); 

    /**
     * Membuat template HTML untuk satu baris dropdown gudang.
     */
    const warehouseRowTemplate = (selectedId = '') => {
        let options = allWarehouses.map(wh => {
            if (!wh) return ''; // Pengaman jika ada data null
            
            const code = wh.WARE_Auto || 'N/A';
            const name = wh.WARE_Name || 'N/A';
            
            let isDisabled = false;
            document.querySelectorAll('.warehouse-select').forEach(select => {
                if (select.value == code && select.value != selectedId) {
                    isDisabled = true;
                }
            });
            // ===================================================================
            // BLOK DUPLIKAT YANG MENYEBABKAN ERROR TELAH DIHAPUS DARI SINI
            // ===================================================================

            return `<option value="${code}" 
                        ${selectedId == code ? 'selected' : ''} 
                        ${isDisabled ? 'disabled' : ''}>
                        ${code} - ${name}
                    </option>`;
        }).join('');

        return `
        <div class="row warehouse-row mb-2">
            <div class="col-10">
                <select class="form-control warehouse-select" name="warehouses[]" required>
                    <option value="">Pilih Gudang</option>
                    ${options}
                </select>
            </div>
            <div class="col-2 d-flex align-items-center">
                <button type="button" class="btn btn-danger btn-sm remove-warehouse-btn" title="Hapus">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>`;
    };

    /**
     * Mengupdate opsi di semua dropdown gudang agar tidak bisa dipilih duplikat.
     */
    function updateWarehouseSelectOptions() {
        const selectedValues = [];
        document.querySelectorAll('.warehouse-select').forEach(select => {
            if(select.value) selectedValues.push(select.value);
        });

        document.querySelectorAll('.warehouse-select').forEach(select => {
            const currentValue = select.value;
            select.querySelectorAll('option').forEach(option => {
                if (option.value && option.value !== currentValue) {
                    option.disabled = selectedValues.includes(option.value);
                }
            });
        });
    }

    /**
     * Mengisi input Nama Pengguna dan Password berdasarkan pilihan Karyawan dari Select2.
     */
    function populateUserDataFromSelect2(data) {
        const userNameInput = document.getElementById('Mem_UserName');
        const passwordInput = document.getElementById('mem_password');
        const confirmPasswordInput = document.getElementById('confirm_password');

        if (data && data.text) {
            const userNameMatch = data.text.match(/^(.*) \((.*)\)$/);
            userNameInput.value = userNameMatch ? userNameMatch[1] : data.text;

            if (data.birth_date) {
                passwordInput.value = data.birth_date;
                confirmPasswordInput.value = data.birth_date;
            } else {
                passwordInput.value = '';
                confirmPasswordInput.value = '';
                passwordInput.placeholder = 'Tanggal lahir tidak tersedia';
                confirmPasswordInput.placeholder = 'Tanggal lahir tidak tersedia';
            }
        } else {
            userNameInput.value = '';
            passwordInput.value = '';
            confirmPasswordInput.value = '';
            passwordInput.placeholder = '';
            confirmPasswordInput.placeholder = '';
        }
    }

    /**
     * Mengatur visibilitas bagian hak akses dan gudang.
     */
    function toggleAccessSection(selectedRoleId) {
        const userAccessSection = document.getElementById('userAccessSection');
        const selectedRoleNameDisplay = document.getElementById('selectedRoleNameDisplay');
        const aksesTambahCheckbox = document.getElementById('akses_role_tambah');
        const aksesUbahCheckbox = document.getElementById('akses_role_ubah');
        const aksesHapusCheckbox = document.getElementById('akses_role_hapus');
        const oldAksesRoleData = @json(old('akses_role', []));

        // Logika untuk Gudang
        const warehouseAccessSection = document.getElementById('warehouse-access-section');
        const warehouseListContainer = document.getElementById('warehouse-list-container');
        // Ambil nama role dari data-attribute
        const selectedRoleName = $(`#role_id option[value="${selectedRoleId}"]`).data('role-name');
        
        if (selectedRoleId) {
            userAccessSection.classList.remove('d-none');
            
            const selectedRole = allRolesData.find(role => role.id == selectedRoleId);
            selectedRoleNameDisplay.textContent = selectedRole ? selectedRole.name : 'N/A';

            // Logika checkbox hak akses
            let isTambah = false, isUbah = false, isHapus = false;
            if (oldAksesRoleData[selectedRoleId]) {
                isTambah = oldAksesRoleData[selectedRoleId].tambah === '1';
                isUbah = oldAksesRoleData[selectedRoleId].ubah === '1';
                isHapus = oldAksesRoleData[selectedRoleId].hapus === '1';
            } else if (simplifiedAccesses[selectedRoleId]) {
                isTambah = simplifiedAccesses[selectedRoleId].tambah === '1';
                isUbah = simplifiedAccesses[selectedRoleId].ubah === '1';
                isHapus = simplifiedAccesses[selectedRoleId].hapus === '1';
            }
            aksesTambahCheckbox.checked = isTambah;
            aksesUbahCheckbox.checked = isUbah;
            aksesHapusCheckbox.checked = isHapus;
            aksesTambahCheckbox.name = `akses_role[${selectedRoleId}][tambah]`;
            aksesUbahCheckbox.name = `akses_role[${selectedRoleId}][ubah]`;
            aksesHapusCheckbox.name = `akses_role[${selectedRoleId}][hapus]`;

            // ===================================================================
            // Cek nama Role. Pastikan 'Admin Gudang' adalah nama yang
            // benar-benar ada di database Anda
            // ===================================================================
            if (selectedRoleName === 'Admin Gudang') { 
                warehouseAccessSection.style.display = 'block';
                if (warehouseListContainer.children.length === 0) {
                    warehouseListContainer.innerHTML = warehouseRowTemplate();
                }
            } else {
                warehouseAccessSection.style.display = 'none';
                warehouseListContainer.innerHTML = ''; // Kosongkan
            }
            // ===================================================================

        } else {
            // Sembunyikan semua jika tidak ada role dipilih
            userAccessSection.classList.add('d-none');
            selectedRoleNameDisplay.textContent = '';
            
            aksesTambahCheckbox.checked = false;
            aksesUbahCheckbox.checked = false;
            aksesHapusCheckbox.checked = false;
            aksesTambahCheckbox.name = `akses_role[0][tambah]`;
            aksesUbahCheckbox.name = `akses_role[0][ubah]`;
            aksesHapusCheckbox.name = `akses_role[0][hapus]`;

            warehouseAccessSection.style.display = 'none';
            warehouseListContainer.innerHTML = ''; 
        }
    }

    /**
     * Mereset form ke mode "Buat Baru".
     */
    function resetForm() {
        document.getElementById('memberForm').reset();
        document.getElementById('memberForm').action = "{{ route('keamanan.member.store') }}";
        
        const methodInput = document.querySelector('#memberForm input[name="_method"]');
        if (methodInput) {
            methodInput.remove();
        }
        $('#Mem_ID').val('').trigger('change');
        $('#Mem_ID').empty().append('<option value="">-- Pilih Karyawan --</option>');
        $('#Mem_ID').prop('disabled', false);
        initSelect2();
        document.getElementById('Mem_UserName').value = '';
        document.getElementById('mem_password').value = '';
        document.getElementById('confirm_password').value = '';
        document.getElementById('mem_password').placeholder = '';
        document.getElementById('confirm_password').placeholder = '';
        document.getElementById('role_id').value = '';
        toggleAccessSection('');
        
        // Sembunyikan tombol "Buat Baru"
        const createNewButton = document.querySelector('a.btn-secondary');
        if (createNewButton) {
            createNewButton.style.display = 'none';
        }

        document.getElementById('warehouse-list-container').innerHTML = '';
        document.getElementById('warehouse-access-section').style.display = 'none';
        
        history.pushState(null, '', '{{ route('keamanan.member.index') }}');
    }

    /**
     * Inisialisasi Select2 untuk Karyawan
     */
    function initSelect2() {
        if ($('#Mem_ID').hasClass('select2-hidden-accessible')) {
            $('#Mem_ID').select2('destroy');
        }
        $('#Mem_ID').select2({
            placeholder: '-- Pilih Karyawan --',
            allowClear: true,
            theme: "bootstrap4",
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('keamanan.member.searchEmployees') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term || '',
                        page: params.page || 1,
                        load_all: params.term ? 0 : 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results || [],
                        pagination: {
                            more: data.pagination?.more || false
                        }
                    };
                },
                cache: true
            },
            templateResult: function(data) {
                if (data.loading) return data.text;
                return data.text || '-- Pilih Karyawan --';
            },
            templateSelection: function(data) {
                if (data.id === '') return data.text;
                if (data.birth_date) {
                    populateUserDataFromSelect2(data);
                }
                return data.text || '-- Pilih Karyawan --';
            }
        });
        $('#Mem_ID').on('select2:open', function() {
            const select = $(this);
            if (select.find('option').length <= 1 && !select.data('initial-load')) {
                select.data('initial-load', true);
                select.select2('search', '');
            }
        });
        if (isEditMode) {
            const selectedEmployeeId = '{{ $memberToEdit->Mem_ID ?? '' }}';
            const selectedEmployeeName = '{{ $memberToEdit->Mem_UserName ?? '' }}';
            const selectedEmployeeBirthDate = '{{ $memberToEdit->employee->emp_DateBorn ?? '' }}';

            if (selectedEmployeeId) {
                const option = new Option(
                    `${selectedEmployeeName} (${selectedEmployeeId})`,
                    selectedEmployeeId,
                    true,
                    true
                );
                $('#Mem_ID').append(option).trigger('change');
                populateUserDataFromSelect2({
                    id: selectedEmployeeId,
                    text: `${selectedEmployeeName} (${selectedEmployeeId})`,
                    birth_date: selectedEmployeeBirthDate ? 
                        moment(selectedEmployeeBirthDate).format('YYYYMMDD') : null
                });
                $('#Mem_ID').prop('disabled', true);
            }
        }
    }

    // ===================================================================
    // Eksekusi saat Dokumen Siap
    // ===================================================================
    $(document).ready(function() {
        
        $('#dataTable').DataTable();
        
        // Notifikasi SweetAlert
        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Sukses', text: '{{ session('success') }}', timer: 3000, showConfirmButton: false });
        @endif
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Error', text: '{{ session('error') }}', timer: 5000 });
        @endif
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                html: `<strong>Validasi gagal:</strong><ul class="text-left mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>`,
                showConfirmButton: true
            });
        @endif

        // Inisialisasi
        initSelect2();

        // Inisialisasi bagian Hak Akses dan Gudang saat halaman dimuat
        const roleIdSelect = document.getElementById('role_id');
        if (roleIdSelect && roleIdSelect.value) {
            toggleAccessSection(roleIdSelect.value);

            const selectedRoleName = $(`#role_id option[value="${roleIdSelect.value}"]`).data('role-name');
            // Pastikan 'Admin Gudang' adalah nama yang benar
            if (selectedRoleName === 'Admin Gudang' && memberWarehouses.length > 0) {
                const warehouseListContainer = document.getElementById('warehouse-list-container');
                warehouseListContainer.innerHTML = ''; // Kosongkan dulu
                memberWarehouses.forEach(whId => {
                    warehouseListContainer.insertAdjacentHTML('beforeend', warehouseRowTemplate(whId));
                });
                updateWarehouseSelectOptions();
            }
        } else {
            document.getElementById('userAccessSection').classList.add('d-none');
        }

        // Event listener saat Role diganti
        $('#role_id').on('change', function() {
            toggleAccessSection(this.value);
        });

        // Event listener untuk tombol Show/Hide Password
        $('.toggle-password').click(function() {
            const input = $(this).closest('.input-group').find('input');
            const icon = $(this).find('i');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Event listener untuk tombol Hapus
        $('.delete-btn').click(function() {
            const form = $(this).closest('form');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Inisialisasi Validasi Parsley
        $('#memberForm').parsley({
            errorClass: 'is-invalid',
            successClass: 'is-valid',
            errorsWrapper: '<div class="invalid-feedback"></div>',
            errorTemplate: '<span></span>'
        });

        // ===================================================================
        // Event Listener untuk Gudang
        // ===================================================================
        
        // Tombol Tambah Gudang (+)
        $('#add-warehouse-btn').on('click', function() {
            $('#warehouse-list-container').append(warehouseRowTemplate());
            updateWarehouseSelectOptions();
        });

        // Tombol Hapus Gudang (Trash)
        $('#warehouse-list-container').on('click', '.remove-warehouse-btn', function() {
            $(this).closest('.warehouse-row').remove();
            updateWarehouseSelectOptions();
        });

        // Saat memilih gudang, update dropdown lainnya
        $('#warehouse-list-container').on('change', '.warehouse-select', function() {
            updateWarehouseSelectOptions();
        });
    });
</script>
@endpush