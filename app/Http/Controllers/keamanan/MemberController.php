<?php

namespace App\Http\Controllers\keamanan;

use App\Http\Controllers\Controller;
use App\Models\keamanan\RightAccess;
use App\Models\keamanan\Member;
use App\Models\keamanan\Role;
use App\Models\Presensi\Employee;
use App\Models\keamanan\RoleMenu;
use App\Models\MutasiGudang\Warehouse; // Pastikan ini ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\keamanan\menu; 

class MemberController extends Controller
{
 
    public function index(Request $request)
    {
        // Pastikan $this->commonData() ada dan mengembalikan array
        $data = $this->commonData() ?? ['menus' => [], 'roleMenus' => []];
        
        $data['members'] = Member::with('role', 'employee')->get();
        $data['roles'] = Role::all();
        $data['warehouses'] = Warehouse::all(); // Mengirim data semua gudang
        $data['memberWarehouses'] = [];
        $karyawans = Employee::all(); // Ini juga mungkin duplikat, tapi biarkan
 
        $memberToEdit = null;
 
        $simplifiedAccesses = []; 
        foreach ($data['roles'] as $role) {
            $simplifiedAccesses[$role->id] = [
                'tambah' => '0', 
                'ubah' => '0',
                'hapus' => '0',
            ];
        }

 
        if ($request->has('edit_id')) {
            $memberToEdit = Member::with('role', 'rightAccesses.roleMenuCombination.role', 'rightAccesses.roleMenuCombination.menu')
                                ->find($request->input('edit_id'));
            
            if ($memberToEdit) {
 
                // Mengisi data gudang milik user
                $data['memberWarehouses'] = $memberToEdit->warehouse_access ?? []; 

                foreach ($memberToEdit->rightAccesses as $access) { 
                    if ($access->roleMenuCombination && $access->roleMenuCombination->role) {
                        $roleId = $access->roleMenuCombination->role->id; 
                        if ($access->AC_AD == 'T') $simplifiedAccesses[$roleId]['tambah'] = '1';
                        if ($access->AC_ED == 'T') $simplifiedAccesses[$roleId]['ubah'] = '1';
                        if ($access->AC_DE == 'T') $simplifiedAccesses[$roleId]['hapus'] = '1';
                    }
                }
            }
        } 
        
        // Gunakan $simplifiedAccesses yang sudah di-loop di atas
        $data['simplifiedAccesses'] = $simplifiedAccesses; 
        
        // Hapus variabel duplikat dari compact
        return view('keamanan.user.index', $data, compact('karyawans', 'memberToEdit'));
    } 

