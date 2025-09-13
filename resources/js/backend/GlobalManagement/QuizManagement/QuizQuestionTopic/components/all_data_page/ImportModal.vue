<template lang="">
     <div
            class="modal fade"
            :class="`${import_csv_modal_show ? 'show d-block' : 'd-none'}`"
            id="primarymodal"
            aria-modal="true"
        >
            <div class="modal-dialog modal-dialog-centered">
                <form @submit.prevent="FileUploadHandler">
                    <div class="modal-content border-primary">
                        <div class="modal-header bg-primary">
                            <h5 class="modal-title text-white">
                                Import {{ setup.prefix }}
                            </h5>
                            <button
                                @click="import_csv_modal_show = false"
                                type="button"
                                class="close text-white"
                                data-dismiss="modal"
                                aria-label="Close"
                            >
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="">
                                <label for="">Upload file</label>
                                <input
                                    type="file"
                                    name="file"
                                    class="form-control"
                                    required
                                />
                            </div>
                            <p class="mt-3">
                                Please check the sample CSV file below to ensure
                                compatibility with the demo data import.
                            </p>
                            <a
                                href=""
                                @click.prevent="export_demo_csv"
                                class="btn btn-sm btn-primary"
                                >Download Demo CSV</a
                            >
                        </div>
                        <div class="modal-footer">
                            <button
                                @click="import_csv_modal_show = false"
                                type="button"
                                class="btn btn-light"
                                data-dismiss="modal"
                            >
                                <i class="fa fa-times"></i> Close
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-download"></i> Import
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
</template>
<script>

import { mapActions, mapWritableState } from 'pinia';
import { store } from '../../store';
import setup from '../../setup';
import get_all from '../../store/async_actions/all'
import export_demo_csv from "../../helpers/export_demo_csv";
export default {
    data: () => ({
        setup,
    }),
    methods: {
        export_demo_csv,
        ...mapActions(store, [
            'import_data',
            'set_only_latest_data'
        ]),
        get_all,
         FileUploadHandler: async function ($event) {
            let response = await this.import_data($event);
            if (response.data.status === "success") {
                await this.get_all();
                window.s_alert(response.data.message);
                this.set_only_latest_data(true);
                this.import_csv_modal_show = false;
            } else {
                window.s_warning(response.data?.message);
            }
        },
    },
    computed: {
        ...mapWritableState(store, [
            'import_csv_modal_show'
        ])
    }
}
</script>
<style lang="">

</style>