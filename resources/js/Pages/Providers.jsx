import React, { useMemo, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { BuildingStorefrontIcon, MagnifyingGlassIcon, StarIcon } from '@heroicons/react/24/outline';
import MainLayout from '../Layouts/MainLayout';
import { search, toCollection } from '../lib/api';

export default function Providers({ initialFilters }) {
    const [keyword, setKeyword] = useState(initialFilters.keyword ?? '');
    const [radiusKm, setRadiusKm] = useState(initialFilters.radius_km ?? 20);

    const queryParams = useMemo(
        () => ({
            type: 'providers',
            keyword: keyword || undefined,
            latitude: initialFilters.latitude,
            longitude: initialFilters.longitude,
            radius_km: radiusKm,
            per_page: 24,
            sort_by: 'rating',
        }),
        [initialFilters.latitude, initialFilters.longitude, keyword, radiusKm],
    );

    const providersQuery = useQuery({
        queryKey: ['providers-page', queryParams],
        queryFn: () => search(queryParams),
    });

    const providers = toCollection(providersQuery.data?.providers);

    return (
        <MainLayout title="Providers">
            <section className="space-y-5">
                <div className="glass-card p-5">
                    <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <h1 className="text-3xl font-bold">Rental Providers</h1>
                            <p className="mt-1 text-sm text-slate-600">
                                Compare ratings, location, and inventory from verified shops.
                            </p>
                        </div>
                        <div className="grid gap-3 sm:grid-cols-[1fr_130px]">
                            <label className="relative block">
                                <MagnifyingGlassIcon className="pointer-events-none absolute left-3 top-3.5 h-4 w-4 text-slate-400" />
                                <input
                                    className="input-shell h-11 min-w-[250px] pl-9 text-sm"
                                    value={keyword}
                                    onChange={(event) => setKeyword(event.target.value)}
                                    placeholder="Search provider..."
                                />
                            </label>
                            <label className="block">
                                <span className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-600">
                                    Radius km
                                </span>
                                <input
                                    type="number"
                                    min="1"
                                    max="100"
                                    className="input-shell h-11 w-full text-sm"
                                    value={radiusKm}
                                    onChange={(event) => setRadiusKm(Number(event.target.value || 10))}
                                />
                            </label>
                        </div>
                    </div>
                </div>

                {providersQuery.isLoading ? (
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {Array.from({ length: 6 }).map((_, idx) => (
                            <div key={idx} className="glass-card h-52 animate-pulse bg-slate-100" />
                        ))}
                    </div>
                ) : (
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {providers.map((provider) => (
                            <article key={provider.id} className="glass-card p-5">
                                <div className="flex items-start gap-3">
                                    <div className="grid h-12 w-12 place-items-center rounded-xl bg-brand-100 text-brand-700">
                                        <BuildingStorefrontIcon className="h-6 w-6" />
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <h2 className="line-clamp-1 font-display text-xl font-bold text-slate-900">
                                            {provider.business_name}
                                        </h2>
                                        <p className="line-clamp-2 text-sm text-slate-600">
                                            {provider.description || 'No description provided yet.'}
                                        </p>
                                    </div>
                                </div>

                                <div className="mt-4 grid grid-cols-2 gap-3 text-sm">
                                    <div className="rounded-xl bg-brand-50 p-3">
                                        <p className="text-xs uppercase tracking-wider text-brand-700">Rating</p>
                                        <p className="mt-1 inline-flex items-center gap-1 font-semibold text-brand-800">
                                            <StarIcon className="h-4 w-4" />
                                            {Number(provider.rating || 0).toFixed(1)}
                                        </p>
                                    </div>
                                    <div className="rounded-xl bg-slate-100 p-3">
                                        <p className="text-xs uppercase tracking-wider text-slate-600">Status</p>
                                        <p className="mt-1 font-semibold capitalize text-slate-800">
                                            {provider.verification_status}
                                        </p>
                                    </div>
                                </div>
                            </article>
                        ))}
                    </div>
                )}

                {!providersQuery.isLoading && providers.length === 0 && (
                    <div className="glass-card p-10 text-center">
                        <h2 className="text-xl font-bold">No providers found</h2>
                        <p className="mt-2 text-sm text-slate-600">
                            Try expanding your radius or using a different keyword.
                        </p>
                    </div>
                )}
            </section>
        </MainLayout>
    );
}
