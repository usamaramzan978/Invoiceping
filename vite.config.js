import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react-swc";
import { viteStaticCopy } from "vite-plugin-static-copy";
import packages from "./package.json";
import fsExtra from "fs-extra";
import { join } from "path";

// Helper function to copy lib files from node_modules
async function copyLibFiles() {
    const destDir = "public/build/assets/libs";

    if (await fsExtra.pathExists(join(destDir, "bootstrap"))) {
        console.log("Library files already exist, skipping copy...");
        return;
    }

    for (const dep of Object.keys(packages.devDependencies || {})) {
        const srcPath = join("node_modules", dep, "dist");
        const destPath = join(destDir, dep);

        try {
            if (await fsExtra.pathExists(srcPath)) {
                await fsExtra.copy(srcPath, destPath, {
                    overwrite: false,
                    errorOnExist: false,
                });

                await fsExtra.remove(join(destPath, "dist"));
            } else {
                const packageSrcPath = join("node_modules", dep);
                const packageDestPath = join(destDir, dep);

                if (
                    (await fsExtra.pathExists(packageSrcPath)) &&
                    !(await fsExtra.pathExists(packageDestPath))
                ) {
                    await fsExtra.copy(packageSrcPath, packageDestPath, {
                        overwrite: false,
                        errorOnExist: false,
                        filter: (src) =>
                            !src.includes(".github") &&
                            !src.includes("node_modules"),
                    });
                }
            }
        } catch (err) {
            // Silently skip problematic files
        }
    }
}

