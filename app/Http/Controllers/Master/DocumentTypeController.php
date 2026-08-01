<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentTypeController extends Controller
{
    public function index()
    {
        $documentTypes = DocumentType::withCount('projects')->orderBy('code', 'asc')->get();
        return Inertia::render('Master/DocumentTypes', compact('documentTypes'));
    }
}
