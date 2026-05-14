import React, { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

const initialForm = {
    location_name: '',
    address_line1: '',
    address_line2: '',
    city: '',
    state: '',
    postal_code: '',
    country: 'Philippines',
    latitude: '',
    longitude: '',
    service_radius_km: 10,
    is_main: false,
    is_active: true,
};

export default function ProviderLocationsIndex({ locations }) {
    const [editingId, setEditingId] = useState(null);
    const { data, setData, post, put, processing, reset } = useForm(initialForm);

    const submit = (event) => {
        event.preventDefault();
        if (editingId) {
            put(`/provider/locations/${editingId}`, {
                onSuccess: () => {
                    setEditingId(null);
                    reset();
                },
            });
            return;
        }
        post('/provider/locations', { onSuccess: () => reset() });
    };

    const startEdit = (location) => {
        setEditingId(location.id);
        setData({
            location_name: location.location_name ?? '',
            address_line1: location.address_line1 ?? '',
            address_line2: location.address_line2 ?? '',
            city: location.city ?? '',
            state: location.state ?? '',
            postal_code: location.postal_code ?? '',
            country: location.country ?? 'Philippines',
            latitude: location.latitude ?? '',
            longitude: location.longitude ?? '',
            service_radius_km: location.service_radius_km ?? 10,
            is_main: location.is_main ?? false,
            is_active: location.is_active ?? true,
        });
    };

    const remove = (id) => {
        if (!window.confirm('Delete this location?')) {
            return;
        }
        router.delete(`/provider/locations/${id}`);
    };

    return (
        <MainLayout title="Provider Locations">
            <section className="grid gap-6 xl:grid-cols-[420px_1fr]">
                <form onSubmit={submit} className="glass-card space-y-4 p-5">
                    <h1 className="text-xl font-bold">{editingId ? 'Edit Location' : 'Add Location'}</h1>
                    <input className="input-shell h-10 w-full text-sm" placeholder="Location name" value={data.location_name} onChange={(e) => setData('location_name', e.target.value)} />
                    <input className="input-shell h-10 w-full text-sm" placeholder="Address line 1" value={data.address_line1} onChange={(e) => setData('address_line1', e.target.value)} />
                    <input className="input-shell h-10 w-full text-sm" placeholder="Address line 2" value={data.address_line2} onChange={(e) => setData('address_line2', e.target.value)} />
                    <div className="grid gap-3 sm:grid-cols-2">
                        <input className="input-shell h-10 text-sm" placeholder="City" value={data.city} onChange={(e) => setData('city', e.target.value)} />
                        <input className="input-shell h-10 text-sm" placeholder="State" value={data.state} onChange={(e) => setData('state', e.target.value)} />
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2">
                        <input className="input-shell h-10 text-sm" placeholder="Latitude" type="number" step="0.000001" value={data.latitude} onChange={(e) => setData('latitude', e.target.value)} />
                        <input className="input-shell h-10 text-sm" placeholder="Longitude" type="number" step="0.000001" value={data.longitude} onChange={(e) => setData('longitude', e.target.value)} />
                    </div>
                    <input className="input-shell h-10 w-full text-sm" placeholder="Service radius km" type="number" min="1" value={data.service_radius_km} onChange={(e) => setData('service_radius_km', e.target.value)} />
                    <label className="flex items-center gap-2 text-sm">
                        <input type="checkbox" checked={data.is_main} onChange={(e) => setData('is_main', e.target.checked)} />
                        Main location
                    </label>
                    <label className="flex items-center gap-2 text-sm">
                        <input type="checkbox" checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} />
                        Active
                    </label>
                    <div className="flex gap-2">
                        <button className="btn-primary" disabled={processing} type="submit">
                            {editingId ? 'Update' : 'Save'}
                        </button>
                        {editingId ? (
                            <button
                                className="btn-soft"
                                type="button"
                                onClick={() => {
                                    setEditingId(null);
                                    reset();
                                }}
                            >
                                Cancel
                            </button>
                        ) : null}
                    </div>
                </form>

                <div className="glass-card overflow-hidden">
                    <div className="border-b border-slate-100 px-5 py-4">
                        <h2 className="text-lg font-bold">Saved Locations</h2>
                    </div>
                    <div className="divide-y divide-slate-100">
                        {locations.map((location) => (
                            <div key={location.id} className="flex items-center justify-between gap-4 px-5 py-4">
                                <div>
                                    <p className="font-semibold text-slate-900">{location.location_name || location.city}</p>
                                    <p className="text-sm text-slate-600">
                                        {location.address_line1}, {location.city}
                                    </p>
                                    <p className="text-xs uppercase tracking-widest text-slate-500">
                                        {location.latitude}, {location.longitude}
                                    </p>
                                </div>
                                <div className="flex gap-2">
                                    <button className="btn-soft !px-3 !py-1.5 text-xs" onClick={() => startEdit(location)}>
                                        Edit
                                    </button>
                                    <button
                                        className="rounded-xl border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50"
                                        onClick={() => remove(location.id)}
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        ))}
                        {locations.length === 0 ? <p className="px-5 py-8 text-sm text-slate-600">No locations yet.</p> : null}
                    </div>
                </div>
            </section>
        </MainLayout>
    );
}

