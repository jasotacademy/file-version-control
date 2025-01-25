<?php

namespace Jasotacademy\FileVersionControl\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Jasotacademy\FileVersionControl\Services\FileVersionService;
use Jasotacademy\FileVersionControl\Tests\TestCase;

class FileVersionServiceTest extends TestCase
{
    public function test_it_create_a_file_version()
    {
        Storage::fake('testing');

        $file = UploadedFile::fake()->create('document.pdf', 100);
        $fileId = 1;

        $service = app(FileVersionService::class);
        $version = $service->uploadVersion($file, $fileId, ['note' => 'Initial Version']);

        Storage::disk('testing')->assertExists($version->path);

        $this->assertDatabaseHas('file_versions', [
            'file_id' => $fileId,
            'version_number' => '1',
            'path' => $version->path,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function test_it_rolls_back_to_a_specific_version()
    {
        Storage::fake('testing');
        $file = UploadedFile::fake()->create('document.pdf', 100);
        $fileId = 1;

        $service = app(FileVersionService::class);

        // Upload first version
        $initialVersion = $service->uploadVersion($file, $fileId);

        // Upload second version
        $updatedFile = UploadedFile::fake()->create('document_updated.pdf', 150);
        $secondVersion = $service->uploadVersion($updatedFile, $fileId);

        // Rollback to initial version
        $newVersion = $service->rollback($fileId, $initialVersion->id);

        // Assert file is restored
        Storage::disk('testing')->assertExists($newVersion->path);


        // Assert new version created
        $this->assertDatabaseHas('file_versions', [
            'file_id' => $fileId,
            'version_number' => $secondVersion->version_number + 1,
            'metadata' => json_encode(['rollback_from' => $initialVersion->id]),
        ]);
    }

    /**
     * @throws \Exception
     */
    public function test_it_logs_a_rollback_action()
    {
        Storage::fake('testing');

        $fileId = 1;
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $service = app(FileVersionService::class);

        // upload initial version
        $initialVersion = $service->uploadVersion($file, $fileId);

        // upload new version
        $updatedFile = UploadedFile::fake()->create('document_updated.pdf', 150);
        $newVersion = $service->uploadVersion($updatedFile, $fileId);


        // Rollback to initial version
        $rollbackVersion = $service->rollback($fileId, $initialVersion->id, 'Restoring to original');

        // Assert rollback log is created
        $this->assertDatabaseHas('rollback_logs', [
            'file_version_id' => $rollbackVersion->id,
            'note' => 'Restoring to original',
        ]);
    }
}