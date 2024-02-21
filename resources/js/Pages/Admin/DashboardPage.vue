<script setup>
import AdminLayouts from './Layouts/AdminLayouts.vue'
import TransactionList from './Components/TransactionList.vue';
import OutOfStockList from './Components/OutOfStockList.vue';

const props = defineProps({
    transactions: Array,
    neededStocks: Array,
    products: Array,
    send: Array,
    receive: Array,
})
</script>

<template>
    <AdminLayouts>
        <div class="mx-auto max-w-screen-xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-4 px-5">
                <div
                    class="max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 text-center">
                    <div class="mb-3">
                        <span class="text-6xl font-bold text-gray-500">{{ props.products.length ? props.products.length : 0 }}</span>
                    </div>
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-700 dark:text-white">Products</h5>
                </div>
                <div
                    class="max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 text-center">
                    <div class="mb-3">
                        <span class="text-6xl font-bold text-gray-500">{{ props.transactions.length ? props.transactions.length : 0 }}</span>
                    </div>
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-700 dark:text-white">Transactions</h5>
                </div>
                <div
                    class="max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 text-center">
                    <div class="mb-3">
                        <span class="text-6xl font-bold text-gray-500">{{ props.send.length ? props.send.length : 0 }}</span>
                    </div>
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-700 dark:text-white">Send</h5>
                </div>
                <div
                    class="max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 text-center">
                    <div class="mb-3">
                        <span class="text-6xl font-bold text-gray-500">{{ props.receive.length ? props.receive.length : 0 }}</span>
                    </div>
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-700 dark:text-white">Received</h5>
                </div>
            </div>
        </div>
        <div class="grid lg:grid-cols-4 md:grid-cols-1">
            <div class="col-span-3">
                <TransactionList :transactions="props.transactions"></TransactionList>
            </div>
            <div class="col">
                <OutOfStockList :neededStocks="props.neededStocks"></OutOfStockList>
            </div>
        </div>
    </AdminLayouts>
</template>