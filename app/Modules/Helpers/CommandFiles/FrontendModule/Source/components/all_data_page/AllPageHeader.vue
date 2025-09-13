<template>
    <div class="row align-items-center">
        <!-- Title Section -->
        <div class="col-12 col-md-3 mb-2 mb-md-0">
            <h5 class="text-capitalize mb-0">
                {{ setup.all_page_title }} ({{ status }})
            </h5>
        </div>


        <!-- Search Input -->
        <div class="col-12 col-md-6 mb-2 mb-md-0">
            <div class="search-input-container position-relative">
                <input 
                    class="form-control" 
                    @keyup="(e) => set_search_key(e)" 
                    placeholder="Search"
                    v-model="search_key"
                />
                <button 
                    v-if="search_key && search_key.length > 0"
                    class="btn btn-sm search-clear-btn position-absolute"
                    @click="clearSearch"
                    title="Clear search"
                    type="button"
                >
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Sorting Button -->
        <div class="col-12 col-md-3 text-md-right text-sm-left">
            <button class="btn btn-outline-warning btn-sm mr-2" @click="reset_filters" title="Reload">
                <i class="fa fa-refresh"></i>
            </button>
            <button class="btn btn-outline-success btn-sm" @click="set_show_filter_canvas">
                <i class="fa fa-gear mx-2"></i>Filter
            </button>

        </div>


    </div>
</template>
<script>
import Search from './Search.vue';
import setup from '../../setup';
import { mapActions, mapWritableState } from 'pinia';
import { store } from '../../store';
import debounce from '../../helpers/debounce';
export default {
    components: { Search },
    data: () => ({
        setup,
    }),
    methods: {
        ...mapActions(store, ['set_show_filter_canvas', 'get_all','reset_filter_criteria']),
        set_search_key: debounce(async function (event) {
            let value = event.target.value;
            this.search_key = value;

            this.only_latest_data = true;
            await this.get_all();
            this.only_latest_data = false;
        }, 500),
        async reset_filters() {
            this.reset_filter_criteria();
            await this.get_all();
        },
        async clearSearch() {
            this.search_key = '';
            this.only_latest_data = true;
            await this.get_all();
            this.only_latest_data = false;
        },
    },
    computed: {
        ...mapWritableState(store, [
            'search_key',
            'only_latest_data',
            'status',
            
        ])
    }
}
</script>
<style scoped>
.search-input-container {
    position: relative;
}

.search-clear-btn {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #dc3545;
    /* color: #6c757d; */
    padding: 2px 6px;
    line-height: 1;
    z-index: 10;
    transition: color 0.2s ease;
}

.search-clear-btn:hover {
    color: #dc3545;
    background: transparent;
}

.search-clear-btn:focus {
    outline: none;
    box-shadow: none;
}

.search-input-container .form-control {
    padding-right: 35px;
}
</style>
