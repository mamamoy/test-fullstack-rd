<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref, onMounted, watch } from 'vue';

const props = defineProps({
    products: Array,
    transactions: Array,
    types: Array,
})

const listProduct = ref([])

const fetchListProduct = () => {
    const savedProduct = localStorage.getItem('storedProduct');
    if (savedProduct) {
        storedProduct.value = JSON.parse(savedProduct);
    }

    listProduct.value = props.products.filter(product => {
        return !storedProduct.value.some(item => item.product_id === product.id);
    });
};

onMounted(fetchListProduct);


const isAdd = ref(false)
const dialogVisible = ref(false)
const editMode = ref(false)

// transaction data
const id = ref('')
const product_id = ref('')
const qty = ref('')
const product_name = ref('')
const product_code = ref('')
const transaction_type_id = ref('')
const user_id = usePage().props.auth.user.id
const maxQty = ref('')
const modeSend = ref(false);
const isSelected = ref(false);

const storedProduct = ref([])
// end

const searchTable = ref('')

// Search data by input search
const filteredTable = computed(() => {
    const dataFilteredByUser = storedProduct.value.filter(data => data.user_id === user_id)

    if (dataFilteredByUser.length > 0) {
        if (!searchTable.value) {
            return dataFilteredByUser
        } else {
            const query = searchTable.value.toLowerCase()
            return dataFilteredByUser.filter(data =>
                data.product_name.toLowerCase().includes(query) ||
                data.product_code.toLowerCase().includes(query) ||
                data.qty.toString().includes(query)
            );
        }
    } else {
        return []
    }
});

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
    if (transaction_type_id.value) {
        checkTransactionType()
        isAdd.value = true
        dialogVisible.value = true
        editMode.value = false
    } else {
        dialogVisible.value = false
        Swal.fire({
            toast: true,
            icon: 'error',
            position: 'top-end',
            showConfirmButton: false,
            title: 'Select transaction type first',
        })
    }

}
// end

// reset form data
const resetFormData = () => {
    id.value = ''
    product_id.value = ''
    qty.value = ''
    product_name.value = ''
    product_code.value = ''
}


// Add product to storedProduct
const addProduct = () => {

    const selectedProduct = props.products.find(item => Number(product_id.value) === item.id)
    if (selectedProduct) {
        const product = {
            id: storedProduct.value.length > 0 ? (storedProduct.value.length + 1) : 1,
            user_id: user_id,
            product_id: selectedProduct.id,
            product_name: selectedProduct.product_name,
            product_code: selectedProduct.product_code,
            qty: qty.value,
        }
        storedProduct.value.push(product)
        saveToStoredProduct()
        dialogVisible.value = false
        resetFormData()
    } else {
        console.error('Selected product not found!')
    }
}

const saveToStoredProduct = () => {
    localStorage.setItem('storedProduct', JSON.stringify(storedProduct.value))
}


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
    product_name.value = product.product_name
    product_code.value = product.product_code

}
// end

// // Update product information
const updateProduct = () => {
    const updatedProduct = {
        id: id.value,
        product_id: product_id.value,
        qty: qty.value,
        product_code: product_code.value,
        product_name: product_name.value,
    };
    updateStoredProduct(id.value, updatedProduct);
    dialogVisible.value = false;
    resetFormData();
}

const updateStoredProduct = (productId, updatedProduct) => {
    const index = storedProduct.value.findIndex(item => item.id === productId)
    if (index !== -1) {
        storedProduct.value.splice(index, 1, updatedProduct)
        saveToStoredProduct()
    } else {
        console.error('Product with ID', productId, 'not found in storedProduct.')
    }
}
// // end

const resetList = () => {
    Swal.fire({
        title: 'Are you sure ?',
        text: 'All product will be reset permanently',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            resetStoredProduct()
        }
    })
}

const resetStoredProduct = () => {
    storedProduct.value = []
    localStorage.removeItem('storedProduct')
}

// // delete selected product with customers relation with
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
            const index = storedProduct.value.findIndex(item => item.id === product.id)
            if (index !== -1) {
                storedProduct.value.splice(index, 1);
                saveToStoredProduct();
            }
            Swal.fire("Deleted!", "Your product has been deleted.", "success")
        }
    })
}
// end

const checkTransactionType = () => {
    if (transaction_type_id.value === 1) {
        modeSend.value = false
    } else if (transaction_type_id.value === 2) {
        modeSend.value = true

    }
};

watch(transaction_type_id, () => {
    checkTransactionType();
});

const updateMaxQty = () => {
    const selectedProduct = editMode ?
        props.products.find(product => product.id === product_id.value) :
        listProduct.find(product => product.id === product_id.value);
    maxQty.value = selectedProduct ? selectedProduct.stock.qty : null;
};

updateMaxQty()

const makeTransaction = async () => {
    const formData = new FormData()
    storedProduct.value.forEach((product, index) => {
        formData.append(`products[${index}][product_id]`, product.product_id)
        formData.append(`products[${index}][qty]`, product.qty)
    })
    formData.append('transaction_type_id', transaction_type_id.value)

    try {
        await router.post('transaction/store', formData, {
            onSuccess: page => {
                Swal.fire({
                    toast: true,
                    icon: 'success',
                    position: 'top-end',
                    showConfirmButton: false,
                    title: page.props.flash.success
                });

                // Reset storedProduct and close dialog
                resetStoredProduct();
                dialogVisible.value = false;
            },
            onError: page => {
                console.error('Error occurred during request:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: page.props.flash.error || 'An error occurred while processing your request. Please try again later.'
                });
                // Close dialog
                dialogVisible.value = false;
            }
        });
    } catch (error) {
        console.error('Error caught during request:', error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'An error occurred while processing your request. Please try again later.'
        });
        // Close dialog
        dialogVisible.value = false;
    }
};





