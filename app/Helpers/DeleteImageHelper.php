<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class DeleteImageHelper
{

    /**
     * Menghapus logo lama jika ada.
     */
    public static function deleteOldImage($filename, string $path): void
    {
        if ($filename && Storage::disk('public')->exists($path . '/' . $filename)) {
            Storage::disk('public')->delete($path . '/' . $filename);
        }
    }
}
