<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerificationRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class VerificationDocumentController extends Controller
{
    public function __invoke(VerificationRequest $verificationRequest, int $index): Response
    {
        $path = $verificationRequest->document_paths[$index] ?? null;

        abort_if(! $path || ! Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
