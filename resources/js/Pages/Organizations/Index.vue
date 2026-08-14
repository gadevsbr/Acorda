<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

interface OrganizationSummary {
    slug: string;
    name: string;
    type: 'secretariat' | 'department' | 'organization';
    parent: { name: string; slug: string } | null;
}

defineProps<{
    organizations: OrganizationSummary[];
    lastUpdatedAt: string | null;
}>();

const typeLabels = {
    secretariat: 'Secretaria',
    department: 'Departamento',
    organization: 'Órgão',
};

const formatDate = (value: string | null): string =>
    value
        ? new Intl.DateTimeFormat('pt-BR', { dateStyle: 'long', timeZone: 'America/Bahia' }).format(new Date(value))
        : 'não informada';
</script>

<template>
    <Head title="Órgãos municipais" />
    <div class="min-h-screen bg-stone-50 text-slate-950">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-5 py-5 sm:px-8">
                <Link href="/" class="text-xl font-black tracking-tight">Acorda <span class="text-teal-700">Alcobaça</span></Link>
                <Link :href="route('sources.index')" class="text-sm font-bold text-slate-600 hover:text-teal-800">Fontes</Link>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-5 py-12 sm:px-8 sm:py-16">
            <p class="text-sm font-black uppercase tracking-[0.18em] text-teal-700">Prefeitura Municipal</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">Estrutura organizacional</h1>
            <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600">
                Órgãos encontrados na fonte oficial monitorada. Responsáveis são exibidos somente na página do órgão e não são associados automaticamente a perfis de pessoas.
            </p>
            <p class="mt-3 text-sm text-slate-500">Atualização informada pela fonte: {{ formatDate(lastUpdatedAt) }}</p>

            <div class="mt-10 grid gap-4 sm:grid-cols-2">
                <Link
                    v-for="organization in organizations"
                    :key="organization.slug"
                    :href="route('organizations.show', organization.slug)"
                    class="border border-slate-200 bg-white p-6 shadow-sm transition hover:border-teal-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700"
                >
                    <span class="text-xs font-black uppercase tracking-widest text-teal-700">{{ typeLabels[organization.type] }}</span>
                    <h2 class="mt-2 text-xl font-bold leading-snug">{{ organization.name }}</h2>
                    <p v-if="organization.parent" class="mt-3 text-sm text-slate-500">Vinculado a {{ organization.parent.name }}</p>
                </Link>
            </div>
        </main>
    </div>
</template>
