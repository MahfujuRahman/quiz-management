import { mapWritableState } from "pinia";
import { store } from "..";

async function set_is_trashed_data (value: boolean = false){
    console.log('set_is_trashed_data', value);
    let state = mapWritableState(store, [
        'is_trased_data'
    ]);
    state.is_trased_data.set(value);
}

export default set_is_trashed_data;
