require("./bootstrap");

import router from "./router";
import VueRouter from "vue-router";

import Index from "./layouts/Index";

window.Vue = require("vue");

Vue.use(VueRouter);

const app = new Vue({
    el: "#app",
    router,
    components: {
        Index
    }
});
