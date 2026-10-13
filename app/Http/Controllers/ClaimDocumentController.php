<?php

namespace App\Http\Controllers;

use App\Enums\ClaimDocumentType;
use App\Http\Requests\StoreClaimDocumentRequest;
use App\Models\Claim;
use App\Models\ClaimDocument;
use App\Services\ClaimDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class ClaimDocumentController extends Controller
{
    public function __construct(
        protected ClaimDocumentService $documents,
    ) {}

    /**
     * Upload a document for a claim.
     */
    public function store(StoreClaimDocumentRequest $request, Claim $claim): RedirectResponse
    {
        $type = ClaimDocumentType::from($request->input('type'));

        $this->documents->upload(
            $claim,
            $type,
            $request->file('file'),
        );

        return redirect()
            ->route('claims.show', $claim)
            ->with('success', "{$type->label()} uploaded successfully.");
    }

    /**
     * Remove a document (soft delete).
     */
    public function destroy(Claim $claim, ClaimDocument $document): RedirectResponse
    {
        // Guard: the document must belong to the claim
        abort_if($document->claim_id !== $claim->id, 404);

        $this->documents->remove($document);

        return redirect()
            ->route('claims.show', $claim)
            ->with('success', "Document removed.");
    }

    /**
     * Download a document via a temporary signed URL.
     */
    public function download(Claim $claim, ClaimDocument $document): RedirectResponse
    {
        abort_if($document->claim_id !== $claim->id, 404);
        abort_if($document->trashed(), 410, 'This document has been removed.');

        $url = $this->documents->temporaryUrl($document, minutes: 15);

        return redirect()->away($url);
    }
}