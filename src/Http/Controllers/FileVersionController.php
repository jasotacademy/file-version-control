<?php

namespace Jasotacademy\FileVersionControl\Http\Controllers;

use Illuminate\Http\Request;
use Jasotacademy\FileVersionControl\Models\FileVersion;
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
}