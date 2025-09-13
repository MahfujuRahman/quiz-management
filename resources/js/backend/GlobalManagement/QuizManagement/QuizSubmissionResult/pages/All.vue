<template>
    <div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header py-2">
                        <all-page-header />
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table_responsive card_body_fixed_height">
                            <table class="table table-hover text-center table-bordered">
                                <thead>
                                    <table-head />
                                </thead>
                                <tbody v-if="all?.data?.length">
                                    <table-body :data="all?.data" :current_page="page" :per_page="paginate" />
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="100%">
                                            <div
                                                style="background-color: #00000080 !important;  display: flex; flex-direction: column; align-items: center; padding: 40px 0;">
                                                <svg width="64" height="64" fill="none" viewBox="0 0 64 64">
                                                    <circle cx="32" cy="32" r="32" fill="#f5f6fa" />
                                                    <path
                                                        d="M32 18c-7.732 0-14 5.373-14 12s6.268 12 14 12 14-5.373 14-12-6.268-12-14-12zm0 22c-5.523 0-10-3.806-10-8.5S26.477 23 32 23s10 3.806 10 8.5S37.523 40 32 40zm0-11a2 2 0 100 4 2 2 0 000-4zm0 7c-2.21 0-4-1.343-4-3h2c0 .552.895 1 2 1s2-.448 2-1h2c0 1.657-1.79 3-4 3z"
                                                        fill="#a5b4fc" />
                                                </svg>
                                                <div
                                                    style="margin-top: 16px; font-size: 1.25rem; font-weight: 600; color: #fba540;">
                                                    No data found
                                                </div>
                                                <div style="color: #fff; margin-top: 4px;">
                                                    Try adjusting your filters or add new data to see it here.
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mx-3" v-if="typeof all == `object`">
                        <pagination :data="all" :get_data="get_all_data" :set_paginate="set_paginate"
                            :set_page="set_page" />
                    </div>
                    <div class="card-footer py-2">
                        <all-page-footer-actions></all-page-footer-actions>
                    </div>
                </div>
            </div>
        </div>
        <export-all-loader />
        <quick-view />
        <filter-data />
        <import-modal />


    </div>
</template>

<script>
/** plugins */
import { mapActions, mapState, mapWritableState } from 'pinia'
import setup from "../setup";
import { store as data_store } from "../store";

/** helper and actions */
import get_all_data from "../store/async_actions/all";

/** components */

import AllPageHeader from '../components/all_data_page/AllPageHeader.vue';
import TableHead from '../components/all_data_page/TableHead.vue';
import TableBody from '../components/all_data_page/TableBody.vue';
import AllPageFooterActions from '../components/all_data_page/AllPageFooterActions.vue';
import ExportAllLoader from '../components/all_data_page/ExportAllLoader.vue';
import QuickView from '../components/canvas/QuickView.vue';
import FilterData from '../components/canvas/FilterData.vue';
import ImportModal from '../components/all_data_page/ImportModal.vue';



export default {

    props: {
        permision: {
            type: Array,
            obeject: true,
            default: () => [{ all: true }],
        },
    },

    data: () => ({
        setup,
    }),
    created: async function () {
        this.reset_filter_criteria();
        this.paginate = 10;
        await this.get_all_data();
    },
    methods: {
        ...mapActions(data_store, [
            'set_page', // needs in pagination props
            'set_paginate', // needs in pagination props
            'reset_filter_criteria', // needs in pagination props
            'set_import_csv_modal_show',
        ]),
        get_all_data,
        async FileUploadHandler(event) {
            const formData = new FormData(event.target);
            try {
                // Add your import logic here
                console.log('Importing file...', formData.get('file'));
                // You can call an API action here
                // await this.import_data(formData);
                this.set_import_csv_modal_show(false);
                await this.get_all_data(); // Refresh data after import
            } catch (error) {
                console.error('Import failed:', error);
            }
        },
        export_demo_csv() {
            // Add your demo CSV export logic here
            console.log('Downloading demo CSV...');
            // You can call an API action here
            // this.download_demo_csv();
        },
    },
    computed: {
        ...mapWritableState(data_store, {
            all: 'all',
            paginate: 'paginate',
            import_csv_modal_show: 'import_csv_modal_show',
            page: 'page',
            paginate: 'paginate',
        })
    },
    components: {
        TableHead,
        TableBody,
        AllPageHeader,
        AllPageFooterActions,
        ExportAllLoader,
        QuickView,
        FilterData,
        ImportModal,
    },
}
</script>

<style scoped>
.table_responsive table tbody tr:hover,
.table_responsive table tbody tr.active {
    background: none;
}
</style>
