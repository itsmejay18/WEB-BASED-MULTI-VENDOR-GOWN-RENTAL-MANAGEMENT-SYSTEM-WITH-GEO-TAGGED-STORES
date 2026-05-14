import React, { useCallback, useMemo, useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import { CalendarIcon, XMarkIcon } from '@heroicons/react/24/outline';
import MainLayout from '../../Layouts/MainLayout';
import SearchBar from '../../Components/Renter/SearchBar';
import AdvancedFilters from '../../Components/Renter/AdvancedFilters';
import ResultsHeader from '../../Components/Renter/ResultsHeader';
import ItemCard from '../../Components/Renter/ItemCard';
import Pagination from '../../Components/Renter/Pagination';
import MapResults from '../../Components/Renter/MapResults';
import EmptyState from '../../Components/Renter/EmptyState';
import SkeletonGrid from '../../Components/Renter/SkeletonGrid';

const DEFAULT_RADIUS = 10;

const toNumberOrEmpty = (value) => {
    if (value === null || value === undefined || value === '') {
        return '';
    }

    const castValue = Number(value);

    return Number.isNaN(castValue) ? '' : castValue;
};

/**
 * Main renter search page.
 * @param {object} props
 * @param {object} props.items
 * @param {Array<{id:number,name:string,items_count:number}>} props.categories
 * @param {Array<{id:number,name:string,slug:string,type:string}>} props.tags
 * @param {object} props.filters
 * @param {{latitude:number|null, longitude:number|null}|null} props.userLocation
 */
export default function RenterSearch({ items, categories, tags = [], filters, userLocation }) {
    const [showFilters, setShowFilters] = useState(false);
    const [viewMode, setViewMode] = useState('grid');
    const [isLoading, setIsLoading] = useState(false);
    const [locationError, setLocationError] = useState('');

    const initialLatitude = toNumberOrEmpty(filters.latitude ?? userLocation?.latitude);
    const initialLongitude = toNumberOrEmpty(filters.longitude ?? userLocation?.longitude);
    const initialHasLocation = initialLatitude !== '' && initialLongitude !== '';

    const { data, setData } = useForm({
        keyword: filters.keyword || '',
        category_id: toNumberOrEmpty(filters.category_id),
        occasion: filters.occasion || '',
        tag: filters.tag || '',
        size: filters.size || '',
        color: filters.color || '',
        min_price: toNumberOrEmpty(filters.min_price),
        max_price: toNumberOrEmpty(filters.max_price),
        start_date: filters.start_date || '',
        end_date: filters.end_date || '',
        latitude: initialLatitude,
        longitude: initialLongitude,
        radius_km: toNumberOrEmpty(filters.radius_km) || DEFAULT_RADIUS,
        sort_by: filters.sort_by || (initialHasLocation ? 'distance' : 'newest'),
    });

    const hasLocation = useMemo(() => data.latitude !== '' && data.longitude !== '', [data.latitude, data.longitude]);
    const sortValue = !hasLocation && data.sort_by === 'distance' ? 'newest' : data.sort_by;
    const categoryNameLookup = useMemo(() => new Map(categories.map((category) => [category.id, category.name])), [categories]);
    const tagNameLookup = useMemo(() => new Map(tags.map((tag) => [tag.slug, tag.name])), [tags]);

    const triggerSearch = useCallback(
        (nextData = data) => {
            setIsLoading(true);
            router.get('/renter/search', nextData, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onFinish: () => setIsLoading(false),
            });
        },
        [data],
    );

    const handleSearch = (event) => {
        event?.preventDefault();
        triggerSearch();
    };

    const handleFilterChange = (key, value) => {
        setData(key, value);
    };

    const handleSortChange = (event) => {
        const value = event.target.value;
        const nextData = { ...data, sort_by: value };
        setData(nextData);
        triggerSearch(nextData);
    };

    const clearAllFilters = () => {
        const nextData = {
            ...data,
            category_id: '',
            occasion: '',
            tag: '',
            size: '',
            color: '',
            min_price: '',
            max_price: '',
            start_date: '',
            end_date: '',
            radius_km: DEFAULT_RADIUS,
            sort_by: hasLocation ? 'distance' : 'newest',
        };
        setData(nextData);
        triggerSearch(nextData);
    };

    const removeFilter = (key) => {
        const nextData = {
            ...data,
            [key]: key === 'radius_km' ? DEFAULT_RADIUS : '',
        };
        setData(nextData);
        triggerSearch(nextData);
    };

    const clearPriceFilter = () => {
        const nextData = {
            ...data,
            min_price: '',
            max_price: '',
        };
        setData(nextData);
        triggerSearch(nextData);
    };

    const clearDateFilter = () => {
        const nextData = {
            ...data,
            start_date: '',
            end_date: '',
        };
        setData(nextData);
        triggerSearch(nextData);
    };

    const setKeywordAndSearch = (keyword) => {
        const nextData = { ...data, keyword };
        setData('keyword', keyword);
        triggerSearch(nextData);
    };

    const getUserLocation = () => {
        if (!navigator.geolocation) {
            setLocationError('Geolocation is not supported in this browser.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const latitude = Number(position.coords.latitude.toFixed(6));
                const longitude = Number(position.coords.longitude.toFixed(6));
                const nextData = {
                    ...data,
                    latitude,
                    longitude,
                    sort_by: 'distance',
                };
                setLocationError('');
                setData(nextData);
                triggerSearch(nextData);
            },
            (error) => {
                if (error.code === error.PERMISSION_DENIED) {
                    setLocationError('Location permission denied. Enable location and try again.');
                    return;
                }

                if (error.code === error.POSITION_UNAVAILABLE) {
                    setLocationError('Location is unavailable right now.');
                    return;
                }

                if (error.code === error.TIMEOUT) {
                    setLocationError('Location request timed out. Please retry.');
                    return;
                }

                setLocationError('Unable to fetch location.');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000,
            },
        );
    };

    const activeFilterCount = useMemo(() => {
        let count = 0;

        if (data.category_id) count++;
        if (data.occasion) count++;
        if (data.tag) count++;
        if (data.size) count++;
        if (data.color) count++;
        if (data.min_price || data.max_price) count++;
        if (data.start_date || data.end_date) count++;
        if (Number(data.radius_km) !== DEFAULT_RADIUS) count++;

        return count;
    }, [data]);

    return (
        <MainLayout title="Browse Items">
            <section className="space-y-5">
                <div className="glass-card overflow-hidden">
                    <div className="bg-gradient-to-r from-brand-700 via-brand-600 to-coral-500 p-6 text-white sm:p-8">
                        <h1 className="text-2xl font-bold text-white sm:text-3xl">Find the Right Outfit for Any Occasion</h1>
                        <p className="mt-2 text-sm text-white/85 sm:text-base">
                            Search verified local inventory with availability and location-based results.
                        </p>
                    </div>

                    <div className="p-4 sm:p-6">
                        <SearchBar
                            data={data}
                            categories={categories}
                            onFilterChange={handleFilterChange}
                            onSearch={handleSearch}
                            onToggleFilters={() => setShowFilters((value) => !value)}
                            onUseLocation={getUserLocation}
                            locationError={locationError}
                            processing={isLoading}
                        />

                        <div className="mt-4 flex flex-wrap items-center gap-2">
                            <span className="text-xs font-semibold uppercase tracking-wide text-slate-500">Popular:</span>
                            {['Wedding Gown', 'Barong Tagalog', 'Prom Dress', 'Black Tuxedo'].map((term) => (
                                <button
                                    key={term}
                                    type="button"
                                    onClick={() => setKeywordAndSearch(term)}
                                    className="rounded-full bg-brand-50 px-3 py-1 text-sm font-semibold text-brand-700 transition hover:bg-brand-100"
                                >
                                    {term}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>

                {showFilters ? (
                    <AdvancedFilters
                        data={data}
                        categories={categories}
                        tags={tags}
                        onFilterChange={handleFilterChange}
                        onApply={handleSearch}
                        onClose={() => setShowFilters(false)}
                    />
                ) : null}

                {activeFilterCount > 0 ? (
                    <div className="glass-card p-3">
                        <div className="flex flex-wrap items-center gap-2">
                            <span className="text-xs font-semibold uppercase tracking-wide text-slate-500">Active filters:</span>

                            {data.category_id ? (
                                <span className="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-sm font-semibold text-brand-700">
                                    Category: {categoryNameLookup.get(Number(data.category_id)) || data.category_id}
                                    <button
                                        type="button"
                                        onClick={() => removeFilter('category_id')}
                                        className="ml-2 rounded-full text-brand-700 hover:text-brand-900"
                                    >
                                        <XMarkIcon className="h-4 w-4" />
                                        <span className="sr-only">Remove category filter</span>
                                    </button>
                                </span>
                            ) : null}

                            {data.occasion ? (
                                <span className="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-sm font-semibold text-brand-700">
                                    Occasion: {data.occasion}
                                    <button
                                        type="button"
                                        onClick={() => removeFilter('occasion')}
                                        className="ml-2 rounded-full text-brand-700 hover:text-brand-900"
                                    >
                                        <XMarkIcon className="h-4 w-4" />
                                        <span className="sr-only">Remove occasion filter</span>
                                    </button>
                                </span>
                            ) : null}

                            {data.tag ? (
                                <span className="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-sm font-semibold text-brand-700">
                                    Tag: {tagNameLookup.get(data.tag) || data.tag}
                                    <button
                                        type="button"
                                        onClick={() => removeFilter('tag')}
                                        className="ml-2 rounded-full text-brand-700 hover:text-brand-900"
                                    >
                                        <XMarkIcon className="h-4 w-4" />
                                        <span className="sr-only">Remove tag filter</span>
                                    </button>
                                </span>
                            ) : null}

                            {data.size ? (
                                <span className="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-sm font-semibold text-brand-700">
                                    Size: {data.size}
                                    <button
                                        type="button"
                                        onClick={() => removeFilter('size')}
                                        className="ml-2 rounded-full text-brand-700 hover:text-brand-900"
                                    >
                                        <XMarkIcon className="h-4 w-4" />
                                        <span className="sr-only">Remove size filter</span>
                                    </button>
                                </span>
                            ) : null}

                            {data.color ? (
                                <span className="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-sm font-semibold text-brand-700">
                                    Color: {data.color}
                                    <button
                                        type="button"
                                        onClick={() => removeFilter('color')}
                                        className="ml-2 rounded-full text-brand-700 hover:text-brand-900"
                                    >
                                        <XMarkIcon className="h-4 w-4" />
                                        <span className="sr-only">Remove color filter</span>
                                    </button>
                                </span>
                            ) : null}

                            {data.min_price || data.max_price ? (
                                <span className="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-sm font-semibold text-brand-700">
                                    Price: P{data.min_price || 0} - P{data.max_price || 'any'}
                                    <button type="button" onClick={clearPriceFilter} className="ml-2 rounded-full text-brand-700 hover:text-brand-900">
                                        <XMarkIcon className="h-4 w-4" />
                                        <span className="sr-only">Remove price filter</span>
                                    </button>
                                </span>
                            ) : null}

                            {data.start_date || data.end_date ? (
                                <span className="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-sm font-semibold text-brand-700">
                                    <CalendarIcon className="mr-1 h-4 w-4" />
                                    {data.start_date || 'Any'} - {data.end_date || 'Any'}
                                    <button type="button" onClick={clearDateFilter} className="ml-2 rounded-full text-brand-700 hover:text-brand-900">
                                        <XMarkIcon className="h-4 w-4" />
                                        <span className="sr-only">Remove date filter</span>
                                    </button>
                                </span>
                            ) : null}

                            {Number(data.radius_km) !== DEFAULT_RADIUS ? (
                                <span className="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-sm font-semibold text-brand-700">
                                    Within {data.radius_km} km
                                    <button
                                        type="button"
                                        onClick={() => removeFilter('radius_km')}
                                        className="ml-2 rounded-full text-brand-700 hover:text-brand-900"
                                    >
                                        <XMarkIcon className="h-4 w-4" />
                                        <span className="sr-only">Reset radius filter</span>
                                    </button>
                                </span>
                            ) : null}

                            <button type="button" onClick={clearAllFilters} className="text-sm font-semibold text-slate-600 underline hover:text-slate-800">
                                Clear all
                            </button>
                        </div>
                    </div>
                ) : null}

                <ResultsHeader
                    totalResults={items?.total || 0}
                    viewMode={viewMode}
                    onViewModeChange={setViewMode}
                    sortValue={sortValue}
                    onSortChange={handleSortChange}
                    hasLocation={hasLocation}
                />

                <div>
                    {isLoading ? (
                        <SkeletonGrid viewMode={viewMode} />
                    ) : items?.data?.length > 0 ? (
                        <>
                            {viewMode === 'grid' ? (
                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                    {items.data.map((item) => (
                                        <ItemCard key={item.id} item={item} />
                                    ))}
                                </div>
                            ) : (
                                <MapResults
                                    items={items.data}
                                    center={hasLocation ? { lat: Number(data.latitude), lng: Number(data.longitude) } : null}
                                    radiusKm={Number(data.radius_km) || DEFAULT_RADIUS}
                                />
                            )}

                            {Array.isArray(items.links) && items.links.length > 3 ? <Pagination links={items.links} /> : null}
                        </>
                    ) : (
                        <EmptyState hasFilters={activeFilterCount > 0} onClearFilters={clearAllFilters} />
                    )}
                </div>
            </section>
        </MainLayout>
    );
}
