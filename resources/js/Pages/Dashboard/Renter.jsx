import React from 'react';
import { Link } from '@inertiajs/react';
import { CalendarDaysIcon, HeartIcon, ShoppingBagIcon, SparklesIcon } from '@heroicons/react/24/outline';
import MainLayout from '../../Layouts/MainLayout';

const money = (value) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0,
    }).format(value || 0);

export default function RenterDashboard({ stats, recentBookings, wishlist }) {
    return (
        <MainLayout title="Renter Dashboard">
            <section className="space-y-6">
                <div className="glass-card bg-gradient-to-r from-brand-700 to-brand-500 p-6 text-white">
                    <p className="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-widest">
                        <SparklesIcon className="h-4 w-4" />
                        Account Overview
                    </p>
                    <h1 className="mt-3 text-3xl font-bold text-white">Renter Dashboard</h1>
                    <p className="mt-1 text-sm text-white/90">
                        Track bookings, manage wishlists, and discover what to rent next.
                    </p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article className="glass-card p-5">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Bookings</p>
                        <p className="mt-2 text-3xl font-bold text-slate-900">{stats.total_bookings}</p>
                        <ShoppingBagIcon className="mt-3 h-5 w-5 text-brand-600" />
                    </article>
                    <article className="glass-card p-5">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Upcoming</p>
                        <p className="mt-2 text-3xl font-bold text-slate-900">{stats.upcoming_bookings}</p>
                        <CalendarDaysIcon className="mt-3 h-5 w-5 text-brand-600" />
                    </article>
                    <article className="glass-card p-5">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Active Rentals</p>
                        <p className="mt-2 text-3xl font-bold text-slate-900">{stats.active_bookings}</p>
                        <CalendarDaysIcon className="mt-3 h-5 w-5 text-coral-600" />
                    </article>
                    <article className="glass-card p-5">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Wishlist</p>
                        <p className="mt-2 text-3xl font-bold text-slate-900">{stats.wishlist_items}</p>
                        <HeartIcon className="mt-3 h-5 w-5 text-coral-600" />
                    </article>
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.3fr_1fr]">
                    <div className="glass-card overflow-hidden">
                        <div className="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                            <h2 className="text-lg font-bold">Recent Bookings</h2>
                            <Link href="/browse" className="text-sm font-semibold text-brand-700 hover:text-brand-800">
                                Find more items
                            </Link>
                        </div>
                        <div className="divide-y divide-slate-100">
                            {recentBookings.length > 0 ? (
                                recentBookings.map((booking) => (
                                    <div key={booking.id} className="flex items-center justify-between px-5 py-4">
                                        <div>
                                            <p className="font-semibold text-slate-900">{booking.item_name || 'Item unavailable'}</p>
                                            <p className="text-xs uppercase tracking-wide text-slate-500">
                                                {booking.provider_name || 'Provider'} • {booking.booking_number}
                                            </p>
                                            <p className="mt-1 text-sm text-slate-600">
                                                {booking.start_date} to {booking.end_date}
                                            </p>
                                        </div>
                                        <div className="text-right">
                                            <p className="font-semibold text-brand-700">{money(booking.total_amount)}</p>
                                            <span className="mt-1 inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold uppercase tracking-wider text-slate-700">
                                                {booking.status}
                                            </span>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <p className="px-5 py-8 text-sm text-slate-600">No bookings yet. Start with a quick search.</p>
                            )}
                        </div>
                    </div>

                    <div className="glass-card overflow-hidden">
                        <div className="border-b border-slate-100 px-5 py-4">
                            <h2 className="text-lg font-bold">Wishlist Snapshot</h2>
                        </div>
                        <div className="divide-y divide-slate-100">
                            {wishlist.length > 0 ? (
                                wishlist.map((item) => (
                                    <Link key={item.id} href={`/item/${item.slug}`} className="flex items-center gap-3 px-5 py-4 hover:bg-slate-50">
                                        <div className="h-12 w-12 overflow-hidden rounded-lg bg-slate-100">
                                            {item.photo_url ? (
                                                <img src={item.photo_url} alt={item.name} className="h-full w-full object-cover" />
                                            ) : null}
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate font-semibold text-slate-900">{item.name}</p>
                                            <p className="truncate text-xs text-slate-500">{item.provider_name || 'Provider'}</p>
                                        </div>
                                        <p className="text-sm font-semibold text-brand-700">{money(item.lowest_price)}</p>
                                    </Link>
                                ))
                            ) : (
                                <p className="px-5 py-8 text-sm text-slate-600">Your wishlist is empty.</p>
                            )}
                        </div>
                    </div>
                </div>
            </section>
        </MainLayout>
    );
}

