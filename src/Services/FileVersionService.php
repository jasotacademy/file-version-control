<?php

namespace Jasotacademy\FileVersionControl\Services;

use Illuminate\Support\Facades\Storage;
use Jasotacademy\FileVersionControl\Models\File;
use Jasotacademy\FileVersionControl\Models\FileVersion;

class FileVersionService
{
    public function uploadVersion($file, $fileId, $metadata = []): FileVersion
    {
        $disk = config('file_version_control.storage_disk');
        $versionNumber = $this->getNextVersionNumber($fileId);

        $path = "files/$fileId/v{$versionNumber}_" . $file->getClientOriginalName();

        Storage::disk($disk)->put($path, file_get_contents($file));

        return FileVersion::create([
            'file_id' => $fileId,
            'version_number' => $versionNumber,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'metadata' => $metadata,
            'size' => $file->getSize(),
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * @throws \Exception
     */
    public function rollback(int $fileId, int $versionId): FileVersion
    {
        $version = FileVersion::where('file_id', $fileId)->findOrFail($versionId);

        $currentVersion = FileVersion::where('file_id', $fileId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$currentVersion) {
            throw new \Exception('No current version found for this file.');
        }

        // Copy the selected version's file to the current version's path
        $disk = config('file_version_control.storage_disk');
        Storage::disk($disk)->copy($version->path, $currentVersion->path);

        // Save a new version entry
        return FileVersion::create([
            'file_id' => $fileId,
            'version_number' => $currentVersion->version_number + 1,
            'path' => $version->path,
            'filename' => $version->filename,
            'mime_type' => $version->mime_type,
            'metadata' => ['rollback_from' => $version->id],
            'size' => $version->size,
            'created_by' => auth()->id(),
        ]);
    }

    public function getNextVersionNumber($fileId): int|string
    {
        $latestVersion = FileVersion::where('file_id', $fileId)->orderBy('id', 'desc')->first();
        return $latestVersion ? $latestVersion->version_number + 1 : 1;
    }

    private function incrementVersion($version): string
    {
        [$major, $minor] = explode('.', $version);
        return $major . '.' . ($minor + 1);
    }
}