import React from 'react';
import { MapPinIcon, Squares2X2Icon, MapIcon } from '@heroicons/react/24/outline';

/**
 * Results header with sort and view controls.
 * @param {object} props
 * @param {number} props.totalResults
 * @param {'grid'|'map'} props.viewMode
 * @param {(mode:'grid'|'map') => void} props.onViewModeChange
 * @param {string} props.sortValue
 * @param {(event: React.ChangeEvent<HTMLSelectElement>) => void} props.onSortChange
 * @param {boolean} props.hasLocation
 */
export default function ResultsHeader({
    totalResults,
    viewMode,
    onViewModeChange,
    sortValue,
    onSortChange,
    hasLocation,
}) {
    const sortOptions = [
        { value: 'distance', label: 'Nearest First', requiresLocation: true },
        { value: 'price_low', label: 'Price: Low to High' },
        { value: 'price_high', label: 'Price: High to Low' },
        { value: 'newest', label: 'Newest First' },
        { value: 'rating', label: 'Top Rated' },
    ];

    return (
        <div className="sticky top-20 z-40 mt-4 border-y border-white/60 bg-white/80 backdrop-blur">
            <div className="shell py-3">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="text-lg font-bold text-slate-900">
                            {totalResults} {totalResults === 1 ? 'item' : 'items'} found
                        </h2>
                        {hasLocation ? (
                            <p className="mt-0.5 inline-flex items-center text-sm text-slate-600">
                                <MapPinIcon className="mr-1 h-4 w-4" />
                                Sorted by distance from your location
                            </p>
                        ) : null}
                    </div>

                    <div className="flex w-full items-center gap-3 sm:w-auto">
                        <select
                            value={sortValue}
                            onChange={onSortChange}
                            className="input-shell h-10 min-w-48 flex-1 bg-white text-sm sm:flex-none"
                        >
                            {sortOptions.map((option) => {
                                if (option.requiresLocation && !hasLocation) {
                                    return null;
                                }

                                return (
                                    <option key={option.value} value={option.value}>
                                        {option.label}
                                    </option>
                                );
                            })}
                        </select>

                        <div className="inline-flex overflow-hidden rounded-xl border border-slate-200">
                            <button
                                type="button"
                                onClick={() => onViewModeChange('grid')}
                                className={`p-2 ${
                                    viewMode === 'grid'
                                        ? 'bg-brand-600 text-white'
                                        : 'bg-white text-slate-600 hover:bg-slate-50'
                                }`}
                                title="Grid view"
                            >
                                <Squares2X2Icon className="h-5 w-5" />
                                <span className="sr-only">Grid view</span>
                            </button>
                            <button
                                type="button"
                                onClick={() => onViewModeChange('map')}
                                className={`p-2 ${
                                    viewMode === 'map'
                                        ? 'bg-brand-600 text-white'
                                        : 'bg-white text-slate-600 hover:bg-slate-50'
                                }`}
                                title="Map view"
                            >
                                <MapIcon className="h-5 w-5" />
                                <span className="sr-only">Map view</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

