import React, { useMemo, useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    Bars3Icon,
    MagnifyingGlassIcon,
    XMarkIcon,
    SparklesIcon,
    MapPinIcon,
} from '@heroicons/react/24/outline';

export default function MainLayout({ title, children }) {
    const { auth } = usePage().props;
    const [mobileOpen, setMobileOpen] = useState(false);
    const [quickSearch, setQuickSearch] = useState('');
    const dashboardHref =
        auth?.user?.role === 'admin' ? '/admin/dashboard' : auth?.user?.role === 'provider' ? '/provider/dashboard' : '/dashboard';
    const links = useMemo(() => {
        const base = [{ href: '/', label: 'Home' }];

        if (!auth?.user) {
            return [...base, { href: '/login', label: 'Login' }, { href: '/register', label: 'Sign Up' }];
        }

        if (auth.user.role === 'renter') {
            return [
                ...base,
                { href: '/user/profile', label: 'Profile' },
                { href: '/renter/search', label: 'Search' },
                { href: '/renter/bookings', label: 'Bookings' },
                { href: '/renter/wishlist', label: 'Wishlist' },
            ];
        }

        if (auth.user.role === 'provider') {
            return [
                ...base,
                { href: '/provider/profile', label: 'Profile' },
                { href: '/provider/items', label: 'Inventory' },
                { href: '/provider/bookings', label: 'Bookings' },
                { href: '/provider/locations', label: 'Locations' },
                { href: '/provider/analytics', label: 'Analytics' },
            ];
        }

        return [
            ...base,
            { href: '/admin/users', label: 'Users' },
            { href: '/admin/providers', label: 'Providers' },
            { href: '/admin/categories', label: 'Categories' },
            { href: '/admin/tags', label: 'Tags' },
        ];
    }, [auth]);

    const initials = useMemo(() => {
        if (!auth?.user) {
            return null;
        }

        return `${auth.user.first_name?.[0] ?? ''}${auth.user.last_name?.[0] ?? ''}`.toUpperCase();
    }, [auth]);

    const submitQuickSearch = (event) => {
        event.preventDefault();
        const query = quickSearch.trim();

        router.get('/browse', query ? { keyword: query } : {}, { preserveScroll: true });
    };

    return (
        <div className="min-h-screen pb-10">
            <Head title={title} />

            <header className="sticky top-0 z-50 border-b border-white/60 bg-white/80 backdrop-blur">
                <div className="shell flex h-20 items-center justify-between">
                    <Link href="/" className="group inline-flex items-center gap-3">
                        <div className="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-brand-500 to-coral-500 text-white shadow-md">
                            <SparklesIcon className="h-5 w-5" />
                        </div>
                        <div>
                            <p className="font-display text-xl font-bold leading-none text-slate-900">RentFit</p>
                            <p className="text-xs font-semibold uppercase tracking-widest text-brand-600">
                                Geo Rental
                            </p>
                        </div>
                    </Link>

                    <nav className="hidden items-center gap-2 md:flex">
                        {links.map((link) => (
                            <Link
                                key={link.href}
                                href={link.href}
                                className="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-brand-50 hover:text-brand-700"
                            >
                                {link.label}
                            </Link>
                        ))}
                    </nav>

                    <div className="hidden items-center gap-3 lg:flex">
                        <form onSubmit={submitQuickSearch} className="relative">
                            <MagnifyingGlassIcon className="pointer-events-none absolute left-3 top-3 h-4 w-4 text-slate-400" />
                            <input
                                value={quickSearch}
                                onChange={(event) => setQuickSearch(event.target.value)}
                                className="input-shell h-10 w-72 pl-9 pr-3 text-sm"
                                placeholder="Search tuxedo, gown, barong..."
                            />
                        </form>

                        {auth?.user ? (
                            <>
                                <Link href={dashboardHref} className="btn-soft text-sm">
                                    Dashboard
                                </Link>
                                <Link href="/logout" method="post" as="button" className="btn-soft text-sm">
                                    Logout
                                </Link>
                                <div className="inline-flex h-10 min-w-10 items-center justify-center rounded-xl bg-slate-900 px-3 font-semibold text-white">
                                    {initials}
                                </div>
                            </>
                        ) : (
                            <>
                                <Link href="/login" className="btn-soft text-sm">
                                    Login
                                </Link>
                                <Link href="/register" className="btn-primary text-sm">
                                    Sign up
                                </Link>
                            </>
                        )}
                    </div>

                    <button
                        type="button"
                        className="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 md:hidden"
                        onClick={() => setMobileOpen((open) => !open)}
                        aria-label="Toggle menu"
                    >
                        {mobileOpen ? <XMarkIcon className="h-5 w-5" /> : <Bars3Icon className="h-5 w-5" />}
                    </button>
                </div>

                {mobileOpen && (
                    <div className="border-t border-slate-200 bg-white px-4 pb-4 pt-3 md:hidden">
                        <form onSubmit={submitQuickSearch} className="mb-3 flex items-center gap-2">
                            <input
                                value={quickSearch}
                                onChange={(event) => setQuickSearch(event.target.value)}
                                className="input-shell h-10 flex-1 text-sm"
                                placeholder="Search..."
                            />
                            <button className="btn-primary h-10 px-4 text-sm" type="submit">
                                Go
                            </button>
                        </form>

                        <div className="grid gap-1">
                            {links.map((link) => (
                                <Link
                                    key={link.href}
                                    href={link.href}
                                    className="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-brand-50"
                                    onClick={() => setMobileOpen(false)}
                                >
                                    {link.label}
                                </Link>
                            ))}

                            {auth?.user ? (
                                <>
                                    <Link
                                        href={dashboardHref}
                                        className="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-brand-50"
                                        onClick={() => setMobileOpen(false)}
                                    >
                                        Dashboard
                                    </Link>
                                    <Link
                                        href="/logout"
                                        method="post"
                                        as="button"
                                        className="rounded-lg px-3 py-2 text-left text-sm font-semibold text-slate-700 hover:bg-brand-50"
                                        onClick={() => setMobileOpen(false)}
                                    >
                                        Logout
                                    </Link>
                                </>
                            ) : (
                                <>
                                    <Link
                                        href="/login"
                                        className="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-brand-50"
                                        onClick={() => setMobileOpen(false)}
                                    >
                                        Login
                                    </Link>
                                    <Link
                                        href="/register"
                                        className="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-brand-50"
                                        onClick={() => setMobileOpen(false)}
                                    >
                                        Sign up
                                    </Link>
                                </>
                            )}
                        </div>
                    </div>
                )}
            </header>

            <main className="shell pt-8">
                {children}
            </main>

            <footer className="shell mt-16">
                <div className="glass-card px-6 py-5 sm:px-8">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p className="font-display text-lg font-bold text-slate-900">Rent better, dress smarter.</p>
                            <p className="text-sm text-slate-600">
                                Built with Laravel + React. Explore local inventory with live availability.
                            </p>
                        </div>
                        <div className="inline-flex items-center gap-2 rounded-full bg-brand-50 px-4 py-2 text-sm font-semibold text-brand-700">
                            <MapPinIcon className="h-4 w-4" />
                            Geo-tagged marketplace ready
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    );
}
