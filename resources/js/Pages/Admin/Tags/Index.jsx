import React, { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

const defaultForm = { name: '', type: 'other' };

export default function AdminTagsIndex({ tags }) {
    const [editingTag, setEditingTag] = useState(null);
    const { data, setData, post, processing, reset } = useForm(defaultForm);

    const submit = (event) => {
        event.preventDefault();
        if (editingTag) {
            router.put(`/admin/tags/${editingTag.id}`, data, {
                onSuccess: () => {
                    setEditingTag(null);
                    reset();
                },
            });
            return;
        }
        post('/admin/tags', { onSuccess: () => reset() });
    };

    const edit = (tag) => {
        setEditingTag(tag);
        setData({ name: tag.name, type: tag.type });
    };

    const destroy = (id) => {
        if (!window.confirm('Delete this tag?')) {
            return;
        }
        router.delete(`/admin/tags/${id}`);
    };

    return (
        <MainLayout title="Tags">
            <section className="grid gap-6 xl:grid-cols-[380px_1fr]">
                <form onSubmit={submit} className="glass-card space-y-4 p-5">
                    <h1 className="text-xl font-bold">{editingTag ? 'Edit Tag' : 'Create Tag'}</h1>
                    <input className="input-shell h-11 w-full text-sm" placeholder="Tag name" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                    <select className="input-shell h-11 w-full text-sm" value={data.type} onChange={(e) => setData('type', e.target.value)}>
                        <option value="occasion">Occasion</option>
                        <option value="style">Style</option>
                        <option value="color">Color</option>
                        <option value="material">Material</option>
                        <option value="other">Other</option>
                    </select>
                    <div className="flex gap-2">
                        <button className="btn-primary" type="submit" disabled={processing}>
                            {processing ? 'Saving...' : editingTag ? 'Update' : 'Create'}
                        </button>
                        {editingTag ? (
                            <button
                                type="button"
                                className="btn-soft"
                                onClick={() => {
                                    setEditingTag(null);
                                    reset();
                                }}
                            >
                                Cancel
                            </button>
                        ) : null}
                    </div>
                </form>

                <div className="glass-card overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead className="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th className="px-4 py-3">Name</th>
                                <th className="px-4 py-3">Type</th>
                                <th className="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 bg-white">
                            {tags.data.map((tag) => (
                                <tr key={tag.id}>
                                    <td className="px-4 py-3">
                                        <p className="font-semibold">{tag.name}</p>
                                        <p className="text-xs text-slate-500">{tag.slug}</p>
                                    </td>
                                    <td className="px-4 py-3">{tag.type}</td>
                                    <td className="px-4 py-3">
                                        <div className="flex gap-2">
                                            <button className="btn-soft !px-3 !py-1.5 text-xs" onClick={() => edit(tag)}>
                                                Edit
                                            </button>
                                            <button
                                                className="rounded-xl border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50"
                                                onClick={() => destroy(tag.id)}
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

