import React from 'react';
import MainLayout from '../../../Layouts/MainLayout';

const money = (value) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0,
    }).format(value || 0);

export default function ProviderAnalyticsIndex({ monthlyRevenue, statusBreakdown, topItems }) {
    return (
        <MainLayout title="Provider Analytics">
            <section className="space-y-6">
                <div className="glass-card p-5">
                    <h1 className="text-2xl font-bold">Analytics</h1>
                    <p className="text-sm text-slate-600">Revenue trend and booking status insights.</p>
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <div className="glass-card p-5">
                        <h2 className="text-lg font-bold">Monthly Revenue</h2>
                        <div className="mt-4 space-y-2">
                            {monthlyRevenue.map((row) => (
                                <div key={row.month} className="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2">
                                    <span className="text-sm font-semibold text-slate-700">{row.month}</span>
                                    <span className="text-sm font-bold text-brand-700">{money(row.revenue)}</span>
                                </div>
                            ))}
                            {monthlyRevenue.length === 0 ? <p className="text-sm text-slate-600">No data yet.</p> : null}
                        </div>
                    </div>

                    <div className="glass-card p-5">
                        <h2 className="text-lg font-bold">Booking Status Breakdown</h2>
                        <div className="mt-4 space-y-2">
                            {Object.entries(statusBreakdown).map(([status, total]) => (
                                <div key={status} className="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2">
                                    <span className="text-sm font-semibold text-slate-700">{status}</span>
                                    <span className="text-sm font-bold text-slate-900">{total}</span>
                                </div>
                            ))}
                            {Object.keys(statusBreakdown).length === 0 ? <p className="text-sm text-slate-600">No booking activity.</p> : null}
                        </div>
                    </div>
                </div>

                <div className="glass-card p-5">
                    <h2 className="text-lg font-bold">Top Items</h2>
                    <div className="mt-4 grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                        {topItems.map((item) => (
                            <div key={item.id} className="rounded-xl bg-slate-50 px-3 py-2">
                                <p className="font-semibold text-slate-900">{item.name}</p>
                                <p className="text-xs text-slate-500">{item.bookings_count} bookings</p>
                            </div>
                        ))}
                        {topItems.length === 0 ? <p className="text-sm text-slate-600">No inventory data.</p> : null}
                    </div>
                </div>
            </section>
        </MainLayout>
    );
}

