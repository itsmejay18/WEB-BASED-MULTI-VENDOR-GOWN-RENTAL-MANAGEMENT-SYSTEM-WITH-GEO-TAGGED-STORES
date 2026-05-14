import React from 'react';
import { Link } from '@inertiajs/react';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/react/24/outline';

/**
 * Safe pagination renderer for Laravel paginator links.
 * @param {object} props
 * @param {Array<{url:string|null,label:string,active:boolean}>} props.links
 */
export default function Pagination({ links }) {
    if (!Array.isArray(links) || links.length <= 3) {
        return null;
    }

    const getLabel = (rawLabel) => {
        const noTags = String(rawLabel).replace(/<[^>]+>/g, '').trim();
        const decoded = noTags
            .replace(/&laquo;/g, '<<')
            .replace(/&raquo;/g, '>>')
            .replace(/&amp;/g, '&')
            .replace(/&nbsp;/g, ' ');

        if (/previous/i.test(decoded)) {
            return 'Previous';
        }
        if (/next/i.test(decoded)) {
            return 'Next';
        }

        return decoded;
    };

    return (
        <div className="mt-8 flex justify-center">
            <nav className="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white p-1" aria-label="Pagination">
                {links.map((link, index) => {
                    const label = getLabel(link.label);
                    const isPrevious = label === 'Previous';
                    const isNext = label === 'Next';
                    const classes = link.active
                        ? 'rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white'
                        : 'rounded-lg px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100';

                    if (isPrevious || isNext) {
                        const icon = isPrevious ? <ChevronLeftIcon className="h-5 w-5" /> : <ChevronRightIcon className="h-5 w-5" />;

                        if (!link.url) {
                            return (
                                <span key={index} className="rounded-lg px-2 py-1.5 text-slate-300">
                                    {icon}
                                    <span className="sr-only">{label}</span>
                                </span>
                            );
                        }

                        return (
                            <Link key={index} href={link.url} preserveScroll className="rounded-lg px-2 py-1.5 text-slate-700 hover:bg-slate-100">
                                {isPrevious ? (
                                    <>
                                        {icon}
                                        <span className="sr-only">{label}</span>
                                    </>
                                ) : (
                                    <>
                                        <span className="sr-only">{label}</span>
                                        {icon}
                                    </>
                                )}
                            </Link>
                        );
                    }

                    if (!link.url) {
                        return (
                            <span key={index} className="rounded-lg px-3 py-1.5 text-sm font-semibold text-slate-300">
                                {label}
                            </span>
                        );
                    }

                    return (
                        <Link key={index} href={link.url} preserveScroll className={classes}>
                            {label}
                        </Link>
                    );
                })}
            </nav>
        </div>
    );
}

