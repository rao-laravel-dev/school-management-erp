<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    protected ImageManager $manager;

    // Sirf ye MIME types allow karenge, kuch bhi aur reject
    protected array $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    // Documents/videos jo as-is (bina webp conversion) save hote hain
    protected array $allowedFileExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'zip', 'mp4', 'webm', 'mov', 'avi', 'wmv', 'mkv'];

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * 'uploads/' prefix hata kar sirf module folder deta hai (e.g. 'uploads/students' -> 'students').
     * Purane callers 'uploads/students' ya 'teacher_images' dono pass karte hain, dono chalte hain.
     */
    protected static function normalizeFolder($folder): string
    {
        $folder = trim((string) $folder, '/');

        if (strpos($folder, 'uploads/') === 0) {
            $folder = substr($folder, strlen('uploads/'));
        }

        return $folder;
    }

    /**
     * Image upload + automatic webp encoding, public/uploads/<folder> mein (storage:link ki zaroorat nahi).
     * $folder e.g. 'teacher_images' ya 'uploads/teacher_images' (dono same folder banate hain)
     */
    public function upload($file, $folder = 'students', $width = 300, $height = 300, $quality = 85)
    {
        $folder = self::normalizeFolder($folder);

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

        // 3. Folder check + create (public/uploads ke andar, agar exist nahi karta)
        $directory = public_path('uploads/' . $folder);
        File::ensureDirectoryExists($directory);

        // 4. Secure random filename — original filename kabhi use nahi karte
        $filename = uniqid() . '_' . time() . '.webp';

        // 5. Resize + encode (Intervention se), phir public/uploads pe likhein
        $image = $this->manager->read($realPath);
        $image = $image->scale($width, $height);
        $encoded = $image->toWebp($quality);

        File::put($directory . '/' . $filename, (string) $encoded);

        // DB me sirf filename jayega (jaisa pehle tha)
        return $filename;
    }

    /**
     * Image ho to upload() (webp), warna document as-is original extension ke sath.
     * Extension whitelist ke bahar koi file (e.g. .php) reject.
     */
    public function uploadFile($file, $folder, $width = 1200, $height = 1200, $quality = 85)
    {
        if (in_array($file->getMimeType(), $this->allowedMimes)) {
            return $this->upload($file, $folder, $width, $height, $quality);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        if (!in_array($ext, $this->allowedFileExtensions, true)) {
            throw new \InvalidArgumentException('Invalid file type. Allowed: JPG, PNG, WEBP, GIF, PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, CSV, ZIP, MP4, WEBM, MOV, AVI, WMV, MKV.');
        }

        $directory = public_path('uploads/' . self::normalizeFolder($folder));
        File::ensureDirectoryExists($directory);

        $filename = uniqid() . '_' . time() . '.' . $ext;
        $file->move($directory, $filename);

        return $filename;
    }

    /**
     * public/uploads/<folder> se file delete. basename() purani DB values (folder prefix wali) ko bhi handle karta hai.
     */
    public function delete($oldFile, $folder = 'students')
    {
        if (!$oldFile) return;

        $path = public_path('uploads/' . self::normalizeFolder($folder) . '/' . basename($oldFile));

        if (File::exists($path)) {
            File::delete($path);
        }
    }

    /**
     * File maujood ho to asset('uploads/<folder>/<name>'), warna $default (current domain follow karta hai).
     */
    public static function url($name, $folder, $default = null)
    {
        if (!$name) return $default;

        $folder = self::normalizeFolder($folder);
        $name = basename($name);

        return File::exists(public_path('uploads/' . $folder . '/' . $name))
            ? asset('uploads/' . $folder . '/' . $name)
            : $default;
    }

    /**
     * Absolute path (exports/PDF/response()->file ke liye), file na ho to null.
     */
    public static function path($name, $folder)
    {
        if (!$name) return null;

        $path = public_path('uploads/' . self::normalizeFolder($folder) . '/' . basename($name));

        return File::exists($path) ? $path : null;
    }
}
