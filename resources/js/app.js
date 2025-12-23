import "./bootstrap";
import Alpine from "alpinejs";
import "./api";
import "./auth";
import "./pwa";
import registerComponents from "./components";

// Make Alpine available globally
window.Alpine = Alpine;

// Register Alpine components BEFORE starting
registerComponents(Alpine);

// Delay Alpine start to ensure DOM is ready and components are registered
document.addEventListener("DOMContentLoaded", () => {
    Alpine.start();
});
