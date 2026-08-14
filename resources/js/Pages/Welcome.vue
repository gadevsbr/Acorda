<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canLogin: boolean;
}>();
</script>

<template>
    <Head title="Informação pública sem burocratês" />

    <div class="min-h-screen bg-stone-50 text-slate-950">
        <header class="border-b border-slate-200 bg-white">
            <div
                class="mx-auto flex max-w-6xl items-center justify-between px-5 py-5 sm:px-8"
            >
                <a href="/" class="group flex items-center gap-3">
                    <span
                        class="grid h-10 w-10 place-items-center rounded-full bg-teal-700 text-white"
                        aria-hidden="true"
                    >
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none">
                            <path
                                d="M3 12c2.4-3.5 5.4-5.3 9-5.3s6.6 1.8 9 5.3c-2.4 3.5-5.4 5.3-9 5.3S5.4 15.5 3 12Z"
                                stroke="currentColor"
                                stroke-width="1.8"
                            />
                            <circle cx="12" cy="12" r="2.7" fill="currentColor" />
                        </svg>
                    </span>
                    <span class="leading-none">
                        <strong class="block text-xl tracking-tight">Acorda</strong>
                        <span class="text-sm font-semibold text-teal-700">Alcobaça</span>
                    </span>
                </a>

                <Link
                    v-if="canLogin"
                    :href="route('login')"
                    class="text-sm font-semibold text-slate-600 hover:text-teal-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-teal-700"
                >
                    Área administrativa
                </Link>
            </div>
        </header>

        <main>
            <section class="border-b border-slate-200 bg-slate-950 text-white">
                <div class="mx-auto max-w-6xl px-5 py-16 sm:px-8 sm:py-24">
                    <p
                        class="mb-5 text-sm font-bold uppercase tracking-[0.18em] text-amber-300"
                    >
                        Plataforma independente de transparência pública
                    </p>
                    <h1
                        class="max-w-4xl text-4xl font-black leading-tight tracking-tight sm:text-6xl"
                    >
                        Informação pública de Alcobaça, sem burocratês.
                    </h1>
                    <p class="mt-7 max-w-2xl text-lg leading-8 text-slate-300">
                        Estamos preparando uma forma simples e rastreável de consultar
                        pessoas, órgãos e gastos públicos, sempre com acesso à fonte oficial.
                    </p>

                    <div
                        class="mt-10 max-w-3xl border-l-4 border-amber-400 bg-white/10 px-6 py-5"
                        role="status"
                    >
                        <p class="font-bold text-white">Base técnica em implantação</p>
                        <p class="mt-1 text-sm leading-6 text-slate-300">
                            Nenhum dado público foi publicado nesta etapa. A busca será
                            liberada somente após a validação do primeiro pipeline com dados
                            reais.
                        </p>
                    </div>
                </div>
            </section>

            <section class="mx-auto grid max-w-6xl gap-10 px-5 py-14 sm:px-8 md:grid-cols-3">
                <article>
                    <p class="text-sm font-black uppercase tracking-widest text-teal-700">
                        Transparência
                    </p>
                    <h2 class="mt-3 text-2xl font-bold">Origem identificável</h2>
                    <p class="mt-3 leading-7 text-slate-600">
                        Cada fato publicado deverá informar fonte, endereço original e datas
                        de coleta e atualização.
                    </p>
                </article>
                <article>
                    <p class="text-sm font-black uppercase tracking-widest text-teal-700">
                        Neutralidade
                    </p>
                    <h2 class="mt-3 text-2xl font-bold">Dados, não julgamentos</h2>
                    <p class="mt-3 leading-7 text-slate-600">
                        A plataforma organiza documentos públicos sem criar acusações,
                        rankings políticos ou conclusões automáticas.
                    </p>
                </article>
                <article>
                    <p class="text-sm font-black uppercase tracking-widest text-teal-700">
                        Simplicidade
                    </p>
                    <h2 class="mt-3 text-2xl font-bold">Feito para o cidadão</h2>
                    <p class="mt-3 leading-7 text-slate-600">
                        Informação clara, legível no celular e acompanhada do contexto
                        necessário para ser compreendida.
                    </p>
                </article>
            </section>
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div
                class="mx-auto flex max-w-6xl flex-col gap-2 px-5 py-7 text-sm text-slate-600 sm:px-8"
            >
                <strong class="text-slate-900">Acorda Alcobaça</strong>
                <span>Projeto independente. Não é um portal oficial da Prefeitura.</span>
                <Link
                    :href="route('sources.index')"
                    class="mt-2 w-fit font-semibold text-teal-700 underline decoration-teal-300 underline-offset-4 hover:text-teal-900"
                >
                    Consulte a saúde das fontes monitoradas
                </Link>
            </div>
        </footer>
    </div>
</template>
