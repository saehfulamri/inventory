<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageService
{
    private const DISK = 'public';

    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Simpan foto produk ke disk public dan kembalikan path relatifnya.
     * Mengembalikan null bila tidak ada file yang diunggah.
     */
    public function store(?UploadedFile $file): ?string
    {
        if ($file === null) {
            return null;
        }

        $filename = 'products/'.Str::lower(Str::random(32)).'.'.$this->extension($file);

        Storage::disk(self::DISK)->putFileAs('products', $file, basename($filename));

        return $filename;
    }

    /**
     * Ganti foto produk bila ada file baru (file lama dihapus).
     * Tanpa file baru, path lama dipertahankan.
     */
    public function replace(?UploadedFile $file, ?string $currentPath): ?string
    {
        $newPath = $this->store($file);

        if ($newPath === null) {
            return $currentPath;
        }

        if ($currentPath !== null && $currentPath !== $newPath) {
            $this->delete($currentPath);
        }

        return $newPath;
    }

    public function delete(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    private function extension(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return in_array($extension, self::ALLOWED_EXTENSIONS, true) ? $extension : 'jpg';
    }
}
