import React from 'react';

const Footer = () => {

    {/* 
    Note: Currently the default theme of Twenty Twenty Five does not have menu locations
    Therefore you cannot query menus out of the box with WPGraphQL. See - https://www.wpgraphql.com/docs/menus
    */}
    const menuItemClass = 'my-4';
    const menuItemLinkClass = 'hover:underline';
    return (
        <footer className="bg-gray-800 text-white py-8">
            <div className="container mx-auto px-4">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <h3 className="text-lg font-semibold mb-4">Our Company</h3>
                        <ul>
                            <li className={menuItemClass}><a href="/about-us" className={menuItemLinkClass}>About Us</a></li>
                            <li className={menuItemClass}><a href="/contact-us" className={menuItemLinkClass}>Contact Us</a></li>
                            <li className={menuItemClass}><a href="/privacy-policy" className={menuItemLinkClass}>Privacy Policy</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 className="text-lg font-semibold mb-4">Latest News</h3>
                        <ul>
                            <li className={menuItemClass}><a href="/blog" className={menuItemLinkClass}>Blog</a></li>
                            <li className={menuItemClass}><a href="/events" className={menuItemLinkClass}>Events</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 className="text-lg font-semibold mb-4">Other Links</h3>
                        <ul>
                            <li className={menuItemClass}><a href="/category/lifestyle" className={menuItemLinkClass}>Lifestyle</a></li>
                            <li className={menuItemClass}><a href="/category/sport" className={menuItemLinkClass}>Sport</a></li>
                            <li className={menuItemClass}><a href="/tag/interior-design" className={menuItemLinkClass}>Interior Design</a></li>
                        </ul>
                    </div>
                </div>
                <div className="mt-8 text-center">
                    <p>&copy; {new Date().getFullYear()} Your Company. All rights reserved.</p>
                </div>
            </div>
        </footer>
    );
};

export default Footer;
