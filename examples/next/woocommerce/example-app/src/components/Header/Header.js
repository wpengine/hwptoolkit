"use client";
import Link from "next/link";
import NavigationItem from "./NavigationItem";
import { flatListToHierarchical } from "@/lib/utils";
import { useRouter } from "next/router";
import { useAuth } from "@/lib/auth/AuthProvider";

export default function Header({ headerData }) {
  const router = useRouter();

  const settingsData = headerData?.settings;
  const navigationData = headerData?.navigation;

  if (!headerData) {
    return;
  }
  //auth
  const { tokens, isLoading, logout, refreshAuth } = useAuth();
  const isAuthenticated = !!tokens?.authToken;

  const siteInfo = {
    title: settingsData?.generalSettings?.title || "My Site",
  };

  const flatMenuItems = navigationData?.menu?.menuItems?.nodes || [];
  const menuItems = flatListToHierarchical(flatMenuItems);

  const isActive = (item) => {
    if (!item.uri) return false;
    let cleanUri = item.uri.startsWith("/") ? item.uri.substring(1) : item.uri;
    cleanUri = cleanUri.endsWith("/") ? cleanUri.slice(0, -1) : cleanUri;
    return `/${cleanUri}` === router.asPath;
  };

  return (
    <header className="header">
      <div className="main-header-wrapper">
        <div className="site-title-wrapper">
          <Link href="/">
            <span>{siteInfo.title}</span>
          </Link>
        </div>

        <nav className="nav">
          {menuItems.length > 0 && (
            <>
              {menuItems.map((item) => (
                <NavigationItem
                  key={item.id}
                  item={item}
                  isActive={isActive(item)}
                />
              ))}
            </>
          )}
          <div><Link href="/cart">Cart</Link></div>
         {isAuthenticated ? (
                <div>
                  <button
                    onClick={logout}
                    className="text-blue-600 hover:underline mr-2"
                  >
                    Sign out
                  </button>
                </div>
              ) : (
                <Link href="/login" className="text-blue-600 hover:underline">
                  Login
                </Link>
              )}{" "}
        </nav>
      </div>

      <style jsx>{`
        .header {
          background: white;
          border-bottom: 1px solid #e1e5e9;
          padding: 1rem 0;
        }

        .main-header-wrapper {
          max-width: 1200px;
          margin: 0 auto;
          padding: 0 1rem;
          display: flex;
          justify-content: space-between;
          align-items: center;
        }

        .site-title-wrapper a {
          text-decoration: none;
          color: #2c3e50;
          font-size: 1.5rem;
          font-weight: bold;
        }

        .nav {
          display: flex;
          gap: 1rem;
        }

        @media (max-width: 768px) {
          .main-header-wrapper {
            flex-direction: column;
            gap: 1rem;
          }
        }
      `}</style>
    </header>
  );
}
