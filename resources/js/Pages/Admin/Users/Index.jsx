import React, { useMemo, useState } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

const statusBadge = (active) =>
    active
        ? 'rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700'
        : 'rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-700';

const roleBadge = (role) => {
    if (role === 'admin') {
        return 'rounded-full bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-700';
    }
    if (role === 'provider') {
        return 'rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700';
    }
    return 'rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700';
};

export default function AdminUsersIndex({ users, filters, stats }) {
    const [selectedUsers, setSelectedUsers] = useState([]);
    const { data, setData, get, processing } = useForm({
        search: filters.search ?? '',
        role: filters.role ?? 'all',
        status: filters.status ?? 'all',
        verification: filters.verification ?? 'all',
    });

    const selectedCount = selectedUsers.length;
    const selectedSet = useMemo(() => new Set(selectedUsers), [selectedUsers]);

    const submitFilter = (event) => {
        event.preventDefault();
        get('/admin/users', { preserveState: true, preserveScroll: true });
    };

    const toggleAll = () => {
        if (selectedUsers.length === users.data.length) {
            setSelectedUsers([]);
            return;
        }
        setSelectedUsers(users.data.map((user) => user.id));
    };

    const toggleOne = (userId) => {
        if (selectedSet.has(userId)) {
            setSelectedUsers(selectedUsers.filter((id) => id !== userId));
            return;
        }
        setSelectedUsers([...selectedUsers, userId]);
    };

    const runBulkAction = (action) => {
        if (selectedCount === 0) {
            return;
        }

        if (!window.confirm(`Apply "${action}" to ${selectedCount} selected users?`)) {
            return;
        }

        router.post(
            '/admin/users/bulk-action',
            { user_ids: selectedUsers, action },
            {
                preserveScroll: true,
                onSuccess: () => setSelectedUsers([]),
            },
        );
    };

    const toggleStatus = (user) => {
        if (!window.confirm(`Set ${user.name} as ${user.is_active ? 'banned' : 'active'}?`)) {
            return;
        }
        router.post(`/admin/users/${user.id}/toggle-status`, {}, { preserveScroll: true });
    };

    const removeUser = (user) => {
        if (!window.confirm(`Delete ${user.name}? This cannot be undone.`)) {
            return;
        }
        router.delete(`/admin/users/${user.id}`, { preserveScroll: true });
    };

    return (
        <MainLayout title="Admin Users">
            <section className="space-y-6">
                <div className="glass-card p-5">
                    <h1 className="text-2xl font-bold">User Management</h1>
                    <p className="mt-1 text-sm text-slate-600">Moderate renters, providers, and admin accounts.</p>
                </div>

                <div className="grid gap-3 sm:grid-cols-3 xl:grid-cols-6">
                    <article className="glass-card p-4">
                        <p className="text-xs uppercase tracking-wider text-slate-500">Total</p>
                        <p className="mt-1 text-2xl font-bold">{stats.total_users}</p>
                    </article>
                    <article className="glass-card p-4">
                        <p className="text-xs uppercase tracking-wider text-emerald-700">Active</p>
                        <p className="mt-1 text-2xl font-bold text-emerald-700">{stats.active_users}</p>
                    </article>
                    <article className="glass-card p-4">
                        <p className="text-xs uppercase tracking-wider text-red-700">Banned</p>
                        <p className="mt-1 text-2xl font-bold text-red-700">{stats.banned_users}</p>
                    </article>
                    <article className="glass-card p-4">
                        <p className="text-xs uppercase tracking-wider text-slate-500">Providers</p>
                        <p className="mt-1 text-2xl font-bold">{stats.total_providers}</p>
                    </article>
                    <article className="glass-card p-4">
                        <p className="text-xs uppercase tracking-wider text-emerald-700">Verified</p>
                        <p className="mt-1 text-2xl font-bold text-emerald-700">{stats.verified_providers}</p>
                    </article>
                    <article className="glass-card p-4">
                        <p className="text-xs uppercase tracking-wider text-amber-700">Pending</p>
                        <p className="mt-1 text-2xl font-bold text-amber-700">{stats.pending_providers}</p>
                    </article>
                </div>

                <form onSubmit={submitFilter} className="glass-card space-y-4 p-4">
                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <input
                            className="input-shell h-10 text-sm"
                            placeholder="Search name or email"
                            value={data.search}
                            onChange={(event) => setData('search', event.target.value)}
                        />
                        <select className="input-shell h-10 text-sm" value={data.role} onChange={(event) => setData('role', event.target.value)}>
                            <option value="all">All roles</option>
                            <option value="renter">Renter</option>
                            <option value="provider">Provider</option>
                            <option value="admin">Admin</option>
                        </select>
                        <select className="input-shell h-10 text-sm" value={data.status} onChange={(event) => setData('status', event.target.value)}>
                            <option value="all">All statuses</option>
                            <option value="active">Active</option>
                            <option value="banned">Banned</option>
                        </select>
                        <select
                            className="input-shell h-10 text-sm"
                            value={data.verification}
                            onChange={(event) => setData('verification', event.target.value)}
                        >
                            <option value="all">All verification</option>
                            <option value="pending">Pending</option>
                            <option value="verified">Verified</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div className="flex justify-end">
                        <button className="btn-primary" disabled={processing} type="submit">
                            {processing ? 'Applying...' : 'Apply Filters'}
                        </button>
                    </div>
                </form>

                {selectedCount > 0 ? (
                    <div className="glass-card flex flex-wrap items-center justify-between gap-3 p-4">
                        <p className="text-sm font-semibold text-slate-700">{selectedCount} selected</p>
                        <div className="flex gap-2">
                            <button className="btn-soft !px-3 !py-2 text-xs" onClick={() => runBulkAction('ban')}>
                                Ban
                            </button>
                            <button className="btn-soft !px-3 !py-2 text-xs" onClick={() => runBulkAction('restore')}>
                                Restore
                            </button>
                            <button
                                className="rounded-xl border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50"
                                onClick={() => runBulkAction('delete')}
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                ) : null}

                <div className="glass-card overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th className="px-4 py-3">
                                    <input
                                        checked={users.data.length > 0 && selectedCount === users.data.length}
                                        className="rounded border-slate-300"
                                        onChange={toggleAll}
                                        type="checkbox"
                                    />
                                </th>
                                <th className="px-4 py-3">User</th>
                                <th className="px-4 py-3">Role</th>
                                <th className="px-4 py-3">Status</th>
                                <th className="px-4 py-3">Verification</th>
                                <th className="px-4 py-3">Joined</th>
                                <th className="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 bg-white">
                            {users.data.map((user) => (
                                <tr key={user.id}>
                                    <td className="px-4 py-3">
                                        <input
                                            checked={selectedSet.has(user.id)}
                                            className="rounded border-slate-300"
                                            onChange={() => toggleOne(user.id)}
                                            type="checkbox"
                                        />
                                    </td>
                                    <td className="px-4 py-3">
                                        <p className="font-semibold text-slate-900">{user.name}</p>
                                        <p className="text-xs text-slate-500">{user.email}</p>
                                    </td>
                                    <td className="px-4 py-3">
                                        <span className={roleBadge(user.role)}>{user.role}</span>
                                    </td>
                                    <td className="px-4 py-3">
                                        <span className={statusBadge(user.is_active)}>{user.is_active ? 'Active' : 'Banned'}</span>
                                    </td>
                                    <td className="px-4 py-3">
                                        {user.provider ? (
                                            <span className="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                                {user.provider.verification_status}
                                            </span>
                                        ) : (
                                            <span className="text-xs text-slate-400">N/A</span>
                                        )}
                                    </td>
                                    <td className="px-4 py-3 text-xs text-slate-500">
                                        {user.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A'}
                                    </td>
                                    <td className="px-4 py-3 text-right">
                                        <div className="inline-flex gap-2">
                                            <Link href={`/admin/users/${user.id}`} className="btn-soft !px-3 !py-1.5 text-xs">
                                                View
                                            </Link>
                                            <button className="btn-soft !px-3 !py-1.5 text-xs" onClick={() => toggleStatus(user)}>
                                                {user.is_active ? 'Ban' : 'Restore'}
                                            </button>
                                            <button
                                                className="rounded-xl border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50"
                                                onClick={() => removeUser(user)}
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    {users.data.length === 0 ? <p className="px-4 py-6 text-sm text-slate-600">No users found.</p> : null}
                </div>

                {users.links?.length > 1 ? (
                    <div className="flex flex-wrap justify-center gap-2">
                        {users.links.map((link, index) => (
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
