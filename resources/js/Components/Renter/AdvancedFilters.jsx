import React from 'react';
import { XMarkIcon } from '@heroicons/react/24/outline';

/**
 * Advanced filters panel.
 * @param {object} props
 * @param {object} props.data
 * @param {Array<{id:number,name:string,items_count:number}>} props.categories
 * @param {Array<{id:number,name:string,slug:string,type:string}>} props.tags
 * @param {(key:string, value:string|number) => void} props.onFilterChange
 * @param {() => void} props.onApply
 * @param {() => void} props.onClose
 */
export default function AdvancedFilters({ data, categories, tags, onFilterChange, onApply, onClose }) {
    const occasions = [
        'wedding',
        'prom',
        'graduation',
        'interview',
        'cocktail',
        'black tie',
        'corporate',
        'photoshoot',
    ];
    const sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'Plus'];
    const today = new Date().toISOString().split('T')[0];

    return (
        <div className="glass-card mt-4 p-5">
            <div className="mb-4 flex items-center justify-between">
                <h2 className="text-lg font-bold text-slate-900">Advanced Filters</h2>
                <button type="button" onClick={onClose} className="rounded-lg p-1 text-slate-500 hover:bg-slate-100">
                    <XMarkIcon className="h-5 w-5" />
                    <span className="sr-only">Close filters</span>
                </button>
            </div>

            <div className="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p className="mb-2 text-sm font-semibold text-slate-700">Category</p>
                    <div className="max-h-44 space-y-2 overflow-y-auto pr-1">
                        <label className="flex cursor-pointer items-center text-sm text-slate-700">
                            <input
                                type="radio"
                                name="category_id"
                                value=""
                                checked={data.category_id === ''}
                                onChange={() => onFilterChange('category_id', '')}
                                className="h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-500"
                            />
                            <span className="ml-2">All categories</span>
                        </label>
                        {categories.map((category) => (
                            <label key={category.id} className="flex cursor-pointer items-center text-sm text-slate-700">
                                <input
                                    type="radio"
                                    name="category_id"
                                    value={category.id}
                                    checked={Number(data.category_id) === category.id}
                                    onChange={() => onFilterChange('category_id', category.id)}
                                    className="h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-500"
                                />
                                <span className="ml-2">
                                    {category.name} ({category.items_count})
                                </span>
                            </label>
                        ))}
                    </div>
                </div>

                <div>
                    <label className="mb-2 block text-sm font-semibold text-slate-700">Occasion</label>
                    <select
                        value={data.occasion}
                        onChange={(event) => onFilterChange('occasion', event.target.value)}
                        className="input-shell h-11 w-full text-sm"
                    >
                        <option value="">Any occasion</option>
                        {occasions.map((occasion) => (
                            <option key={occasion} value={occasion}>
                                {occasion
                                    .split(' ')
                                    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
                                    .join(' ')}
                            </option>
                        ))}
                    </select>

                    <label className="mb-2 mt-4 block text-sm font-semibold text-slate-700">Tag</label>
                    <select
                        value={data.tag}
                        onChange={(event) => onFilterChange('tag', event.target.value)}
                        className="input-shell h-11 w-full text-sm"
                    >
                        <option value="">Any tag</option>
                        {tags.map((tag) => (
                            <option key={tag.id} value={tag.slug}>
                                {tag.name} ({tag.type})
                            </option>
                        ))}
                    </select>

                    <label className="mb-2 mt-4 block text-sm font-semibold text-slate-700">Color</label>
                    <input
                        type="text"
                        value={data.color}
                        onChange={(event) => onFilterChange('color', event.target.value)}
                        placeholder="e.g. black, navy, ivory"
                        className="input-shell h-11 w-full text-sm"
                    />
                </div>

                <div>
                    <p className="mb-2 text-sm font-semibold text-slate-700">Size</p>
                    <div className="grid grid-cols-3 gap-2">
                        {sizes.map((size) => (
                            <button
                                key={size}
                                type="button"
                                onClick={() => onFilterChange('size', data.size === size ? '' : size)}
                                className={`rounded-lg px-2 py-1.5 text-sm font-semibold transition ${
                                    data.size === size
                                        ? 'bg-brand-600 text-white'
                                        : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                                }`}
                            >
                                {size}
                            </button>
                        ))}
                    </div>

                    <div className="mt-4 grid grid-cols-2 gap-2">
                        <div>
                            <label className="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Min price
                            </label>
                            <input
                                type="number"
                                min="0"
                                step="100"
                                value={data.min_price}
                                onChange={(event) =>
                                    onFilterChange('min_price', event.target.value ? Number(event.target.value) : '')
                                }
                                className="input-shell h-10 w-full text-sm"
                            />
                        </div>
                        <div>
                            <label className="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Max price
                            </label>
                            <input
                                type="number"
                                min="0"
                                step="100"
                                value={data.max_price}
                                onChange={(event) =>
                                    onFilterChange('max_price', event.target.value ? Number(event.target.value) : '')
                                }
                                className="input-shell h-10 w-full text-sm"
                            />
                        </div>
                    </div>
                </div>

                <div>
                    <p className="mb-2 text-sm font-semibold text-slate-700">Availability Dates</p>
                    <div className="space-y-2">
                        <input
                            type="date"
                            value={data.start_date}
                            onChange={(event) => onFilterChange('start_date', event.target.value)}
                            min={today}
                            className="input-shell h-11 w-full text-sm"
                        />
                        <input
                            type="date"
                            value={data.end_date}
                            onChange={(event) => onFilterChange('end_date', event.target.value)}
                            min={data.start_date || today}
                            className="input-shell h-11 w-full text-sm"
                        />
                    </div>
                </div>
            </div>

            <div className="mt-5">
                <label className="mb-2 block text-sm font-semibold text-slate-700">Search Radius: {data.radius_km} km</label>
                <input
                    type="range"
                    min="1"
                    max="50"
                    value={data.radius_km}
                    onChange={(event) => onFilterChange('radius_km', Number(event.target.value))}
                    className="h-2 w-full cursor-pointer appearance-none rounded-lg bg-slate-200 accent-brand-600"
                />
                <div className="mt-1 flex justify-between text-xs text-slate-500">
                    <span>1km</span>
                    <span>25km</span>
                    <span>50km</span>
                </div>
            </div>

            <div className="mt-5 flex justify-end">
                <button type="button" onClick={onApply} className="btn-primary !rounded-lg !px-4 !py-2 text-sm">
                    Apply Filters
                </button>
            </div>
        </div>
    );
}
