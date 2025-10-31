import dynamic from "next/dynamic";

/* const name must be camelCase to match the one from getTemplate() from /lib/templates.js */
const home = dynamic(() => import("./home.js"));
const index = dynamic(() => import("./default.js"));
const single = dynamic(() => import("./single.js"));
const frontPage = dynamic(() => import("./front-page.js"));
const page = dynamic(() => import("./page.js"));

// WooCommerce Templates
const archiveProduct = dynamic(() => import("./archive-product.js"));
const taxonomyProductCat = dynamic(() => import("./taxonomy-product_cat.js"));
const singleProduct = dynamic(() => import("./single-product.js"));
const pageMyAccount = dynamic(() => import("./page-my-account.js"));
const pageCart = dynamic(() => import("./page-cart.js"));
const pageCheckout = dynamic(() => import("./page-checkout.js"));

export default {
    home,
    index,
    page,
    single,
    frontPage,
    archiveProduct,
    taxonomyProductCat,
    singleProduct,
    pageCart,
    pageMyAccount,
    pageCheckout,
};