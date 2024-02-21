<script setup>
import StaffLayout from './Layouts/StaffLayout.vue';
import ProductList from './Components/ProductList.vue';

const props = defineProps({
    products: Array,
})
</script>

<template>
    <StaffLayout>
        <ProductList :products="props.products"></ProductList>
    </StaffLayout>
</template>