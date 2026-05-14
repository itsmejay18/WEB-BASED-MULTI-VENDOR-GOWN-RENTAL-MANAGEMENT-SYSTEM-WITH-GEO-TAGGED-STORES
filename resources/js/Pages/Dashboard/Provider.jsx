import React from 'react';
import { Link } from '@inertiajs/react';
import { CalendarDaysIcon, CurrencyDollarIcon, ExclamationTriangleIcon, SparklesIcon, Squares2X2Icon } from '@heroicons/react/24/outline';
import MainLayout from '../../Layouts/MainLayout';

const money = (value) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0,
    }).format(value || 0);

export default function ProviderDashboard({ provider, stats, recentBookings, popularItems }) {
    const verificationPending = provider.verification_status !== 'verified';

    return (
        <MainLayout title="Provider Dashboard">
            <section className="space-y-6">
                <div className="glass-card bg-gradient-to-r from-slate-900 via-brand-800 to-brand-600 p-6 text-white">
                    <p className="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-widest">
                        <SparklesIcon className="h-4 w-4" />
                        Provider Portal
                    </p>
                    <h1 className="mt-3 text-3xl font-bold text-white">{provider.business_name}</h1>
                    <p className="mt-1 text-sm text-white/85">
                        Manage inventory, monitor bookings, and grow your rental business.
                    </p>
                </div>

                {verificationPending ? (
                    <div className="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
                        <p className="flex items-center gap-2 text-sm font-semibold">
                            <ExclamationTriangleIcon className="h-5 w-5" />
                            Verification status: {provider.verification_status}
                        </p>
                        <p className="mt-1 text-sm">Your listings can be managed now, but public visibility depends on verification.</p>
                    </div>
                ) : null}

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article className="glass-card p-5">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Items</p>
                        <p className="mt-2 text-3xl font-bold text-slate-900">{stats.total_items}</p>
                        <p className="mt-1 text-xs text-slate-500">{stats.active_items} active</p>
                        <Squares2X2Icon className="mt-3 h-5 w-5 text-brand-600" />
                    </article>
                    <article className="glass-card p-5">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Bookings</p>
                        <p className="mt-2 text-3xl font-bold text-slate-900">{stats.total_bookings}</p>
                        <p className="mt-1 text-xs text-slate-500">{stats.pending_bookings} pending action</p>
                        <CalendarDaysIcon className="mt-3 h-5 w-5 text-brand-600" />
                    </article>
                    <article className="glass-card p-5">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Active Rentals</p>
                        <p className="mt-2 text-3xl font-bold text-slate-900">{stats.active_bookings}</p>
                        <CalendarDaysIcon className="mt-3 h-5 w-5 text-coral-600" />
                    </article>
                    <article className="glass-card p-5">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Revenue</p>
                        <p className="mt-2 text-3xl font-bold text-slate-900">{money(stats.total_revenue)}</p>
                        <CurrencyDollarIcon className="mt-3 h-5 w-5 text-coral-600" />
                    </article>
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.25fr_1fr]">
                    <div className="glass-card overflow-hidden">
                        <div className="border-b border-slate-100 px-5 py-4">
                            <h2 className="text-lg font-bold">Latest Bookings</h2>
                        </div>
                        <div className="divide-y divide-slate-100">
                            {recentBookings.length > 0 ? (
                                recentBookings.map((booking) => (
                                    <div key={booking.id} className="flex items-center justify-between px-5 py-4">
                                        <div>
                                            <p className="font-semibold text-slate-900">{booking.item_name || 'Item unavailable'}</p>
                                            <p className="text-xs uppercase tracking-wide text-slate-500">
                                                {booking.renter_name || 'Renter'} - {booking.booking_number}
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
                                <p className="px-5 py-8 text-sm text-slate-600">No booking activity yet.</p>
                            )}
                        </div>
                    </div>

                    <div className="glass-card overflow-hidden">
                        <div className="border-b border-slate-100 px-5 py-4">
                            <h2 className="text-lg font-bold">Popular Items</h2>
                        </div>
                        <div className="divide-y divide-slate-100">
                            {popularItems.length > 0 ? (
                                popularItems.map((item) => (
                                    <Link key={item.id} href={`/item/${item.slug}`} className="flex items-center gap-3 px-5 py-4 hover:bg-slate-50">
                                        <div className="h-12 w-12 overflow-hidden rounded-lg bg-slate-100">
                                            {item.photo_url ? (
                                                <img src={item.photo_url} alt={item.name} className="h-full w-full object-cover" />
                                            ) : null}
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate font-semibold text-slate-900">{item.name}</p>
                                            <p className="text-xs text-slate-500">{item.bookings_count} bookings</p>
                                        </div>
                                        <p className="text-sm font-semibold text-brand-700">{money(item.lowest_price)}</p>
                                    </Link>
                                ))
                            ) : (
                                <p className="px-5 py-8 text-sm text-slate-600">No items yet.</p>
                            )}
                        </div>
                    </div>
                </div>
            </section>
        </MainLayout>
    );
}


