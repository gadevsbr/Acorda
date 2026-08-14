<?php

namespace App\Collectors\Alcobaca\Prefeitura;

use Illuminate\Support\Facades\Validator;

final class ProcurementValidator
{
    public function errors(string $resource, array $record): array
    {
        $rules = match ($resource) {
            'fornecedores' => ['id' => ['required'], 'nome' => ['required', 'string']],
            'contratos' => ['id' => ['required'], 'numero' => ['nullable', 'string'], 'contratada' => ['nullable', 'string'], 'objeto' => ['nullable', 'string'], 'valor' => ['nullable', 'numeric']],
            'licitacoes' => ['id' => ['required'], 'numero' => ['nullable', 'string'], 'objeto' => ['nullable', 'string'], 'modalidade_descricao' => ['nullable', 'string']],
            'fiscais-contrato' => ['id' => ['required']],
            default => throw new \InvalidArgumentException('Recurso inválido.'),
        };
        $validator = Validator::make($record, $rules);

        return $validator->fails() ? $validator->errors()->all() : [];
    }
}
