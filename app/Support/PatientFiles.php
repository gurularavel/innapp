<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Where patient uploads live and how they are handed back to the browser.
 *
 * Photos, visit files and custom-field files hold medical data, so they are
 * written to the private disk and streamed through an authenticated route —
 * never exposed under public/storage. Files uploaded before this change still
 * sit on the public disk; they are found there as a fallback until
 * `patient-files:privatize` has moved them.
 */
class PatientFiles
{
    public const DISK        = 'local';
    public const LEGACY_DISK = 'public';

    public const PHOTOS  = 'patients/photos';
    public const VISITS  = 'patients/visits';
    public const CUSTOM  = 'patients/custom_files';

    /** Types the browser may render inline; everything else is a download. */
    private const INLINE_TYPES = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf',
    ];

    public static function store(UploadedFile $file, string $dir): string
    {
        return $file->store($dir, self::DISK);
    }

    /** Disk the file currently sits on, or null when it is gone. */
    public static function diskOf(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        foreach ([self::DISK, self::LEGACY_DISK] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return $disk;
            }
        }

        return null;
    }

    public static function exists(?string $path): bool
    {
        return self::diskOf($path) !== null;
    }

    public static function delete(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        foreach ([self::DISK, self::LEGACY_DISK] as $disk) {
            Storage::disk($disk)->delete($path);
        }
    }

    /**
     * Stream a stored file. The content type comes from the file itself, not
     * from the request or the stored name, and is pinned with nosniff so a
     * disguised upload cannot run as HTML or script in the app's origin.
     */
    public static function response(string $path, ?string $downloadName = null): BinaryFileResponse
    {
        $disk = self::diskOf($path);

        abort_if($disk === null, 404);

        $absolute = Storage::disk($disk)->path($path);
        $mime     = Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';
        $name     = self::safeName($downloadName ?: basename($path));

        $disposition = in_array($mime, self::INLINE_TYPES, true) ? 'inline' : 'attachment';

        return response()->file($absolute, [
            'Content-Type'            => $mime,
            'Content-Disposition'     => $disposition . '; filename="' . $name . '"',
            'X-Content-Type-Options'  => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
            'Cache-Control'           => 'private, max-age=300',
        ]);
    }

    /** Move one file from the public disk to the private one; true when moved. */
    public static function privatize(string $path): bool
    {
        $public  = Storage::disk(self::LEGACY_DISK);
        $private = Storage::disk(self::DISK);

        if (! $public->exists($path)) {
            return false;
        }

        if (! $private->exists($path)) {
            $private->writeStream($path, $public->readStream($path));
        }

        return $public->delete($path);
    }

    private static function safeName(string $name): string
    {
        $name = preg_replace('/[\r\n"\\\\\/]+/', '_', $name) ?? 'file';

        return mb_substr($name, 0, 150) ?: 'file';
    }
}
