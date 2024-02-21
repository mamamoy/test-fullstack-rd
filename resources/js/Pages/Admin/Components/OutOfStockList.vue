<script setup>

const props = defineProps({
    neededStocks: Array,
})

const oos = []
</script>

<template>
    <div
        class="max-w-sm p-6 me-4 mt-4 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">

        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Product Needed</h5>
        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">The following is a list of products that have low stock
            with the percentage of stock against the minimum stock set.</p>
            <hr>
        <div class="mt-4">
            <template v-if="props.neededStocks && props.neededStocks.length">
                <div v-for="item in props.neededStocks" :key="item.product_name" class="grid grid-cols-4 items-center">
                    <div class="col ">
                        <div class="truncate">
                            <span class="text-sm text-gray-700">{{ item.product_name }}</span>
                        </div>
                    </div>
                    <div class="col-span-3 ms-2">
    
                        <div class="w-full bg-gray-200 rounded-full dark:bg-gray-700">
                            <div class="bg-yellow-600 text-xs font-medium text-yellow-100 text-center p-0.1 leading-none rounded-full"
                                :style="{ width: item.percentage + '%' }"> {{ item.percentage }}%</div>
                        </div>
    
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="text-gray-700 text-md text-center">All Stock has been filled</div>
            </template>
        </div>
    </div>
</template>