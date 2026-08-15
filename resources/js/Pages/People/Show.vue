<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PlainLanguageLegend from '@/Components/PlainLanguageLegend.vue';

interface Provenance { sourceName: string; officialUrl: string; fetchedAt: string | null; validationStatus: string }
interface Employment { registration: string; position: string | null; organization: { name: string; slug: string } | null; costCenter: string | null; regime: string; monthlyWorkload: string | null; admissionDate: string | null; isCurrent: boolean; provenance: Provenance }
interface Payroll { id: number; registration: string; reference: string; calculationType: string; grossCents: number; deductionsCents: number; netCents: number; positionName: string | null; workplace: string | null; isLatest: boolean; supersedesId: number | null; provenance: Provenance }
interface Person { slug: string; name: string; registration: string; position: string | null; regime: string | null; isCurrent: boolean; employments: Employment[]; payroll: Payroll[]; provenance: Provenance }
defineProps<{ person: Person }>();
const money = (cents: number): string => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(cents / 100);
const date = (value: string | null): string => value ? new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeZone: 'America/Bahia' }).format(new Date(`${value}T12:00:00`)) : 'Não informada';
const dateTime = (value: string | null): string => value ? new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short', timeZone: 'America/Bahia' }).format(new Date(value)) : 'Não informada';
</script>

<template>
    <Head :title="person.name" />
    <div class="min-h-screen bg-stone-50 text-slate-950">
        <header class="border-b border-slate-200 bg-white"><div class="mx-auto flex max-w-5xl items-center justify-between px-5 py-5 sm:px-8"><Link :href="route('home')" class="text-xl font-black">Acorda <span class="text-teal-700">Alcobaça</span></Link><Link :href="route('people.index')" class="text-sm font-bold text-slate-600">Nova busca</Link></div></header>
        <main class="mx-auto max-w-5xl px-5 py-12 sm:px-8 sm:py-16">
            <div class="border-l-4 border-amber-400 bg-amber-50 p-4 text-sm leading-6"><strong>Identidade funcional da fonte.</strong> Este perfil representa uma matrícula publicada e não confirma identidade civil nem reúne automaticamente homônimos.</div>
            <p class="mt-9 text-sm font-black uppercase tracking-widest text-teal-700">Perfil funcional</p><h1 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">{{ person.name }}</h1><p class="mt-3 text-lg text-slate-600">Matrícula {{ person.registration }}</p>

            <section class="mt-10"><h2 class="text-2xl font-black">Vínculos</h2><div class="mt-4 grid gap-4">
                <article v-for="employment in person.employments" :key="employment.registration" class="border border-slate-200 bg-white p-6">
                    <div class="flex flex-wrap items-center gap-3"><h3 class="text-xl font-bold">{{ employment.position ?? 'Cargo não informado' }}</h3><span class="rounded-full px-3 py-1 text-xs font-black" :class="employment.isCurrent ? 'bg-teal-100 text-teal-900' : 'bg-slate-200 text-slate-700'">{{ employment.isCurrent ? 'Atual na última coleta' : 'Não observado na última coleta' }}</span></div>
                    <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-3"><div><dt class="font-bold text-slate-500">Regime</dt><dd>{{ employment.regime }}</dd></div><div><dt class="font-bold text-slate-500">Admissão</dt><dd>{{ date(employment.admissionDate) }}</dd></div><div><dt class="font-bold text-slate-500">Carga mensal</dt><dd>{{ employment.monthlyWorkload ?? 'Não informada' }}</dd></div><div><dt class="font-bold text-slate-500">Lotação</dt><dd>{{ employment.costCenter ?? 'Não informada' }}</dd></div><div v-if="employment.organization"><dt class="font-bold text-slate-500">Órgão</dt><dd><Link :href="route('organizations.show', employment.organization.slug)" class="text-teal-800 underline">{{ employment.organization.name }}</Link></dd></div></dl>
                    <p class="mt-5 border-t border-slate-100 pt-4 text-xs text-slate-500">{{ employment.provenance.sourceName }} · coletado em {{ dateTime(employment.provenance.fetchedAt) }} · <a :href="employment.provenance.officialUrl" target="_blank" rel="noopener noreferrer" class="font-bold text-teal-800 underline">abrir fonte oficial</a></p>
                </article>
            </div></section>

            <section class="mt-12"><h2 class="text-2xl font-black">Pagamentos</h2><p class="mt-2 text-sm leading-6 text-slate-600">Os valores são organizados pelo mês a que pertencem. Correções antigas continuam visíveis no histórico.</p><PlainLanguageLegend type="people" class="mt-5" />
                <div v-if="person.payroll.length" class="mt-5 overflow-x-auto border border-slate-200 bg-white"><table class="w-full min-w-[760px] text-left text-sm"><thead class="bg-slate-950 text-white"><tr><th class="p-4">Competência</th><th class="p-4">Tipo</th><th class="p-4 text-right">Bruto</th><th class="p-4 text-right">Descontos</th><th class="p-4 text-right">Líquido</th><th class="p-4">Versão</th><th class="p-4">Fonte</th></tr></thead><tbody><tr v-for="record in person.payroll" :key="record.id" class="border-t border-slate-200"><td class="p-4 font-bold">{{ record.reference }}</td><td class="p-4">{{ record.calculationType }}</td><td class="p-4 text-right">{{ money(record.grossCents) }}</td><td class="p-4 text-right">{{ money(record.deductionsCents) }}</td><td class="p-4 text-right font-bold">{{ money(record.netCents) }}</td><td class="p-4"><span :class="record.isLatest ? 'text-teal-800' : 'text-slate-500'" class="font-bold">{{ record.isLatest ? (record.supersedesId ? 'Atual corrigida' : 'Atual') : 'Substituída' }}</span></td><td class="p-4"><a :href="record.provenance.officialUrl" target="_blank" rel="noopener noreferrer" class="font-bold text-teal-800 underline">Oficial</a><span class="mt-1 block text-xs text-slate-500">{{ dateTime(record.provenance.fetchedAt) }}</span></td></tr></tbody></table></div>
                <p v-else class="mt-5 border-l-4 border-amber-400 bg-white p-5">Nenhum pagamento foi associado com segurança a esta matrícula. Isso não comprova ausência de pagamento na fonte.</p>
            </section>
        </main>
    </div>
</template>
