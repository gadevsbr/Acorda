<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

type SourceStatus =
    | 'operational'
    | 'partial'
    | 'unavailable'
    | 'schema_changed'
    | 'maintenance'
    | 'not_integrated';

interface PublicSource {
    key: string;
    name: string;
    entity: string;
    officialUrl: string;
    status: SourceStatus;
    lastSuccessfulAt: string | null;
    lastCheckedAt: string | null;
    httpStatus: number | null;
    recordsCount: number | null;
    message: string | null;
}

defineProps<{ sources: PublicSource[] }>();

const statusLabels: Record<SourceStatus, string> = {
    operational: 'Operacional',
    partial: 'Parcial',
    unavailable: 'Temporariamente indisponível',
    schema_changed: 'Formato alterado',
    maintenance: 'Em manutenção',
    not_integrated: 'Não integrada',
};

const statusClasses: Record<SourceStatus, string> = {
    operational: 'bg-emerald-100 text-emerald-900',
    partial: 'bg-amber-100 text-amber-950',
    unavailable: 'bg-red-100 text-red-900',
    schema_changed: 'bg-red-100 text-red-900',
    maintenance: 'bg-slate-200 text-slate-800',
    not_integrated: 'bg-slate-200 text-slate-800',
};

const formatDate = (value: string | null): string => {
    if (!value) return 'Ainda não registrada';

    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
        timeZone: 'America/Bahia',
    }).format(new Date(value));
};
</script>

<template>
    <Head title="Fontes monitoradas" />

    <div class="min-h-screen bg-stone-50 text-slate-950">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-5 py-5 sm:px-8">
                <Link href="/" class="text-xl font-black tracking-tight">
                    Acorda <span class="text-teal-700">Alcobaça</span>
                </Link>
                <span class="text-sm font-semibold text-slate-500">Transparência da plataforma</span>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-5 py-12 sm:px-8 sm:py-16">
            <p class="text-sm font-black uppercase tracking-[0.18em] text-teal-700">Rastreabilidade</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">Fontes monitoradas</h1>
            <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600">
                Aqui mostramos de onde vêm os dados e quando conseguimos consultar cada fonte.
                Uma resposta vazia ou uma fonte indisponível nunca é apresentada como prova de que
                determinado fato não existe.
            </p>

            <div v-if="sources.length" class="mt-10 space-y-5">
                <article
                    v-for="source in sources"
                    :key="source.key"
                    class="border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
                >
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">{{ source.entity }}</p>
                            <h2 class="mt-1 text-2xl font-bold">{{ source.name }}</h2>
                        </div>
                        <span
                            class="w-fit rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide"
                            :class="statusClasses[source.status]"
                        >
                            {{ statusLabels[source.status] }}
                        </span>
                    </div>

                    <dl class="mt-6 grid gap-5 border-t border-slate-200 pt-6 sm:grid-cols-3">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Última consulta</dt>
                            <dd class="mt-1 font-semibold">{{ formatDate(source.lastCheckedAt) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">HTTP</dt>
                            <dd class="mt-1 font-semibold">{{ source.httpStatus ?? 'Não disponível' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Registros informados</dt>
                            <dd class="mt-1 font-semibold">{{ source.recordsCount ?? 'Não disponível' }}</dd>
                        </div>
                    </dl>

                    <p v-if="source.message" class="mt-6 border-l-4 border-amber-400 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-950">
                        {{ source.message }}
                    </p>

                    <a
                        :href="source.officialUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-6 inline-flex font-bold text-teal-700 underline decoration-teal-300 underline-offset-4 hover:text-teal-900"
                    >
                        Ver fonte oficial
                        <span class="sr-only"> de {{ source.name }} (abre em nova aba)</span>
                    </a>
                </article>
            </div>

            <p v-else class="mt-10 border border-slate-200 bg-white p-6 text-slate-600">
                Nenhuma fonte foi configurada nesta instalação.
            </p>
        </main>
    </div>
</template>
