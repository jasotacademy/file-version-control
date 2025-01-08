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
            'version_number' => $versionNumber,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'metadata' => $metadata,
            'size' => $file->getSize(),
            'created_by' => auth()->id(),
        ]);
    }

    public function rollbackToVersion(File $file, FileVersion $version): void
    {
        if ($version->file_id !== $file->id) {
            throw new \InvalidArgumentException("The version does not belong to the specified file.");
        }

        $file->update([
            'path' => $version->path,
            'size' => $version->size,
            'mime_type' => $version->mime_type,
        ]);

        $file->versions()->create([
            'version' => $this->getNextVersionNumber($file),
            'path' => $version->path,
            'filename' => $version->filename,
            'mime_type' => $version->mime_type,
            'size' => $version->size,
            'metadata' => [
                'action' => 'rollback',
                'rolled_back_to' => $version->version,
            ],
        ]);
    }

    public function getNextVersionNumber($fileId): int|string
    {
        $latestVersion = FileVersion::where('file_id', $fileId)->orderBy('id', 'desc')->first();
        return $latestVersion ? $this->incrementVersion($latestVersion->version) : 1;
    }

    private function incrementVersion($version): string
    {
        [$major, $minor] = explode('.', $version);
        return $major . '.' . ($minor + 1);
    }
}