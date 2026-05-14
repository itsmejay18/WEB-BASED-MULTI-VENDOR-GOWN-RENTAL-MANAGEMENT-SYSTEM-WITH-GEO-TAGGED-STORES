import React, { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { useQuery } from '@tanstack/react-query';
import {
    ArrowTrendingUpIcon,
    CalendarDaysIcon,
    MapPinIcon,
    MagnifyingGlassIcon,
    SparklesIcon,
    TruckIcon,
} from '@heroicons/react/24/outline';
import MainLayout from '../Layouts/MainLayout';
import { listCategories, listItems, search, toCollection } from '../lib/api';

function currency(value) {
    if (typeof value !== 'number') {
        return 'PHP --';
    }

    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0,
    }).format(value);
}

export default function Home({ initialCoords }) {
    const [keyword, setKeyword] = useState('');

    const categoriesQuery = useQuery({
        queryKey: ['categories-home'],
        queryFn: listCategories,
    });

    const featuredQuery = useQuery({
        queryKey: ['featured-items'],
        queryFn: () => listItems({ per_page: 8 }),
    });

    const nearbyQuery = useQuery({
        queryKey: ['home-nearby', initialCoords.latitude, initialCoords.longitude],
        queryFn: () =>
            search({
                type: 'providers',
                latitude: initialCoords.latitude,
                longitude: initialCoords.longitude,
                radius_km: 12,
                per_page: 6,
            }),
    });

    const categories = categoriesQuery.data ?? [];
    const featuredItems = toCollection(featuredQuery.data);
    const nearbyProviders = toCollection(nearbyQuery.data?.providers);

    const handleHeroSearch = (event) => {
        event.preventDefault();
        const query = keyword.trim();

        router.get('/browse', query ? { keyword: query } : {});
    };

    return (
        <MainLayout title="Discover">
            <section className="relative overflow-hidden rounded-[2rem] border border-white/60 bg-gradient-to-br from-brand-700 via-brand-600 to-coral-500 p-8 text-white shadow-floating sm:p-12">
                <div className="pointer-events-none absolute -right-24 -top-24 h-64 w-64 rounded-full bg-white/10 blur-2xl" />
                <div className="pointer-events-none absolute -bottom-16 left-8 h-44 w-44 rounded-full bg-coral-300/40 blur-2xl" />

                <div className="relative grid gap-10 lg:grid-cols-[1.15fr_0.85fr]">
                    <div className="animate-reveal">
                        <p className="mb-4 inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-widest text-white/90">
                            <SparklesIcon className="h-4 w-4" />
                            Search-first Rental Marketplace
                        </p>
                        <h1 className="max-w-2xl text-4xl font-bold leading-tight text-white sm:text-5xl">
                            Rent standout outfits nearby without the endless store hopping.
                        </h1>
                        <p className="mt-4 max-w-xl text-base text-white/85 sm:text-lg">
                            Filter by style, size, date, and budget. RentFit shows live inventory from local providers
                            with pickup and delivery options.
                        </p>

                        <form onSubmit={handleHeroSearch} className="mt-8 flex flex-col gap-3 sm:flex-row">
                            <label className="relative block flex-1">
                                <MagnifyingGlassIcon className="pointer-events-none absolute left-3 top-3 h-5 w-5 text-slate-400" />
                                <input
                                    className="h-12 w-full rounded-xl border-none bg-white pl-10 pr-3 text-slate-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-coral-300"
                                    value={keyword}
                                    onChange={(event) => setKeyword(event.target.value)}
                                    placeholder="Try: black tuxedo, filipiniana, cocktail dress"
                                />
                            </label>
                            <button type="submit" className="h-12 rounded-xl bg-slate-900 px-7 font-semibold text-white transition hover:bg-slate-800">
                                Search
                            </button>
                        </form>

                        <div className="mt-5 flex flex-wrap gap-2">
                            {['wedding gown', 'barong tagalog', 'interview blazer', 'prom dress'].map((chip) => (
                                <button
                                    key={chip}
                                    type="button"
                                    className="rounded-full border border-white/35 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white/90 transition hover:bg-white/20"
                                    onClick={() => router.get('/browse', { keyword: chip })}
                                >
                                    {chip}
                                </button>
                            ))}
                        </div>
                    </div>

                    <div className="glass-card animate-float p-6 text-slate-800">
                        <h2 className="font-display text-xl font-bold">Why RentFit</h2>
                        <div className="mt-5 grid gap-4 text-sm">
                            <div className="flex items-start gap-3 rounded-xl bg-brand-50 p-3">
                                <MapPinIcon className="mt-0.5 h-5 w-5 text-brand-700" />
                                <div>
                                    <p className="font-semibold">Geo-smart recommendations</p>
                                    <p className="text-slate-600">Find closest providers with available stock.</p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3 rounded-xl bg-coral-50 p-3">
                                <CalendarDaysIcon className="mt-0.5 h-5 w-5 text-coral-700" />
                                <div>
                                    <p className="font-semibold">Date-based availability</p>
                                    <p className="text-slate-600">No guesswork. View what is bookable now.</p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3 rounded-xl bg-slate-100 p-3">
                                <TruckIcon className="mt-0.5 h-5 w-5 text-slate-700" />
                                <div>
                                    <p className="font-semibold">Pickup or delivery</p>
                                    <p className="text-slate-600">Choose the fulfillment method that fits your schedule.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section className="mt-10">
                <div className="mb-5 flex items-center justify-between">
                    <h2 className="text-2xl font-bold">Popular Categories</h2>
                    <Link className="text-sm font-semibold text-brand-700 hover:text-brand-800" href="/browse">
                        Explore all
                    </Link>
                </div>

                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    {categories.slice(0, 8).map((category) => (
                        <Link
                            key={category.id}
                            href={`/browse?category_id=${category.id}`}
                            className="glass-card group p-5 transition hover:-translate-y-1 hover:border-brand-200"
                        >
                            <p className="text-xs font-semibold uppercase tracking-wider text-brand-700">
                                Category
                            </p>
                            <h3 className="mt-2 text-xl font-bold text-slate-900 group-hover:text-brand-700">
                                {category.name}
                            </h3>
                            <p className="mt-2 line-clamp-2 text-sm text-slate-600">
                                {category.description || 'Formal and occasion-ready pieces from verified providers.'}
                            </p>
                        </Link>
                    ))}
                </div>
            </section>

            <section className="mt-12">
                <div className="mb-5 flex items-center justify-between">
                    <h2 className="text-2xl font-bold">Featured Inventory</h2>
                    <Link href="/browse" className="text-sm font-semibold text-brand-700 hover:text-brand-800">
                        Browse all items
                    </Link>
                </div>

                <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    {featuredItems.map((item) => (
                        <Link
                            key={item.id}
                            href={`/item/${item.slug}`}
                            className="glass-card overflow-hidden transition hover:-translate-y-1"
                        >
                            <div className="aspect-[4/5] bg-gradient-to-br from-slate-100 to-slate-200">
                                {item.primary_photo?.photo_url ? (
                                    <img
                                        src={item.primary_photo.photo_url}
                                        alt={item.name}
                                        className="h-full w-full object-cover"
                                        loading="lazy"
                                    />
                                ) : (
                                    <div className="grid h-full place-items-center text-sm font-semibold text-slate-500">
                                        No image
                                    </div>
                                )}
                            </div>
                            <div className="space-y-1 p-4">
                                <p className="line-clamp-1 font-display text-lg font-bold text-slate-900">{item.name}</p>
                                <p className="line-clamp-1 text-sm text-slate-600">{item.brand || 'Unbranded'}</p>
                                <div className="flex items-center justify-between pt-2">
                                    <p className="font-semibold text-brand-700">{currency(item.lowest_price)}</p>
                                    <span className="rounded-full bg-brand-50 px-2 py-1 text-xs font-semibold text-brand-700">
                                        {item.condition_rating}
                                    </span>
                                </div>
                            </div>
                        </Link>
                    ))}
                </div>
            </section>

            <section className="mt-12">
                <div className="mb-5 flex items-center justify-between">
                    <h2 className="text-2xl font-bold">Nearby Providers</h2>
                    <Link href="/providers" className="text-sm font-semibold text-brand-700 hover:text-brand-800">
                        View providers
                    </Link>
                </div>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {nearbyProviders.map((provider) => (
                        <Link
                            key={provider.id}
                            href={`/providers?provider_id=${provider.id}`}
                            className="glass-card p-5 transition hover:-translate-y-1"
                        >
                            <div className="flex items-start gap-4">
                                <div className="grid h-12 w-12 place-items-center rounded-xl bg-brand-100 text-brand-700">
                                    <ArrowTrendingUpIcon className="h-6 w-6" />
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p className="line-clamp-1 font-display text-lg font-bold text-slate-900">
                                        {provider.business_name}
                                    </p>
                                    <p className="mt-1 text-sm text-slate-600">
                                        Rating {Number(provider.rating || 0).toFixed(1)} / 5
                                    </p>
                                    <p className="mt-1 text-xs uppercase tracking-wide text-brand-700">
                                        {provider.main_location?.city || 'Location not set'}
                                    </p>
                                </div>
                            </div>
                        </Link>
                    ))}
                </div>
            </section>
        </MainLayout>
    );
}
