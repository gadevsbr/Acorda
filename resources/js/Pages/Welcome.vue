<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    canLogin: boolean;
    stats: { organizations: number; people: number; activeEmployments: number; positions: number; payrollRecords: number; netPayrollCents: number; payrollReference: string | null; operationalSources: number; partialSources: number };
}>();
const number = new Intl.NumberFormat('pt-BR');
const money = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
const hasPublicData = props.stats.organizations > 0 || props.stats.people > 0 || props.stats.payrollRecords > 0;
</script>

<template>
    <Head title="Informação pública sem burocratês" />
    <div class="min-h-screen bg-stone-50 text-slate-950">
        <header class="border-b border-slate-200 bg-white"><div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-5 sm:px-8">
            <Link :href="route('home')" class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-full bg-teal-700 text-white" aria-hidden="true"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="none"><path d="M3 12c2.4-3.5 5.4-5.3 9-5.3s6.6 1.8 9 5.3c-2.4 3.5-5.4 5.3-9 5.3S5.4 15.5 3 12Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="2.7" fill="currentColor"/></svg></span><span class="leading-none"><strong class="block text-xl tracking-tight">Acorda</strong><span class="text-sm font-semibold text-teal-700">Alcobaça</span></span></Link>
            <Link v-if="canLogin" :href="route('login')" class="text-sm font-semibold text-slate-600 hover:text-teal-800">Área administrativa</Link>
        </div></header>

        <main>
            <section class="border-b border-slate-200 bg-slate-950 text-white"><div class="mx-auto max-w-6xl px-5 py-16 sm:px-8 sm:py-24">
                <p class="mb-5 text-sm font-bold uppercase tracking-[0.18em] text-amber-300">Plataforma independente de transparência pública</p>
                <h1 class="max-w-4xl text-4xl font-black leading-tight tracking-tight sm:text-6xl">Informação pública de Alcobaça, com fonte e contexto.</h1>
                <p class="mt-7 max-w-3xl text-lg leading-8 text-slate-300">Dados oficiais organizados de forma simples, rastreável e neutra. Cada conjunto informa sua origem e condição de coleta.</p>
                <div v-if="hasPublicData" class="mt-10 flex flex-wrap gap-3"><Link :href="route('organizations.index')" class="rounded bg-amber-400 px-5 py-3 font-bold text-slate-950 hover:bg-amber-300">Explorar órgãos públicos</Link><Link :href="route('sources.index')" class="rounded border border-white/30 px-5 py-3 font-bold text-white hover:bg-white/10">Ver fontes e saúde</Link></div>
                <div v-else class="mt-10 max-w-3xl border-l-4 border-amber-400 bg-white/10 px-6 py-5" role="status"><p class="font-bold">Esta instalação ainda não coletou dados.</p><p class="mt-1 text-sm leading-6 text-slate-300">Execute as migrations e os coletores documentados antes de publicar informações.</p></div>
            </div></section>

            <section v-if="hasPublicData" class="mx-auto max-w-6xl px-5 py-14 sm:px-8">
                <div class="mb-8 max-w-2xl"><p class="text-sm font-black uppercase tracking-widest text-teal-700">Base disponível</p><h2 class="mt-3 text-3xl font-black">O que já foi coletado</h2><p class="mt-3 leading-7 text-slate-600">Contagens calculadas diretamente do banco desta instalação.</p></div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <article class="rounded-xl border border-slate-200 bg-white p-5"><strong class="text-3xl">{{ number.format(stats.organizations) }}</strong><p class="mt-2 text-sm text-slate-600">órgãos públicos atuais</p></article>
                    <article class="rounded-xl border border-slate-200 bg-white p-5"><strong class="text-3xl">{{ number.format(stats.activeEmployments) }}</strong><p class="mt-2 text-sm text-slate-600">vínculos funcionais atuais</p></article>
                    <article class="rounded-xl border border-slate-200 bg-white p-5"><strong class="text-3xl">{{ number.format(stats.positions) }}</strong><p class="mt-2 text-sm text-slate-600">cargos e funções</p></article>
                    <article class="rounded-xl border border-slate-200 bg-white p-5"><strong class="text-3xl">{{ number.format(stats.payrollRecords) }}</strong><p class="mt-2 text-sm text-slate-600">pagamentos em {{ stats.payrollReference ?? 'competência não informada' }}</p></article>
                </div>
                <div v-if="stats.payrollRecords > 0" class="mt-4 rounded-xl bg-teal-900 p-6 text-white"><p class="text-sm font-bold uppercase tracking-widest text-teal-200">Total líquido da competência {{ stats.payrollReference }}</p><strong class="mt-2 block text-3xl">{{ money.format(stats.netPayrollCents / 100) }}</strong><p class="mt-2 text-sm text-teal-100">Valor agregado da versão mais recente. Perfis individuais ainda serão publicados na próxima etapa.</p></div>
                <p class="mt-6 text-sm text-slate-600">Fontes operacionais: {{ stats.operationalSources }}. Fontes parciais: {{ stats.partialSources }}. Uma fonte parcial não comprova ausência do fato.</p>
            </section>

            <section class="border-t border-slate-200 bg-white"><div class="mx-auto grid max-w-6xl gap-10 px-5 py-14 sm:px-8 md:grid-cols-3">
                <article><p class="text-sm font-black uppercase tracking-widest text-teal-700">Transparência</p><h2 class="mt-3 text-2xl font-bold">Origem identificável</h2><p class="mt-3 leading-7 text-slate-600">Cada fato preserva fonte, endereço original, coleta e revisão.</p></article>
                <article><p class="text-sm font-black uppercase tracking-widest text-teal-700">Neutralidade</p><h2 class="mt-3 text-2xl font-bold">Dados, não julgamentos</h2><p class="mt-3 leading-7 text-slate-600">Sem acusações, rankings políticos ou conclusões automáticas.</p></article>
                <article><p class="text-sm font-black uppercase tracking-widest text-teal-700">Próxima etapa</p><h2 class="mt-3 text-2xl font-bold">Busca e perfis</h2><p class="mt-3 leading-7 text-slate-600">Pessoas e remunerações já estão estruturadas; os perfis públicos ainda passam por implementação e validação.</p></article>
            </div></section>
        </main>
        <footer class="border-t border-slate-200 bg-white"><div class="mx-auto flex max-w-6xl flex-col gap-2 px-5 py-7 text-sm text-slate-600 sm:px-8"><strong class="text-slate-900">Acorda Alcobaça</strong><span>Projeto independente. Não é um portal oficial da Prefeitura.</span><div class="mt-2 flex gap-5"><Link :href="route('sources.index')" class="font-semibold text-teal-700 underline">Fontes monitoradas</Link><Link :href="route('organizations.index')" class="font-semibold text-teal-700 underline">Estrutura organizacional</Link></div></div></footer>
    </div>
</template>
