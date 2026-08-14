<?php

namespace App\Collectors\Alcobaca\Prefeitura;

use Illuminate\Support\Facades\Validator;

final class ExpenseValidator
{
    public function validateEnvelope(array $payload): void
    {
        Validator::make($payload, [
            'total' => ['required', 'integer', 'min:0'], 'per_page' => ['required', 'integer', 'min:1'],
            'current_page' => ['required', 'integer', 'min:1'], 'last_page' => ['required', 'integer', 'min:0'],
            'data' => ['present', 'array'], 'data.*' => ['array'],
        ])->validate();
    }

    public function recordErrors(array $record): array
    {
        $validator = Validator::make($record, [
            'des_codigo' => ['required', 'numeric'], 'des_data' => ['required', 'date'],
            'des_unidade_gestora' => ['required', 'string'], 'des_credor' => ['required', 'string'],
            'des_fase' => ['required', 'string'], 'des_valor' => ['required', 'numeric'],
            'diaria' => ['sometimes', 'boolean'], 'covid_19' => ['sometimes', 'boolean'],
            'repasse_transferencia' => ['sometimes', 'boolean'],
        ]);

        return $validator->fails() ? $validator->errors()->all() : [];
    }
}
