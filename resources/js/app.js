import './bootstrap';
import {createApp} from "vue/dist/vue.esm-bundler";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

const app = createApp({});

app.use(Toast, {});

app.mount("#app");