    public function store(Request $request)
    {
        $request->validate([
            'Mem_ID' => 'required|string|max:10|unique:m_members,Mem_ID',
            'Mem_UserName' => 'required|string|max:50',
            'mem_password' => 'required|string|min:4|same:confirm_password',
            'Mem_ActiveYN' => 'required|in:Y,N',
            'role_id' => 'required|exists:roles,id', 
            'akses_role' => 'nullable|array', 
            'akses_role.*.tambah' => 'nullable|in:1',
            'akses_role.*.ubah' => 'nullable|in:1',
            'akses_role.*.hapus' => 'nullable|in:1',
            'warehouses' => 'nullable|array',
            'warehouse_access.*' => [
                'string',
                Rule::exists('m_warehouse', 'WARE_Auto')->whereNot('WARE_Auto', 0),
            ]
        ]);

        DB::beginTransaction(); 
        try {

            $warehouseData = $request->warehouse_access;
            

            if ($request->role_id == 1) {
                $warehouseData = ["0"];
            }
 
            $role = Role::findOrFail($request->role_id);
            $warehouseData = null; 

            if ($role->name == 'Admin Gudang' && $request->has('warehouses')) {
                $warehouseData = $request->warehouses;
            }


            $member = Member::create([
                'Mem_ID' => $request->Mem_ID,
                'Mem_UserName' => $request->Mem_UserName,
                'mem_password' => Hash::make($request->mem_password),
                'Mem_ActiveYN' => $request->Mem_ActiveYN,
                'role_id' => $request->role_id,
                'warehouse_access' => $warehouseData, // Simpan data warehouse
            ]); 
            
            // Assign role ke Spatie (jika Anda menggunakannya)
            // $member->assignRole($role->name);

            if ($request->has('akses_role') && is_array($request->akses_role)) { 
                foreach ($request->akses_role as $roleId => $permissions) { 
                    $roleExists = Role::where('id', $roleId)->exists();
                    if (!$roleExists) {
                        continue;
                    } 
                    $roleMenusForThisRole = RoleMenu::where('role_id', $roleId)->get();

                    foreach ($roleMenusForThisRole as $rm) { 
                        RightAccess::updateOrCreate(
                            ['AC_USER' => $member->Mem_ID, 'AC_MAINMENU' => $rm->id],
                            [
                                'AC_AD' => isset($permissions['tambah']) && $permissions['tambah'] == '1' ? 'T' : 'F',
                                'AC_ED' => isset($permissions['ubah']) && $permissions['ubah'] == '1' ? 'T' : 'F',
                                'AC_DE' => isset($permissions['hapus']) && $permissions['hapus'] == '1' ? 'T' : 'F',
                                'AC_USERID' => auth()->id() ?? 'admin', // Ambil ID user yg login
                                'AC_LASTUPDATE' => now(),
                            ]
                        );
                    }
                }
            }

            DB::commit();
            return redirect()->route('keamanan.member.index')->with('success', 'Pengguna dan hak akses berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollback();
            // PERBAIKAN: Ganti redirect()->back()
            return redirect()->route('keamanan.member.index')->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    } 
    
    public function edit($id)
    {
        // Ini cara yang benar untuk memuat halaman edit
        return redirect()->route('keamanan.member.index', ['edit_id' => $id]);
    } 
    
    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id); 
        $request->validate([
            'Mem_UserName' => 'required|string|max:50',
            'Mem_ActiveYN' => 'required|in:Y,N',
            'role_id' => 'required|exists:roles,id',
            'mem_password' => 'nullable|string|min:4|same:confirm_password',
            'akses_role' => 'nullable|array', 
            'akses_role.*.tambah' => 'nullable|in:1',
            'akses_role.*.ubah' => 'nullable|in:1',
            'akses_role.*.hapus' => 'nullable|in:1',
            'warehouses' => 'nullable|array',
            'warehouse_access.*' => [
                'string',
                Rule::exists('m_warehouse', 'WARE_Auto')->whereNot('WARE_Auto', 0),
            ]
        ]);

        DB::beginTransaction();
        try {
            

            $warehouseData = $request->warehouse_access;
            

            if ($request->role_id == 1) {
                $warehouseData = ["0"];
            }

            $role = Role::findOrFail($request->role_id);
            $warehouseData = null; 

            if ($role->name == 'Admin Gudang' && $request->has('warehouses')) {
                $warehouseData = $request->warehouses;
            }

            $member->Mem_UserName = $request->Mem_UserName;
            $member->Mem_ActiveYN = $request->Mem_ActiveYN;
            $member->role_id = $request->role_id;
            $member->warehouse_access = $warehouseData; // Simpan data warehouse
            
            if ($request->filled('mem_password')) {
                $member->mem_password = Hash::make($request->mem_password);
            }
            $member->save(); 

            // Sync role Spatie (jika pakai)
            // $member->syncRoles($role->name);

            $member->rightAccesses()->delete(); 
            if ($request->has('akses_role') && is_array($request->akses_role)) {
                foreach ($request->akses_role as $roleId => $permissions) {
                    $roleExists = Role::where('id', $roleId)->exists();
                    if (!$roleExists) {
                        continue;
                    }
 
                    $roleMenusForThisRole = RoleMenu::where('role_id', $roleId)->get();

                    foreach ($roleMenusForThisRole as $rm) {
                        RightAccess::updateOrCreate( 
                            ['AC_USER' => $member->Mem_ID, 'AC_MAINMENU' => $rm->id],
                            [
                                'AC_AD' => isset($permissions['tambah']) && $permissions['tambah'] == '1' ? 'T' : 'F',
                                'AC_ED' => isset($permissions['ubah']) && $permissions['ubah'] == '1' ? 'T' : 'F',
                                'AC_DE' => isset($permissions['hapus']) && $permissions['hapus'] == '1' ? 'T' : 'F',
                                'AC_USERID' => auth()->id() ?? 'admin',
                                'AC_LASTUPDATE' => now(),
                            ]
                        );
                    }
                }
            }

            DB::commit();
            return redirect()->route('keamanan.member.index')->with('success', 'Data member dan hak akses diperbarui.');
        } catch (\Exception | \Illuminate\Database\QueryException $e) {
            DB::rollback();
            // PERBAIKAN: Ganti redirect()->back()
            return redirect()->route('keamanan.member.index', ['edit_id' => $id])->with('error', 'Terjadi kesalahan: '. $e->getMessage())->withInput();
        }
    } 
    
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $member = Member::findOrFail($id);
            $member->rightAccesses()->delete(); 
            $member->delete(); 

