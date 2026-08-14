<?php

namespace App\Collectors\Alcobaca\Prefeitura;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class PayrollEnvelopeValidator
{
    /**
     * @param  array<string, mixed>  $payload
     *
     * @throws ValidationException
     */
    public function validate(array $payload): void
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
            'funcionario' => ['sometimes', 'string'],
            'mes_de_referencia' => ['sometimes', 'string'],
            'salario_bruto' => ['sometimes', 'numeric'],
            'salario_liquido' => ['sometimes', 'numeric'],
            'total_de_descontos' => ['sometimes', 'numeric'],
        ]);

        return $validator->fails() ? $validator->errors()->all() : [];
    }
}
