import "./bootstrap";
import Alpine from "alpinejs";

import "./api";
import "./auth";
import "./pwa";

import registerComponents from "./components";

// expose Alpine
window.Alpine = Alpine;

// register Alpine components
registerComponents(Alpine);

// start Alpine
Alpine.start();