            DB::commit();
            return redirect()->route('keamanan.member.index')->with('success', 'Pengguna berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollback();
            // PERBAIKAN: Ganti redirect()->back()
            return redirect()->route('keamanan.member.index')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function searchEmployees(Request $request)
    {
        $search = $request->query('term');
        $page = $request->query('page', 1);
        $perPage = 10; 

        $query = Employee::query(); 

        if ($search) {
            $query->where('emp_Name', 'LIKE', '%' . $search . '%')
                  ->orWhere('emp_Code', 'LIKE', '%' . $search . '%');
        }

        $employees = $query->paginate($perPage, ['*'], 'page', $page);

        $results = [];
        foreach ($employees->items() as $employee) {
            $formattedBirthDate = null;
            if ($employee->emp_DateBorn) {
                try { 
                    $dateBorn = Carbon::parse($employee->emp_DateBorn);
                    $formattedBirthDate = $dateBorn->format('Ymd'); 
                } catch (\Exception $e) {
                    \Log::error("Error parsing birth date for employee {$employee->emp_Code}: {$e->getMessage()}");
                }
            }

            $results[] = [
                'id' => $employee->emp_Code, 
                'text' => $employee->emp_Name . ' (' . $employee->emp_Code . ')',
                'birth_date' => $formattedBirthDate, 
            ];
        }

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $employees->hasMorePages()
            ]
        ]);
    }

    // ===================================================================
    // PASTIKAN FUNGSI HELPER INI ADA DAN SESUAI
    // ===================================================================
    
    private function commonData()
    {
        // Sesuaikan dengan kebutuhan data Anda
        return [
            'menus' => menu::where('parent_id', 0)->with('children')->get(),
            'roleMenus' => RoleMenu::all(),
            // 'nama_var_lain' => ModelLain::all(),
        ];
    }

    private function getSimplifiedAccesses($memberToEdit)
    {
        $roles = Role::all();
        $simplifiedAccesses = []; 

        foreach ($roles as $role) {
            $simplifiedAccesses[$role->id] = [
                'tambah' => '0', 
                'ubah' => '0',
                'hapus' => '0',
            ];
        }

        if ($memberToEdit) {
             // Pastikan relasi 'rightAccesses' sudah di-load
            $memberToEdit->loadMissing('rightAccesses.roleMenuCombination.role');

            foreach ($memberToEdit->rightAccesses as $access) { 
                if ($access->roleMenuCombination && $access->roleMenuCombination->role) {
                    $roleId = $access->roleMenuCombination->role->id; 
                    if (!isset($simplifiedAccesses[$roleId])) continue; 
                    if ($access->AC_AD == 'T') $simplifiedAccesses[$roleId]['tambah'] = '1';
                    if ($access->AC_ED == 'T') $simplifiedAccesses[$roleId]['ubah'] = '1';
                    if ($access->AC_DE == 'T') $simplifiedAccesses[$roleId]['hapus'] = '1';
                }
            }
        }
        
        return $simplifiedAccesses;
    }
}