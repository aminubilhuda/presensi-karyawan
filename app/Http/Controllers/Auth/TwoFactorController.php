<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    /**
     * Menampilkan form untuk mengaktifkan 2FA
     */
    public function showTwoFactorForm()
    {
        $user = Auth::user();
        
        // Gunakan library lain sebagai alternatif jika Google2FA tidak tersedia
        $secret = $this->generateSecretKey();
        session()->put('2fa_secret', $secret);
        
        $qrCodeUrl = $this->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
        
        // Generate QR Code menggunakan BaconQrCode
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        
        $writer = new Writer($renderer);
        $qrCode = $writer->writeString($qrCodeUrl);
        
        return view('auth.two-factor', compact('secret', 'qrCode'));
    }
    
    /**
     * Mengaktifkan 2FA untuk user
     */
    public function enableTwoFactor(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric|digits:6',
        ]);
        
        $user = Auth::user();
        $secret = session()->get('2fa_secret');
        
        // Verifikasi kode (dalam implementasi nyata, gunakan Google2FA atau library lain)
        // Untuk contoh ini, kita hanya memeriksa apakah kode = 123456
        if ($request->code === '123456') {
            $user->two_factor_secret = $secret;
            $user->two_factor_enabled = true;
            $user->two_factor_confirmed_at = now();
            $user->save();
            
            return redirect()->route('profile.edit')->with('success', 'Autentikasi dua faktor berhasil diaktifkan');
        }
        
        return back()->withErrors(['code' => 'Kode verifikasi tidak valid']);
    }
    
    /**
     * Memeriksa kode 2FA saat login
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);
        
        $user = Auth::user();
        
        // Verifikasi kode (dalam implementasi nyata, gunakan Google2FA)
        // Untuk contoh ini, kita hanya memeriksa apakah kode = 123456
        if ($request->code !== '123456') {
            return back()->withErrors(['code' => 'Kode tidak valid']);
        }
        
        session()->put('auth.two_factor_confirmed', true);
        
        return redirect()->intended(route('dashboard'));
    }
    
    /**
     * Menonaktifkan 2FA untuk user
     */
    public function disableTwoFactor(Request $request)
    {
        $user = Auth::user();
        
        $user->two_factor_secret = null;
        $user->two_factor_enabled = false;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_recovery_codes = null;
        $user->save();
        
        return redirect()->route('profile.edit')->with('success', 'Autentikasi dua faktor berhasil dinonaktifkan');
    }
    
    /**
     * Generate a secret key
     */
    private function generateSecretKey()
    {
        return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'), 0, 16);
    }
    
    /**
     * Get the QR code URL for the given data
     */
    private function getQRCodeUrl($appName, $email, $secret)
    {
        $appName = urlencode($appName);
        $email = urlencode($email);
        
        return "otpauth://totp/{$appName}:{$email}?secret={$secret}&issuer={$appName}";
    }
}
