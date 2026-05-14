import React from 'react';
import { Link } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

export default function AdminUserShow({ user, recentActivity }) {
    return (
        <MainLayout title={`User ${user.name}`}>
            <section className="space-y-6">
                <div className="glass-card p-5">
                    <div className="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p className="text-sm text-slate-500">User Detail</p>
                            <h1 className="text-2xl font-bold text-slate-900">{user.name}</h1>
                            <p className="text-sm text-slate-600">{user.email}</p>
                            <p className="text-xs text-slate-500">Joined {user.created_at ? new Date(user.created_at).toLocaleString() : 'N/A'}</p>
                        </div>
                        <Link href="/admin/users" className="btn-soft">
                            Back to users
                        </Link>
                    </div>
                </div>

                <div className="grid gap-6 xl:grid-cols-[1fr_1fr]">
                    <div className="glass-card p-5">
                        <h2 className="text-lg font-bold">Account</h2>
                        <dl className="mt-3 space-y-2 text-sm">
                            <div className="flex justify-between gap-3">
                                <dt className="text-slate-500">Role</dt>
                                <dd className="font-semibold text-slate-900">{user.role}</dd>
                            </div>
                            <div className="flex justify-between gap-3">
                                <dt className="text-slate-500">Status</dt>
                                <dd className="font-semibold text-slate-900">{user.is_active ? 'Active' : 'Banned'}</dd>
                            </div>
                            <div className="flex justify-between gap-3">
                                <dt className="text-slate-500">Phone</dt>
                                <dd className="font-semibold text-slate-900">{user.phone || 'N/A'}</dd>
                            </div>
                            <div className="flex justify-between gap-3">
                                <dt className="text-slate-500">Bookings</dt>
                                <dd className="font-semibold text-slate-900">{user.bookings_count}</dd>
                            </div>
                            {!user.is_active ? (
                                <div>
                                    <dt className="text-slate-500">Ban reason</dt>
                                    <dd className="mt-1 rounded-lg bg-red-50 p-2 text-red-800">{user.banned_reason || 'No reason recorded.'}</dd>
                                </div>
                            ) : null}
                        </dl>
                    </div>

                    <div className="glass-card p-5">
                        <h2 className="text-lg font-bold">Addresses</h2>
                        <div className="mt-3 space-y-2">
                            {user.addresses?.length > 0 ? (
                                user.addresses.map((address) => (
                                    <article key={address.id} className="rounded-lg border border-slate-200 p-3 text-sm">
                                        <p className="font-semibold text-slate-900">{address.address_line1}</p>
                                        <p className="text-slate-600">
                                            {[address.address_line2, address.city, address.state, address.country].filter(Boolean).join(', ')}
                                        </p>
                                    </article>
                                ))
                            ) : (
                                <p className="text-sm text-slate-500">No addresses saved.</p>
                            )}
                        </div>
                    </div>
                </div>

                {user.provider ? (
                    <div className="glass-card p-5">
                        <h2 className="text-lg font-bold">Provider Profile</h2>
                        <p className="mt-1 text-sm text-slate-600">{user.provider.business_name}</p>
                        <div className="mt-3 grid gap-3 md:grid-cols-3">
                            <div className="rounded-lg border border-slate-200 p-3 text-sm">
                                <p className="text-slate-500">Verification</p>
                                <p className="font-semibold text-slate-900">{user.provider.verification_status}</p>
                            </div>
                            <div className="rounded-lg border border-slate-200 p-3 text-sm">
                                <p className="text-slate-500">Suspended</p>
                                <p className="font-semibold text-slate-900">{user.provider.is_suspended ? 'Yes' : 'No'}</p>
                            </div>
                            <div className="rounded-lg border border-slate-200 p-3 text-sm">
                                <p className="text-slate-500">Items</p>
                                <p className="font-semibold text-slate-900">{user.provider.items?.length || 0}</p>
                            </div>
                        </div>
                    </div>
                ) : null}

                <div className="glass-card p-5">
                    <h2 className="text-lg font-bold">Recent Activity</h2>
                    <div className="mt-3 space-y-2">
                        {recentActivity.length > 0 ? (
                            recentActivity.map((activity) => (
                                <article key={activity.id} className="rounded-lg border border-slate-200 p-3">
                                    <div className="flex flex-wrap items-center justify-between gap-2">
                                        <p className="text-sm font-semibold text-slate-900">
                                            {activity.status} - {activity.booking_number}
                                        </p>
                                        <p className="text-xs text-slate-500">
                                            {activity.created_at ? new Date(activity.created_at).toLocaleString() : 'N/A'}
                                        </p>
                                    </div>
                                    <p className="mt-1 text-sm text-slate-600">{activity.notes || 'No notes'}</p>
                                </article>
                            ))
                        ) : (
                            <p className="text-sm text-slate-500">No activity records found.</p>
                        )}
                    </div>
                </div>
            </section>
        </MainLayout>
    );
}
