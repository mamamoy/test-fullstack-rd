<script setup>
import AdminLayouts from './Layouts/AdminLayouts.vue'
import ProductList from './Components/ProductList.vue'

const props = defineProps({
    products: Array,
    product_code: String,
})
</script>

<template>
    <AdminLayouts>
        <ProductList :products="props.products" :code="props.product_code"></ProductList>
    </AdminLayouts>
</template>