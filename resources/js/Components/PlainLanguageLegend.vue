<script setup lang="ts">
defineProps<{ type: 'general' | 'people' | 'procurement' | 'sources' }>();
</script>

<template>
    <aside class="rounded-xl border border-sky-200 bg-sky-50 p-5 text-sm text-slate-700" aria-labelledby="legend-title">
        <h2 id="legend-title" class="font-black text-slate-950">Entenda os dados</h2>
        <dl v-if="type === 'people'" class="mt-3 grid gap-3 sm:grid-cols-2">
            <div><dt class="font-bold">Competência</dt><dd>Mês e ano a que o pagamento se refere.</dd></div>
            <div><dt class="font-bold">Bruto</dt><dd>Valor antes dos descontos.</dd></div>
            <div><dt class="font-bold">Descontos</dt><dd>Valores retirados do pagamento.</dd></div>
            <div><dt class="font-bold">Líquido</dt><dd>Valor depois dos descontos.</dd></div>
        </dl>
        <dl v-else-if="type === 'procurement'" class="mt-3 grid gap-3 sm:grid-cols-2">
            <div><dt class="font-bold">Licitação</dt><dd>Processo usado pelo poder público para escolher uma proposta.</dd></div>
            <div><dt class="font-bold">Contrato</dt><dd>Acordo firmado depois da contratação.</dd></div>
            <div><dt class="font-bold">Fornecedor</dt><dd>Pessoa ou empresa que fornece o produto ou serviço.</dd></div>
            <div><dt class="font-bold">Valor estimado</dt><dd>Previsão inicial; pode ser diferente do valor contratado.</dd></div>
        </dl>
        <dl v-else-if="type === 'sources'" class="mt-3 grid gap-3 sm:grid-cols-2">
            <div><dt class="font-bold">Disponível</dt><dd>A consulta funcionou e trouxe um conjunto completo.</dd></div>
            <div><dt class="font-bold">Parcial</dt><dd>A consulta funcionou, mas veio vazia, incompleta ou com alguma limitação.</dd></div>
            <div><dt class="font-bold">Indisponível</dt><dd>A fonte não respondeu naquele momento.</dd></div>
            <div><dt class="font-bold">Fonte oficial</dt><dd>Site público de onde a informação foi coletada.</dd></div>
        </dl>
        <dl v-else class="mt-3 grid gap-3 sm:grid-cols-2">
            <div><dt class="font-bold">Fonte oficial</dt><dd>Local público de onde a informação veio.</dd></div>
            <div><dt class="font-bold">Última atualização</dt><dd>Quando a fonte informou ou entregou aquele dado.</dd></div>
            <div><dt class="font-bold">Sem resultado</dt><dd>Não significa que o fato não existe; apenas que não foi localizado nesta base.</dd></div>
            <div><dt class="font-bold">Registro separado</dt><dd>Dados só são ligados quando existe identificação oficial segura.</dd></div>
        </dl>
    </aside>
</template>
