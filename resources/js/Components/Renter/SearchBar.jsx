import React from 'react';
import {
    MagnifyingGlassIcon,
    FunnelIcon,
    MapPinIcon,
    ChevronDownIcon,
} from '@heroicons/react/24/outline';

/**
 * Search bar component with primary controls.
 * @param {object} props
 * @param {object} props.data
 * @param {Array<{id:number,name:string,items_count:number}>} props.categories
 * @param {(key:string, value:string|number) => void} props.onFilterChange
 * @param {(event?: React.FormEvent) => void} props.onSearch
 * @param {() => void} props.onToggleFilters
 * @param {() => void} props.onUseLocation
 * @param {string} props.locationError
 * @param {boolean} props.processing
 */
export default function SearchBar({
    data,
    categories,
    onFilterChange,
    onSearch,
    onToggleFilters,
    onUseLocation,
    locationError,
    processing,
}) {
    const sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'Plus'];

    return (
        <form onSubmit={onSearch} className="glass-card p-4 sm:p-5">
            <div className="grid grid-cols-1 gap-3 md:grid-cols-4">
                <div className="relative md:col-span-2">
                    <MagnifyingGlassIcon className="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                    <input
                        type="text"
                        value={data.keyword}
                        onChange={(event) => onFilterChange('keyword', event.target.value)}
                        placeholder="Search outfit, dress, tuxedo..."
                        className="input-shell h-11 w-full pl-10 pr-3 text-sm"
                    />
                </div>

                <div className="relative">
                    <select
                        value={data.category_id}
                        onChange={(event) =>
                            onFilterChange('category_id', event.target.value ? Number(event.target.value) : '')
                        }
                        className="input-shell h-11 w-full appearance-none pr-10 text-sm"
                    >
                        <option value="">All categories</option>
                        {categories.map((category) => (
                            <option key={category.id} value={category.id}>
                                {category.name} ({category.items_count})
                            </option>
                        ))}
                    </select>
                    <ChevronDownIcon className="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                </div>

                <div className="relative">
                    <select
                        value={data.size}
                        onChange={(event) => onFilterChange('size', event.target.value)}
                        className="input-shell h-11 w-full appearance-none pr-10 text-sm"
                    >
                        <option value="">All sizes</option>
                        {sizes.map((size) => (
                            <option key={size} value={size}>
                                Size {size}
                            </option>
                        ))}
                    </select>
                    <ChevronDownIcon className="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                </div>

                <div className="relative">
                    <span className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">P</span>
                    <input
                        type="number"
                        min="0"
                        step="100"
                        value={data.max_price}
                        onChange={(event) =>
                            onFilterChange('max_price', event.target.value ? Number(event.target.value) : '')
                        }
                        placeholder="Max budget"
                        className="input-shell h-11 w-full pl-8 pr-3 text-sm"
                    />
                </div>
            </div>

            <div className="mt-4 flex flex-wrap items-center justify-between gap-3">
                <div className="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        onClick={onToggleFilters}
                        className="btn-soft !rounded-lg !px-3 !py-1.5 text-sm"
                    >
                        <FunnelIcon className="mr-1 h-4 w-4" />
                        Filters
                    </button>
                    <button
                        type="button"
                        onClick={onUseLocation}
                        className="btn-soft !rounded-lg !px-3 !py-1.5 text-sm"
                    >
                        <MapPinIcon className="mr-1 h-4 w-4" />
                        Near Me
                    </button>
                    {locationError ? <span className="text-xs font-medium text-coral-700">{locationError}</span> : null}
                </div>

                <button type="submit" disabled={processing} className="btn-primary !rounded-lg !px-6 !py-2 text-sm">
                    {processing ? 'Searching...' : 'Search'}
                </button>
            </div>
        </form>
    );
}

