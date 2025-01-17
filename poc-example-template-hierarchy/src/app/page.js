export default function Home() {
  return <>
  <div>
  <h1 className="text-2xl font-bold w-full my-4">Home</h1>
    <p className="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative">
      This is the homepage for the site. Even though there is routing for /index/page.js, as we are using next.config.mjs to map this, the file system takes preference. If we did this in middleware we might be able to overwrite this but we also won't be able to cache using ISR.
    </p>
  </div>
  </>;
}
