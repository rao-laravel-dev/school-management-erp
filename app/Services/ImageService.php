<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    protected ImageManager $manager;

    // Sirf ye MIME types allow karenge, kuch bhi aur reject
    protected array $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Image upload + automatic webp encoding, storage/app/public disk pe.
     * $folder e.g. 'teacher_images' (bina 'uploads/' prefix ke — disk khud public/ ke andar hai)
     */
    public function upload($file, $folder = 'students', $width = 300, $height = 300, $quality = 85)
    {
        $folder = trim($folder, '/');

        // 1. MIME type validate karein — real content check, sirf extension pe bharosa nahi
        $mime = $file->getMimeType();
        if (!in_array($mime, $this->allowedMimes)) {
            throw new \InvalidArgumentException('Invalid file type. Only JPG, PNG, WEBP, GIF images allowed.');
        }

        // 2. Double-check: ye waqai ek valid image hai (getimagesize corrupt/fake files ko reject karta hai)
        $realPath = $file->getRealPath();
        if (@getimagesize($realPath) === false) {
            throw new \InvalidArgumentException('Uploaded file is not a valid image.');
        }

        // 3. Folder check + create (disk ke andar, agar exist nahi karta)
        if (!Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        // 4. Secure random filename — original filename kabhi use nahi karte
        $filename = Str::random(32) . '.webp';

        // 5. Resize + encode (Intervention se), phir Storage disk pe likhein
        $image = $this->manager->read($realPath);
        $image = $image->scale($width, $height);
        $encoded = $image->toWebp($quality);

        Storage::disk('public')->put($folder . '/' . $filename, (string) $encoded);

        // DB me sirf filename jayega (jaisa pehle tha)
        return $filename;
    }

    /**
     * Clear dynamic resource assets from storage disk.
     */
    public function delete($oldFile, $folder = 'students')
    {
        if (!$oldFile) return;

        $folder = trim($folder, '/');
        $path = $folder . '/' . $oldFile;

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}