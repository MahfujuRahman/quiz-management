import axios from "axios";
import setup from "../../setup";

async function execute(payload){
    let url = `${setup.api_host}/${setup.api_version}/${setup.api_end_point}/store`;
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
