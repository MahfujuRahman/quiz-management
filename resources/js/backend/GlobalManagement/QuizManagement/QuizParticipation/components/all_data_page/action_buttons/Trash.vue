<template lang="">
   <div class="">
            <a href="" @click.prevent="change_status(`trased`), set_is_trashed_data(true)"
                class="btn action_btn btn-sm btn-danger d-flex align-items-center py-2">
                <i class="fa fa-trash mr-2"></i> Trased
                ({{ trased_data_count }})
            </a>
        </div>
</template>
<script>
import { mapActions, mapState } from 'pinia';
import { store } from '../../../store';

export default {
    methods: {
        ...mapActions(store, [
            'set_status',
            'get_all',
            'set_only_latest_data',
            'set_page',
            'set_is_trashed_data',
        ]),
        change_status: function (status = 'active') {
            this.set_only_latest_data(true);
            this.set_status(status);
            this.set_page(1);

            this.get_all();

            this.set_only_latest_data(false);
        }
    },
    computed: {
        ...mapState(store, [
            'trased_data_count',
            'is_trased_data',
        ]),
    }
}
</script>
<style lang="">

</style>
