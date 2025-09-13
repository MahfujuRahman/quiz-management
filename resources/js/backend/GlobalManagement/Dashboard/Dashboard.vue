<template>
  <div class="container-fluid">
    <!--Start Dashboard Content-->

   
  </div>
  <!-- End container-fluid-->
</template>

<script>
export default {
  data: () => ({
    data: {},
  }),
  created: async function () {
    await this.get_all_dashboard_data();
  },
  methods: {
    get_all_dashboard_data: async function () {
      let response = await axios.get("get-all-dashboard-data");
      if (response.status == 200) {
        this.data = response.data.data;
      }
    },
  },
};
</script>

<style></style>
