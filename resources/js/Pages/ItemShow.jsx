import React, { useMemo, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { CalendarDaysIcon, ShieldCheckIcon, SparklesIcon } from '@heroicons/react/24/outline';
import toast from 'react-hot-toast';
import MainLayout from '../Layouts/MainLayout';
import { checkItemAvailability, getItem } from '../lib/api';

function currency(value) {
    if (typeof value !== 'number') {
        return '--';
    }

    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0,
    }).format(value);
}

function today(offset = 0) {
    const value = new Date();
    value.setDate(value.getDate() + offset);
    return value.toISOString().slice(0, 10);
}

export default function ItemShow({ slug }) {
    const [startDate, setStartDate] = useState(today(3));
    const [endDate, setEndDate] = useState(today(5));
    const [selectedVariant, setSelectedVariant] = useState('');
    const [availabilityState, setAvailabilityState] = useState(null);
    const [checking, setChecking] = useState(false);

    const itemQuery = useQuery({
        queryKey: ['item', slug],
        queryFn: () => getItem(slug),
    });

    const item = itemQuery.data;
    const variants = item?.variants ?? [];

    const defaultGallery = useMemo(() => {
        const fromPhotos = item?.photos ?? [];

        if (fromPhotos.length > 0) {
            return fromPhotos;
        }

        if (item?.primary_photo) {
            return [item.primary_photo];
        }

        return [];
    }, [item]);

    const providerAddress = useMemo(() => {
        const location = item?.provider?.main_location;
        if (!location) {
            return 'Address unavailable';
        }

        return [
            location.address_line1,
            location.address_line2,
            location.city,
            location.state,
            location.postal_code,
            location.country,
        ]
            .filter(Boolean)
            .join(', ');
    }, [item]);

    const checkAvailabilityNow = async () => {
        if (!startDate || !endDate) {
            toast.error('Please set both start and end dates.');
            return;
        }

        setChecking(true);
        try {
            const payload = await checkItemAvailability(slug, {
                start_date: startDate,
                end_date: endDate,
                variant_id: selectedVariant || undefined,
            });

            setAvailabilityState(payload);
            const available = payload.availability.some((entry) => entry.available);
            if (available) {
                toast.success('Great news, this item is available in your selected range.');
            } else {
                toast.error('No available stock found for that date range.');
            }
        } catch (error) {
            toast.error('Availability check failed. Please try again.');
        } finally {
            setChecking(false);
        }
    };

    return (
        <MainLayout title={item ? item.name : 'Item details'}>
            {itemQuery.isLoading ? (
                <div className="grid gap-6 lg:grid-cols-2">
                    <div className="glass-card h-[32rem] animate-pulse bg-slate-100" />
                    <div className="glass-card h-[32rem] animate-pulse bg-slate-100" />
                </div>
            ) : !item ? (
                <div className="glass-card p-10 text-center">
                    <h1 className="text-2xl font-bold">Item not found</h1>
                    <p className="mt-2 text-sm text-slate-600">
                        The listing may have been archived or the link is invalid.
                    </p>
                </div>
            ) : (
                <div className="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
                    <section className="space-y-4">
                        <div className="glass-card overflow-hidden">
                            <div className="aspect-[4/5] bg-slate-100">
                                {defaultGallery[0]?.photo_url ? (
                                    <img
                                        src={defaultGallery[0].photo_url}
                                        alt={item.name}
                                        className="h-full w-full object-cover"
                                    />
                                ) : (
                                    <div className="grid h-full place-items-center text-sm text-slate-500">No image</div>
                                )}
                            </div>
                        </div>

                        {defaultGallery.length > 1 && (
                            <div className="grid grid-cols-4 gap-3">
                                {defaultGallery.slice(1, 5).map((photo) => (
                                    <div
                                        key={photo.id}
                                        className="glass-card aspect-square overflow-hidden bg-slate-100"
                                    >
                                        <img src={photo.photo_url} alt={item.name} className="h-full w-full object-cover" />
                                    </div>
                                ))}
                            </div>
                        )}
                    </section>

                    <section className="space-y-5">
                        <div className="glass-card p-6">
                            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-brand-700">
                                {item.category?.name || 'Listing'}
                            </p>
                            <h1 className="mt-2 text-3xl font-bold">{item.name}</h1>
                            <p className="mt-2 text-sm text-slate-600">{item.description}</p>

                            <div className="mt-5 grid gap-3 sm:grid-cols-3">
                                <div className="rounded-xl bg-brand-50 p-3">
                                    <p className="text-xs uppercase tracking-wider text-brand-700">From</p>
                                    <p className="text-lg font-bold text-brand-800">{currency(item.lowest_price)}</p>
                                </div>
                                <div className="rounded-xl bg-slate-100 p-3">
                                    <p className="text-xs uppercase tracking-wider text-slate-600">Deposit</p>
                                    <p className="text-lg font-bold text-slate-900">{currency(item.security_deposit)}</p>
                                </div>
                                <div className="rounded-xl bg-coral-50 p-3">
                                    <p className="text-xs uppercase tracking-wider text-coral-700">Condition</p>
                                    <p className="text-lg font-bold capitalize text-coral-800">{item.condition_rating}</p>
                                </div>
                            </div>

                            <div className="mt-6 grid gap-4 sm:grid-cols-2">
                                <label className="block">
                                    <span className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-600">
                                        Start date
                                    </span>
                                    <input
                                        type="date"
                                        value={startDate}
                                        onChange={(event) => setStartDate(event.target.value)}
                                        className="input-shell h-11 w-full text-sm"
                                    />
                                </label>
                                <label className="block">
                                    <span className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-600">
                                        End date
                                    </span>
                                    <input
                                        type="date"
                                        value={endDate}
                                        onChange={(event) => setEndDate(event.target.value)}
                                        className="input-shell h-11 w-full text-sm"
                                    />
                                </label>
                            </div>

                            <div className="mt-4">
                                <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-600">
                                    Variant / size
                                </label>
                                <select
                                    className="input-shell h-11 w-full text-sm"
                                    value={selectedVariant}
                                    onChange={(event) => setSelectedVariant(event.target.value)}
                                >
                                    <option value="">Any available size</option>
                                    {variants.map((variant) => (
                                        <option key={variant.id} value={variant.id}>
                                            {variant.size_label} • {variant.color || 'No color'} • qty {variant.quantity_available}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <button
                                type="button"
                                className="btn-primary mt-5 w-full"
                                onClick={checkAvailabilityNow}
                                disabled={checking}
                            >
                                {checking ? 'Checking availability...' : 'Check availability'}
                            </button>
                        </div>

                        <div className="glass-card p-6">
                            <h2 className="text-lg font-bold">Provider</h2>
                            <p className="mt-2 text-sm text-slate-700">{item.provider?.business_name || 'Unknown provider'}</p>
                            <p className="text-sm text-slate-600">{providerAddress}</p>
                            <div className="mt-4 flex flex-wrap gap-2 text-xs">
                                <span className="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-1 font-semibold text-brand-700">
                                    <SparklesIcon className="h-4 w-4" />
                                    Verified listing
                                </span>
                                <span className="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-slate-700">
                                    <ShieldCheckIcon className="h-4 w-4" />
                                    Cleaning policy included
                                </span>
                                <span className="inline-flex items-center gap-1 rounded-full bg-coral-50 px-2.5 py-1 font-semibold text-coral-700">
                                    <CalendarDaysIcon className="h-4 w-4" />
                                    Flexible durations
                                </span>
                            </div>
                        </div>

                        {availabilityState && (
                            <div className="glass-card p-6">
                                <h2 className="text-lg font-bold">Availability result</h2>
                                <ul className="mt-3 space-y-2 text-sm">
                                    {availabilityState.availability.map((entry) => (
                                        <li
                                            key={entry.variant_id}
                                            className="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2"
                                        >
                                            <span>Variant #{entry.variant_id}</span>
                                            <span
                                                className={`rounded-full px-2 py-1 text-xs font-semibold ${
                                                    entry.available
                                                        ? 'bg-emerald-100 text-emerald-800'
                                                        : 'bg-rose-100 text-rose-800'
                                                }`}
                                            >
                                                {entry.available ? 'Available' : 'Unavailable'}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}
                    </section>
                </div>
            )}
        </MainLayout>
    );
}
