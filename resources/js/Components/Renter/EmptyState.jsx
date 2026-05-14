import React from 'react';
import { MagnifyingGlassIcon } from '@heroicons/react/24/outline';

/**
 * Empty results state.
 * @param {object} props
 * @param {boolean} props.hasFilters
 * @param {() => void} props.onClearFilters
 */
export default function EmptyState({ hasFilters, onClearFilters }) {
    return (
        <div className="glass-card py-14 text-center">
            <MagnifyingGlassIcon className="mx-auto h-12 w-12 text-slate-400" />
            <h3 className="mt-4 text-lg font-bold text-slate-900">No items found</h3>
            <p className="mt-2 text-sm text-slate-600">
                {hasFilters ? 'Try adjusting your filters or date range.' : 'Try searching for another keyword.'}
            </p>
            {hasFilters ? (
                <button type="button" onClick={onClearFilters} className="btn-primary mt-4 !rounded-lg !px-4 !py-2 text-sm">
                    Clear all filters
                </button>
            ) : null}
        </div>
    );
}

