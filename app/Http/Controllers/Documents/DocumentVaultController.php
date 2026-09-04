<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\ShipmentDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentVaultController extends Controller
{
    public function index(): Response
    {
        $documents = ShipmentDocument::with(['documentType', 'documentable', 'verifiedByUser', 'uploadedByUser'])
            ->latest()
            ->paginate(15);

        $documentTypes = DocumentType::where('is_active', true)->get();

        return Inertia::render('Documents/VerificationVault', [
            'documents' => $documents,
            'document_types' => $documentTypes,
        ]);
    }

    public function verify(ShipmentDocument $document): RedirectResponse
    {
        $document->update([
            'status' => 'VERIFIED',
            'verified_by_user_id' => 1,
            'verified_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Document verified and approved into vault.');
    }

    public function reject(Request $request, ShipmentDocument $document): RedirectResponse
    {
        $request->validate(['rejection_notes' => 'required|string']);

        $document->update([
            'status' => 'REJECTED',
            'rejection_notes' => $request->rejection_notes,
            'verified_by_user_id' => 1,
            'verified_at' => now(),
        ]);

        return redirect()->back()->with('warning', 'Document rejected.');
    }
}
