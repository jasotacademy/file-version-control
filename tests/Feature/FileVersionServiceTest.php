<?php

namespace Jasotacademy\FileVersionControl\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Jasotacademy\FileVersionControl\Models\FileVersion;
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

    public function test_it_shows_differences_between_file_versions()
    {
        Storage::fake('testing');
        $file1 = UploadedFile::fake()->create('document.pdf')->storeAs('files/1', 'v1_document.txt', 'testing');
        $file2 = UploadedFile::fake()->create('document.pdf')->storeAs('files/2', 'v2_1_document.txt', 'testing');

        $version1 = FileVersion::create([
            'file_id' => 1,
            'version_number' => 1,
            'path' => $file1,
            'filename' => 'v1_document.txt',
            'metadata' => ['author' => 'Author 1'],
            'created_by' => 1,
        ]);

        $version2 = FileVersion::create([
            'file_id' => 1,
            'version_number' => 2,
            'path' => $file2,
            'filename' => 'v2_1_document.txt',
            'metadata' => ['author' => 'Author 2'],
            'created_by' => 1,
        ]);

        $response = $this->getJson("/file-version-control/file-version/diff/{$version1->id}/{$version2->id}");

        $response->assertJsonStructure([
            'text_diff',
            'path_diff',
            'metadata_diff',
        ]);
    }

    public function test_it_soft_deletes_a_version()
    {
        $version = FileVersion::factory()->create();

        $version->softDelete();

        $this->assertSoftDeleted('file_versions', ['id' => $version->id]);
    }

    public function test_it_restores_a_soft_deleted_version()
    {
        $version = FileVersion::factory()->create();
        $version->softDelete();

        $version->restoreVersion();

        $this->assertDatabaseHas('file_versions', [
            'id' => $version->id,
            'deleted_at' => null,
        ]);
    }

    public function test_it_force_deletes_a_version_fluently()
    {
        $version = FileVersion::factory()->create();

        $version->forceDeleteVersion();

        $this->assertDatabaseMissing('file_versions', ['id' => $version->id]);
    }
}