<?php

namespace App\Modules\Legal\Controllers;

use App\Modules\Auth\Resources\UserResource;
use App\Modules\Legal\Services\LegalService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LegalController
{
    public function __construct(
        private LegalService $legal
    ) {}

    public function accept(Request $request): UserResource
    {
        $data = $request->validate([
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['string', Rule::in(array_keys($this->legal->currentVersions()))],
        ], [
            'documents.required' => 'Indica los documentos que aceptas.',
            'documents.*.in' => 'El documento indicado no existe.',
        ]);

        $this->legal->accept($request->user(), $data['documents'], $request);

        return new UserResource($request->user());
    }
}
