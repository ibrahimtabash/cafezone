<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompressedImageUploader
{
    public static function store(
        UploadedFile $file,
        string $directory,
        string $disk = 'public',
        int $maxDimension = 1600,
        int $quality = 78,
    ): string {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return $file->store($directory, $disk);
        }

        $contents = file_get_contents($file->getRealPath());
        $source = $contents === false ? false : @imagecreatefromstring($contents);

        if ($source === false) {
            return $file->store($directory, $disk);
        }

        try {
            $width = imagesx($source);
            $height = imagesy($source);
            $scale = min(1, $maxDimension / max($width, $height));
            $targetWidth = max(1, (int) round($width * $scale));
            $targetHeight = max(1, (int) round($height * $scale));

            $target = imagecreatetruecolor($targetWidth, $targetHeight);
            imagealphablending($target, false);
            imagesavealpha($target, true);
            $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
            imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $transparent);

            imagecopyresampled(
                $target,
                $source,
                0,
                0,
                0,
                0,
                $targetWidth,
                $targetHeight,
                $width,
                $height,
            );

            ob_start();
            $encoded = imagewebp($target, null, $quality);
            $webp = ob_get_clean();
            imagedestroy($target);

            if (! $encoded || ! is_string($webp) || $webp === '') {
                return $file->store($directory, $disk);
            }

            $path = trim($directory, '/') . '/' . Str::uuid() . '.webp';
            Storage::disk($disk)->put($path, $webp, ['visibility' => 'public']);

            return $path;
        } finally {
            imagedestroy($source);
        }
    }
}
