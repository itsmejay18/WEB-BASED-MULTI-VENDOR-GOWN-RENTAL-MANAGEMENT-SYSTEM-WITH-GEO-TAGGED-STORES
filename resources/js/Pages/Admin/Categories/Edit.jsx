import React from 'react';
import { Link, useForm } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

export default function AdminCategoryEdit({ category, parents }) {
    const { data, setData, put, processing, errors } = useForm({
        name: category.name ?? '',
        parent_id: category.parent_id ?? '',
        description: category.description ?? '',
        image: category.image ?? '',
        is_active: category.is_active ?? true,
        sort_order: category.sort_order ?? 0,
    });

    const submit = (event) => {
        event.preventDefault();
        put(`/admin/categories/${category.id}`);
    };

    return (
        <MainLayout title="Edit Category">
            <form onSubmit={submit} className="glass-card mx-auto max-w-2xl space-y-4 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Edit Category</h1>
                    <Link href="/admin/categories" className="btn-soft">
                        Back
                    </Link>
                </div>
                <input className="input-shell h-11 w-full text-sm" placeholder="Name" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                {errors.name ? <p className="text-xs font-semibold text-red-600">{errors.name}</p> : null}
                <select className="input-shell h-11 w-full text-sm" value={data.parent_id} onChange={(e) => setData('parent_id', e.target.value)}>
                    <option value="">No parent</option>
                    {parents.map((parent) => (
                        <option key={parent.id} value={parent.id}>
                            {parent.name}
                        </option>
                    ))}
                </select>
                <textarea className="input-shell w-full text-sm" rows={3} placeholder="Description" value={data.description} onChange={(e) => setData('description', e.target.value)} />
                <input className="input-shell h-11 w-full text-sm" placeholder="Image URL (optional)" value={data.image} onChange={(e) => setData('image', e.target.value)} />
                <input className="input-shell h-11 w-full text-sm" placeholder="Sort order" type="number" value={data.sort_order} onChange={(e) => setData('sort_order', e.target.value)} />
                <label className="flex items-center gap-2 text-sm">
                    <input type="checkbox" checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} />
                    Active
                </label>
                <button className="btn-primary" disabled={processing} type="submit">
                    {processing ? 'Saving...' : 'Update'}
                </button>
            </form>
        </MainLayout>
    );
}

