"use client";
import Link from "next/link";
import Image from "next/image";
import { useState } from "react";
import { useAuthAdmin } from "@/lib/providers/AuthProvider";
import { useCart } from "@/lib/providers/CartProvider";
import NavigationItem from "./NavigationItem";
import MiniCart from "../Cart/MiniCart";
import { flatListToHierarchical } from "@/lib/utils";
import { useRouter } from "next/router";
import CartIconSVG from "@/assets/icons/cart-shopping-light-full.svg";
import UserIconSVG from "@/assets/icons/user-regular-full.svg";
import LoginSVG from "@/assets/icons/arrow-right-to-bracket-solid-full.svg";
import type { HeaderData, NavigationItem as NavItem } from "@/interfaces/navigation.interface";

// Wrapper components for imported SVGs
const CartIcon = ({ className = "w-6 h-6" }) => (
	<Image src={CartIconSVG} alt="Shopping Cart" width={24} height={24} className={className} />
);

const UserIcon = ({ className = "w-5 h-5" }) => (
	<Image src={UserIconSVG} alt="User Account" width={20} height={20} className={className} />
);

const LoginIcon = ({ className = "w-5 h-5" }) => (
	<Image src={LoginSVG} alt="User Account" width={20} height={20} className={className} />
);

// Dropdown arrow SVG
const DropdownArrow = ({ className = "w-4 h-4" }) => (
	<svg className={className} fill="none" stroke="currentColor" viewBox="0 0 24 24">
		<path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
	</svg>
);

