import React from 'react';
import { Link, router } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

const money = (value) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0,
    }).format(value || 0);

export default function RenterWishlistIndex({ items }) {
    const remove = (id) => {
        router.delete(`/renter/wishlist/${id}`);
    };

    return (
        <MainLayout title="Wishlist">
            <section className="space-y-5">
                <div className="glass-card p-5">
                    <h1 className="text-2xl font-bold">Wishlist</h1>
                    <p className="text-sm text-slate-600">Saved outfits you may want to rent soon.</p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {items.data.map((item) => (
                        <div key={item.id} className="glass-card overflow-hidden">
                            <div className="aspect-[4/3] bg-slate-100">
                                {item.photo_url ? <img src={item.photo_url} alt={item.name} className="h-full w-full object-cover" /> : null}
                            </div>
                            <div className="space-y-2 p-4">
                                <p className="font-bold text-slate-900">{item.name}</p>
                                <p className="text-sm text-slate-600">{item.provider_name}</p>
                                <p className="font-semibold text-brand-700">{money(item.lowest_price)}</p>
                                <div className="flex gap-2">
                                    <Link href={`/item/${item.slug}`} className="btn-soft !px-3 !py-1.5 text-xs">
                                        View
                                    </Link>
                                    <button
                                        type="button"
                                        className="rounded-xl border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50"
                                        onClick={() => remove(item.id)}
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))}
                    {items.data.length === 0 ? <p className="text-sm text-slate-600">Wishlist is empty.</p> : null}
                </div>
            </section>
        </MainLayout>
    );
}

