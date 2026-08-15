<script setup lang="ts">
import PlainLanguageLegend from '@/Components/PlainLanguageLegend.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
interface Item { slug:string; title:string; subtitle:string|null; description:string|null; valueCents:number|null; date:string|null }
const props=defineProps<{kind:'contracts'|'procurements'|'suppliers';title:string;query:string;items:Item[]}>();
const q=ref(props.query); const indexRoute=()=>route(`${props.kind}.index`); const showRoute=(slug:string)=>route(`${props.kind}.show`,slug);
const submit=()=>router.get(indexRoute(),{q:q.value},{preserveState:true,replace:true});
const money=(v:number)=>new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(v/100);
const formatDate=(value:string)=>new Intl.DateTimeFormat('pt-BR',{dateStyle:'short',timeZone:'America/Bahia'}).format(new Date(`${value}T12:00:00`));
</script>
<template><Head :title="title"/><div class="min-h-screen bg-stone-50 text-slate-950">
<header class="border-b bg-white"><div class="mx-auto flex max-w-5xl flex-wrap justify-between gap-3 px-5 py-5"><Link :href="route('home')" class="text-xl font-black">Acorda <span class="text-teal-700">Alcobaça</span></Link><nav class="flex flex-wrap gap-4 text-sm font-bold"><Link :href="route('contracts.index')">Contratos</Link><Link :href="route('procurements.index')">Licitações</Link><Link :href="route('suppliers.index')">Fornecedores</Link></nav></div></header>
<main class="mx-auto max-w-5xl px-5 py-12"><p class="text-sm font-black uppercase tracking-widest text-teal-700">Dinheiro público</p><h1 class="mt-3 text-4xl font-black">{{ title }}</h1><p class="mt-4 max-w-3xl text-slate-600">Consulte informações publicadas pela Prefeitura. Se a busca não encontrar algo, isso não prova que o registro não existe.</p><PlainLanguageLegend type="procurement" class="mt-6" />
<form class="mt-7 flex gap-3" role="search" @submit.prevent="submit"><label for="procurement-search" class="sr-only">Buscar em {{ title }}</label><input id="procurement-search" v-model="q" type="search" :placeholder="kind==='suppliers'?'Digite o nome do fornecedor':'Digite número, objeto ou participante'" class="min-w-0 flex-1 rounded border border-slate-300 bg-white p-3"/><button class="rounded bg-teal-700 px-5 font-bold text-white">Buscar</button></form>
<p v-if="kind==='suppliers' && query.length<2" class="mt-8 border-l-4 border-amber-400 bg-white p-4">Digite pelo menos duas letras do nome.</p><p v-else-if="items.length===0" class="mt-8 border-l-4 border-amber-400 bg-white p-4">Nenhum resultado encontrado nesta base. Tente outras palavras.</p>
<section v-else class="mt-8"><h2 class="mb-4 text-lg font-bold">{{ items.length }} resultado(s) exibido(s)</h2><ul class="grid gap-4"><li v-for="item in items" :key="item.slug" class="rounded-lg border border-slate-200 bg-white p-5"><Link :href="showRoute(item.slug)" class="text-xl font-black text-teal-800 underline underline-offset-4">{{ item.title }}</Link><p v-if="item.subtitle" class="mt-2 font-semibold">{{ item.subtitle }}</p><p v-if="item.description" class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ item.description }}</p><div class="mt-3 flex flex-wrap gap-5 text-sm"><strong v-if="item.valueCents!==null">{{ money(item.valueCents) }}</strong><span v-if="item.date">Publicado em {{ formatDate(item.date) }}</span></div></li></ul></section>
</main></div></template>
