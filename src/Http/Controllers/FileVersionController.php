<?php

namespace Jasotacademy\FileVersionControl\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Jasotacademy\FileVersionControl\Models\File;
use Jasotacademy\FileVersionControl\Models\FileVersion;
use Jasotacademy\FileVersionControl\Services\FileVersionDiffService;
use Jasotacademy\FileVersionControl\Services\FileVersionService;

class FileVersionController extends Controller
{
    protected FileVersionService $service;

    public function __construct(FileVersionService $service) {
        $this->service = $service;
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'file_id' => 'required',
        ]);

        $file = $request->file('file');
        $fileId = $request->input('file_id');
        $metadata = $request->input('metadata', []);

        $version = $this->service->uploadVersion($file, $fileId, $metadata);
        return response()->json(['message' => 'File uploaded successfully', 'version' => $version]);
    }

    public function index($fileId)
    {
        $versions = FileVersion::where('file_id', $fileId)->get();
        return response()->json($versions);
    }

    public function getFileHistory(File $file)
    {
        return response()->json($file->versions);
    }

    public function rollback($fileId, $versionId)
    {
        try {
            $newVersion = $this->service->rollback($fileId, $versionId);
            return response()->json([
                'message' => 'File rollback successfully',
                'version' => $newVersion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getRollbackLogs($fileId)
    {
        $logs = DB::table('rollback_logs')
                    ->join('file_versions', 'rollback_logs.file_version_id', '=', 'file_versions.id')
                    ->where('file_versions.file_id', $fileId)
                    ->select('rollback_logs.*', 'file_versions.version_number', 'file_versions.path')
                    ->orderBy('rollback_logs.rolled_back_at', 'desc')
                    ->get();

        return response()->json($logs);
    }

    public function diff($versionId1, $versionId2)
    {
        var_dump('hello');
        $diffService = app(FileVersionDiffService::class);
        $diff = $diffService->getDiff($versionId1, $versionId2);

        return response()->json($diff);
    }
}