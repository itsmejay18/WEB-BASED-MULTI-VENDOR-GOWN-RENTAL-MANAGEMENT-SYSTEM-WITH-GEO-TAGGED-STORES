import React from 'react';
import { BuildingStorefrontIcon, CalendarDaysIcon, CurrencyDollarIcon, SparklesIcon, UserGroupIcon } from '@heroicons/react/24/outline';
import MainLayout from '../../Layouts/MainLayout';

const money = (value) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0,
    }).format(value || 0);

export default function AdminDashboard({ stats, pendingProviders, recentUsers, recentBookings }) {
    return (
        <MainLayout title="Admin Dashboard">
            <section className="space-y-6">
                <div className="glass-card bg-gradient-to-r from-slate-900 via-coral-700 to-coral-500 p-6 text-white">
                    <p className="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-widest">
                        <SparklesIcon className="h-4 w-4" />
                        Platform Control
                    </p>
                    <h1 className="mt-3 text-3xl font-bold text-white">Admin Dashboard</h1>
                    <p className="mt-1 text-sm text-white/85">Monitor growth, providers, and booking health across the marketplace.</p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article className="glass-card p-5">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Users</p>
                        <p className="mt-2 text-3xl font-bold text-slate-900">{stats.total_users}</p>
                        <p className="mt-1 text-xs text-slate-500">{stats.total_renters} renters</p>
                        <UserGroupIcon className="mt-3 h-5 w-5 text-brand-600" />
                    </article>
                    <article className="glass-card p-5">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Providers</p>
                        <p className="mt-2 text-3xl font-bold text-slate-900">{stats.total_providers}</p>
                        <p className="mt-1 text-xs text-amber-700">{stats.pending_provider_reviews} pending review</p>
                        <BuildingStorefrontIcon className="mt-3 h-5 w-5 text-brand-600" />
                    </article>
                    <article className="glass-card p-5">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Bookings</p>
                        <p className="mt-2 text-3xl font-bold text-slate-900">{stats.total_bookings}</p>
                        <CalendarDaysIcon className="mt-3 h-5 w-5 text-coral-600" />
                    </article>
                    <article className="glass-card p-5">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Revenue</p>
                        <p className="mt-2 text-3xl font-bold text-slate-900">{money(stats.total_revenue)}</p>
                        <CurrencyDollarIcon className="mt-3 h-5 w-5 text-coral-600" />
                    </article>
                </div>

                <div className="grid gap-6 xl:grid-cols-3">
                    <div className="glass-card overflow-hidden">
                        <div className="border-b border-slate-100 px-5 py-4">
                            <h2 className="text-lg font-bold">Pending Providers</h2>
                        </div>
                        <div className="divide-y divide-slate-100">
                            {pendingProviders.length > 0 ? (
                                pendingProviders.map((provider) => (
                                    <div key={provider.id} className="px-5 py-4">
                                        <p className="font-semibold text-slate-900">{provider.business_name}</p>
                                        <p className="text-sm text-slate-600">{provider.owner_name || 'Owner'}</p>
                                        <p className="text-xs text-slate-500">{provider.owner_email}</p>
                                    </div>
                                ))
                            ) : (
                                <p className="px-5 py-8 text-sm text-slate-600">No pending verification requests.</p>
                            )}
                        </div>
                    </div>

                    <div className="glass-card overflow-hidden">
                        <div className="border-b border-slate-100 px-5 py-4">
                            <h2 className="text-lg font-bold">Recent Users</h2>
                        </div>
                        <div className="divide-y divide-slate-100">
                            {recentUsers.map((user) => (
                                <div key={user.id} className="px-5 py-4">
                                    <p className="font-semibold text-slate-900">{user.name}</p>
                                    <p className="text-sm text-slate-600">{user.email}</p>
                                    <p className="text-xs uppercase tracking-wider text-slate-500">{user.role}</p>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="glass-card overflow-hidden">
                        <div className="border-b border-slate-100 px-5 py-4">
                            <h2 className="text-lg font-bold">Recent Bookings</h2>
                        </div>
                        <div className="divide-y divide-slate-100">
                            {recentBookings.map((booking) => (
                                <div key={booking.id} className="px-5 py-4">
                                    <p className="font-semibold text-slate-900">{booking.item_name || 'Item unavailable'}</p>
                                    <p className="text-sm text-slate-600">
                                        {booking.renter_name || 'Renter'} • {booking.provider_name || 'Provider'}
                                    </p>
                                    <p className="text-xs uppercase tracking-wider text-slate-500">{booking.status}</p>
                                    <p className="mt-1 text-sm font-semibold text-brand-700">{money(booking.total_amount)}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </section>
        </MainLayout>
    );
}

