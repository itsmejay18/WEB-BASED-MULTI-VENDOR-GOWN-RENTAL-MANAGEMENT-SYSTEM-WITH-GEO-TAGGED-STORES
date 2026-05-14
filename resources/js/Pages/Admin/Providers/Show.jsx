import React from 'react';
import { Link, router } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

export default function AdminProviderShow({ provider }) {
    const verify = () => {
        if (!window.confirm('Verify this provider?')) {
            return;
        }
        router.post(`/admin/providers/${provider.id}/verify`, {}, { preserveScroll: true });
    };

    const reject = () => {
        const reason = window.prompt('Rejection reason (required):');
        if (!reason) {
            return;
        }
        router.post(`/admin/providers/${provider.id}/reject`, { reason }, { preserveScroll: true });
    };

    const suspend = () => {
        const reason = window.prompt('Suspension reason (required):');
        if (!reason) {
            return;
        }
        const duration = window.prompt('Duration in days: 3, 7, 30, or permanent', '7');
        if (!duration) {
            return;
        }
        router.post(`/admin/providers/${provider.id}/suspend`, { reason, duration }, { preserveScroll: true });
    };

    const unsuspend = () => {
        if (!window.confirm('Lift suspension for this provider?')) {
            return;
        }
        router.post(`/admin/providers/${provider.id}/unsuspend`, {}, { preserveScroll: true });
    };

    return (
        <MainLayout title={`Provider ${provider.business_name}`}>
            <section className="space-y-6">
                <div className="glass-card p-5">
                    <div className="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p className="text-sm text-slate-500">Provider Detail</p>
                            <h1 className="text-2xl font-bold text-slate-900">{provider.business_name}</h1>
                            <p className="text-sm text-slate-600">
                                Owner: {provider.user?.first_name} {provider.user?.last_name}
                            </p>
                            <p className="text-sm text-slate-600">{provider.user?.email}</p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Link href="/admin/providers" className="btn-soft">
                                Back
                            </Link>
                            {provider.verification_status !== 'verified' ? (
                                <button className="btn-soft" onClick={verify}>
                                    Verify
                                </button>
                            ) : null}
                            <button className="btn-soft" onClick={reject}>
                                Reject
                            </button>
                            {!provider.is_suspended ? (
                                <button
                                    className="rounded-xl border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50"
                                    onClick={suspend}
                                >
                                    Suspend
                                </button>
                            ) : (
                                <button className="btn-soft" onClick={unsuspend}>
                                    Unsuspend
                                </button>
                            )}
                        </div>
                    </div>
                </div>

                <div className="grid gap-6 xl:grid-cols-[1fr_1fr]">
                    <div className="glass-card p-5">
                        <h2 className="text-lg font-bold">Account Status</h2>
                        <dl className="mt-3 space-y-2 text-sm">
                            <div className="flex justify-between gap-3">
                                <dt className="text-slate-500">Verification</dt>
                                <dd className="font-semibold text-slate-900">{provider.verification_status}</dd>
                            </div>
                            <div className="flex justify-between gap-3">
                                <dt className="text-slate-500">Suspended</dt>
                                <dd className="font-semibold text-slate-900">{provider.is_suspended ? 'Yes' : 'No'}</dd>
                            </div>
                            <div className="flex justify-between gap-3">
                                <dt className="text-slate-500">Suspended Until</dt>
                                <dd className="font-semibold text-slate-900">
                                    {provider.suspended_until ? new Date(provider.suspended_until).toLocaleString() : 'N/A'}
                                </dd>
                            </div>
                            <div className="flex justify-between gap-3">
                                <dt className="text-slate-500">Rejection Reason</dt>
                                <dd className="font-semibold text-slate-900">{provider.rejection_reason || 'N/A'}</dd>
                            </div>
                            <div className="flex justify-between gap-3">
                                <dt className="text-slate-500">Suspension Reason</dt>
                                <dd className="font-semibold text-slate-900">{provider.suspension_reason || 'N/A'}</dd>
                            </div>
                        </dl>
                    </div>

                    <div className="glass-card p-5">
                        <h2 className="text-lg font-bold">Business</h2>
                        <p className="mt-2 text-sm text-slate-700">{provider.description || 'No description.'}</p>
                        <div className="mt-3 grid gap-3 sm:grid-cols-3">
                            <div className="rounded-lg border border-slate-200 p-3">
                                <p className="text-xs text-slate-500">Rating</p>
                                <p className="text-lg font-semibold text-slate-900">{provider.rating}</p>
                            </div>
                            <div className="rounded-lg border border-slate-200 p-3">
                                <p className="text-xs text-slate-500">Reviews</p>
                                <p className="text-lg font-semibold text-slate-900">{provider.total_reviews}</p>
                            </div>
                            <div className="rounded-lg border border-slate-200 p-3">
                                <p className="text-xs text-slate-500">Commission</p>
                                <p className="text-lg font-semibold text-slate-900">{provider.commission_rate}%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="glass-card p-5">
                    <h2 className="text-lg font-bold">Locations</h2>
                    <div className="mt-3 grid gap-3 md:grid-cols-2">
                        {provider.locations?.length > 0 ? (
                            provider.locations.map((location) => (
                                <article key={location.id} className="rounded-lg border border-slate-200 p-3 text-sm">
                                    <p className="font-semibold text-slate-900">{location.location_name || location.city}</p>
                                    <p className="text-slate-600">
                                        {[location.address_line1, location.address_line2, location.city, location.state, location.country]
                                            .filter(Boolean)
                                            .join(', ')}
                                    </p>
                                    <p className="mt-1 text-xs text-slate-500">
                                        {location.latitude}, {location.longitude}
                                    </p>
                                </article>
                            ))
                        ) : (
                            <p className="text-sm text-slate-500">No locations configured.</p>
                        )}
                    </div>
                </div>

                <div className="grid gap-6 xl:grid-cols-[1fr_1fr]">
                    <div className="glass-card p-5">
                        <h2 className="text-lg font-bold">Items</h2>
                        <div className="mt-3 space-y-2">
                            {provider.items?.length > 0 ? (
                                provider.items.map((item) => (
                                    <article key={item.id} className="rounded-lg border border-slate-200 p-3 text-sm">
                                        <p className="font-semibold text-slate-900">{item.name}</p>
                                        <p className="text-slate-600">{item.bookings_count} bookings</p>
                                    </article>
                                ))
                            ) : (
                                <p className="text-sm text-slate-500">No items yet.</p>
                            )}
                        </div>
                    </div>

                    <div className="glass-card p-5">
                        <h2 className="text-lg font-bold">Verification History</h2>
                        <div className="mt-3 space-y-2">
                            {provider.verification_logs?.length > 0 ? (
                                provider.verification_logs.map((log) => (
                                    <article key={log.id} className="rounded-lg border border-slate-200 p-3 text-sm">
                                        <div className="flex flex-wrap items-center justify-between gap-2">
                                            <p className="font-semibold text-slate-900">{log.action}</p>
                                            <p className="text-xs text-slate-500">
                                                {log.created_at ? new Date(log.created_at).toLocaleString() : 'N/A'}
                                            </p>
                                        </div>
                                        <p className="mt-1 text-slate-600">{log.notes || 'No notes'}</p>
                                        <p className="mt-1 text-xs text-slate-500">By: {log.performed_by || 'System'}</p>
                                    </article>
                                ))
                            ) : (
                                <p className="text-sm text-slate-500">No verification history yet.</p>
                            )}
                        </div>
                    </div>
                </div>
            </section>
        </MainLayout>
    );
}
