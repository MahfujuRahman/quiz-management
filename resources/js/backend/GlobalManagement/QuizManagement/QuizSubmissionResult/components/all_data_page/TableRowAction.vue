<template lang="">
    <span @click.prevent="active_row($event)" class="icon"></span>
    <div class="table_action_btns">
        <ul>
            <li  v-if="!is_trased_data">
                <details-btn :item="item" />
            </li>
            <li v-if="!is_trased_data">
                <edit :item="item"/>
            </li>
            <li v-if="!is_trased_data">
                <status-button :item="item"/>
            </li>
            <li>
                <delete-restore :item="item"/>
            </li>
            <li v-if="!is_trased_data">
                <destroy :item="item"/>
            </li>


            <slot />
        </ul>
    </div>
</template>
<script>
import { mapWritableState } from 'pinia';
import active_row from '../../helpers/table_active_row';
import Details from './action_buttons/Details.vue';
import Edit from './action_buttons/Edit.vue';
import InActive from './action_buttons/InActive.vue';
import Destroy from './action_buttons/Destroy.vue';
import StatusButton from './action_buttons/StatusButton.vue';
import DeleteRestore from './action_buttons/DeleteRestore.vue';
import { store as data_store } from '../../store';
export default {
    props: {
        item: {
            type: Object,
            default: {},
        }
    },
    components: {
        'details-btn': Details,
        'edit': Edit,
        'in-active': InActive,
        'destroy': Destroy,
        'status-button': StatusButton,
        'delete-restore': DeleteRestore,
    },
    methods: {
        active_row,
    },
    computed: {
        ...mapWritableState(data_store, {
            is_trased_data: 'is_trased_data',
        })
    },
}
</script>
<style scoped>
.table_action_btns {
    position: absolute;
    top: auto !important;
    right: -2px !important;

    /* Make sure it aligns to the end of the row */
}
</style>
