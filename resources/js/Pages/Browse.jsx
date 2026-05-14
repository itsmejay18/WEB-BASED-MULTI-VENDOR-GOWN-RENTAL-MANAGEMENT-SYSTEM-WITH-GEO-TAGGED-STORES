import React, { useMemo, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { useQuery } from '@tanstack/react-query';
import { FunnelIcon, MagnifyingGlassIcon, Squares2X2Icon } from '@heroicons/react/24/outline';
import MainLayout from '../Layouts/MainLayout';
import { listCategories, search, toCollection } from '../lib/api';

const sizeOptions = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];
const sortOptions = [
    { value: 'relevance', label: 'Most Relevant' },
    { value: 'price_low', label: 'Price: Low to High' },
    { value: 'price_high', label: 'Price: High to Low' },
    { value: 'rating', label: 'Top Rated' },
    { value: 'newest', label: 'Newest' },
];

function toMoney(value) {
    if (typeof value !== 'number') {
        return '--';
    }

    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0,
    }).format(value);
}

export default function Browse({ filters, pageTitle }) {
    const [form, setForm] = useState({
        keyword: filters.keyword ?? '',
        category_id: filters.category_id ?? '',
        size: filters.size ?? '',
        color: filters.color ?? '',
        min_price: filters.min_price ?? '',
        max_price: filters.max_price ?? '',
        sort_by: filters.sort_by ?? 'relevance',
        per_page: 18,
    });

    const categoriesQuery = useQuery({
        queryKey: ['browse-categories'],
        queryFn: listCategories,
    });

    const searchParams = useMemo(() => {
        const params = { ...form, type: 'items' };

        Object.keys(params).forEach((key) => {
            if (params[key] === '' || params[key] === null || params[key] === undefined) {
                delete params[key];
            }
        });

        return params;
    }, [form]);

    const itemsQuery = useQuery({
        queryKey: ['browse-items', searchParams],
        queryFn: () => search(searchParams),
    });

    const itemsCollection = itemsQuery.data?.items ?? { data: [], meta: { total: 0 } };
    const items = toCollection(itemsCollection);
    const categories = categoriesQuery.data ?? [];

    const submitFilters = (event) => {
        event.preventDefault();
        router.get('/browse', form, { preserveScroll: true, preserveState: true, replace: true });
        itemsQuery.refetch();
    };

    return (
        <MainLayout title={pageTitle}>
            <section className="grid gap-6 lg:grid-cols-[290px_1fr]">
                <aside className="glass-card h-fit p-5">
                    <div className="mb-4 flex items-center gap-2">
                        <FunnelIcon className="h-5 w-5 text-brand-700" />
                        <h2 className="text-lg font-bold">Filters</h2>
                    </div>

                    <form className="space-y-4" onSubmit={submitFilters}>
                        <div>
                            <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Keyword
                            </label>
                            <div className="relative">
                                <MagnifyingGlassIcon className="pointer-events-none absolute left-3 top-3.5 h-4 w-4 text-slate-400" />
                                <input
                                    className="input-shell h-11 w-full pl-9 text-sm"
                                    value={form.keyword}
                                    onChange={(event) => setForm((current) => ({ ...current, keyword: event.target.value }))}
                                    placeholder="tuxedo, filipiniana..."
                                />
                            </div>
                        </div>

                        <div>
                            <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Category
                            </label>
                            <select
                                className="input-shell h-11 w-full text-sm"
                                value={form.category_id}
                                onChange={(event) =>
                                    setForm((current) => ({
                                        ...current,
                                        category_id: event.target.value,
                                    }))
                                }
                            >
                                <option value="">All categories</option>
                                {categories.map((category) => (
                                    <option key={category.id} value={category.id}>
                                        {category.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="grid grid-cols-2 gap-3">
                            <div>
                                <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-600">
                                    Size
                                </label>
                                <select
                                    className="input-shell h-11 w-full text-sm"
                                    value={form.size}
                                    onChange={(event) => setForm((current) => ({ ...current, size: event.target.value }))}
                                >
                                    <option value="">Any</option>
                                    {sizeOptions.map((size) => (
                                        <option key={size} value={size}>
                                            {size}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-600">
                                    Color
                                </label>
                                <input
                                    className="input-shell h-11 w-full text-sm"
                                    value={form.color}
                                    onChange={(event) => setForm((current) => ({ ...current, color: event.target.value }))}
                                    placeholder="Black"
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-3">
                            <div>
                                <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-600">
                                    Min Price
                                </label>
                                <input
                                    className="input-shell h-11 w-full text-sm"
                                    type="number"
                                    min="0"
                                    value={form.min_price}
                                    onChange={(event) =>
                                        setForm((current) => ({ ...current, min_price: event.target.value }))
                                    }
                                />
                            </div>
                            <div>
                                <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-600">
                                    Max Price
                                </label>
                                <input
                                    className="input-shell h-11 w-full text-sm"
                                    type="number"
                                    min="0"
                                    value={form.max_price}
                                    onChange={(event) =>
                                        setForm((current) => ({ ...current, max_price: event.target.value }))
                                    }
                                />
                            </div>
                        </div>

                        <div>
                            <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Sort by
                            </label>
                            <select
                                className="input-shell h-11 w-full text-sm"
                                value={form.sort_by}
                                onChange={(event) => setForm((current) => ({ ...current, sort_by: event.target.value }))}
                            >
                                {sortOptions.map((option) => (
                                    <option key={option.value} value={option.value}>
                                        {option.label}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <button type="submit" className="btn-primary w-full">
                            Apply filters
                        </button>
                    </form>
                </aside>

                <section className="space-y-4">
                    <div className="glass-card flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                        <div>
                            <h1 className="text-2xl font-bold">Browse Inventory</h1>
                            <p className="text-sm text-slate-600">
                                {itemsCollection.meta?.total ?? 0} matches across verified providers
                            </p>
                        </div>
                        <div className="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-brand-700">
                            <Squares2X2Icon className="h-4 w-4" />
                            Live from API
                        </div>
                    </div>

                    {itemsQuery.isLoading ? (
                        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            {Array.from({ length: 6 }).map((_, idx) => (
                                <div key={idx} className="glass-card h-72 animate-pulse bg-slate-100" />
                            ))}
                        </div>
                    ) : (
                        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            {items.map((item) => (
                                <Link
                                    key={item.id}
                                    href={`/item/${item.slug}`}
                                    className="glass-card overflow-hidden transition hover:-translate-y-1"
                                >
                                    <div className="aspect-[4/5] bg-slate-100">
                                        {item.primary_photo?.photo_url ? (
                                            <img
                                                src={item.primary_photo.photo_url}
                                                alt={item.name}
                                                className="h-full w-full object-cover"
                                                loading="lazy"
                                            />
                                        ) : (
                                            <div className="grid h-full place-items-center text-sm text-slate-500">
                                                No image
                                            </div>
                                        )}
                                    </div>
                                    <div className="space-y-1 p-4">
                                        <h3 className="line-clamp-1 font-display text-lg font-bold text-slate-900">
                                            {item.name}
                                        </h3>
                                        <p className="line-clamp-1 text-sm text-slate-600">{item.brand || 'Unbranded'}</p>
                                        <div className="flex items-center justify-between pt-2">
                                            <p className="font-semibold text-brand-700">{toMoney(item.lowest_price)}</p>
                                            <span className="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                                {item.condition_rating}
                                            </span>
                                        </div>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    )}

                    {!itemsQuery.isLoading && items.length === 0 && (
                        <div className="glass-card p-10 text-center">
                            <h2 className="text-xl font-bold">No items found</h2>
                            <p className="mt-2 text-sm text-slate-600">
                                Try a broader keyword or clear some filters.
                            </p>
                            <button
                                type="button"
                                className="btn-soft mt-4"
                                onClick={() =>
                                    setForm({
                                        keyword: '',
                                        category_id: '',
                                        size: '',
                                        color: '',
                                        min_price: '',
                                        max_price: '',
                                        sort_by: 'relevance',
                                        per_page: 18,
                                    })
                                }
                            >
                                Reset filters
                            </button>
                        </div>
                    )}
                </section>
            </section>
        </MainLayout>
    );
}
