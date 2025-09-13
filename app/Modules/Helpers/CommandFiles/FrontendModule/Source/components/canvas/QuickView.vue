<template lang="">
    <div class="off_canvas quick_view" :class="{active: show_details_canvas}">
        <div class="off_canvas_body">
            <div class="header">
                <h3 class="heading_text">Quick View</h3>
                <button @click.prevent="set_show_details_canvas(false)"
                    class="btn btn-sm btn-outline-danger">
                    <i class="fa fa-close"></i>
                </button>
            </div>
            <div class="data_content">
                <table class="table quick_modal_table">
                    <tbody>
                        <template v-for="(row_item, index) in setup.select_fields" :key="index">
                            <tr>
                                <th>{{ row_item }}</th>
                                <th class="text-center">:</th>
                                <th class="text-trim">
                                    <template v-if="row_item === 'image' && item[row_item]">
                                        <a :href="item[row_item]" data-lightbox="image" data-title="Preview">
                                            <img
                                                :src="item[row_item]"
                                                style="width: 120px; height: 80px; object-fit: cover"
                                                alt="image"
                                            />
                                        </a>
                                    </template>
                                    <template v-else>
                                        {{ trim_content(item[row_item], row_item) }}
                                    </template>
                                </th>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="off_canvas_overlay"></div>
    </div>
</template>
<script>
import { mapActions, mapWritableState } from 'pinia';
import { store } from '../../store';
import setup from '../../setup';

export default {
    data: () => ({
        setup,
    }),
    methods: {
        ...mapActions(store, [
            'set_show_details_canvas'
        ]),
        trim_content(content, row_item = null) {
            if (typeof content == "string") {
                if (row_item == "created_at" || row_item == "updated_at") {
                    return new Intl.DateTimeFormat("en-US", {
                        year: "numeric",
                        month: "2-digit",
                        day: "2-digit",
                        hour: "2-digit",
                        minute: "2-digit",
                        second: "2-digit",
                    }).format(new Date(content));
                }
                return content.length > 50 ? content.substring(0, 50) + "..." : content;
            }
            if (content && typeof content === "object") {
                for (const key of Object.keys(content)) {
                    if (key === "title" && content.title) {
                        return content.title;
                    }
                    if (key === "name" && content.name) {
                        return content.name;
                    }
                }
            }

            return content || "";
        },
    },
    computed:{
        ...mapWritableState(store, [
            'show_details_canvas',
            'item',
        ])
    }
};
</script>
<style lang="">
</style>
