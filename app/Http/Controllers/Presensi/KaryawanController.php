<?php

namespace App\Http\Controllers\Presensi;
use App\Http\Controllers\Controller;
use App\Models\Presensi\Employee;
use App\Models\Presensi\Divisi;
use App\Models\Presensi\SubDivisi;
use App\Models\Presensi\Posisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage; // Penting
use Illuminate\Support\Facades\File;      // Penting

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Employees = Employee::all();
        $Divisis = Divisi::all();
        $SubDivisis = SubDivisi::all();
        $Posisis = Posisi::all();  
        return view('presensi.employee.index', compact('Employees','Divisis','SubDivisis','Posisis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('presensi.employee.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'emp_Code' => 'required|string|max:20|unique:m_employee,emp_Code',
            'emp_NID' => 'required|string|max:30',
            'emp_Name' => 'required|string|max:50',
            'emp_ActiveYN' => 'required|string|max:1',
            'emp_Address' => 'nullable|string|max:200',
            'emp_CityCode' => 'nullable|string|max:20',
            'emp_ProvinceCode' => 'nullable|string|max:20',
            'emp_DivCode' => 'nullable|string|max:20',
            'EMP_SUBDIVCODE' => 'nullable|string|max:20',
            'emp_PosCode' => 'nullable|string|max:20',
            'emp_ZipCode' => 'nullable|string|max:5',
            'emp_Phone1' => 'nullable|string|max:15',
            'emp_Phone2' => 'nullable|string|max:15',
            'emp_hp1' => 'nullable|string|max:15',
            'emp_hp2' => 'nullable|string|max:15',
            'emp_Address2' => 'nullable|string|max:200',
            'emp_CityCode2' => 'nullable|string|max:20',
            'emp_ProvinceCode2' => 'nullable|string|max:20',
            'emp_ZipCode2' => 'nullable|string|max:5',
            'emp_Phone3' => 'nullable|string|max:15',
            'emp_Phone4' => 'nullable|string|max:15',
            'emp_hp3' => 'nullable|string|max:15',
            'emp_hp4' => 'nullable|string|max:15',
            'emp_Email' => 'nullable|email|max:50',
            'emp_Email2' => 'nullable|email|max:50',
            'emp_Web' => 'nullable|string|max:50',
            'emp_Sex' => 'nullable|string|max:2',
            'emp_Marital' => 'nullable|string|max:2',
            'emp_Religion' => 'nullable|string|max:30',
            'emp_PlaceBorn' => 'nullable|string|max:30',
            'emp_DateBorn' => 'required|date',
            'emp_Enroll' => 'nullable|date',
            'emp_startcontract' => 'nullable|date',
            'emp_Expired' => 'nullable|date',
            'emp_permanent' => 'nullable|date',
            'emp_quit' => 'nullable|date',
            'emp_reason' => 'nullable|string|max:3',
            'emp_office' => 'nullable|string|max:10',
            'emp_ptkp' => 'nullable|string|max:10',
            'emp_blood' => 'nullable|string|max:2',
            'EMP_SHIF' => 'nullable|string|max:10',
            'EMP_PAJAK' => 'nullable|string|max:2',
            'EMP_status' => 'nullable|string|max:2',
            'emp_bayar' => 'nullable|string|max:2',
            'emp_BANK' => 'nullable|string|max:10',
            'emp_NOREK' => 'nullable|string|max:20',
            'emp_PEMILIK' => 'nullable|string|max:50',
            'emp_NPWP' => 'nullable|string|max:50',
            'emp_education' => 'nullable|string|max:3',
            'EMP_JAMSOSTEK' => 'nullable|string|max:50',
            'emp_datejamsostek' => 'nullable|date',
            'emp_ktp' => 'nullable|string|max:3',
            'emp_no_ktp' => 'nullable|string|max:30',
            'EMP_PICT' => 'nullable|image|mimes:jpeg,jpg,png|max:8048', 
            'emp_ENTRYID' => 'nullable|string|max:10',
            'emp_FirstEntry' => 'nullable|date',
            'emp_UpdateID' => 'nullable|string|max:10',
            'emp_LastUpdate' => 'nullable|date',
        ]);

        // Buat password secara otomatis dari tanggal lahir jika ada
        if (!empty($validated['emp_DateBorn'])) {
            // Format tanggal menjadi ddmmyyyy. Contoh: 15051990
            $defaultPassword = Carbon::parse($validated['emp_DateBorn'])->format('dmY');
            
            // Hash password yang dibuat otomatis
            $validated['emp_password'] = Hash::make($defaultPassword);
        } else {
            // Pastikan password bernilai null jika tanggal lahir tidak diisi
            $validated['emp_password'] = null;
        }

        // Handle Upload Gambar
        if ($request->hasFile('EMP_PICT')) {
            $file = $request->file('EMP_PICT');
            $empCode = $validated['emp_Code'];
            $date = Carbon::now()->format('Ymd'); // Format tanggal: TahunBulanHari (e.g., 20250611)
            // $filename = time() . '_' . uniqid() . '.png'; // Gunakan nama unik
            $filename = $empCode . '_' . uniqid() . '_' . $date . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/employee_pictures', $filename);
            $validated['EMP_PICT'] = $filename;
        }

        
        // Tambahkan data user login dan waktu entry
        $validated['emp_ENTRYID'] = Auth::User()->id;
        $validated['emp_FirstEntry'] = Carbon::now();

    
        Employee::create($validated);
        
        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data Karyawan berhasil ditambahkan.',
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $Employee = Employee::with(['Divisi', 'SubDivisi', 'Posisi'])
        ->findOrFail($id);

        return response()->json($Employee);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $Employee)
    {
        return view('presensi.employee.edit', compact('Employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $Employee)
    {
        $validated = $request->validate([
            'emp_Code' => 'nullable|string|max:20|',
            'emp_NID' => 'nullable|string|max:30',
            'emp_Name' => 'nullable|string|max:50',
            'emp_password' => 'nullable|string|min:8|confirmed',
            'emp_ActiveYN' => 'nullable|string|max:1',
            'emp_Address' => 'nullable|string|max:200',
            'emp_CityCode' => 'nullable|string|max:20',
            'emp_ProvinceCode' => 'nullable|string|max:20',
            'emp_DivCode' => 'nullable|string|max:20',
            'EMP_SUBDIVCODE' => 'nullable|string|max:20',
            'emp_PosCode' => 'nullable|string|max:20',
            'emp_ZipCode' => 'nullable|string|max:5',
            'emp_Phone1' => 'nullable|string|max:15',
            'emp_Phone2' => 'nullable|string|max:15',
            'emp_hp1' => 'nullable|string|max:15',
            'emp_hp2' => 'nullable|string|max:15',
            'emp_Address2' => 'nullable|string|max:200',
            'emp_CityCode2' => 'nullable|string|max:20',
            'emp_ProvinceCode2' => 'nullable|string|max:20',
            'emp_ZipCode2' => 'nullable|string|max:5',
            'emp_Phone3' => 'nullable|string|max:15',
            'emp_Phone4' => 'nullable|string|max:15',
            'emp_hp3' => 'nullable|string|max:15',
            'emp_hp4' => 'nullable|string|max:15',
            'emp_Email' => 'nullable|email|max:50',
            'emp_Email2' => 'nullable|email|max:50',
            'emp_Web' => 'nullable|string|max:50',
            'emp_Sex' => 'nullable|string|max:2',
            'emp_Marital' => 'nullable|string|max:2',
            'emp_Religion' => 'nullable|string|max:30',
            'emp_PlaceBorn' => 'nullable|string|max:30',
            'emp_DateBorn' => 'nullable|date',
            'emp_Enroll' => 'nullable|date',
            'emp_startcontract' => 'nullable|date',
            'emp_Expired' => 'nullable|date',
            'emp_permanent' => 'nullable|date',
            'emp_quit' => 'nullable|date',
            'emp_reason' => 'nullable|string|max:3',
            'emp_office' => 'nullable|string|max:10',
            'emp_ptkp' => 'nullable|string|max:10',
            'emp_blood' => 'nullable|string|max:2',
            'EMP_SHIF' => 'nullable|string|max:10',
            'EMP_PAJAK' => 'nullable|string|max:2',
            'EMP_status' => 'nullable|string|max:2',
            'emp_bayar' => 'nullable|string|max:2',
            'emp_BANK' => 'nullable|string|max:10',
            'emp_NOREK' => 'nullable|string|max:20',
            'emp_PEMILIK' => 'nullable|string|max:50',
            'emp_NPWP' => 'nullable|string|max:50',
            'emp_education' => 'nullable|string|max:3',
            'EMP_JAMSOSTEK' => 'nullable|string|max:50',
            'emp_datejamsostek' => 'nullable|date',
            'emp_ktp' => 'nullable|string|max:3',
            'emp_no_ktp' => 'nullable|string|max:30',
            'EMP_PICT' => 'nullable|image|mimes:jpeg,jpg,png|max:8048',
            'emp_ENTRYID' => 'nullable|string|max:10',
            'emp_FirstEntry' => 'nullable|date',
            'emp_UpdateID' => 'nullable|string|max:10',
            'emp_LastUpdate' => 'nullable|date',
            'delete_photo' => 'nullable|in:0,1',
        ]);

        // Periksa apakah ada input password baru dari form
        if (!empty($validated['emp_password'])) {
            // Jika ada, hash password baru
            $validated['emp_password'] = Hash::make($validated['emp_password']);
        } else {
            // Jika tidak ada password baru yang diinput, hapus dari array 
            // agar tidak menimpa password lama dengan NULL
            unset($validated['emp_password']); 
        }
    
        $currentPhoto = $Employee->EMP_PICT;

        if ($request->input('delete_photo') == '1' && $currentPhoto) {
            File::delete(storage_path('app/public/employee_pictures/' . $currentPhoto));
            $validated['EMP_PICT'] = null;
        }

        if ($request->hasFile('EMP_PICT')) {
            if ($currentPhoto) {
                File::delete(storage_path('app/public/employee_pictures/' . $currentPhoto));
            }
            $file = $request->file('EMP_PICT');
            
            // === PERUBAHAN DI SINI ===
            // Membuat nama file baru dengan format: kodekaryawan_uniqid_tanggal.extensi
            $empCode = $validated['emp_Code'];
            $date = Carbon::now()->format('Ymd'); // Format tanggal: TahunBulanHari (e.g., 20250611)
            $filename = $empCode . '_' . uniqid() . '_' . $date . '.' . $file->getClientOriginalExtension();

            $file->storeAs('public/employee_pictures', $filename);
            $validated['EMP_PICT'] = $filename;
        }
        
        unset($validated['delete_photo']);


        $validated['emp_UpdateID'] = Auth::user()->id; // ID user yang login
        $validated['emp_LastUpdate'] = Carbon::now(); // Timestamp saat update
        
        $Employee->update($validated);
    
        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data Karyawan berhasil diperbarui.',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $Employee = Employee::findOrFail($id);
        if ($Employee->EMP_PICT) {
            File::delete(storage_path('app/public/employee_pictures/' . $Employee->EMP_PICT));
        }
        
        $Employee->delete();

        return response()->json(['message' => 'Data karyawan berhasil dihapus.']);
    }

}
