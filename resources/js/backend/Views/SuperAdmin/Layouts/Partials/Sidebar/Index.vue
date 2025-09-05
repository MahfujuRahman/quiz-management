`~`<template>
  <!--Start sidebar-wrapper-->
  <div id="sidebar-wrapper">
    <div class="brand-logo">
      <router-link :to="{ name: `adminDashboard` }" class="d-flex align-items-center">
        <img :src="`${get_setting_value('image') ?? 'avatar.png'} `" class="logo-icon" alt="logo icon" />
        <h5 class="logo-text">Super Admin Panel</h5>
      </router-link>
      <div class="close-btn">
        <i class="zmdi zmdi-close" @click="toggle_menu"></i>
      </div>
    </div>

    <div class="text-center mt-3">
      <img class="rounded-circle p-1" height="70" width="70" :src="`${auth_info.image ?? 'avatar.png'}`" alt="" />
      <p class="mt-2">Mr. {{ auth_info.first_name }} {{ auth_info.last_name }}</p>
    </div>
    <hr />
    <ul class="metismenu" id="menu">
      <side-bar-single-menu :icon="`zmdi zmdi-view-dashboard`" :menu_title="`Dashboard`"
        :route_name="`adminDashboard`" />
      <!-- Management start -->
      <side-bar-single-menu :icon="`zmdi zmdi-account`" :menu_title="`User Management`" :route_name="`AllUser`" />


      <side-bar-single-menu :icon="`zmdi zmdi-label`" :menu_title="`Quiz Topic`" :route_name="`AllQuizQuestionTopic`" />
      <side-bar-single-menu :icon="`zmdi zmdi-edit`" :menu_title="`Quiz Question`" :route_name="`AllQuizQuestion`" />
      <side-bar-single-menu :icon="`zmdi zmdi-collection-text`" :menu_title="`All Quiz`" :route_name="`AllQuiz`" />
      <!-- <side-bar-single-menu :icon="`zmdi zmdi-chart`" :menu_title="`Quiz Result`" :route_name="`AllQuizSubmissionResult`" /> -->
      <side-bar-single-menu :icon="`zmdi zmdi-chart`" :menu_title="`Quiz Participation`"
        :route_name="`AllQuizParticipation`" />


      <!-- Management end -->
    </ul>
  </div>
</template>

<script>
//auth_store
import { auth_store } from "../../../../../GlobalStore/auth_store";
import { site_settings_store } from "../../../../../GlobalStore/site_settings_store";
import { mapState, mapActions } from "pinia";
//components
import SideBarDropDownMenus from "./SideBarDropDownMenus.vue";
import SideBarSingleMenu from "./SideBarSingleMenu.vue";
export default {
  components: { SideBarDropDownMenus, SideBarSingleMenu },
  methods: {
    ...mapActions(site_settings_store, {
      get_setting_value: "get_setting_value",
    }),

    logout_submit: function () {
      let confirm = window.confirm("logout");
      if (confirm) {
        this.log_out();
      }
    },
    toggle_menu: function () {
      document.getElementById("wrapper").classList.toggle("toggled");
    },
    hide_menu: function () {
      document.getElementById("wrapper").classList.add("toggled");
    },
    onDashboardClick() {
      // Close all dropdown menus when dashboard is clicked
      window.dispatchEvent(
        new CustomEvent("collapse-all-menus", {
          detail: { except: null },
        })
      );
    },
  },
  watch: {
    '$route'(to, from) {
      // Auto-hide sidebar when navigating to task board
      if (to.name === 'TaskBoard') {
        this.hide_menu();
      }
    }
  },
  computed: {
    ...mapState(auth_store, {
      auth_info: "auth_info",
    }),
  },
};
</script>

<style>
/* Dashboard active state styling */
#menu>li.active>a,
#menu>li>a.active {
  background-color: #007bff !important;
  color: white !important;
  border-radius: 4px;
  margin: 2px;
}
</style>
<!-- <side-bar-drop-down-menus :icon="`fa fa-plus`" :icon_image="`https://files.etek.com.bd/images/icon_sales.png`"
    :menu_title="`title Management`" :menus="[
                {
                    route_name: `AllUser`,
                    title: `title`,
                    icon: `zmdi zmdi-dot-circle-alt`,
                },
            ]" /> -->
