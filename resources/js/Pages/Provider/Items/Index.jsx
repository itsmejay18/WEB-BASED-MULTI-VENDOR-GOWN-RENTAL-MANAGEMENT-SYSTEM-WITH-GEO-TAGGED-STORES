import React from 'react';
import { Link, router } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

const money = (value) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0,
    }).format(value || 0);

export default function ProviderItemsIndex({ items }) {
    const destroyItem = (id) => {
        if (!window.confirm('Delete this item?')) {
            return;
        }

        router.delete(`/provider/items/${id}`);
    };

    return (
        <MainLayout title="Provider Items">
            <section className="space-y-5">
                <div className="glass-card flex items-center justify-between p-5">
                    <div>
                        <h1 className="text-2xl font-bold">My Inventory</h1>
                        <p className="text-sm text-slate-600">Manage your catalog, visibility, and pricing.</p>
                    </div>
                    <Link href="/provider/items/create" className="btn-primary">
                        Add Item
                    </Link>
                </div>

                <div className="glass-card overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th className="px-4 py-3">Item</th>
                                <th className="px-4 py-3">Category</th>
                                <th className="px-4 py-3">Variants</th>
                                <th className="px-4 py-3">Price</th>
                                <th className="px-4 py-3">Status</th>
                                <th className="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 bg-white">
                            {items.data.map((item) => (
                                <tr key={item.id}>
                                    <td className="px-4 py-3">
                                        <p className="font-semibold text-slate-900">{item.name}</p>
                                        <p className="text-xs text-slate-500">{item.condition_rating}</p>
                                    </td>
                                    <td className="px-4 py-3">{item.category || '-'}</td>
                                    <td className="px-4 py-3">{item.variants_count}</td>
                                    <td className="px-4 py-3 font-semibold text-brand-700">{money(item.lowest_price)}</td>
                                    <td className="px-4 py-3">
                                        <span
                                            className={`rounded-full px-2 py-1 text-xs font-semibold ${
                                                item.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700'
                                            }`}
                                        >
                                            {item.is_active ? 'Active' : 'Hidden'}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex gap-2">
                                            <Link className="btn-soft !px-3 !py-1.5 text-xs" href={`/provider/items/${item.id}/edit`}>
                                                Edit
                                            </Link>
                                            <button
                                                type="button"
                                                className="rounded-xl border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50"
                                                onClick={() => destroyItem(item.id)}
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </section>
        </MainLayout>
    );
}

