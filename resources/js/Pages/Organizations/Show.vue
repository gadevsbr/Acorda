<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PlainLanguageLegend from '@/Components/PlainLanguageLegend.vue';

interface OrganizationSummary {
    slug: string;
    name: string;
    type: string;
    parent: { name: string; slug: string } | null;
}

interface OrganizationDetail extends OrganizationSummary {
    responsibleName: string | null;
    phone: string | null;
    email: string | null;
    address: string | null;
    competencies: string | null;
    openingHours: string | null;
    children: OrganizationSummary[];
    provenance: {
        sourceName: string;
        officialUrl: string;
        fetchedAt: string | null;
        sourceUpdatedAt: string | null;
        validationStatus: string;
    };
}

defineProps<{ organization: OrganizationDetail }>();

const formatDateTime = (value: string | null): string =>
    value
        ? new Intl.DateTimeFormat('pt-BR', {
              dateStyle: 'short',
              timeStyle: 'short',
              timeZone: 'America/Bahia',
          }).format(new Date(value))
        : 'não informada';
</script>

<template>
    <Head :title="organization.name" />
    <div class="min-h-screen bg-stone-50 text-slate-950">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-5 py-5 sm:px-8">
                <Link :href="route('home')" class="text-xl font-black tracking-tight">Acorda <span class="text-teal-700">Alcobaça</span></Link>
                <Link :href="route('organizations.index')" class="text-sm font-bold text-slate-600 hover:text-teal-800">Todos os órgãos</Link>
            </div>
        </header>

        <main class="mx-auto max-w-4xl px-5 py-12 sm:px-8 sm:py-16">
            <nav v-if="organization.parent" aria-label="Hierarquia" class="text-sm text-slate-500">
                <Link :href="route('organizations.show', organization.parent.slug)" class="underline underline-offset-4 hover:text-teal-800">{{ organization.parent.name }}</Link>
                <span aria-hidden="true"> / </span>
            </nav>
            <h1 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">{{ organization.name }}</h1>
            <PlainLanguageLegend type="general" class="mt-7" />

            <section class="mt-10 border border-slate-200 bg-white p-6 sm:p-8">
                <h2 class="text-xl font-bold">Informações publicadas</h2>
                <dl class="mt-6 grid gap-6 sm:grid-cols-2">
                    <div v-if="organization.responsibleName">
                        <dt class="text-xs font-black uppercase tracking-wide text-slate-500">Responsável informado pela fonte</dt>
                        <dd class="mt-1 font-semibold">{{ organization.responsibleName }}</dd>
                    </div>
                    <div v-if="organization.openingHours">
                        <dt class="text-xs font-black uppercase tracking-wide text-slate-500">Funcionamento</dt>
                        <dd class="mt-1">{{ organization.openingHours }}</dd>
                    </div>
                    <div v-if="organization.phone">
                        <dt class="text-xs font-black uppercase tracking-wide text-slate-500">Telefone</dt>
                        <dd class="mt-1">{{ organization.phone }}</dd>
                    </div>
                    <div v-if="organization.email">
                        <dt class="text-xs font-black uppercase tracking-wide text-slate-500">E-mail</dt>
                        <dd class="mt-1 break-all">{{ organization.email }}</dd>
                    </div>
                    <div v-if="organization.address" class="sm:col-span-2">
                        <dt class="text-xs font-black uppercase tracking-wide text-slate-500">Endereço</dt>
                        <dd class="mt-1">{{ organization.address }}</dd>
                    </div>
                </dl>
            </section>

            <section v-if="organization.competencies" class="mt-6 border border-slate-200 bg-white p-6 sm:p-8">
                <h2 class="text-xl font-bold">Competências</h2>
                <p class="mt-4 whitespace-pre-line leading-7 text-slate-700">{{ organization.competencies }}</p>
            </section>

            <section v-if="organization.children.length" class="mt-6 border border-slate-200 bg-white p-6 sm:p-8">
                <h2 class="text-xl font-bold">Órgãos vinculados</h2>
                <ul class="mt-4 space-y-3">
                    <li v-for="child in organization.children" :key="child.slug">
                        <Link :href="route('organizations.show', child.slug)" class="font-semibold text-teal-700 underline underline-offset-4 hover:text-teal-900">{{ child.name }}</Link>
                    </li>
                </ul>
            </section>

            <aside class="mt-8 border-l-4 border-teal-700 bg-teal-50 p-5 text-sm leading-6 text-slate-700">
                <p class="font-bold text-slate-950">Fonte oficial</p>
                <p>Coletado em {{ formatDateTime(organization.provenance.fetchedAt) }}. Atualização informada pela fonte: {{ formatDateTime(organization.provenance.sourceUpdatedAt) }}.</p>
                <a :href="organization.provenance.officialUrl" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex font-bold text-teal-800 underline underline-offset-4">Ver registro na fonte oficial <span class="sr-only">(abre em nova aba)</span></a>
            </aside>
        </main>
    </div>
</template>
