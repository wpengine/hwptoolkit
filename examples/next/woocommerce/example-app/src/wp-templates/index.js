import dynamic from "next/dynamic";

const home = dynamic(() => import("./home.js"));
const index = dynamic(() => import("./default.js"));
const single = dynamic(() => import("./single.js"));
const frontPage = dynamic(() => import("./front-page.js"));
const archiveProduct = dynamic(() => import("./archive-product.js"));
const page = dynamic(() => import("./page.js"));

// WooCommerce Templates
const singleProduct = dynamic(() => import("./single-product.js"));
const pageCart = dynamic(() => import("./page-cart.js"));
const pageMyAccount = dynamic(() => import("./page-my-account.js"));

export default {
    home,
    index,
    page,
    single,
    frontPage,
    archiveProduct,
    singleProduct,
    pageCart,
    pageMyAccount,
};