<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref, onMounted } from 'vue';

const props = defineProps({
    products: Array,
    transactions: Array,
})

const isAdd = ref(false)
const dialogVisible = ref(false)
const editMode = ref(false)

const user_id = usePage().props.auth.user.id

// transaction data
const id = ref('')
const product_id = ref('')
const qty = ref('')
const storedProduct = ref([])
// end

const searchTable = ref('')

// Search data by input search
const filteredTable = computed(() => {
    if (!searchTable.value) {
        return props.transactions;
    } else {
        const query = searchTable.value.toLowerCase();
        return props.transactions.filter(data => data.transactions_id.toLowerCase().includes(query));
    }
})
// end

// Pagination for table
const pagination = reactive({
    currentPage: 1,
    perPage: 10,
    get totalPages() {
        return Math.ceil(filteredTable.value.length / this.perPage);
    }
})

// Data show by paginate
const paginatedTable = computed(() => {
    const startIndex = (pagination.currentPage - 1) * pagination.perPage
    const endIndex = startIndex + pagination.perPage
    return filteredTable.value.slice(startIndex, endIndex)
})
// end



// open add modal
const openAddModal = () => {
    isAdd.value = true
    dialogVisible.value = true
    editMode.value = false
}
// end

// reset form data
const resetFormData = () => {
    id.value = ''
    product_id.value = ''
    qty.value = ''
}


// Add product to storedProduct
const addProduct = () => {
    const product = {
        product_id: product_id.value,
        qty: qty.value,
    }
    storedProduct.value.push(product)
    saveToStoredProduct()
    dialogVisible.value = false
    resetFormData()
}

const saveToStoredProduct = () => {
    localStorage.setItem('storedProduct', JSON.stringify(storedProduct.value))
}

onMounted(() => {
    const savedProduct = localStorage.getItem('storedProduct')
    if (savedProduct) {
        storedProduct.value = JSON.parse(savedProduct)
    }
})



// End


// handle close dialog modal
const handleClose = (done) => {
    resetFormData()
    done()
}
// end


// open edit modal
const openEditModal = (product) => {
    editMode.value = true
    isAdd.value = false
    dialogVisible.value = true

    // update data
    id.value = product.id
    product_id.value = product.product_id
    qty.value = product.qty
}
// end

// Update product information
const updateProduct = async () => {
    const formData = new FormData
    formData.append('product_name', product_name.value)
    formData.append('minimum_qty', minimum_qty.value)
    formData.append('_method', 'PUT')

    try {
        await router.post('product/update/' + slug.value, formData, {
            onSuccess: (page) => {
                dialogVisible.value = false
                resetFormData()
                Swal.fire({
                    toast: true,
                    icon: 'success',
                    position: 'top-end',
                    showConfirmButton: false,
                    title: page.props.flash.success
                })
            }
        })
    } catch (errors) {
        console.log(errors)
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Product has not been updated, try again!'
        })
        dialogVisible.value = false
        resetFormData()
    }
}
// end

// delete selected product with customers relation with
const deleteProduct = (product) => {
    Swal.fire({
        title: 'Are you sure ?',
        text: 'Product will be deleted permanently',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            try {
                router.delete('product/destroy/' + product.slug, {
                    onSuccess: (page) => {
                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            position: 'top-end',
                            showConfirmButton: false,
                            title: page.props.flash.success
                        })
                    }
                })
            } catch (error) {
                console.log(error)
            }
        }
    })
}
// end
</script>
<template>
    <!-- Start block -->
    <section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5 antialiased">

        <div class="mx-auto max-w-screen-xl px-4 lg:px-12">
            <!-- Start coding here -->
            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                <div class="ms-4 mt-2 mb-2 items-start">
                    <h2 class="text-gray-700 text-lg">Transaction List</h2>
                    <span class="text-sm text-gray-500">This table showing a list of transaction has been made.</span>
                </div>
                <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                    <div class="w-full md:w-1/2">
                        <form class="flex items-center">
                            <label for="simple-search" class="sr-only">Search</label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                        fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input type="text" id="simple-search" v-model="searchTable"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Search" required="">
                            </div>
                        </form>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead
                            class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center">
                            <tr>
                                <th scope="col" class="px-4 py-4">Transaction ID</th>
                                <th scope="col" class="px-4 py-3">Date Created</th>
                                <th scope="col" class="px-4 py-3">Transaction Type</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(transaction, index) in paginatedTable" :key="transaction.id"
                                class="border-b dark:border-gray-700 text-center">
                                
                                <th scope="row"
                                    class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{
                                        transaction.transaction_id }}</th>
                                <td class="px-4 py-3">{{ new Date(transaction.created_at).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }) }}</td>
                                <td class="px-4 py-3">{{ transaction.type.transaction_name }}</td>
                                <td v-if="transaction.is_approved" class="px-4 py-3"><span class="bg-green-100 text-green-800 text-md font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">{{ transaction.is_approved }}</span></td>
                                <td v-else class="px-4 py-3">
                                    <span class="bg-yellow-100 text-yellow-800 text-md font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-yellow-900 dark:text-yellow-300">{{ transaction.is_approved }}</span>
                                </td>
                                
                            </tr>
                        </tbody>
                    </table>
                </div>
                <nav class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0 p-4"
                    aria-label="Table navigation">
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">

                    </span>
                    <ul class="inline-flex items-stretch -space-x-px">
                        <li>
                            <button :disabled="pagination.currentPage <= 1" @click="pagination.currentPage--"
                                class="flex items-center justify-center h-full py-1.5 px-3 ml-0 text-gray-500 bg-white rounded-l-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                <span class="sr-only">Previous</span>
                                <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </li>
                        <li>
                            <button disabled
                                class=" flex items-center justify-center text-sm py-2 px-3 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Page
                                {{ pagination.currentPage }} of {{ pagination.totalPages }}</button>
                        </li>
                        <li>
                            <button :disabled="pagination.currentPage >= pagination.totalPages"
                                @click="pagination.currentPage++"
                                class="flex items-center justify-center h-full py-1.5 px-3 leading-tight text-gray-500 bg-white rounded-r-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                <span class="sr-only">Next</span>
                                <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </section>
    <!-- End block -->
</template>