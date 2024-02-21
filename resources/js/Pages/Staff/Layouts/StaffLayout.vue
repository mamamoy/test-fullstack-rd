<script setup>
import { onMounted } from 'vue'
import { initFlowbite } from 'flowbite'
import Header from './Header.vue';
import Sidebar from './Sidebar.vue'

onMounted(() => {
    initFlowbite();
})
</script>

<template>
    <div class="antialiased bg-gray-50 dark:bg-gray-900">
        <!-- Header -->
        <Header></Header>
        <!-- End header -->

        <!-- Sidebar -->
        <Sidebar></Sidebar>
        <!-- End sidebar -->

        <!-- Content -->
        <main class="p-4 md:ml-64 h-auto pt-20">
            <slot />
        </main>
        <!-- End content -->
    </div>
</template>