<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Menampilkan form untuk mengubah profil
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }
    
    /**
     * Memperbarui profil pengguna
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|regex:/^[1-9][0-9]{8,15}$/|max:20',
            'wa_notifications' => 'nullable|boolean',
            'photo' => 'nullable|image|max:2048',
        ], [
            'phone.regex' => 'Format nomor telepon tidak valid. Gunakan format 81234567890 (tanpa 0 di depan).'
        ]);
        
        // Log input data untuk debug
        \Log::info('Profile update request', [
            'user_id' => $user->id,
            'phone_input' => $request->phone,
            'has_wa_notifications' => $request->has('wa_notifications')
        ]);
        
        // Update basic info
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->wa_notifications = $request->has('wa_notifications');
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            
            $photoPath = $request->file('photo')->store('profile_photos', 'public');
            $user->photo = $photoPath;
        }
        
        try {
            $saved = $user->save();
            
            // Log hasil penyimpanan
            \Log::info('Profile update result', [
                'user_id' => $user->id,
                'saved' => $saved,
                'phone_after_save' => $user->phone,
                'wa_notifications_after_save' => $user->wa_notifications
            ]);
            
            return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            \Log::error('Error updating profile', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('profile.edit')->with('error', 'Gagal memperbarui profil: ' . $e->getMessage());
        }
    }
    
    /**
     * Memperbarui password pengguna
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = Auth::user();
        
        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }
        
        $user->password = Hash::make($request->password);
        $user->save();
        
        return redirect()->route('profile.edit')->with('success', 'Password berhasil diperbarui.');
    }
}
