import VueRouter from "vue-router";
import ExampleComponent from "../components/ExampleComponent";
import ExampleComponent2 from "../components/ExampleComponent2";

// 同時在路由指定 components
const routes = [
    {
        path: "/",
        component: ExampleComponent,
        name: "home"
    },
    {
        path: "/second",
        component: ExampleComponent2,
        name: "second"
    }
];

const router = new VueRouter({
    // mode: "history",
    routes //縮寫 for `routes: routes`
});

// to app.js
export default router;
