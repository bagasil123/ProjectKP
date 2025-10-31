<?php

namespace App\Http\Controllers\Comprof;

use App\Http\Controllers\Controller;
use App\Models\Comprof\SetPerusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SetPerusahaanController extends Controller
{
    public function index()
    {
        $setting = SetPerusahaan::first();
        return view('comprof.setperusahaan.index', compact('setting'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            'email_account' => 'nullable|email|max:255',
            'email_password' => 'nullable|string|max:255',
            'email_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|string|max:10',
            'tagline' => 'nullable|string|max:255',
            'map_location' => 'nullable|url|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,ico|max:1024',
        ]);

        try {
            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                $oldLogo = SetPerusahaan::value('logo');
                if ($oldLogo) {
                    Storage::disk('public')->delete($oldLogo);
                }
                $validated['logo'] = $request->file('logo')->store('setperusahaan', 'public');
            } elseif ($request->has('remove_logo')) {
                $oldLogo = SetPerusahaan::value('logo');
                if ($oldLogo) {
                    Storage::disk('public')->delete($oldLogo);
                }
                $validated['logo'] = null;
            }

            // Handle icon upload
            if ($request->hasFile('icon')) {
                // Delete old icon if exists
                $oldIcon = SetPerusahaan::value('icon');
                if ($oldIcon) {
                    Storage::disk('public')->delete($oldIcon);
                }
                $validated['icon'] = $request->file('icon')->store('setperusahaan', 'public');
            } elseif ($request->has('remove_icon')) {
                $oldIcon = SetPerusahaan::value('icon');
                if ($oldIcon) {
                    Storage::disk('public')->delete($oldIcon);
                }
                $validated['icon'] = null;
            }

            // Create or update settings
            $setting = SetPerusahaan::updateOrCreate(['id' => 1], $validated);

            return response()->json([
                'message' => 'Pengaturan perusahaan berhasil disimpan',
                'data' => $setting
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error saving company settings: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            $path = $request->file('image')->store('setperusahaan/summernote', 'public');
            $url = asset('storage/' . $path);

            return response()->json([
                'success' => true,
                'url' => $url
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading image: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah gambar'
            ], 500);
        }
    }
}