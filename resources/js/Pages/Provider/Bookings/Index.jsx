import React from 'react';
import { Link, router } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

const money = (value) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0,
    }).format(value || 0);

export default function ProviderBookingsIndex({ bookings, status, statusOptions }) {
    const act = (bookingId, action, payload = {}) => {
        router.post(`/provider/bookings/${bookingId}/${action}`, payload);
    };

    return (
        <MainLayout title="Provider Bookings">
            <section className="space-y-5">
                <div className="glass-card flex flex-wrap items-center justify-between gap-3 p-5">
                    <div>
                        <h1 className="text-2xl font-bold">Booking Management</h1>
                        <p className="text-sm text-slate-600">Approve, reject, and track fulfillment status.</p>
                    </div>
                    <div className="flex items-center gap-2">
                        <span className="text-xs font-semibold uppercase tracking-widest text-slate-500">Filter</span>
                        <select
                            className="input-shell h-10 text-sm"
                            value={status || ''}
                            onChange={(event) => router.get('/provider/bookings', event.target.value ? { status: event.target.value } : {})}
                        >
                            <option value="">All statuses</option>
                            {statusOptions.map((option) => (
                                <option key={option} value={option}>
                                    {option}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>

                <div className="glass-card overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th className="px-4 py-3">Booking</th>
                                <th className="px-4 py-3">Renter</th>
                                <th className="px-4 py-3">Item</th>
                                <th className="px-4 py-3">Dates</th>
                                <th className="px-4 py-3">Amount</th>
                                <th className="px-4 py-3">Status</th>
                                <th className="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 bg-white">
                            {bookings.data.map((booking) => (
                                <tr key={booking.id}>
                                    <td className="px-4 py-3 font-semibold text-slate-900">{booking.booking_number}</td>
                                    <td className="px-4 py-3">
                                        <p>{booking.renter_name}</p>
                                        <p className="text-xs text-slate-500">{booking.renter_email}</p>
                                    </td>
                                    <td className="px-4 py-3">{booking.item_name || '-'}</td>
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
                                        <div className="flex flex-wrap gap-2">
                                            {booking.status === 'pending' ? (
                                                <>
                                                    <button className="btn-soft !px-2 !py-1 text-xs" onClick={() => act(booking.id, 'approve')}>
                                                        Approve
                                                    </button>
                                                    <button
                                                        className="rounded-xl border border-red-200 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-50"
                                                        onClick={() => {
                                                            const reason = window.prompt('Reason for rejection');
                                                            if (reason) act(booking.id, 'reject', { reason });
                                                        }}
                                                    >
                                                        Reject
                                                    </button>
                                                </>
                                            ) : null}
                                            {booking.status === 'approved' ? (
                                                <button className="btn-soft !px-2 !py-1 text-xs" onClick={() => act(booking.id, 'ready')}>
                                                    Mark Ready
                                                </button>
                                            ) : null}
                                            {booking.status === 'ready_for_pickup' ? (
                                                <button className="btn-soft !px-2 !py-1 text-xs" onClick={() => act(booking.id, 'picked-up')}>
                                                    Picked Up
                                                </button>
                                            ) : null}
                                            {booking.status === 'picked_up' ? (
                                                <button className="btn-soft !px-2 !py-1 text-xs" onClick={() => act(booking.id, 'returned')}>
                                                    Returned
                                                </button>
                                            ) : null}
                                            {booking.item_slug ? (
                                                <Link href={`/item/${booking.item_slug}`} className="btn-soft !px-2 !py-1 text-xs">
                                                    View Item
                                                </Link>
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

