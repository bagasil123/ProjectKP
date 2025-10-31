<?php

namespace App\Http\Controllers\Comprof;

use App\Http\Controllers\Controller;
use App\Models\Comprof\Slider;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::orderBy('created_at', 'desc')->get();
        return view('comprof.slider.index', compact('sliders'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'link' => 'required|url|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|boolean',
        ]);

        try {
            $imagePath = $request->file('image')->store('sliders', 'public');
            
            Slider::create([
                'title' => strip_tags($validated['title']),
                'link' => $validated['link'],
                'image' => $imagePath,
                'status' => $validated['status']
            ]);

            return response()->json(['message' => 'Slider berhasil ditambahkan'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Slider $slider): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'link' => 'required|url|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|boolean',
        ]);

        try {
            $data = [
                'title' => strip_tags($validated['title']),
                'link' => $validated['link'],
                'status' => $validated['status']
            ];

            if ($request->hasFile('image')) {
                // Hapus gambar lama
                if ($slider->image) {
                    Storage::disk('public')->delete($slider->image);
                }
                $data['image'] = $request->file('image')->store('sliders', 'public');
            }

            $slider->update($data);

            return response()->json(['message' => 'Slider berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Slider $slider): JsonResponse
    {
        try {
            if ($slider->image) {
                Storage::disk('public')->delete($slider->image);
            }
            $slider->delete();

            return response()->json(['message' => 'Slider berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}