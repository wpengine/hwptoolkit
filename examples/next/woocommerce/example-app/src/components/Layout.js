import Header from "@/components/Header/Header";
import Footer from "@/components/Footer/Footer";
export default function Layout(pageProps) {
    return (
        <>
            <Header headerData={pageProps.pageProps.headerData} />
            <main id="main">
                <div className="container mx-auto px-4">
                    {pageProps.children}
                </div>
            </main>
            <Footer footerData={pageProps.pageProps.headerData} />
        </>
    );
}