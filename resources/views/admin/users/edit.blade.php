@extends('layouts.admin')

@section('title', 'Edit Pengguna | Panel Kelola')
@section('page_title', 'Perbarui Data Pengguna')
@section('page_subtitle', 'Edit informasi akun ' . $user->name)

@section('content')
<div class="max-w-2xl">
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-2xs">
        
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Nama Lengkap -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" required value="{{ old('name', $user->name) }}"
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                @error('name')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Username -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Username <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="username" required value="{{ old('username', $user->username) }}"
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                @error('username')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Alamat Email <span class="text-rose-500">*</span>
                </label>
                <input type="email" name="email" required value="{{ old('email', $user->email) }}"
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                @error('email')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Peran / Hak Akses (Role) <span class="text-rose-500">*</span>
                </label>
                <select name="role" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    <option value="siswa" {{ old('role', $user->role) === 'siswa' ? 'selected' : '' }}>Siswa (Peminjam)</option>
                    <option value="guru" {{ old('role', $user->role) === 'guru' ? 'selected' : '' }}>Guru / Tenaga Pendidik</option>
                    <option value="pustakawan" {{ old('role', $user->role) === 'pustakawan' ? 'selected' : '' }}>Pustakawan (Kelola Sirkulasi & Buku)</option>
                    <option value="kepala_perpustakaan" {{ old('role', $user->role) === 'kepala_perpustakaan' ? 'selected' : '' }}>Kepala Perpustakaan (Monitoring & Review)</option>
                </select>
                @error('role')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password (Optional on Edit) -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Ganti Kata Sandi (Kosongkan jika tidak ingin mengubah)
                </label>
                <input type="password" name="password"
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                    placeholder="Masukkan sandi baru (opsional)">
                @error('password')
                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-xl transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-xs transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
