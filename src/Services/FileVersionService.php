<?php

namespace Jasotacademy\FileVersionControl\Services;

use Illuminate\Support\Facades\Storage;
use Jasotacademy\FileVersionControl\Models\FileVersion;

class FileVersionService
{
    public function uploadVersion($file, $fileId, $metadata = [])
    {
        $disk = config('file_version_control.storage_disk');
        $versionNumber = $this->getNextVersionNumber($fileId);

        $path = "files/$fileId/v{$versionNumber}_" . $file->getClientOriginalName();

        Storage::disk($disk)->put($path, file_get_contents($file));

        return FileVersion::create([
           'file_id' => $fileId,
            'version_number' => $versionNumber,
            'path' => $path,
            'metadata' => $metadata,
            'created_by' => auth()->id(),
        ]);
    }

    public function getNextVersionNumber($fileId)
    {
        $latestVersion = FileVersion::where('file_id', $fileId)->orderBy('id', 'desc')->first();
        return $latestVersion ? $latestVersion->version_number + 1 : 1;
    }
}