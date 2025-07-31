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

export default { home, index, single };
