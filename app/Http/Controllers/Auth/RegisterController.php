<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Inertia\Inertia;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'nip_nik' => ['required', 'string', 'max:50'],
            'company_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'nip_nik' => $validated['nip_nik'],
            'company_name' => $validated['company_name'],
            'password' => Hash::make($validated['password']),
        ]);

        // Default role for registration is 'pemohon'
        $user->assignRole('pemohon');

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Registrasi Akun Pemohon Berhasil! Selamat Datang di SIPERDOK.');
    }
}
