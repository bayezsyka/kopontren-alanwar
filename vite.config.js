import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig(({ mode }) => ({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
    ],

    /**
     * ⛔ server hanya untuk LOCAL DEV
     * TIDAK dipakai di production (npm run build)
     */
    server:
        mode === "development"
            ? {
                  host: "localhost",
                  port: 8001,
                  strictPort: true,
                  cors: true,
                  hmr: {
                      host: "localhost",
                  },
                  watch: {
                      ignored: ["**/storage/framework/views/**"],
                  },
              }
            : undefined,

    /**
     * ✅ WAJIB untuk production
     */
    base: "/", // JANGAN pakai http://localhost
    build: {
        outDir: "public/build",
        emptyOutDir: true,
        manifest: true,
    },
}));