</script>
<template>
    <!-- Start block -->
    <section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5 antialiased">

        <div class="mx-auto max-w-screen-xl px-4 lg:px-12">
            <!-- Start coding here -->
            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                <div class="ms-4 mt-2 mb-2 items-start">
                    <h2 class="text-gray-700 text-lg">Transaction Cart List</h2>
                    <span class="text-sm text-gray-500">This table showing a list of products.</span>
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
                    <div
                        class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
                        <button @click="openAddModal" type="button"
                            class="flex items-center justify-center text-white bg-purple-500 hover:bg-purple-600 focus:ring-4 focus:ring-purple-100 font-medium rounded-lg text-sm px-4 py-2 dark:bg-purple-400 dark:hover:bg-purple-500 focus:outline-none dark:focus:ring-purple-800">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>

                            Add New Product
                        </button>

                        <button @click="resetList()" type="button"
                            class="flex items-center justify-center focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900"><svg
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg> Reset Cart</button>
                    </div>

                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead
                            class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center">
                            <tr>
                                <th scope="col" class="px-4 py-4">Product name</th>
                                <th scope="col" class="px-4 py-3">Product Code</th>
                                <th scope="col" class="px-4 py-3">Quantity</th>
                                <th scope="col" class="px-4 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(product, index) in paginatedTable" :key="product.id"
                                class="border-b dark:border-gray-700 text-center">
                                <th scope="row"
                                    class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{
                                        product.product_name }}</th>
                                <td class="px-4 py-3">{{ product.product_code }}</td>
                                <td class="px-4 py-3">{{ product.qty }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <button type="button" @click="openEditModal(product)"
                                                class="text-yellow-400 border border-yellow-400 hover:bg-yellow-400 hover:text-white focus:ring-4 focus:outline-none focus:ring-yellow-100 font-medium rounded-full text-sm p-1 text-center inline-flex items-center dark:border-yellow-200 dark:text-yellow-200 dark:hover:text-white dark:focus:ring-yellow-500 dark:hover:bg-yellow-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>

                                            </button>
                                        </div>
                                        <div>
                                            <button type="button" @click="deleteProduct(product)"
                                                class="text-red-700 border border-red-700 hover:bg-red-700 hover:text-white focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-full text-sm p-1 text-center inline-flex items-center dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:focus:ring-red-800 dark:hover:bg-red-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>


                                            </button>
                                        </div>
                                    </div>
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
                <div
                    class="flex mx-4 my-4 flex-col md:flex-row w-full md:w-auto space-y-2 md:space-y-0 items-stretch md:items-center justify-center md:space-x-2 flex-shrink-0">

                    <div
                        class="flex flex-col md:flex-row w-full md:w-auto space-y-2 md:space-y-0 items-stretch md:items-center justify-center md:space-x-2 flex-shrink-0">
                        <button @click="makeTransaction()"
                            class=" inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-green-400 to-blue-600 group-hover:from-green-400 group-hover:to-blue-600 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-green-200 dark:focus:ring-green-800">
                            <span
                                class=" px-5 py-1.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0">
                                Make a Transaction
                            </span>
                        </button>
                    </div>
                    <div class="min-w-[180px]">
                        <el-select v-model="transaction_type_id" clearable placeholder="Transaction Type"
                            @change="checkTransactionType">
                            <el-option v-for="item in props.types" :key="item.id" :label="item.transaction_name"
                                :value="item.id" />
                        </el-select>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- End block -->
    <!-- Dialog for Form Add/Edit Product -->
    <el-dialog v-model="dialogVisible" :title="editMode ? 'Edit product' : 'Add new product'" width="500"
        :before-close="handleClose">


        <form @submit.prevent="editMode ? updateProduct() : addProduct()" class="max-w-md mx-auto">
            <div class="relative z-0 w-full mb-5 group">
                <div class="mb-2">
                    <label for="product_id text-md text-gray-500">Product Name</label>
                </div>
                <div>
                    <el-select v-model="product_id" id="product_id" clearable placeholder="Select" @change="updateMaxQty">
                        <el-option v-for="product in listProduct" :key="product.id"
                            :label="product.product_name" :value="product.id">
                            <span style="float: left">{{ product.product_name }}</span>
                            <span style="float: right;color: var(--el-text-color-secondary);font-size: 13px;">stock: {{
                                product.stock.qty }}</span>
                        </el-option>
                    </el-select>
                </div>
            </div>
            <div class="relative z-0 w-full mb-5 group">
                <input type="number" v-model="qty" name="qty" id="qty" :max="modeSend ? maxQty : undefined"
                    class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                    placeholder=" " required />
                <label for="qty"
                    class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Quantity</label>
            </div>
            <button type="submit"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Submit</button>
        </form>

    </el-dialog>
    <!-- End dialog -->
</template>