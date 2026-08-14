<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Result { slug: string; name: string; registration: string; position: string | null; regime: string | null; isCurrent: boolean }
const props = defineProps<{ query: string; minimumQueryLength: number; results: Result[] }>();
const search = ref(props.query);
const submit = (): void => router.get(route('people.index'), { q: search.value }, { preserveState: true, replace: true });
</script>

<template>
    <Head title="Buscar pessoas" />
    <div class="min-h-screen bg-stone-50 text-slate-950">
        <header class="border-b border-slate-200 bg-white"><div class="mx-auto flex max-w-5xl items-center justify-between px-5 py-5 sm:px-8"><Link :href="route('home')" class="text-xl font-black">Acorda <span class="text-teal-700">Alcobaça</span></Link><Link :href="route('organizations.index')" class="text-sm font-bold text-slate-600">Órgãos públicos</Link></div></header>
        <main class="mx-auto max-w-5xl px-5 py-12 sm:px-8 sm:py-16">
            <p class="text-sm font-black uppercase tracking-widest text-teal-700">Servidores públicos</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight">Buscar pessoa</h1>
            <p class="mt-4 max-w-3xl leading-7 text-slate-600">Consulte nomes ou matrículas publicados nas fontes oficiais. Pessoas com o mesmo nome permanecem em registros separados por matrícula.</p>
            <form class="mt-8 flex max-w-3xl gap-3" role="search" @submit.prevent="submit">
                <label for="people-search" class="sr-only">Nome ou matrícula</label>
                <input id="people-search" v-model="search" type="search" :minlength="minimumQueryLength" required placeholder="Digite ao menos 2 caracteres" class="min-w-0 flex-1 rounded border border-slate-300 bg-white px-4 py-3 focus:border-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-200" />
                <button class="rounded bg-teal-700 px-5 py-3 font-bold text-white hover:bg-teal-800">Buscar</button>
            </form>

            <p v-if="query && query.length < minimumQueryLength" class="mt-8 rounded border border-amber-300 bg-amber-50 p-4" role="alert">Digite ao menos {{ minimumQueryLength }} caracteres.</p>
            <section v-else-if="query" class="mt-10" aria-live="polite">
                <h2 class="text-xl font-bold">{{ results.length }} resultado(s)</h2>
                <ul v-if="results.length" class="mt-4 grid gap-4">
                    <li v-for="person in results" :key="person.slug" class="border border-slate-200 bg-white p-5">
                        <Link :href="route('people.show', person.slug)" class="text-xl font-black text-teal-800 underline decoration-2 underline-offset-4">{{ person.name }}</Link>
                        <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-3"><div><dt class="font-bold text-slate-500">Matrícula</dt><dd>{{ person.registration }}</dd></div><div><dt class="font-bold text-slate-500">Cargo/função</dt><dd>{{ person.position ?? 'Não informado' }}</dd></div><div><dt class="font-bold text-slate-500">Situação na última coleta</dt><dd>{{ person.isCurrent ? 'Vínculo atual' : 'Vínculo não observado' }}</dd></div></dl>
                    </li>
                </ul>
                <p v-else class="mt-4 border-l-4 border-amber-400 bg-white p-5">Nenhum registro correspondente foi localizado na base coletada. Isso não comprova ausência na fonte oficial.</p>
            </section>
        </main>
    </div>
</template>
