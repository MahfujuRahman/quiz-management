
function initiate_our_course_types() {
    let our_course_types = document.getElementById('our_course_types');

    if (our_course_types) {
        new Vue({
            el: '#our_course_types',
            data: () => ({
                types: [],
                courses: {},
                course_url: "",
            }),
            created: async function () {
                console.log('our_course_types created');

                this.course_url = "/api/v1/course/all-course";

                let response = await axios.get('/api/v1/course/all-types');
                this.types = response.data;

                this.get_courses(this.course_url);
            },
            methods: {
                get_courses: async function (url) {
                    response = await axios.get(url);
                    console.log(response);
                    this.courses = response.data;
                },
                get_course_by_type: async function (type_id) {
                    let url = this.course_url + '?course_type=' + type_id;
                    this.get_courses(url);
                }
            }
        })
    }
}



