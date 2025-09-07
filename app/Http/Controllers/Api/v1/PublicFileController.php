<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicFileController extends Controller
{
    public function getFile(Request $request, $filetype, $filename)
    {
        if (!in_array($filetype, ['uploads', 'thumbnails'])) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $path = "public/{$filetype}/{$filename}";

        if (!Storage::exists($path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->file(storage_path('app/' . $path));
    }
}
