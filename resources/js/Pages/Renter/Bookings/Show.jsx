import React from 'react';
import { Link, router } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

const money = (value) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0,
    }).format(value || 0);

export default function RenterBookingShow({ booking }) {
    return (
        <MainLayout title={`Booking ${booking.booking_number}`}>
            <section className="space-y-5">
                <div className="glass-card flex items-center justify-between p-5">
                    <div>
                        <h1 className="text-2xl font-bold">{booking.booking_number}</h1>
                        <p className="text-sm text-slate-600">
                            {booking.variant?.item?.name} • {booking.provider?.business_name}
                        </p>
                    </div>
                    <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-slate-700">
                        {booking.status}
                    </span>
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <div className="glass-card p-5">
                        <h2 className="text-lg font-bold">Booking Details</h2>
                        <dl className="mt-4 space-y-2 text-sm">
                            <div className="flex justify-between">
                                <dt className="text-slate-500">Start date</dt>
                                <dd>{booking.start_date}</dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-slate-500">End date</dt>
                                <dd>{booking.end_date}</dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-slate-500">Rental price</dt>
                                <dd>{money(booking.rental_price)}</dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-slate-500">Security deposit</dt>
                                <dd>{money(booking.security_deposit)}</dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-slate-500">Total amount</dt>
                                <dd className="font-semibold text-brand-700">{money(booking.total_amount)}</dd>
                            </div>
                        </dl>
                    </div>

                    <div className="glass-card p-5">
                        <h2 className="text-lg font-bold">Timeline</h2>
                        <div className="mt-4 space-y-3">
                            {(booking.timeline ?? []).map((entry) => (
                                <div key={entry.id} className="rounded-xl bg-slate-50 px-3 py-2">
                                    <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">{entry.status}</p>
                                    <p className="text-sm text-slate-700">{entry.notes}</p>
                                    <p className="text-xs text-slate-500">{entry.created_at}</p>
                                </div>
                            ))}
                            {(booking.timeline ?? []).length === 0 ? <p className="text-sm text-slate-600">No timeline entries.</p> : null}
                        </div>
                    </div>
                </div>

                <div className="flex gap-2">
                    <Link href="/renter/bookings" className="btn-soft">
                        Back to bookings
                    </Link>
                    {['pending', 'approved'].includes(booking.status) ? (
                        <button
                            type="button"
                            className="rounded-xl border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50"
                            onClick={() => {
                                const reason = window.prompt('Cancellation reason (optional)');
                                router.post(`/renter/bookings/${booking.id}/cancel`, reason ? { reason } : {});
                            }}
                        >
                            Cancel booking
                        </button>
                    ) : null}
                </div>
            </section>
        </MainLayout>
    );
}

