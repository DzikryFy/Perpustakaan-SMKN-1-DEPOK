<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $category = $request->query('kategori');

        $books = Book::query()
            ->search($search)
            ->category($category)
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $categories = Book::select('kategori')
            ->distinct()
            ->whereNotNull('kategori')
            ->orderBy('kategori')
            ->pluck('kategori');

        return view('admin.books.index', compact('books', 'categories', 'search', 'category'));
    }

    public function create(): View
    {
        return view('admin.books.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'no_bos' => ['nullable', 'string', 'max:50'],
            'kategori' => ['required', 'string', 'max:100'],
            'penerbit' => ['nullable', 'string', 'max:100'],
            'jenis_barang' => ['nullable', 'string', 'max:255'],
            'stok' => ['required', 'integer', 'min:0'],
            'tahun_cetak' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'asal_perolehan' => ['nullable', 'string', 'max:50'],
            'deskripsi' => ['nullable', 'string'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $imageName = null;
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $imageName = 'book_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('books', $imageName, 'public');
        }

        Book::create([
            'judul' => $validated['judul'],
            'no_bos' => $validated['no_bos'] ?? null,
            'kategori' => $validated['kategori'],
            'penerbit' => $validated['penerbit'] ?? null,
            'jenis_barang' => $validated['jenis_barang'] ?? 'Koleksi Umum',
            'stok' => $validated['stok'],
            'tahun_cetak' => $validated['tahun_cetak'] ?? null,
            'asal_perolehan' => $validated['asal_perolehan'] ?? 'BOS',
            'deskripsi' => $validated['deskripsi'] ?? null,
            'gambar' => $imageName,
        ]);

        return redirect()->route('admin.books.index')
            ->with('success', 'Buku baru berhasil ditambahkan ke katalog!');
    }

    public function edit(Book $book): View
    {
        return view('admin.books.edit', compact('book'));
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'no_bos' => ['nullable', 'string', 'max:50'],
            'kategori' => ['required', 'string', 'max:100'],
            'penerbit' => ['nullable', 'string', 'max:100'],
            'jenis_barang' => ['nullable', 'string', 'max:255'],
            'stok' => ['required', 'integer', 'min:0'],
            'tahun_cetak' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'asal_perolehan' => ['nullable', 'string', 'max:50'],
            'deskripsi' => ['nullable', 'string'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_gambar' => ['nullable', 'boolean'],
        ]);

        $imageName = $book->gambar;

        if ($request->boolean('hapus_gambar')) {
            if ($imageName && Storage::disk('public')->exists('books/' . $imageName)) {
                Storage::disk('public')->delete('books/' . $imageName);
            }
            $imageName = null;
        }

        if ($request->hasFile('gambar')) {
            if ($imageName && Storage::disk('public')->exists('books/' . $imageName)) {
                Storage::disk('public')->delete('books/' . $imageName);
            }
            $file = $request->file('gambar');
            $imageName = 'book_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('books', $imageName, 'public');
        }

        $book->update([
            'judul' => $validated['judul'],
            'no_bos' => $validated['no_bos'] ?? null,
            'kategori' => $validated['kategori'],
            'penerbit' => $validated['penerbit'] ?? null,
            'jenis_barang' => $validated['jenis_barang'] ?? 'Koleksi Umum',
            'stok' => $validated['stok'],
            'tahun_cetak' => $validated['tahun_cetak'] ?? null,
            'asal_perolehan' => $validated['asal_perolehan'] ?? 'BOS',
            'deskripsi' => $validated['deskripsi'] ?? null,
            'gambar' => $imageName,
        ]);

        return redirect()->route('admin.books.index')
            ->with('success', 'Data buku berhasil diperbarui!');
    }

    public function destroy(Book $book): RedirectResponse
    {
        if ($book->loans()->where('status', 'dipinjam')->exists()) {
            return back()->with('error', 'Buku tidak dapat dihapus karena sedang ada dalam status peminjaman aktif.');
        }

        if ($book->gambar && Storage::disk('public')->exists('books/' . $book->gambar)) {
            Storage::disk('public')->delete('books/' . $book->gambar);
        }

        $book->delete();

        return redirect()->route('admin.books.index')
            ->with('success', 'Buku berhasil dihapus dari sistem.');
    }
}
