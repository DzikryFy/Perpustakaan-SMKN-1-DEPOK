@extends('layouts.app')

@section('title', 'Pendaftaran Anggota | Perpustakaan SMKN 1 Depok')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-6 bg-white p-8 sm:p-10 rounded-2xl border border-slate-200/90 shadow-sm">
        
        <!-- Header -->
        <div class="text-center">
            <img src="{{ asset('images/logo-onedek.png') }}" alt="Logo SMKN 1 Depok" class="mx-auto h-16 w-auto object-contain mb-3">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Daftar Anggota</h2>
            <p class="text-xs text-slate-500 mt-1">Layanan Peminjaman Digital Perpustakaan SMKN 1 Depok</p>
        </div>

        <!-- Form Register -->
        <form class="mt-6 space-y-4" action="{{ route('register') }}" method="POST">
            @csrf

            <!-- Nama Lengkap -->
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                    Nama Lengkap
                </label>
                <input id="name" name="name" type="text" required value="{{ old('name') }}"
                    class="block w-full px-3.5 py-2.5 text-sm rounded-xl border @error('name') border-rose-400 bg-rose-50/30 @else border-slate-300 bg-white @enderror placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                    placeholder="Contoh: Muhammad Rizky">
                @error('name')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Username -->
            <div>
                <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                    Username
                </label>
                <input id="username" name="username" type="text" required value="{{ old('username') }}"
                    class="block w-full px-3.5 py-2.5 text-sm rounded-xl border @error('username') border-rose-400 bg-rose-50/30 @else border-slate-300 bg-white @enderror placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                    placeholder="Hanya huruf & angka (tanpa spasi)">
                @error('username')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                    Alamat Email
                </label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}"
                    class="block w-full px-3.5 py-2.5 text-sm rounded-xl border @error('email') border-rose-400 bg-rose-50/30 @else border-slate-300 bg-white @enderror placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                    placeholder="email@smkn1depok.sch.id">
                @error('email')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role Selector: Siswa or Guru -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                    Status Keanggotaan
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition has-checked:border-blue-600 has-checked:bg-blue-50/40">
                        <input type="radio" name="role" value="siswa" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300" {{ old('role', 'siswa') === 'siswa' ? 'checked' : '' }}>
                        <div class="ml-2.5">
                            <span class="block text-xs font-bold text-slate-900">Siswa</span>
                            <span class="block text-[11px] text-slate-500">Pelajar aktif</span>
                        </div>
                    </label>
                    <label class="flex items-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition has-checked:border-blue-600 has-checked:bg-blue-50/40">
                        <input type="radio" name="role" value="guru" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300" {{ old('role') === 'guru' ? 'checked' : '' }}>
                        <div class="ml-2.5">
                            <span class="block text-xs font-bold text-slate-900">Guru / Staff</span>
                            <span class="block text-[11px] text-slate-500">Pendidik sekolah</span>
                        </div>
                    </label>
                </div>
                @error('role')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password & Confirm Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                        Kata Sandi
                    </label>
                    <input id="password" name="password" type="password" required
                        class="block w-full px-3.5 py-2.5 text-sm rounded-xl border @error('password') border-rose-400 bg-rose-50/30 @else border-slate-300 bg-white @enderror placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                        placeholder="Min. 6 karakter">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                        Ulangi Sandi
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                        class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                        placeholder="Konfirmasi sandi">
                </div>
            </div>
            @error('password')
            <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit"
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-xs text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                    Daftar Sekarang
                </button>
            </div>
        </form>

        <!-- Footer Link -->
        <p class="text-center text-xs text-slate-600 pt-2 border-t border-slate-100">
            Sudah memiliki akun terdaftar?
            <a href="{{ route('login') }}" class="font-bold text-blue-600 hover:text-blue-700 underline">
                Masuk di sini
            </a>
        </p>
    </div>
</div>
@endsection
