import axios from "axios";
import setup from "../../setup";
import { mapState } from "pinia";
import { store } from "..";

async function execute(payload){
    let state = mapState(store,['item']);
    payload.id = state.item().id;
    let url = `${setup.api_host}/${setup.api_version}/${setup.api_end_point}/update/${state.item().slug}`;
    try {
        let response = await axios.post(url, payload);
        return response;
    } catch (error) {
        if(error.response && error.response.status == 422){
            (window as any).s_alert('Fill the required input fields.','error');
        }
        return error.response;
    }
}

export default execute;