// Helper function to copy static assets for dev mode
async function copyStaticAssets() {
    const destDir = "public/build/assets";

    const imagesExist = await fsExtra.pathExists(join(destDir, "images"));
    const iconFontsExist = await fsExtra.pathExists(
        join(destDir, "icon-fonts"),
    );

    if (imagesExist && iconFontsExist) {
        console.log("Static assets already exist, skipping copy...");
        return;
    }

    const staticAssets = [
        { src: "resources/assets/images", dest: join(destDir, "images") },
        {
            src: "resources/assets/icon-fonts",
            dest: join(destDir, "icon-fonts"),
        },
        { src: "resources/assets/video", dest: join(destDir, "video") },
    ];

    const staticFiles = [
        "resources/assets/js/apex-github-data.js",
        "resources/assets/js/apexcharts-dayjs.js",
        "resources/assets/js/apexcharts-irregulardata.js",
        "resources/assets/js/apexcharts-stock-prices.js",
        "resources/assets/js/apexcharts-candlestick-seriesdata.js",
        "resources/assets/js/authentication-main.js",
        "resources/assets/js/chat.js",
        "resources/assets/js/coming-soon.js",
        "resources/assets/js/dataseries.js",
        "resources/assets/js/main.js",
        "resources/assets/js/show-password.js",
        "resources/assets/js/sticky.js",
        "resources/assets/js/two-step-verification.js",
        "resources/assets/js/under-maintenance.js",
    ];

    await fsExtra.ensureDir(destDir);

    for (const asset of staticAssets) {
        if (
            (await fsExtra.pathExists(asset.src)) &&
            !(await fsExtra.pathExists(asset.dest))
        ) {
            try {
                await fsExtra.copy(asset.src, asset.dest, {
                    overwrite: false,
                    errorOnExist: false,
                    filter: (src) =>
                        !src.includes(".github") &&
                        !src.includes("node_modules"),
                });
            } catch (err) {
                console.log(`Skipping ${asset.src}: ${err.message}`);
            }
        }
    }

    for (const file of staticFiles) {
        if (await fsExtra.pathExists(file)) {
            const fileName = file.split("/").pop();
            const destPath = join(destDir, fileName);
            if (!(await fsExtra.pathExists(destPath))) {
                try {
                    await fsExtra.copy(file, destPath, { overwrite: false });
                } catch (err) {
                    console.log(`Skipping ${file}: ${err.message}`);
                }
            }
        }
    }
}

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Resources paths
                "resources/css/app.css",
                "resources/sass/app.scss",
                "resources/js/app.js",

                // React Email Builder entry point
                "resources/js/main.tsx",

                // Resources assets js file paths
                "resources/assets/js/add-products.js",
                "resources/assets/js/alerts.js",
                "resources/assets/js/analytics-dashboard.js",
                "resources/assets/js/apexcharts-area.js",
                "resources/assets/js/apexcharts-bar.js",
                "resources/assets/js/apexcharts-boxplot.js",
                "resources/assets/js/apexcharts-bubble.js",
                "resources/assets/js/apexcharts-candlestick.js",
                "resources/assets/js/apexcharts-column.js",
                "resources/assets/js/apexcharts-heatmap.js",
                "resources/assets/js/apexcharts-line.js",
                "resources/assets/js/apexcharts-mixed.js",
                "resources/assets/js/apexcharts-pie.js",
                "resources/assets/js/apexcharts-polararea.js",
                "resources/assets/js/apexcharts-radar.js",
                "resources/assets/js/apexcharts-radialbar.js",
                "resources/assets/js/apexcharts-rangearea.js",
                "resources/assets/js/apexcharts-scatter.js",
                "resources/assets/js/apexcharts-timeline.js",
                "resources/assets/js/apexcharts-treemap.js",
                "resources/assets/js/authentication.js",
                "resources/assets/js/blog-create.js",
                "resources/assets/js/canada.js",
                "resources/assets/js/cart.js",
                "resources/assets/js/chartjs-charts.js",
                "resources/assets/js/checkout.js",
                "resources/assets/js/choices.js",
                "resources/assets/js/color-picker.js",
                "resources/assets/js/courses-dashboard.js",
                "resources/assets/js/create-invoice.js",
                "resources/assets/js/create-project.js",
                "resources/assets/js/crm-companies.js",
                "resources/assets/js/crm-contacts.js",
                "resources/assets/js/crm-dashboard.js",
                "resources/assets/js/crm-deals.js",
                "resources/assets/js/crm-leads.js",
                "resources/assets/js/crypto-buy_sell.js",
                "resources/assets/js/crypto-currency-exchange.js",
                "resources/assets/js/crypto-dashboard.js",
                "resources/assets/js/crypto-marketcap.js",
                "resources/assets/js/crypto-transactions-list.js",
                "resources/assets/js/custom-switcher.js",
                "resources/assets/js/datatables.js",
                "resources/assets/js/date&time_pickers.js",
                "resources/assets/js/defaultmenu.js",
                "resources/assets/js/draggable-cards.js",
                "resources/assets/js/echarts.js",
                "resources/assets/js/ecommerce-dashboard.js",
                "resources/assets/js/edit-products.js",
                "resources/assets/js/error.js",
                "resources/assets/js/file-manager.js",
                "resources/assets/js/fileupload.js",
                "resources/assets/js/form-input-mask.js",
                "resources/assets/js/fullcalendar.js",
                "resources/assets/js/gallery.js",
                "resources/assets/js/google-maps.js",
                "resources/assets/js/grid.js",
                "resources/assets/js/hrm-dashboard.js",
                "resources/assets/js/invoice-list.js",
                "resources/assets/js/italy.js",
                "resources/assets/js/job-candidate-details.js",
                "resources/assets/js/job-details.js",
                "resources/assets/js/job-search-candidate.js",
                "resources/assets/js/job-search.js",
                "resources/assets/js/jobs-dashboard.js",
                "resources/assets/js/jobs-post.js",
                "resources/assets/js/jsvectormap.js",
                "resources/assets/js/landing.js",
                "resources/assets/js/leaflet.js",
                "resources/assets/js/mail.js",
                "resources/assets/js/mail-settings.js",
                "resources/assets/js/masonry.js",
                "resources/assets/js/modal.js",
                "resources/assets/js/nft-create.js",
                "resources/assets/js/nft-dashboard.js",
                "resources/assets/js/nouislider.js",
                "resources/assets/js/personal-dashboard",
                "resources/assets/js/prism-custom.js",
                "resources/assets/js/product-details.js",
                "resources/assets/js/product-list.js",
                "resources/assets/js/products.js",
                "resources/assets/js/profile.js",
                "resources/assets/js/projects-dashboard.js",
                "resources/assets/js/quill-editor.js",
                "resources/assets/js/ratings.js",
                "resources/assets/js/russia.js",
                "resources/assets/js/sales-dashboard.js",
                "resources/assets/js/select2.js",
                "resources/assets/js/simplebar.js",
                "resources/assets/js/spain.js",
                "resources/assets/js/stocks-dashboard.js",
                "resources/assets/js/sweet-alerts.js",
                "resources/assets/js/swiper.js",
                "resources/assets/js/tabulator.js",
                "resources/assets/js/team.js",
                "resources/assets/js/task-kanban-board.js",
                "resources/assets/js/task-list.js",
                "resources/assets/js/team.js",
                "resources/assets/js/terms_conditions.js",
                "resources/assets/js/Toasts.js",
                "resources/assets/js/todolist.js",
                "resources/assets/js/us-merc-en.js",
                "resources/assets/js/validation.js",
                "resources/assets/js/widgets.js",
                "resources/assets/js/wishlist.js",
                "resources/assets/js/jobs-simplebar.js",
                "resources/assets/js/sales-simplebar.js",
            ],
            refresh: true,
        }),

        react(),

        viteStaticCopy({
            targets: [
                {
                    src: [
                        "resources/assets/images/",
                        "resources/assets/icon-fonts/",
                        "resources/assets/video/",

                        "resources/assets/js/apex-github-data.js",
                        "resources/assets/js/apexcharts-dayjs.js",
                        "resources/assets/js/apexcharts-irregulardata.js",
                        "resources/assets/js/apexcharts-stock-prices.js",
                        "resources/assets/js/apexcharts-candlestick-seriesdata.js",
                        "resources/assets/js/authentication-main.js",
                        "resources/assets/js/chat.js",
                        "resources/assets/js/coming-soon.js",
                        "resources/assets/js/dataseries.js",
                        "resources/assets/js/main.js",
                        "resources/assets/js/show-password.js",
                        "resources/assets/js/sticky.js",
                        "resources/assets/js/two-step-verification.js",
                        "resources/assets/js/under-maintenance.js",
                    ],
                    dest: "assets/",
                },
            ],
        }),

        {
            name: "copy-dist-files",
            writeBundle: async () => {
                await copyLibFiles();
            },
            configureServer: async () => {
                console.log(
                    "Copying static assets and library files for dev server...",
                );
                await copyStaticAssets();
                await copyLibFiles();
                console.log(
                    "Static assets and library files copied successfully.",
                );
            },
        },

        {
            name: "blade",
            handleHotUpdate({ file, server }) {
                if (file.endsWith(".blade.php")) {
                    server.ws.send({
                        type: "full-reload",
                        path: "*",
                    });
                }
            },
        },
    ],

    build: {
        chunkSizeWarningLimit: 1600,
        outDir: "public/build",
        emptyOutDir: true,
    },

    server: {
        host: "127.0.0.1",
        cors: {
            origin: "*",
            methods: ["GET", "HEAD", "PUT", "PATCH", "POST", "DELETE"],
            credentials: true,
        },
        hmr: {
            host: "127.0.0.1",
            protocol: "ws",
        },
    },

    css: {
        preprocessorOptions: {
            scss: {
                quietDeps: true,
            },
        },
    },
});