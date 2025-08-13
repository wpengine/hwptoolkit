import dynamic from "next/dynamic";

const home = dynamic(() => import("./home.js"), {
  loading: () => <p>Loading Home Template...</p>,
});

const index = dynamic(() => import("./default.js"), {
  loading: () => <p>Loading Index Template...</p>,
});

const single = dynamic(() => import("./single.js"), {
  loading: () => <p>Loading Single Template...</p>,
});

const frontPage = dynamic(() => import("./front-page.js"), {
  loading: () => <p>Loading Front Page Template...</p>,
});
const archiveProduct = dynamic(() => import("./archive-product.js"), {
  loading: () => <p>Loading Shop Template...</p>,
});
const page = dynamic(() => import("./page.js"), {
  loading: () => <p>Loading Page Template...</p>,
});
const singleProduct = dynamic(() => import("./single-product.js"), {
  loading: () => <p>Loading Product Template...</p>,
});
const pageCart = dynamic(() => import("./page-cart.js"), {
  loading: () => <p>Loading Cart Template...</p>,
});
export default { home, index, page, single, frontPage, archiveProduct, singleProduct, pageCart };
