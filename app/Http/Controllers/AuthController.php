<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // عرض صفحة Login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:8',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            $role = Auth::user()->role;

            if ($role === 'admin')   return redirect('/admin/dashboard');
            if ($role === 'doctor')  return redirect('/doctor/dashboard');
            return redirect('/patient/dashboard');
        }

        return back()->withErrors(['email' => 'Identifiants incorrects']);
    }

    // عرض صفحة Register
    public function showRegister()
    {
        return view('auth.register');
    }

    // Register
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role'     => 'required|in:patient,doctor',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return redirect('/login')->with('success', 'Compte créé avec succès');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
