import React from 'react';
import { Link, router } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

export default function AdminCategoriesIndex({ categories }) {
    const remove = (id) => {
        if (!window.confirm('Delete this category?')) {
            return;
        }
        router.delete(`/admin/categories/${id}`);
    };

    return (
        <MainLayout title="Categories">
            <section className="space-y-5">
                <div className="glass-card flex items-center justify-between p-5">
                    <div>
                        <h1 className="text-2xl font-bold">Category Management</h1>
                    </div>
                    <Link href="/admin/categories/create" className="btn-primary">
                        Add Category
                    </Link>
                </div>

                <div className="glass-card overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th className="px-4 py-3">Name</th>
                                <th className="px-4 py-3">Parent</th>
                                <th className="px-4 py-3">Items</th>
                                <th className="px-4 py-3">Status</th>
                                <th className="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 bg-white">
                            {categories.data.map((category) => (
                                <tr key={category.id}>
                                    <td className="px-4 py-3">
                                        <p className="font-semibold">{category.name}</p>
                                        <p className="text-xs text-slate-500">{category.slug}</p>
                                    </td>
                                    <td className="px-4 py-3">{category.parent || '-'}</td>
                                    <td className="px-4 py-3">{category.items_count}</td>
                                    <td className="px-4 py-3">
                                        <span
                                            className={`rounded-full px-2 py-1 text-xs font-semibold ${
                                                category.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700'
                                            }`}
                                        >
                                            {category.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex gap-2">
                                            <Link href={`/admin/categories/${category.id}/edit`} className="btn-soft !px-3 !py-1.5 text-xs">
                                                Edit
                                            </Link>
                                            <button
                                                className="rounded-xl border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50"
                                                onClick={() => remove(category.id)}
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

