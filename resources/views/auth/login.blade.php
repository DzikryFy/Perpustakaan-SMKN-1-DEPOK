@extends('layouts.app')

@section('title', 'Masuk Akun | Perpustakaan SMKN 1 Depok')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 sm:p-10 rounded-2xl border border-slate-200/90 shadow-sm">
        
        <!-- Header & Logo -->
        <div class="text-center">
            <img src="{{ asset('images/logo-onedek.png') }}" alt="Logo SMKN 1 Depok" class="mx-auto h-16 w-auto object-contain mb-3">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Masuk E-Library</h2>
            <p class="text-xs text-slate-500 mt-1">Perpustakaan Digital SMK Negeri 1 Depok</p>
        </div>

        <!-- Form Login -->
        <form class="mt-8 space-y-5" action="{{ route('login') }}" method="POST" x-data="{ showPass: false }">
            @csrf

            <!-- Username or Email -->
            <div>
                <label for="login" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Username atau Email
                </label>
                <div class="relative rounded-xl shadow-2xs">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="bi bi-person"></i>
                    </div>
                    <input id="login" name="login" type="text" autocomplete="username" required value="{{ old('login') }}"
                        class="block w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border @error('login') border-rose-400 bg-rose-50/30 @else border-slate-300 bg-white @enderror placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                        placeholder="Contoh: putra atau pustakawan">
                </div>
                @error('login')
                <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Kata Sandi
                    </label>
                </div>
                <div class="relative rounded-xl shadow-2xs">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="bi bi-lock"></i>
                    </div>
                    <input id="password" name="password" :type="showPass ? 'text' : 'password'" autocomplete="current-password" required
                        class="block w-full pl-10 pr-10 py-2.5 text-sm rounded-xl border border-slate-300 bg-white placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                        placeholder="Masukkan kata sandi">
                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-hidden">
                        <i class="bi" :class="showPass ? 'bi-eye-slash' : 'bi-eye'"></i>
                    </button>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded">
                    <label for="remember" class="ml-2 block text-xs text-slate-600">
                        Ingat saya di perangkat ini
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit"
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-xs text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                    Masuk ke Sistem
                </button>
            </div>
        </form>

        <!-- Quick Demo Credentials Box -->
        <div class="mt-6 pt-6 border-t border-slate-100 bg-slate-50/70 p-4 rounded-xl text-xs text-slate-600" x-data="{}">
            <div class="font-bold text-slate-800 mb-2 flex items-center gap-1.5">
                <i class="bi bi-info-circle text-blue-600"></i> Akun Uji Coba Bawaan:
            </div>
            <div class="grid grid-cols-2 gap-2 text-[11px]">
                <button type="button" @click="document.getElementById('login').value = 'pustakawan'; document.getElementById('password').value = 'password';" class="text-left p-2 rounded-lg bg-white border border-slate-200 hover:border-blue-300 hover:bg-blue-50/50 transition">
                    <div class="font-bold text-slate-900">Pustakawan</div>
                    <div class="text-slate-500">user: pustakawan</div>
                </button>
                <button type="button" @click="document.getElementById('login').value = 'kepala'; document.getElementById('password').value = 'password';" class="text-left p-2 rounded-lg bg-white border border-slate-200 hover:border-purple-300 hover:bg-purple-50/50 transition">
                    <div class="font-bold text-slate-900">Kepala Perpus</div>
                    <div class="text-slate-500">user: kepala</div>
                </button>
                <button type="button" @click="document.getElementById('login').value = 'putra'; document.getElementById('password').value = 'password';" class="text-left p-2 rounded-lg bg-white border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50/50 transition">
                    <div class="font-bold text-slate-900">Siswa (Putra)</div>
                    <div class="text-slate-500">user: putra</div>
                </button>
                <button type="button" @click="document.getElementById('login').value = 'dzikry'; document.getElementById('password').value = 'password';" class="text-left p-2 rounded-lg bg-white border border-slate-200 hover:border-amber-300 hover:bg-amber-50/50 transition">
                    <div class="font-bold text-slate-900">Guru (Dzikry)</div>
                    <div class="text-slate-500">user: dzikry</div>
                </button>
            </div>
            <div class="mt-2 text-[11px] text-slate-500 italic text-center">
                * Password semua akun demo: <span class="font-mono font-semibold text-slate-700">password</span> (klik salah satu kotak untuk auto-fill).
            </div>
        </div>

        <!-- Footer Link -->
        <p class="text-center text-xs text-slate-600">
            Belum memiliki akun anggota?
            <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:text-blue-700 underline">
                Daftar sekarang
            </a>
        </p>
    </div>
</div>
@endsection
