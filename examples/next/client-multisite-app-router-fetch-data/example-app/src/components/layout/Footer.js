import React from "react";
import Link from "next/link";

const Footer = () => {
  {
    /*
    Note: Currently the default theme of Twenty Twenty Five does not have menu locations
    Therefore you cannot query menus out of the box with WPGraphQL. See - https://www.wpgraphql.com/docs/menus
    */
  }
  const menuItemClass = "my-4";
  const menuItemLinkClass = "hover:underline";
  return (
    <footer className="bg-gray-800 text-white py-8">
      <div className="container mx-auto px-4">
        <div className="grid grid-cols-1 gap-8 md:grid-cols-3">
          <div>
            <h3 className="text-lg font-semibold mb-4">Our Company</h3>
            <ul>
              <li className={menuItemClass}>
                <Link href="/about-us" className={menuItemLinkClass}>
                  About Us
                </Link>
              </li>
              <li className={menuItemClass}>
                <Link href="/bookings" className={menuItemLinkClass}>
                  Bookings
                </Link>
              </li>
              <li className={menuItemClass}>
                <Link href="/privacy-policy" className={menuItemLinkClass}>
                  Privacy Policy
                </Link>
              </li>
            </ul>
          </div>
          <div>
            <h3 className="text-lg font-semibold mb-4">Categories</h3>
            <ul>
              <li className={menuItemClass}>
                <Link href="/category/lifestyle" className={menuItemLinkClass}>
                  Lifestyle
                </Link>
              </li>
              <li className={menuItemClass}>
                <Link
                  href="/category/mental-health"
                  className={menuItemLinkClass}
                >
                  Mental Health
                </Link>
              </li>
              <li className={menuItemClass}>
                <Link href="/category/travel" className={menuItemLinkClass}>
                  Travel
                </Link>
              </li>
            </ul>
          </div>
          <div>
            <h3 className="text-lg font-semibold mb-4">Tags</h3>
            <ul>
              <li className={menuItemClass}>
                <Link href="/tag/mindfulness" className={menuItemLinkClass}>
                  Mindfulness
                </Link>
              </li>
              <li className={menuItemClass}>
                <Link href="/tag/nutrition" className={menuItemLinkClass}>
                  Nutrition
                </Link>
              </li>
              <li className={menuItemClass}>
                <Link href="/tag/mindset" className={menuItemLinkClass}>
                  Mindset
                </Link>
              </li>
              <li className={menuItemClass}>
                <Link href="/tag/wellness" className={menuItemLinkClass}>
                  Wellness
                </Link>
              </li>
            </ul>
          </div>
        </div>
        <div className="mt-8 text-center">
          <p>
            &copy; {new Date().getFullYear()} Your Company. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