export default function Header({ headerData }: { headerData: HeaderData | null }) {
	const router = useRouter();
	const { user, logout } = useAuthAdmin();
	const { cartItemCount } = useCart();
	const [isMiniCartOpen, setIsMiniCartOpen] = useState(false);
	const isAuthenticated = !!user;

	const settingsData = headerData?.settings;
	const navigationData = headerData?.navigation;

	if (!headerData) {
		return;
	}

	const siteInfo = {
		title: settingsData?.generalSettings?.title || "My Site",
	};

	const flatMenuItems = navigationData?.menu?.menuItems?.nodes || [];
	const menuItems = flatListToHierarchical(flatMenuItems);

	const isActive = (item: NavItem) => {
		if (!item.uri) return false;
		let cleanUri = item.uri.startsWith("/") ? item.uri.substring(1) : item.uri;
		cleanUri = cleanUri.endsWith("/") ? cleanUri.slice(0, -1) : cleanUri;
		return `/${cleanUri}` === router.asPath;
	};

	const handleCartClick = (e) => {
		e.preventDefault();
		setIsMiniCartOpen(true);
	};

	const closeMiniCart = () => {
		setIsMiniCartOpen(false);
	};

	return (
		<>
			<header className="header">
				<div className="container mx-auto px-4 main-header-wrapper">
					<div className="site-title-wrapper">
						<Link href="/">
							<span>{siteInfo.title}</span>
						</Link>
					</div>

					<nav className="nav">
						{/* Main navigation menu */}
						{menuItems.length > 0 && (
							<div className="main-nav">
								{menuItems.map((item) => (
									<NavigationItem key={item.id} item={item} isActive={isActive(item)} />
								))}
							</div>
						)}

						{/* Action items with icons */}
						<div className="action-items">
							{/* Auth */}
							{isAuthenticated ? (
								<div className="auth-container group relative">
									<div className="action-item user-dropdown-trigger">
										<UserIcon className="icon" />
										<DropdownArrow className="dropdown-arrow transition-transform duration-200 group-hover:scale-110" />
									</div>

									{/* User Dropdown Menu */}
									<div className="user-dropdown absolute top-full right-0 min-w-[200px] bg-white shadow-lg border border-gray-200 rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform translate-y-2 group-hover:translate-y-0 z-50">
										{/* Dropdown arrow */}
										<div className="absolute top-0 right-4 -translate-y-1 w-2 h-2 bg-white border-l border-t border-gray-200 transform rotate-45"></div>

										<div className="py-2">
											<div className="px-4 py-3 border-b border-gray-100">
												<p className="text-sm font-medium text-gray-900">Hi, {user?.name}</p>
											</div>

											<Link
												href="/my-account"
												className="dropdown-item block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition-colors duration-150"
											>
												<span className="flex items-center gap-3">
													<svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
														<path
															strokeLinecap="round"
															strokeLinejoin="round"
															strokeWidth={2}
															d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
														/>
													</svg>
													My Account
												</span>
											</Link>

											<Link
												href="/my-account?tab=orders"
												className="dropdown-item block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition-colors duration-150 border-b border-gray-100"
											>
												<span className="flex items-center gap-3">
													<svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
														<path
															strokeLinecap="round"
															strokeLinejoin="round"
															strokeWidth={2}
															d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
														/>
													</svg>
													Order History
												</span>
											</Link>

											<Link
												href="/my-account?tab=addresses"
												className="dropdown-item block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition-colors duration-150"
											>
												<span className="flex items-center gap-3">
													<svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
														<path
															strokeLinecap="round"
															strokeLinejoin="round"
															strokeWidth={2}
															d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
														/>
														<path
															strokeLinecap="round"
															strokeLinejoin="round"
															strokeWidth={2}
															d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
														/>
													</svg>
													Addresses
												</span>
											</Link>

											<Link
												href="/wishlist"
												className="dropdown-item block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition-colors duration-150 border-b border-gray-100"
											>
												<span className="flex items-center gap-3">
													<svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
														<path
															strokeLinecap="round"
															strokeLinejoin="round"
															strokeWidth={2}
															d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
														/>
													</svg>
													Wishlist
												</span>
											</Link>

											<button
												onClick={logout}
												className="dropdown-item w-full text-left block px-4 py-3 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors duration-150"
											>
												<span className="flex items-center gap-3">
													<svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
														<path
															strokeLinecap="round"
															strokeLinejoin="round"
															strokeWidth={2}
															d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
														/>
													</svg>
													Sign Out
												</span>
											</button>
										</div>
									</div>
								</div>
							) : (
								<Link href="/my-account" title="Login" className="action-item login-link">
									<LoginIcon className="icon" />
								</Link>
							)}

							{/* Cart */}
							<button onClick={handleCartClick} className="action-item cart-button" title="View Cart">
								<div className="relative">
									<CartIcon className="icon" />
									{cartItemCount > 0 && (
										<span className="cart-count absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold">
											{cartItemCount > 99 ? "99+" : cartItemCount}
										</span>
									)}
								</div>
							</button>
						</div>
					</nav>
				</div>

				<style jsx>{`
					.header {
						background: white;
						border-bottom: 1px solid #e1e5e9;
						padding: 1rem 0;
						box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
					}

					.main-header-wrapper {
						display: flex;
						justify-content: space-between;
						align-items: center;
					}

					.site-title-wrapper a {
						text-decoration: none;
						color: #2c3e50;
						font-size: 1.75rem;
						font-weight: bold;
						transition: color 0.2s ease;
					}

					.site-title-wrapper a:hover {
						color: #3498db;
					}

					.nav {
						display: flex;
						align-items: center;
						gap: 0.5rem;
					}

					.main-nav {
						display: flex;
						gap: 1rem;
					}

					.action-items {
						display: flex;
						align-items: center;
						gap: 1rem;
						padding-left: 1rem;
						border-left: 1px solid #eee;
					}

					.action-item,
					.cart-button {
						display: flex;
						align-items: center;
						gap: 0.5rem;
						padding: 0.5rem 1rem;
						text-decoration: none;
						color: #64748b;
						background: transparent;
						border: none;
						border-radius: 8px;
						transition: all 0.2s ease;
						font-size: 0.875rem;
						font-weight: 500;
						cursor: pointer;
					}

					.action-item:hover,
					.cart-button:hover {
						color: #3498db;
						background: #f8fafc;
						transform: translateY(-1px);
					}

					.cart-button:hover {
						color: #16a085;
					}

					.login-link:hover {
						color: #2980b9;
					}

					.action-text {
						font-weight: 500;
					}

					.auth-container {
						position: relative;
					}

					/* Cart count badge */
					.cart-count {
						animation: cartPulse 0.3s ease-in-out;
					}

					@keyframes cartPulse {
						0% {
							transform: scale(1);
						}
						50% {
							transform: scale(1.2);
						}
						100% {
							transform: scale(1);
						}
					}

					/* User dropdown specific styles */
					.user-dropdown-trigger {
						cursor: pointer;
					}

					.user-dropdown-trigger:hover {
						color: #3498db;
						background: #f8fafc;
					}

					.user-dropdown {
						box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
						backdrop-filter: blur(10px);
					}

					.dropdown-item {
						transition: all 0.15s ease;
					}

					.dropdown-item:hover {
						transform: translateX(2px);
					}

					.dropdown-item:first-child {
						border-radius: 8px 8px 0 0;
					}

					.dropdown-item:last-child {
						border-radius: 0 0 8px 8px;
					}

					/* Icon styling */
					.icon {
						transition: all 0.2s ease;
					}

					.action-item:hover .icon,
					.cart-button:hover .icon {
						transform: scale(1.1);
					}

					/* Dropdown arrow animation */
					.group:hover .dropdown-arrow {
						transform: rotate(180deg);
					}

					/* Mobile responsive */
					@media (max-width: 1024px) {
						.action-text {
							display: none;
						}

						.action-item,
						.cart-button {
							padding: 0.5rem;
							min-width: 40px;
							justify-content: center;
						}

						.user-dropdown {
							right: -50px;
							min-width: 180px;
						}
					}

					@media (max-width: 768px) {
						.main-header-wrapper {
							flex-direction: column;
							gap: 1rem;
						}

						.nav {
							width: 100%;
							justify-content: space-between;
							gap: 1rem;
						}

						.main-nav {
							flex: 1;
							justify-content: center;
						}

						.action-items {
							gap: 0.5rem;
						}

						.action-text {
							display: block;
							font-size: 0.75rem;
						}

						.user-dropdown {
							right: -75px;
							min-width: 160px;
						}
					}

					@media (max-width: 480px) {
						.main-header-wrapper {
							padding: 0 0.5rem;
						}

						.site-title-wrapper a {
							font-size: 1.25rem;
						}

						.nav {
							flex-direction: column;
							gap: 0.75rem;
						}

						.main-nav {
							order: 2;
						}

						.action-items {
							order: 1;
							justify-content: center;
							width: 100%;
						}

						.user-dropdown {
							right: -100px;
						}
					}
				`}</style>
			</header>

			{/* Mini Cart Component */}
			<MiniCart isVisible={isMiniCartOpen} onClose={closeMiniCart} />
		</>
	);
}
