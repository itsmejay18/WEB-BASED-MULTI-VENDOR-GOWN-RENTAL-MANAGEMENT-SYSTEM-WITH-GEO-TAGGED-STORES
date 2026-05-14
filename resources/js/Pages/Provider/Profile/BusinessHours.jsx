import React, { useMemo, useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

const dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

const defaultHours = dayNames.map((_, index) => ({
    day_of_week: index,
    open_time: '09:00',
    close_time: '18:00',
    is_closed: false,
}));

export default function ProviderBusinessHours({ locations }) {
    const [selectedLocationId, setSelectedLocationId] = useState(locations[0]?.id ?? null);
    const selectedLocation = useMemo(
        () => locations.find((location) => location.id === selectedLocationId) ?? null,
        [locations, selectedLocationId],
    );

    const normalizedHours = useMemo(() => {
        if (!selectedLocation) {
            return defaultHours;
        }

        const byDay = new Map((selectedLocation.business_hours || []).map((row) => [row.day_of_week, row]));
        return dayNames.map((_, index) => ({
            day_of_week: index,
            open_time: byDay.get(index)?.open_time?.slice(0, 5) || '09:00',
            close_time: byDay.get(index)?.close_time?.slice(0, 5) || '18:00',
            is_closed: Boolean(byDay.get(index)?.is_closed),
        }));
    }, [selectedLocation]);

    const { data, setData, put, processing } = useForm({
        hours: normalizedHours,
    });

    React.useEffect(() => {
        setData('hours', normalizedHours);
    }, [normalizedHours]);

    const updateRow = (index, field, value) => {
        const next = [...data.hours];
        next[index] = { ...next[index], [field]: value };
        setData('hours', next);
    };

    const submit = (event) => {
        event.preventDefault();
        if (!selectedLocationId) {
            return;
        }
        put(`/provider/locations/${selectedLocationId}/hours`, { preserveScroll: true });
    };

    return (
        <MainLayout title="Provider Business Hours">
            <section className="space-y-6">
                <div className="glass-card p-5">
                    <div className="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h1 className="text-2xl font-bold">Business Hours</h1>
                            <p className="mt-1 text-sm text-slate-600">Set operating hours for each provider location.</p>
                        </div>
                        <div className="flex gap-2">
                            <Link href="/provider/profile" className="btn-soft">
                                Profile
                            </Link>
                            <Link href="/provider/locations" className="btn-soft">
                                Locations
                            </Link>
                        </div>
                    </div>
                </div>

                {locations.length > 0 ? (
                    <form onSubmit={submit} className="glass-card space-y-4 p-5">
                        <div>
                            <label className="mb-1 block text-sm font-semibold text-slate-700">Location</label>
                            <select
                                className="input-shell h-11 w-full text-sm"
                                value={selectedLocationId || ''}
                                onChange={(event) => setSelectedLocationId(Number(event.target.value))}
                            >
                                {locations.map((location) => (
                                    <option key={location.id} value={location.id}>
                                        {location.location_name || location.city} - {[location.city, location.state, location.country]
                                            .filter(Boolean)
                                            .join(', ')}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="space-y-3">
                            {dayNames.map((dayName, index) => (
                                <article key={dayName} className="rounded-xl border border-slate-200 p-3">
                                    <div className="flex flex-wrap items-center gap-3">
                                        <div className="w-32 text-sm font-semibold text-slate-800">{dayName}</div>
                                        <label className="inline-flex items-center gap-2 text-sm">
                                            <input
                                                type="checkbox"
                                                checked={data.hours[index]?.is_closed || false}
                                                onChange={(event) => updateRow(index, 'is_closed', event.target.checked)}
                                            />
                                            Closed
                                        </label>
                                        <input
                                            className="input-shell h-10 text-sm"
                                            type="time"
                                            value={data.hours[index]?.open_time || ''}
                                            disabled={data.hours[index]?.is_closed}
                                            onChange={(event) => updateRow(index, 'open_time', event.target.value)}
                                        />
                                        <span className="text-sm text-slate-500">to</span>
                                        <input
                                            className="input-shell h-10 text-sm"
                                            type="time"
                                            value={data.hours[index]?.close_time || ''}
                                            disabled={data.hours[index]?.is_closed}
                                            onChange={(event) => updateRow(index, 'close_time', event.target.value)}
                                        />
                                    </div>
                                </article>
                            ))}
                        </div>

                        <div className="flex justify-end">
                            <button className="btn-primary" disabled={processing} type="submit">
                                {processing ? 'Saving...' : 'Save Hours'}
                            </button>
                        </div>
                    </form>
                ) : (
                    <div className="glass-card p-5">
                        <p className="text-sm text-slate-600">
                            No locations found. Add at least one location before setting business hours.
                        </p>
                        <div className="mt-3">
                            <Link href="/provider/locations" className="btn-primary">
                                Add location
                            </Link>
                        </div>
                    </div>
                )}
            </section>
        </MainLayout>
    );
}
