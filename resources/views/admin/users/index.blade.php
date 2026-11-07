@extends('layouts.admin')

@section('title', 'Kelola Pengguna | Panel Kelola')
@section('page_title', 'Manajemen Pengguna & Anggota')
@section('page_subtitle', 'Kelola akun siswa, guru, pustakawan, dan kepala perpustakaan')

@section('content')
<div class="space-y-6">
    
    <!-- Top Action Bar -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Search & Filter Form -->
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex-1 flex flex-wrap items-center gap-2">
            <div class="relative flex-1 min-w-[200px]">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama, username, email..."
                    class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600">
                <i class="bi bi-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
            </div>

            <select name="role" class="px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-blue-600">
                <option value="">Semua Role</option>
                <option value="siswa" {{ $role === 'siswa' ? 'selected' : '' }}>Siswa</option>
                <option value="guru" {{ $role === 'guru' ? 'selected' : '' }}>Guru</option>
                <option value="pustakawan" {{ $role === 'pustakawan' ? 'selected' : '' }}>Pustakawan</option>
                <option value="kepala_perpustakaan" {{ $role === 'kepala_perpustakaan' ? 'selected' : '' }}>Kepala Perpustakaan</option>
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 transition">
                Filter
            </button>
            @if($search || $role)
            <a href="{{ route('admin.users.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800">
                Reset
            </a>
            @endif
        </form>

        <!-- Add User Button -->
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 shadow-xs transition inline-flex items-center gap-1.5 self-start md:self-auto shrink-0">
            <i class="bi bi-person-plus"></i> Tambah Pengguna Baru
        </a>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Daftar Pengguna Terdaftar ({{ $users->total() }} Total)</h3>
            <span class="text-xs text-slate-500 font-medium">Hal. {{ $users->currentPage() }} dari {{ $users->lastPage() }}</span>
        </div>

        @if($users->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Nama Lengkap</th>
                        <th class="px-5 py-3">Username</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3">Role Akses</th>
                        <th class="px-5 py-3">Tgl Daftar</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($users as $u)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-5 py-3.5 font-bold text-slate-900 flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[11px] shrink-0">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <span>{{ $u->name }}</span>
                        </td>
                        <td class="px-5 py-3.5 font-mono text-slate-600">{{ $u->username }}</td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $u->email }}</td>
                        <td class="px-5 py-3.5">
                            @php
                                $roleClass = match($u->role) {
                                    'pustakawan' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'kepala_perpustakaan' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'guru' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                };
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $roleClass }}">
                                {{ $u->role_label }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-400">
                            {{ $u->created_at ? $u->created_at->translatedFormat('d M Y') : '-' }}
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.users.edit', $u) }}" class="p-1.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-slate-100 transition" title="Edit Pengguna">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                @if($u->id !== Auth::id())
                                <form action="{{ route('admin.users.destroy', $u) }}" method="POST" onsubmit="return confirm('Hapus akun pengguna {{ $u->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Pengguna">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
        @else
        <div class="p-10 text-center text-slate-400 text-xs">
            Tidak ada pengguna ditemukan.
        </div>
        @endif
    </div>
</div>
@endsection
