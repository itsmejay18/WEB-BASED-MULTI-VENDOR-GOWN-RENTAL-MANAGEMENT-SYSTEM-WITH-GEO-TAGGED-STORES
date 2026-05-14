import React, { useState } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

const defaultForm = {
    address_line1: '',
    address_line2: '',
    city: '',
    state: '',
    postal_code: '',
    country: 'Philippines',
    latitude: '',
    longitude: '',
    is_default: false,
    address_type: 'home',
};

export default function UserAddresses({ addresses }) {
    const [editingId, setEditingId] = useState(null);
    const { data, setData, post, put, processing, reset, errors } = useForm(defaultForm);

    const startEdit = (address) => {
        setEditingId(address.id);
        setData({
            address_line1: address.address_line1 || '',
            address_line2: address.address_line2 || '',
            city: address.city || '',
            state: address.state || '',
            postal_code: address.postal_code || '',
            country: address.country || 'Philippines',
            latitude: address.latitude || '',
            longitude: address.longitude || '',
            is_default: Boolean(address.is_default),
            address_type: address.address_type || 'home',
        });
    };

    const submit = (event) => {
        event.preventDefault();

        if (editingId) {
            put(`/user/addresses/${editingId}`, {
                preserveScroll: true,
                onSuccess: () => {
                    setEditingId(null);
                    reset(defaultForm);
                },
            });
            return;
        }

        post('/user/addresses', {
            preserveScroll: true,
            onSuccess: () => reset(defaultForm),
        });
    };

    const removeAddress = (id) => {
        if (!window.confirm('Delete this address?')) {
            return;
        }
        router.delete(`/user/addresses/${id}`, { preserveScroll: true });
    };

    return (
        <MainLayout title="My Addresses">
            <section className="grid gap-6 xl:grid-cols-[420px_1fr]">
                <form onSubmit={submit} className="glass-card space-y-4 p-5">
                    <div className="flex items-center justify-between">
                        <h1 className="text-xl font-bold">{editingId ? 'Edit Address' : 'Add Address'}</h1>
                        <Link href="/user/profile" className="text-xs font-semibold text-brand-700 hover:text-brand-800">
                            Back to profile
                        </Link>
                    </div>

                    <label className="block text-sm">
                        <span className="mb-1 block font-semibold text-slate-700">Address line 1</span>
                        <input
                            className="input-shell h-10 w-full text-sm"
                            value={data.address_line1}
                            onChange={(event) => setData('address_line1', event.target.value)}
                        />
                    </label>
                    <label className="block text-sm">
                        <span className="mb-1 block font-semibold text-slate-700">Address line 2</span>
                        <input
                            className="input-shell h-10 w-full text-sm"
                            value={data.address_line2}
                            onChange={(event) => setData('address_line2', event.target.value)}
                        />
                    </label>
                    <div className="grid gap-3 sm:grid-cols-2">
                        <label className="block text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">City</span>
                            <input className="input-shell h-10 w-full text-sm" value={data.city} onChange={(event) => setData('city', event.target.value)} />
                        </label>
                        <label className="block text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">State</span>
                            <input className="input-shell h-10 w-full text-sm" value={data.state} onChange={(event) => setData('state', event.target.value)} />
                        </label>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2">
                        <label className="block text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">Postal code</span>
                            <input
                                className="input-shell h-10 w-full text-sm"
                                value={data.postal_code}
                                onChange={(event) => setData('postal_code', event.target.value)}
                            />
                        </label>
                        <label className="block text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">Country</span>
                            <input className="input-shell h-10 w-full text-sm" value={data.country} onChange={(event) => setData('country', event.target.value)} />
                        </label>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2">
                        <label className="block text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">Latitude</span>
                            <input
                                className="input-shell h-10 w-full text-sm"
                                type="number"
                                step="0.000001"
                                value={data.latitude}
                                onChange={(event) => setData('latitude', event.target.value)}
                            />
                        </label>
                        <label className="block text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">Longitude</span>
                            <input
                                className="input-shell h-10 w-full text-sm"
                                type="number"
                                step="0.000001"
                                value={data.longitude}
                                onChange={(event) => setData('longitude', event.target.value)}
                            />
                        </label>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2">
                        <label className="block text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">Address type</span>
                            <select
                                className="input-shell h-10 w-full text-sm"
                                value={data.address_type}
                                onChange={(event) => setData('address_type', event.target.value)}
                            >
                                <option value="home">Home</option>
                                <option value="work">Work</option>
                                <option value="other">Other</option>
                            </select>
                        </label>
                        <label className="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <input type="checkbox" checked={data.is_default} onChange={(event) => setData('is_default', event.target.checked)} />
                            Default address
                        </label>
                    </div>
                    {Object.keys(errors).length > 0 ? (
                        <div className="rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-700">Please review address fields.</div>
                    ) : null}
                    <div className="flex gap-2">
                        <button className="btn-primary" disabled={processing} type="submit">
                            {processing ? 'Saving...' : editingId ? 'Update Address' : 'Add Address'}
                        </button>
                        {editingId ? (
                            <button
                                className="btn-soft"
                                type="button"
                                onClick={() => {
                                    setEditingId(null);
                                    reset(defaultForm);
                                }}
                            >
                                Cancel
                            </button>
                        ) : null}
                    </div>
                </form>

                <div className="glass-card overflow-hidden">
                    <div className="border-b border-slate-100 px-5 py-4">
                        <h2 className="text-lg font-bold">Saved Addresses</h2>
                    </div>
                    <div className="divide-y divide-slate-100">
                        {addresses.length > 0 ? (
                            addresses.map((address) => (
                                <article key={address.id} className="flex items-start justify-between gap-3 px-5 py-4">
                                    <div>
                                        <p className="font-semibold text-slate-900">
                                            {address.address_line1}{' '}
                                            {address.is_default ? (
                                                <span className="rounded-full bg-brand-100 px-2 py-0.5 text-[10px] uppercase tracking-wider text-brand-700">
                                                    Default
                                                </span>
                                            ) : null}
                                        </p>
                                        <p className="text-sm text-slate-600">
                                            {[address.address_line2, address.city, address.state, address.postal_code, address.country]
                                                .filter(Boolean)
                                                .join(', ')}
                                        </p>
                                        <p className="text-xs uppercase tracking-wider text-slate-500">{address.address_type}</p>
                                    </div>
                                    <div className="flex gap-2">
                                        <button className="btn-soft !px-3 !py-1.5 text-xs" onClick={() => startEdit(address)}>
                                            Edit
                                        </button>
                                        <button
                                            className="rounded-xl border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50"
                                            onClick={() => removeAddress(address.id)}
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </article>
                            ))
                        ) : (
                            <p className="px-5 py-8 text-sm text-slate-600">No addresses saved.</p>
                        )}
                    </div>
                </div>
            </section>
        </MainLayout>
    );
}
