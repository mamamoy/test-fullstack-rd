<script setup>
import { router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue'
const props = defineProps({
    transactions: Array,
})

const dialogVisible = ref(false)

// product data
const id = ref('')
const transaction_id = ref('')
const is_approved = ref('')
const dataTableModal = ref([])
// end

const searchTable = ref('')

const transactionDetails = (transaction) => {
    const selectedTransaction = props.transactions.find(data => data.id === transaction.id)
    dataTableModal.value = selectedTransaction
    dialogVisible.value = true
}

// Search data by input search
const filteredTable = computed(() => {
    if (!searchTable.value) {
        return props.transactions;
    } else {
        const query = searchTable.value.toLowerCase();
        return props.transactions.filter(data => data.transaction_id.toLowerCase().includes(query) || data.is_approved.toLowerCase().includes(query) || data.type.transaction_name.toLowerCase().includes(query));
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


// // open edit modal
const openEditModal = (transaction) => {
    dialogVisible.value = false
    Swal.fire({
        title: 'Are you sure ?',
        text: 'Product to approve this transaction ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: "#057A55",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, approve!"
    }).then((result) => {
        if (result.isConfirmed) {
            try {
                appproveTransaction(transaction)
            } catch (error) {
                console.log(error)
            }
        }
    })


}

const appproveTransaction = async (transaction) => {
    // update data
    id.value = transaction.id
    transaction_id.value = transaction.transaction_id
    is_approved.value = 1

    const formData = new FormData
    formData.append('id', id.value)
    formData.append('is_approved', is_approved.value)
    formData.append('_method', 'PUT')

    try {
        await router.post('transaction/update/' + transaction_id.value, formData, {
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
    } catch (errors) {
        console.log(errors)
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Product has not been updated, try again!'
        })
    }
}
// // end

// // delete selected transaction with detail relationship
const deleteTransaction = (transaction) => {
    Swal.fire({
        title: 'Are you sure ?',
        text: 'Transaction will be deleted permanently',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            try {
                router.delete('transaction/destroy/' + transaction.transaction_id, {
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

const download = async (transaction) => {
    try {
        const response = await fetch('export/' + transaction.transaction_id, {
            method: 'GET',
        })
        if (response.ok) {
            const blob = await response.blob()
            const url = window.URL.createObjectURL(new Blob([blob]));
            const link = document.createElement('a')
            link.href = url
            link.setAttribute('download', 'transaction.pdf')

            document.body.appendChild(link)
            link.click()
            document.body.removeChild(link)
            Swal.fire({
                toast: true,
                icon: 'success',
                position: 'top-end',
                showConfirmButton: false,
                title: 'PDF downloaded successfully'
            })
        } else {
            Swal.fire({
                toast: true,
                icon: 'error',
                position: 'top-end',
                showConfirmButton: false,
                title: 'Failed to download PDF'
            })
        }
    } catch (error) {
        console.log(error);
        Swal.fire({
            toast: true,
            icon: 'error',
            position: 'top-end',
            showConfirmButton: false,
            title: 'An error occurred while downloading PDF'
        })
    }
}
</script>
<template>
    <!-- Start block -->
    <section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5 antialiased">

        <div class="mx-auto max-w-screen-xl">
            <!-- Start coding here -->
            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                <div class="ms-4 mt-2 mb-2 items-start">
                    <h2 class="text-gray-700 text-lg">Transaction List</h2>
                    <span class="text-sm text-gray-500">This table showing a list of transaction has been made.</span>
                </div>
                <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                    <div class="w-full md:w-1/2">
                        <form class="flex items-center" @submit.prevent>
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
                                <input v-model="searchTable" type="text" id="simple-search"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
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
                                <th scope="col" class="px-4 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(transaction, index) in paginatedTable" :key="transaction.id"
                                class="border-b dark:border-gray-700 text-center">

                                <th scope="row"
                                    class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{
                                        transaction.transaction_id }}</th>
                                <td class="px-4 py-3">{{ new Date(transaction.created_at).toLocaleString('en-US', {
                                    dateStyle: 'medium', timeStyle: 'short'
                                }) }}</td>
                                <td class="px-4 py-3">{{ transaction.type.transaction_name }}</td>
                                <td v-if="transaction.is_approved" class="px-4 py-3"><span
                                        class="bg-green-100 text-green-800 text-md font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">{{
                                            transaction.is_approved }}</span></td>
                                <td v-else class="px-4 py-3">
                                    <span
                                        class="bg-yellow-100 text-yellow-800 text-md font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-yellow-900 dark:text-yellow-300">{{
                                            transaction.is_approved }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <button type="button" @click="download(transaction)"
                                                class="text-green-400 border border-green-400 hover:bg-green-400 hover:text-white focus:ring-4 focus:outline-none focus:ring-green-100 font-medium rounded-full text-sm p-1 text-center inline-flex items-center dark:border-green-200 dark:text-green-200 dark:hover:text-white dark:focus:ring-green-500 dark:hover:bg-green-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                </svg>



                                            </button>
                                        </div>
                                        <div>
                                            <button type="button" @click="transactionDetails(transaction)"
                                                class="text-cyan-400 border border-cyan-400 hover:bg-cyan-400 hover:text-white focus:ring-4 focus:outline-none focus:ring-cyan-100 font-medium rounded-full text-sm p-1 text-center inline-flex items-center dark:border-cyan-200 dark:text-cyan-200 dark:hover:text-white dark:focus:ring-cyan-500 dark:hover:bg-cyan-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>

                                            </button>
                                        </div>
                                        <div>
                                            <button type="button" @click="deleteTransaction(transaction)"
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
            </div>
        </div>
    </section>
    <!-- End block -->


    <!-- Main modal -->
    <el-dialog v-model="dialogVisible" title="Approve Data" width="800">
        <el-scrollbar>
            <div class="mx-4 rounded-md mt-2 mb-4">
                <dl>
                    <dt class="mb-2 font-semibold leading-none text-gray-900 dark:text-white">Details</dt>
                </dl>
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3 bg-gray-50 dark:text-white dark:bg-gray-800">
                                Product name
                            </th>
                            <th scope="col" class="px-6 py-3 bg-gray-50 dark:text-white dark:bg-gray-800">
                                Quantity
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in dataTableModal.detail" :key="item.id"
                            class="border-b border-gray-200 dark:border-gray-700">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap ">
                                {{ item.product.product_name }}</th>
                            <td class="px-6 py-4">
                                {{ item.qty }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end items-center gap-4">
                <button v-if="!dataTableModal.isApproved" @click="openEditModal(dataTableModal)" type="button"
                    class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">Approve</button>


                <button v-else type="button"
                    class="text-white bg-green-400 dark:bg-green-500 cursor-not-allowed font-medium rounded-lg text-sm px-5 py-2.5 text-center"
                    disabled>Has Been Approved</button>


            </div>
        </el-scrollbar>
</el-dialog></template>