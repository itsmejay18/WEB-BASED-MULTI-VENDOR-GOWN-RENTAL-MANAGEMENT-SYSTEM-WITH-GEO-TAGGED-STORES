import React from 'react';
import { Link, router } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

const money = (value) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0,
    }).format(value || 0);

export default function RenterBookingsIndex({ bookings }) {
    return (
        <MainLayout title="My Bookings">
            <section className="space-y-5">
                <div className="glass-card p-5">
                    <h1 className="text-2xl font-bold">My Bookings</h1>
                    <p className="text-sm text-slate-600">Track your upcoming and completed rentals.</p>
                </div>

                <div className="glass-card overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th className="px-4 py-3">Booking</th>
                                <th className="px-4 py-3">Item</th>
                                <th className="px-4 py-3">Provider</th>
                                <th className="px-4 py-3">Dates</th>
                                <th className="px-4 py-3">Amount</th>
                                <th className="px-4 py-3">Status</th>
                                <th className="px-4 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 bg-white">
                            {bookings.data.map((booking) => (
                                <tr key={booking.id}>
                                    <td className="px-4 py-3 font-semibold">{booking.booking_number}</td>
                                    <td className="px-4 py-3">{booking.item_name || '-'}</td>
                                    <td className="px-4 py-3">{booking.provider_name || '-'}</td>
                                    <td className="px-4 py-3 text-xs text-slate-600">
                                        {booking.start_date} to {booking.end_date}
                                    </td>
                                    <td className="px-4 py-3 font-semibold text-brand-700">{money(booking.total_amount)}</td>
                                    <td className="px-4 py-3">
                                        <span className="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold uppercase tracking-wider text-slate-700">
                                            {booking.status}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex gap-2">
                                            <Link href={`/renter/bookings/${booking.id}`} className="btn-soft !px-3 !py-1.5 text-xs">
                                                View
                                            </Link>
                                            {['pending', 'approved'].includes(booking.status) ? (
                                                <button
                                                    type="button"
                                                    className="rounded-xl border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50"
                                                    onClick={() => {
                                                        const reason = window.prompt('Cancellation reason (optional)');
                                                        router.post(`/renter/bookings/${booking.id}/cancel`, reason ? { reason } : {});
                                                    }}
                                                >
                                                    Cancel
                                                </button>
                                            ) : null}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </section>
        </MainLayout>
    );
}

