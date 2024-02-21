<script setup>
import StaffLayout from './Layouts/StaffLayout.vue';
import TransactionList from './Components/TransactionList.vue';
import CreateTransaction from './Components/CreateTransaction.vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    products: Array,
    transactions: Array,
    types: Array,
})

const user_id = usePage().props.auth.user.id
const userTransactions = props.transactions.filter(data => data=>user_id === user_id)
</script>

<template>
    <StaffLayout>
        <CreateTransaction :products="props.products" :types="props.types"></CreateTransaction>
        <TransactionList :products="props.products" :transactions="userTransactions"></TransactionList>
    </StaffLayout>
</template>