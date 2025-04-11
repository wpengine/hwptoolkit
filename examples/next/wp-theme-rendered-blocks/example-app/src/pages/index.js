import Head from "next/head";

export default function Home() {
  return (
    <>
      <Head>
        <title>WordPress Theme Styles Example</title>
      </Head>
      <main>
        <h1>WordPress Global Styles in Next.js</h1>
        <p>
          This example shows how to fetch and apply global styles from a WordPress site using WPGraphQL. The theme styles you're seeing here were fetched from the WP instance and applied globally via CSS.
        </p>
        <p>
          You can now render blocks in another part of your app and they will inherit the theme styling from your WordPress site.
        </p>
      </main>
    </>
  );
}
