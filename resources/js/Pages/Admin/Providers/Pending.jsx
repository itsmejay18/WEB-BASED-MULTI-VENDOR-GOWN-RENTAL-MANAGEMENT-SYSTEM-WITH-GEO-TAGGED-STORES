import React from 'react';
import { router } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

export default function AdminPendingProviders({ providers }) {
    const verify = (id) => router.post(`/admin/providers/${id}/verify`);
    const reject = (id) => {
        const reason = window.prompt('Rejection reason (optional)');
        router.post(`/admin/providers/${id}/reject`, reason ? { reason } : {});
    };

    return (
        <MainLayout title="Pending Providers">
            <section className="space-y-5">
                <div className="glass-card p-5">
                    <h1 className="text-2xl font-bold">Pending Provider Verification</h1>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {providers.data.map((provider) => (
                        <article key={provider.id} className="glass-card space-y-3 p-4">
                            <p className="font-bold text-slate-900">{provider.business_name}</p>
                            <p className="text-sm text-slate-600">{provider.owner?.name}</p>
                            <p className="text-xs text-slate-500">{provider.owner?.email}</p>
                            <p className="text-xs uppercase tracking-wider text-slate-500">Submitted {provider.created_at}</p>
                            <div className="flex gap-2">
                                <button className="btn-soft !px-3 !py-1.5 text-xs" onClick={() => verify(provider.id)}>
                                    Verify
                                </button>
                                <button
                                    className="rounded-xl border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50"
                                    onClick={() => reject(provider.id)}
                                >
                                    Reject
                                </button>
                            </div>
                        </article>
                    ))}
                    {providers.data.length === 0 ? <p className="text-sm text-slate-600">No pending providers.</p> : null}
                </div>
            </section>
        </MainLayout>
    );
}

