import React from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

const statusClasses = {
    pending: 'rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800',
    verified: 'rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-800',
    rejected: 'rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800',
};

export default function AdminProvidersIndex({ providers, filters, stats }) {
    const { data, setData, get, processing } = useForm({
        search: filters.search ?? '',
        status: filters.status ?? 'all',
        suspended: filters.suspended ?? 'all',
    });

    const submitFilter = (event) => {
        event.preventDefault();
        get('/admin/providers', { preserveState: true, preserveScroll: true });
    };

    const verify = (providerId) => {
        if (!window.confirm('Verify this provider?')) {
            return;
        }
        router.post(`/admin/providers/${providerId}/verify`, {}, { preserveScroll: true });
    };

    const reject = (providerId) => {
        const reason = window.prompt('Rejection reason (required):');
        if (!reason) {
            return;
        }
        router.post(`/admin/providers/${providerId}/reject`, { reason }, { preserveScroll: true });
    };

    const suspend = (providerId) => {
        const reason = window.prompt('Suspension reason (required):');
        if (!reason) {
            return;
        }
        const duration = window.prompt('Duration in days: 3, 7, 30, or permanent', '7');
        if (!duration) {
            return;
        }
        router.post(`/admin/providers/${providerId}/suspend`, { reason, duration }, { preserveScroll: true });
    };

    const unsuspend = (providerId) => {
        if (!window.confirm('Lift suspension for this provider?')) {
            return;
        }
        router.post(`/admin/providers/${providerId}/unsuspend`, {}, { preserveScroll: true });
    };

    return (
        <MainLayout title="Admin Providers">
            <section className="space-y-6">
                <div className="glass-card p-5">
                    <h1 className="text-2xl font-bold">Provider Controls</h1>
                    <p className="mt-1 text-sm text-slate-600">Review and moderate provider accounts.</p>
                </div>

                <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                    <article className="glass-card p-4">
                        <p className="text-xs uppercase tracking-wider text-slate-500">Total</p>
                        <p className="mt-1 text-2xl font-bold">{stats.total}</p>
                    </article>
                    <article className="glass-card p-4">
                        <p className="text-xs uppercase tracking-wider text-amber-700">Pending</p>
                        <p className="mt-1 text-2xl font-bold text-amber-700">{stats.pending}</p>
                    </article>
                    <article className="glass-card p-4">
                        <p className="text-xs uppercase tracking-wider text-emerald-700">Verified</p>
                        <p className="mt-1 text-2xl font-bold text-emerald-700">{stats.verified}</p>
                    </article>
                    <article className="glass-card p-4">
                        <p className="text-xs uppercase tracking-wider text-red-700">Rejected</p>
                        <p className="mt-1 text-2xl font-bold text-red-700">{stats.rejected}</p>
                    </article>
                    <article className="glass-card p-4">
                        <p className="text-xs uppercase tracking-wider text-slate-500">Suspended</p>
                        <p className="mt-1 text-2xl font-bold">{stats.suspended}</p>
                    </article>
                </div>

                <form onSubmit={submitFilter} className="glass-card space-y-4 p-4">
                    <div className="grid gap-3 md:grid-cols-3">
                        <input
                            className="input-shell h-10 text-sm"
                            placeholder="Search business or owner"
                            value={data.search}
                            onChange={(event) => setData('search', event.target.value)}
                        />
                        <select className="input-shell h-10 text-sm" value={data.status} onChange={(event) => setData('status', event.target.value)}>
                            <option value="all">All verification statuses</option>
                            <option value="pending">Pending</option>
                            <option value="verified">Verified</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <select
                            className="input-shell h-10 text-sm"
                            value={data.suspended}
                            onChange={(event) => setData('suspended', event.target.value)}
                        >
                            <option value="all">All suspension states</option>
                            <option value="yes">Suspended</option>
                            <option value="no">Not suspended</option>
                        </select>
                    </div>
                    <div className="flex justify-end">
                        <button className="btn-primary" disabled={processing} type="submit">
                            {processing ? 'Applying...' : 'Apply Filters'}
                        </button>
                    </div>
                </form>

                <div className="glass-card overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th className="px-4 py-3">Business</th>
                                <th className="px-4 py-3">Owner</th>
                                <th className="px-4 py-3">Status</th>
                                <th className="px-4 py-3">Suspended</th>
                                <th className="px-4 py-3">Items</th>
                                <th className="px-4 py-3">Location</th>
                                <th className="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 bg-white">
                            {providers.data.map((provider) => (
                                <tr key={provider.id}>
                                    <td className="px-4 py-3">
                                        <p className="font-semibold text-slate-900">{provider.business_name}</p>
                                        <p className="text-xs text-slate-500">
                                            {provider.created_at ? new Date(provider.created_at).toLocaleDateString() : 'N/A'}
                                        </p>
                                    </td>
                                    <td className="px-4 py-3">
                                        <p className="text-sm text-slate-900">{provider.owner?.name || 'N/A'}</p>
                                        <p className="text-xs text-slate-500">{provider.owner?.email || 'N/A'}</p>
                                    </td>
                                    <td className="px-4 py-3">
                                        <span className={statusClasses[provider.verification_status] || statusClasses.pending}>{provider.verification_status}</span>
                                    </td>
                                    <td className="px-4 py-3">
                                        <span
                                            className={`rounded-full px-2 py-1 text-xs font-semibold ${
                                                provider.is_suspended ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-700'
                                            }`}
                                        >
                                            {provider.is_suspended ? 'Yes' : 'No'}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3">{provider.items_count}</td>
                                    <td className="px-4 py-3 text-xs text-slate-600">
                                        {provider.main_location
                                            ? [provider.main_location.city, provider.main_location.state, provider.main_location.country]
                                                  .filter(Boolean)
                                                  .join(', ')
                                            : 'N/A'}
                                    </td>
                                    <td className="px-4 py-3 text-right">
                                        <div className="inline-flex flex-wrap justify-end gap-2">
                                            <Link href={`/admin/providers/${provider.id}`} className="btn-soft !px-3 !py-1.5 text-xs">
                                                View
                                            </Link>
                                            {provider.verification_status !== 'verified' ? (
                                                <button className="btn-soft !px-3 !py-1.5 text-xs" onClick={() => verify(provider.id)}>
                                                    Verify
                                                </button>
                                            ) : null}
                                            <button className="btn-soft !px-3 !py-1.5 text-xs" onClick={() => reject(provider.id)}>
                                                Reject
                                            </button>
                                            {!provider.is_suspended ? (
                                                <button
                                                    className="rounded-xl border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50"
                                                    onClick={() => suspend(provider.id)}
                                                >
                                                    Suspend
                                                </button>
                                            ) : (
                                                <button className="btn-soft !px-3 !py-1.5 text-xs" onClick={() => unsuspend(provider.id)}>
                                                    Unsuspend
                                                </button>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    {providers.data.length === 0 ? <p className="px-4 py-6 text-sm text-slate-600">No providers found.</p> : null}
                </div>

                {providers.links?.length > 1 ? (
                    <div className="flex flex-wrap justify-center gap-2">
                        {providers.links.map((link, index) => (
                            <Link
                                key={`${link.label}-${index}`}
                                href={link.url || '#'}
                                className={`rounded-lg px-3 py-1.5 text-sm ${
                                    link.active
                                        ? 'bg-brand-600 text-white'
                                        : link.url
                                          ? 'bg-white text-slate-700 hover:bg-slate-50'
                                          : 'cursor-not-allowed bg-slate-100 text-slate-400'
                                }`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                ) : null}
            </section>
        </MainLayout>
    );
}
