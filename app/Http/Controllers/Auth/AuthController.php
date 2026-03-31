<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Update last login
            $user->update(['last_login_at' => now()]);

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda telah dinonaktifkan.']);
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak sesuai.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:100',
            'name'         => 'required|string|max:100',
            'email'        => 'required|email|unique:users',
            'phone'        => 'required|string|max:20',
            'password'     => 'required|min:8|confirmed',
        ]);

        // Create company
        $company = Company::create([
            'name'  => $request->company_name,
            'slug'  => Str::slug($request->company_name) . '-' . Str::random(6),
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        // Create owner user
        $user = User::create([
            'company_id' => $company->id,
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password),
            'role'       => 'owner',
        ]);

        // Create trial subscription (14 days, Starter plan)
        $starterPlan = SubscriptionPlan::where('slug', 'starter')->first();
        if ($starterPlan) {
            Subscription::create([
                'company_id'    => $company->id,
                'plan_id'       => $starterPlan->id,
                'status'        => 'trial',
                'billing_cycle' => 'monthly',
                'starts_at'     => now(),
                'ends_at'       => now()->addDays(14),
                'trial_ends_at' => now()->addDays(14),
            ]);
        }

        Auth::login($user);
        return redirect()->route('dashboard')->with('success', 'Selamat datang! Akun Anda berhasil dibuat dengan masa trial 14 hari.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
