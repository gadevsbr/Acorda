<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

type Person = { id: number; name: string; external_id: string; employments: Array<{ employment_regime: string; position: { name: string } | null }> };
type Candidate = { id: number; status: string; reason: string; evidence: Record<string, string>; review_notes: string | null; left_person: Person; right_person: Person };
type Page<T> = { data: T[]; links: Array<{ url: string | null; label: string; active: boolean }>; total: number };
defineProps<{ status: string; candidates: Page<Candidate> }>();

const decide = (candidate: Candidate, status: 'confirmed' | 'rejected') => {
    const notes = window.prompt('Registre a evidência ou justificativa da decisão:');
    if (!notes) return;
    useForm({ status, review_notes: notes }).patch(route('identity-candidates.update', candidate.id), { preserveScroll: true });
};
const filter = (status: string) => router.get(route('identity-candidates.index'), { status }, { preserveState: true });
const pageLabel = (label: string) => label.replace('&laquo;', '‹').replace('&raquo;', '›');
</script>

<template>
    <Head title="Revisão de identidades" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-800">Revisão de identidades</h2></template>
        <div class="mx-auto max-w-7xl space-y-6 p-6">
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                Nome igual é apenas um candidato. Confirmar registra uma decisão revisada; os registros de origem não são fundidos.
            </div>
            <div class="flex gap-2">
                <button v-for="item in ['pending','confirmed','rejected']" :key="item" type="button" @click="filter(item)" class="rounded px-3 py-2 text-sm" :class="status === item ? 'bg-slate-900 text-white' : 'bg-white text-slate-700'">{{ item }}</button>
            </div>
            <div class="text-sm text-slate-600">{{ candidates.total }} candidato(s)</div>
            <article v-for="candidate in candidates.data" :key="candidate.id" class="rounded-lg bg-white p-5 shadow-sm">
                <div class="grid gap-4 md:grid-cols-2">
                    <div v-for="person in [candidate.left_person, candidate.right_person]" :key="person.id">
                        <h3 class="font-semibold text-slate-900">{{ person.name }}</h3>
                        <p class="text-sm text-slate-600">Matrícula {{ person.external_id }}</p>
                        <p class="text-sm text-slate-600">{{ person.employments[0]?.position?.name ?? 'Cargo não informado' }} · {{ person.employments[0]?.employment_regime ?? 'Regime não informado' }}</p>
                    </div>
                </div>
                <div v-if="candidate.status === 'pending'" class="mt-4 flex gap-2">
                    <button type="button" @click="decide(candidate, 'confirmed')" class="rounded bg-emerald-700 px-3 py-2 text-sm text-white">Confirmar correspondência</button>
                    <button type="button" @click="decide(candidate, 'rejected')" class="rounded bg-rose-700 px-3 py-2 text-sm text-white">Rejeitar</button>
                </div>
                <p v-else class="mt-4 text-sm text-slate-600">Justificativa: {{ candidate.review_notes }}</p>
            </article>
            <nav class="flex flex-wrap gap-2">
                <Link v-for="link in candidates.links" :key="link.label" :href="link.url ?? '#'" class="rounded border px-3 py-2 text-sm" :class="{ 'bg-slate-900 text-white': link.active, 'pointer-events-none opacity-40': !link.url }">{{ pageLabel(link.label) }}</Link>
            </nav>
        </div>
    </AuthenticatedLayout>
</template>
