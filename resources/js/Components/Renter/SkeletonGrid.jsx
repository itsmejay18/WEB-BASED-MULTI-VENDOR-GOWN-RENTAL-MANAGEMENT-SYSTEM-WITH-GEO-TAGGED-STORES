import React from 'react';

/**
 * Loading skeleton for grid or map state.
 * @param {object} props
 * @param {'grid'|'map'} props.viewMode
 */
export default function SkeletonGrid({ viewMode }) {
    if (viewMode === 'grid') {
        return (
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                {Array.from({ length: 9 }).map((_, index) => (
                    <div key={index} className="glass-card animate-pulse overflow-hidden">
                        <div className="aspect-[4/3] bg-slate-200" />
                        <div className="space-y-3 p-4">
                            <div className="h-4 w-3/4 rounded bg-slate-200" />
                            <div className="h-3 w-1/2 rounded bg-slate-200" />
                            <div className="h-6 w-1/3 rounded bg-slate-200" />
                        </div>
                    </div>
                ))}
            </div>
        );
    }

    return (
        <div className="glass-card h-96 p-4">
            <div className="flex h-full items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                Loading map view...
            </div>
        </div>
    );
}

