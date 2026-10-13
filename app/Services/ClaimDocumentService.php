<?php

namespace App\Services;

use App\Enums\ClaimDocumentType;
use App\Models\Claim;
use App\Models\ClaimDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class ClaimDocumentService
{
    /**
     * Store a new document for a claim.
     */
    public function upload(Claim $claim, ClaimDocumentType $type, UploadedFile $file): ClaimDocument
    {
        // 1. Store the file on the configured disk
        $path = $this->storeFile($claim, $file);

        // 2. Create the document record
        return $claim->documents()->create([
            'type'              => $type,
            'original_filename' => $file->getClientOriginalName(),
            'path'              => $path,
            'mime_type'         => $file->getMimeType(),
            'size_bytes'        => $file->getSize(),
            'uploaded_by'       => Auth::id(),
            'received_at'       => now(),
        ]);
    }

    /**
     * Soft-delete a document. The file is left on disk for audit purposes.
     */
    public function remove(ClaimDocument $document): void
    {
        // Soft delete the record — the file stays in R2 for audit
        $document->delete();
    }

    /**
     * Generate a temporary download URL for the document.
     */

    
    public function temporaryUrl(ClaimDocument $document, int $minutes = 15): string
    {
        return Storage::disk(config('filesystems.default'))
            ->temporaryUrl($document->path, now()->addMinutes($minutes));
    }

    /**
     * Store the file on the configured disk, using a structured path.
     */
    protected function storeFile(Claim $claim, UploadedFile $file): string
    {
        $prefix = "claims/{$claim->id}/" . now()->format('Y/m');

        return $file->store($prefix, config('filesystems.default'));
    }
}