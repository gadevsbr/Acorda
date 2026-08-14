<?php

namespace App\Collectors\Alcobaca\Prefeitura;

use Illuminate\Support\Facades\Validator;

final class OrganizationalStructureValidator
{
    /** @param array<string, mixed> $payload */
    public function validateEnvelope(array $payload): void
    {
        Validator::make($payload, [
            'total' => ['required', 'integer', 'min:0'],
            'per_page' => ['required', 'integer', 'min:1'],
            'current_page' => ['required', 'integer', 'min:1'],
            'last_page' => ['required', 'integer', 'min:0'],
            'data' => ['present', 'array'],
            'data.*' => ['array'],
        ])->validate();
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<int, string>
     */
    public function recordErrors(array $record): array
    {
        $validator = Validator::make($record, [
            'id' => ['required'],
            'nome' => ['required', 'string', 'max:255'],
            'responsavel' => ['sometimes', 'string', 'max:255'],
            'orgao_vinculado_nome' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'updated_at' => ['sometimes', 'date'],
        ]);

        return $validator->fails() ? $validator->errors()->all() : [];
    }
}
