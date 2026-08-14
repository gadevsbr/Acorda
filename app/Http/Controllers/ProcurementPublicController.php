<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Procurement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProcurementPublicController extends Controller
{
    public function contracts(Request $request): Response
    {
        $q = trim((string) $request->query('q'));
        $items = Contract::query()->with('supplier:id,name,public_slug')->when($q, fn ($b) => $b->where(fn ($x) => $x->where('number', 'like', "%{$q}%")->orWhere('supplier_name', 'like', "%{$q}%")->orWhere('object', 'like', "%{$q}%")))->latest('signature_date')->limit(50)->get()->map(fn ($item) => ['slug' => $item->public_slug, 'title' => $item->number ?: 'Contrato sem número', 'subtitle' => $item->supplier_name ?: 'Contratada não informada', 'description' => $item->object, 'valueCents' => $item->value_cents, 'date' => $item->signature_date?->toDateString()]);

        return Inertia::render('Procurement/Index', ['kind' => 'contracts', 'title' => 'Contratos', 'query' => $q, 'items' => $items]);
    }

    public function procurements(Request $request): Response
    {
        $q = trim((string) $request->query('q'));
        $items = Procurement::query()->when($q, fn ($b) => $b->where(fn ($x) => $x->where('number', 'like', "%{$q}%")->orWhere('object', 'like', "%{$q}%")->orWhere('modality', 'like', "%{$q}%")))->latest('publication_date')->limit(50)->get()->map(fn ($item) => ['slug' => $item->public_slug, 'title' => $item->number ?: 'Licitação sem número', 'subtitle' => $item->modality ?: 'Modalidade não informada', 'description' => $item->object, 'valueCents' => $item->estimated_cents, 'date' => $item->publication_date?->toDateString()]);

        return Inertia::render('Procurement/Index', ['kind' => 'procurements', 'title' => 'Licitações', 'query' => $q, 'items' => $items]);
    }

    public function suppliers(Request $request): Response
    {
        $q = Str::of((string) $request->query('q'))->trim()->limit(100)->toString();
        $normalized = Str::of($q)->ascii()->upper()->toString();
        $items = Supplier::query()->withCount('contracts')->when(mb_strlen($normalized) >= 2, fn ($b) => $b->where('normalized_name', 'like', "%{$normalized}%"))->when(mb_strlen($normalized) < 2, fn ($b) => $b->whereRaw('1=0'))->orderBy('name')->limit(50)->get()->map(fn ($item) => ['slug' => $item->public_slug, 'title' => $item->name, 'subtitle' => $this->publicTaxId($item->tax_identifier, $item->tax_identifier_type), 'description' => $item->contracts_count.' contrato(s) relacionado(s)', 'valueCents' => null, 'date' => null]);

        return Inertia::render('Procurement/Index', ['kind' => 'suppliers', 'title' => 'Fornecedores', 'query' => $q, 'items' => $items]);
    }

    public function contract(Contract $contract): Response
    {
        $contract->load(['supplier', 'procurement', 'sourceRecord']);

        return Inertia::render('Procurement/Show', ['kind' => 'contract', 'record' => $this->contractData($contract)]);
    }

    public function procurement(Procurement $procurement): Response
    {
        $procurement->load(['contracts.supplier', 'sourceRecord']);

        return Inertia::render('Procurement/Show', ['kind' => 'procurement', 'record' => ['title' => $procurement->number ?: 'Licitação sem número', 'subtitle' => $procurement->modality, 'object' => $procurement->object, 'organization' => $procurement->organization_name, 'status' => $procurement->status, 'valueCents' => $procurement->estimated_cents, 'approvedCents' => $procurement->approved_cents, 'date' => $procurement->publication_date?->toDateString(), 'sourceUrl' => $procurement->sourceRecord->source_url, 'contracts' => $procurement->contracts->map(fn ($c) => $this->contractData($c))]]);
    }

    public function supplier(Supplier $supplier): Response
    {
        $supplier->load(['contracts.procurement', 'sourceRecord']);

        return Inertia::render('Procurement/Show', ['kind' => 'supplier', 'record' => ['title' => $supplier->name, 'subtitle' => $this->publicTaxId($supplier->tax_identifier, $supplier->tax_identifier_type), 'sourceUrl' => $supplier->sourceRecord->source_url, 'contracts' => $supplier->contracts->map(fn ($c) => $this->contractData($c))]]);
    }

    private function contractData(Contract $c): array
    {
        return ['slug' => $c->public_slug, 'title' => $c->number ?: 'Contrato sem número', 'subtitle' => $c->supplier_name, 'object' => $c->object, 'organization' => $c->organization_name, 'status' => $c->status, 'valueCents' => $c->value_cents, 'date' => $c->signature_date?->toDateString(), 'supplier' => $c->supplier ? ['name' => $c->supplier->name, 'slug' => $c->supplier->public_slug] : null, 'procurement' => $c->procurement ? ['number' => $c->procurement->number, 'slug' => $c->procurement->public_slug] : null, 'sourceUrl' => $c->sourceRecord?->source_url];
    }

    private function publicTaxId(?string $value, ?string $type): ?string
    {
        if (! $value) {
            return null;
        } if ($type !== 'cpf') {
            return $value;
        } $digits = preg_replace('/\D/', '', $value);

        return strlen($digits) === 11 ? '***.***.'.substr($digits, 6, 3).'-**' : $value;
    }
}
