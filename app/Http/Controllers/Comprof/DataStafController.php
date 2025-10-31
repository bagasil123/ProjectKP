<?php

namespace App\Http\Controllers\Comprof;

use App\Http\Controllers\Controller;
use App\Models\Comprof\Datastaf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DataStafController extends Controller
{
    public function teamPage()
    {
        $staffs = Datastaf::where('status', true)
            ->orderBy('name')
            ->get();
            
        return view('frontend.team', compact('staffs'));
    }

    public function index()
    {
        $staffs = Datastaf::orderBy('name')->get();
        return view('comprof.datastaf.index', compact('staffs'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'required|string',
            'education' => 'required|string',
        ]);
    
        try {
            $cleanDescription = $this->cleanSummernoteContent($validated['description']);
            $path = $request->file('profile_image')->store('staff-profiles', 'public');
            
            $staff = Datastaf::create([
                'name' => $validated['name'],
                'jabatan' => $validated['jabatan'],
                'profile_image' => $path,
                'description' => $cleanDescription,
                'education' => $validated['education'],
                'status' => true
            ]);
    
            return response()->json([
                'message' => 'Data staf berhasil ditambahkan',
                'data' => $staff
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating staff: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    private function cleanSummernoteContent($content)
    {
        $content = trim($content);
        $content = preg_replace('/<p[^>]*>(&nbsp;|\s)*<\/p>/', '', $content);
        $content = str_replace('&nbsp;', ' ', $content);
        $content = strip_tags($content, '<br><strong><em><u><a>');
        $content = preg_replace('/(<br\s*\/?>\s*){2,}/', '<br>', $content);
        
        if (empty(trim(strip_tags($content)))) {
            return '';
        }
        
        return $content;
    }

    public function update(Request $request, Datastaf $datastaf): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'required|string',
            'education' => 'required|string',
        ]);

        try {
            $cleanDescription = $this->cleanSummernoteContent($validated['description']);
            $validated['description'] = $cleanDescription;

            if ($request->hasFile('profile_image')) {
                if ($datastaf->profile_image) {
                    Storage::disk('public')->delete($datastaf->profile_image);
                }
                
                $path = $request->file('profile_image')->store('staff-profiles', 'public');
                $validated['profile_image'] = $path;
            }

            $datastaf->update($validated);

            return response()->json([
                'message' => 'Data staf berhasil diperbarui',
                'data' => $datastaf
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating staff: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Datastaf $datastaf): JsonResponse
    {
        try {
            if ($datastaf->profile_image) {
                Storage::disk('public')->delete($datastaf->profile_image);
            }

            $datastaf->delete();

            return response()->json([
                'message' => 'Data staf berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting staff: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}