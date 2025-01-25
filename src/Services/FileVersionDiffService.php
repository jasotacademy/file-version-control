<?php

namespace Jasotacademy\FileVersionControl\Services;

use Illuminate\Support\Facades\Storage;
use Jasotacademy\FileVersionControl\Models\FileVersion;
use Symfony\Component\Process\Process;

class FileVersionDiffService
{
    /**
     * Get the difference between two file versions.
     *
     * @param int $version1Id
     * @param int $version2Id
     * @return array
     */
    public function getDiff(int $version1Id, int $version2Id): array
    {
        $version1 = FileVersion::findOrFail($version1Id);
        $version2 = FileVersion::findOrFail($version2Id);

        $diff = [];

        if ($this->isTextFile($version1->path) && $this->isTextFile($version2->path)) {
            $diff['text_diff'] = $this->getTextDiff($version1, $version2);
        }

        // compare paths
        if ($version1->path !== $version2->path) {
            $diff['path_diff'] = [
              'version1' => $version1->path,
              'version2' => $version2->path,
            ];
        }

        // compare metadata
        $metadataDiff = $this->getArrayDiff($version1->metadata, $version2->metadata);
        if (! empty($metadataDiff)) {
            $diff['metadata_diff'] = $metadataDiff;
        }

        return $diff;
    }

    /**
     * Check if a file is text based.
     *
     * @param string $path
     * @return bool
     */
    protected function isTextFile(string $path): bool {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        return in_array($extension, ['txt', 'md', 'json', 'xml', 'html', 'csv']);
    }

    /**
     * Get text differences between two file versions.
     *
     * @param FileVersion $version1
     * @param FileVersion $version2
     * @return string
     */
    protected function getTextDiff(FileVersion $version1, FileVersion $version2) : string
    {
        $disk = config('file_version_control.storage_disk');

        $content1 = Storage::disk($disk)->get($version1->path);
        $content2 = Storage::disk($disk)->get($version2->path);

        // use a diff tool to compute the differences
        $tempFile1 = tempnam(sys_get_temp_dir(), 'diff_');
        $tempFile2 = tempnam(sys_get_temp_dir(), 'diff_');

        file_put_contents($tempFile1, $content1);
        file_put_contents($tempFile2, $content2);

        $process = new Process(['diff', '-u', $tempFile1, $tempFile2]);
        $process->run();

        unlink($tempFile1);
        unlink($tempFile2);

        return $process->getOutput();
    }

    /**
     * Get the differences between two array.
     *
     * @param array|null $array1
     * @param array|null $array2
     * @return array
     */
    protected function getArrayDiff(?array $array1, ?array $array2): array
    {
        $array1 = $array1 ?? [];
        $array2 = $array2 ?? [];

        return array_diff_assoc($array1, $array2);
    }
}