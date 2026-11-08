<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetDocument;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function photo(Asset $asset): StreamedResponse
    {
        Gate::authorize('module-view', 'assets');
        abort_unless($asset->photo_path && Storage::disk('local')->exists($asset->photo_path), 404);

        return Storage::disk('local')->response($asset->photo_path, null, ['X-Content-Type-Options' => 'nosniff']);
    }

    public function document(AssetDocument $document): StreamedResponse
    {
        Gate::authorize('module-view', 'documents');
        abort_unless(Storage::disk('local')->exists($document->file_path), 404, 'Berkas fisik belum tersedia.');

        return Storage::disk('local')->download($document->file_path, $document->file_name, ['X-Content-Type-Options' => 'nosniff']);
    }

    public function preview(AssetDocument $document): StreamedResponse
    {
        Gate::authorize('module-view', 'documents');
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);
        abort_unless(in_array($document->file_type, ['application/pdf', 'image/jpeg', 'image/png'], true), 415);

        return Storage::disk('local')->response($document->file_path, $document->file_name, [
            'Content-Type' => $document->file_type, 'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ]);
    }
}
