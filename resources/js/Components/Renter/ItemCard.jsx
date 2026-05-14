import React from 'react';
import { Link } from '@inertiajs/react';
import { MapPinIcon, StarIcon } from '@heroicons/react/24/outline';

const money = (value) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0,
    }).format(value || 0);

/**
 * Search result card.
 * @param {object} props
 * @param {object} props.item
 */
export default function ItemCard({ item }) {
    const photoUrl = item.primary_photo_url || item.primary_photo?.photo_url || 'https://via.placeholder.com/400x300?text=No+Image';
    const distance = item.distance !== null && item.distance !== undefined ? Number(item.distance) : null;
    const rating = item.rating !== null && item.rating !== undefined ? Number(item.rating) : null;
    const tags = Array.isArray(item.tags) ? item.tags : [];
    const conditionLabel = item.condition_rating ? String(item.condition_rating).replace(/_/g, ' ') : null;

    return (
        <Link href={`/renter/items/${item.slug}`} className="glass-card group overflow-hidden transition hover:-translate-y-1">
            <div className="relative aspect-[4/3] overflow-hidden bg-slate-100">
                <img
                    src={photoUrl}
                    alt={item.name}
                    className="h-full w-full object-cover transition duration-200 group-hover:scale-105"
                    loading="lazy"
                />

                {distance !== null ? (
                    <span className="absolute right-2 top-2 inline-flex items-center rounded-full bg-white/90 px-2 py-1 text-xs text-slate-700 shadow-sm">
                        <MapPinIcon className="mr-1 h-3 w-3 text-slate-500" />
                        {distance.toFixed(1)} km
                    </span>
                ) : null}

                {conditionLabel ? (
                    <span className="absolute left-2 top-2 rounded-full bg-slate-900/80 px-2 py-1 text-xs font-semibold text-white">
                        {conditionLabel}
                    </span>
                ) : null}
            </div>

            <div className="space-y-2 p-4">
                <div>
                    <h3 className="line-clamp-1 font-bold text-slate-900 group-hover:text-brand-700">{item.name}</h3>
                    <p className="line-clamp-1 text-sm text-slate-600">{item.provider?.business_name || 'Unknown provider'}</p>
                </div>

                {rating && rating > 0 ? (
                    <p className="inline-flex items-center text-sm text-slate-600">
                        <StarIcon className="mr-1 h-4 w-4 text-amber-500" />
                        {rating.toFixed(1)}
                    </p>
                ) : null}

                {tags.length > 0 ? (
                    <div className="flex flex-wrap gap-1">
                        {tags.slice(0, 3).map((tag) => (
                            <span key={tag.id} className="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-700">
                                #{tag.name}
                            </span>
                        ))}
                    </div>
                ) : null}

                <div className="flex items-end justify-between">
                    <div>
                        <p className="text-lg font-bold text-brand-700">{money(item.lowest_price)}</p>
                        <p className="text-xs text-slate-500">starting price</p>
                    </div>
                    <span className="text-xs font-semibold text-brand-700 group-hover:underline">View Details</span>
                </div>
            </div>
        </Link>
    );
}
