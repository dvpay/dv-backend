import './bootstrap';
import {createApp} from "vue/dist/vue.esm-bundler";
import DatabaseWizard from './components/installer/DatabaseWizard.vue'
import Installer from "./components/installer/Installer.vue";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

const app = createApp({});

app.component('AppDatabaseWizard', DatabaseWizard)
app.component('AppInstallWizard', Installer)
app.use(Toast, {});

app.mount("#app");
