<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presensi\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'User not authenticated.'], 401);
            }

            $notifications = Notification::where('employee_id', $user->getKey()) 
                                         ->orderBy('created_at', 'desc')
                                         ->get();
            
            $unreadCount = $notifications->where('is_read', false)->count();

            // PERBAIKAN: Selalu kembalikan data dalam format Map yang konsisten
            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil notifikasi: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan di server saat mengambil notifikasi.'], 500);
        }
    }

    public function markAsRead()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'User not authenticated.'], 401);
            }

            Notification::where('employee_id', $user->getKey())
                        ->where('is_read', false)
                        ->update(['is_read' => true]);

            return response()->json(['message' => 'Semua notifikasi ditandai sudah dibaca.']);

        } catch (\Exception $e) {
            Log::error('Gagal menandai notifikasi: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan di server.'], 500);
        }
    }
}