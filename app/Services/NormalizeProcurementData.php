<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Procurement;
use App\Models\RawSourceRecord;
use App\Models\Source;
use App\Models\Supplier;
use Illuminate\Support\Str;

final class NormalizeProcurementData
{
    public function handle(): array
    {
        $counts = ['suppliers' => 0, 'procurements' => 0, 'contracts' => 0];
        foreach (['fornecedores', 'licitacoes', 'contratos'] as $resource) {
            $source = Source::query()->where('key', 'alcobaca.prefeitura.'.$resource)->sole();
            RawSourceRecord::query()->where('source_id', $source->id)->where('validation_status', 'valid')->cursor()->each(function (RawSourceRecord $raw) use ($resource, $source, &$counts): void {
                $payload = $raw->payload;
                if ($resource === 'fornecedores') {
                    $this->supplier($source, $raw, $payload);
                    $counts['suppliers']++;
                } elseif ($resource === 'licitacoes') {
                    $this->procurement($source, $raw, $payload);
                    $counts['procurements']++;
                } else {
                    $this->contract($source, $raw, $payload);
                    $counts['contracts']++;
                }
            });
        }

        return $counts;
    }

    private function supplier(Source $source, RawSourceRecord $raw, array $data): void
    {
        $name = trim((string) (($data['razao_social'] ?? null) ?: $data['nome']));
        $tax = $this->nullable($data['cpf_cnpj'] ?? null);
        $digits = preg_replace('/\D/', '', $tax ?? '');
        Supplier::query()->updateOrCreate(['source_id' => $source->id, 'external_id' => (string) $data['id']], ['source_record_id' => $raw->id, 'public_slug' => Str::slug($name).'-'.$data['id'], 'name' => $name, 'normalized_name' => Str::of($name)->ascii()->upper()->squish(), 'tax_identifier' => $tax, 'tax_identifier_type' => strlen($digits) === 14 ? 'cnpj' : (strlen($digits) === 11 || str_contains($tax ?? '', '#') ? 'cpf' : null)]);
    }

    private function procurement(Source $source, RawSourceRecord $raw, array $data): void
    {
        Procurement::query()->updateOrCreate(['source_id' => $source->id, 'external_id' => (string) $data['id']], ['source_record_id' => $raw->id, 'public_slug' => Str::slug($data['numero'] ?? 'licitacao').'-'.$data['id'], 'number' => $this->nullable($data['numero'] ?? null), 'process_number' => $this->nullable($data['numero_processo'] ?? null), 'object' => $this->nullable($data['objeto'] ?? null), 'modality' => $this->nullable($data['modalidade_descricao'] ?? null), 'organization_name' => $this->nullable($data['orgao'] ?? null), 'status' => $this->nullable(implode(', ', $data['situacoes'] ?? [])), 'publication_date' => $data['data_publicacao'] ?? null, 'event_date' => $data['data_realizacao'] ?? null, 'estimated_cents' => $this->cents($data['valor_estimado'] ?? null), 'approved_cents' => $this->cents($data['valor_homologado'] ?? null), 'pncp_url' => $this->nullable($data['pncp_item_url'] ?? null), 'situations' => $data['situacoes'] ?? []]);
    }

    private function contract(Source $source, RawSourceRecord $raw, array $data): void
    {
        $supplierExternal = isset($data['contratada_id']) ? (string) $data['contratada_id'] : null;
        $procurementExternal = isset($data['licitacao_id']) ? (string) $data['licitacao_id'] : null;
        $supplier = $supplierExternal ? Supplier::query()->where('external_id', $supplierExternal)->first() : null;
        $procurement = $procurementExternal ? Procurement::query()->where('external_id', $procurementExternal)->first() : null;
        Contract::query()->updateOrCreate(['source_id' => $source->id, 'external_id' => (string) $data['id']], ['source_record_id' => $raw->id, 'supplier_id' => $supplier?->id, 'procurement_id' => $procurement?->id, 'public_slug' => Str::slug($data['numero'] ?? 'contrato').'-'.$data['id'], 'number' => $this->nullable($data['numero'] ?? null), 'process_number' => $this->nullable($data['numero_processo'] ?? null), 'object' => $this->nullable($data['objeto'] ?? null), 'organization_name' => $this->nullable($data['orgao'] ?? null), 'supplier_name' => $this->nullable($data['contratada'] ?? null), 'supplier_external_id' => $supplierExternal, 'supplier_tax_identifier' => $this->nullable($data['contratada_cpf_cnpj'] ?? null), 'value_cents' => $this->cents($data['valor'] ?? null), 'status' => $this->nullable($data['situacao'] ?? null), 'signature_date' => $data['data_assinatura'] ?? null, 'start_date' => $data['data_inicio_vigencia'] ?? null, 'end_date' => $data['data_final_vigencia'] ?? null, 'pncp_url' => $this->nullable($data['pncp_item_url'] ?? null)]);
    }

    private function cents(mixed $value): ?int
    {
        return is_numeric($value) ? (int) round((float) $value * 100) : null;
    }

    private function nullable(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }
}
