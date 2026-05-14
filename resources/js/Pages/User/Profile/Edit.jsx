import React, { useMemo, useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

export default function UserProfileEdit({ user }) {
    const [photoPreview, setPhotoPreview] = useState(user.profile_photo || null);
    const initials = useMemo(
        () => `${user.first_name?.[0] || ''}${user.last_name?.[0] || ''}`.toUpperCase(),
        [user.first_name, user.last_name],
    );

    const { data, setData, post, processing, errors } = useForm({
        first_name: user.first_name || '',
        last_name: user.last_name || '',
        email: user.email || '',
        phone: user.phone || '',
        profile_photo: null,
        _method: 'PUT',
    });

    const submit = (event) => {
        event.preventDefault();
        post('/user/profile', { forceFormData: true });
    };

    return (
        <MainLayout title="My Profile">
            <section className="space-y-6">
                <div className="glass-card overflow-hidden">
                    <div className="bg-gradient-to-r from-brand-700 to-brand-500 px-6 py-8 text-white">
                        <h1 className="text-2xl font-bold">My Profile</h1>
                        <p className="mt-1 text-sm text-white/85">Update your account details and contact information.</p>
                    </div>
                    <div className="px-6 py-6">
                        <div className="mb-6 flex items-center gap-4">
                            <div className="relative h-20 w-20 overflow-hidden rounded-full bg-slate-100">
                                {photoPreview ? (
                                    <img src={photoPreview} alt={user.full_name} className="h-full w-full object-cover" />
                                ) : (
                                    <div className="grid h-full w-full place-items-center bg-brand-100 text-xl font-bold text-brand-700">{initials}</div>
                                )}
                            </div>
                            <div>
                                <label className="cursor-pointer rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    Change photo
                                    <input
                                        className="hidden"
                                        type="file"
                                        accept="image/*"
                                        onChange={(event) => {
                                            const file = event.target.files?.[0];
                                            if (!file) {
                                                return;
                                            }
                                            setData('profile_photo', file);
                                            setPhotoPreview(URL.createObjectURL(file));
                                        }}
                                    />
                                </label>
                                <p className="mt-2 text-xs text-slate-500">
                                    Member since {user.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A'}
                                </p>
                            </div>
                        </div>

                        <form className="space-y-5" onSubmit={submit}>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">First name</label>
                                    <input
                                        className="input-shell h-11 w-full text-sm"
                                        value={data.first_name}
                                        onChange={(event) => setData('first_name', event.target.value)}
                                    />
                                    {errors.first_name ? <p className="mt-1 text-xs font-semibold text-red-600">{errors.first_name}</p> : null}
                                </div>
                                <div>
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">Last name</label>
                                    <input
                                        className="input-shell h-11 w-full text-sm"
                                        value={data.last_name}
                                        onChange={(event) => setData('last_name', event.target.value)}
                                    />
                                    {errors.last_name ? <p className="mt-1 text-xs font-semibold text-red-600">{errors.last_name}</p> : null}
                                </div>
                                <div>
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">Email</label>
                                    <input
                                        className="input-shell h-11 w-full text-sm"
                                        type="email"
                                        value={data.email}
                                        onChange={(event) => setData('email', event.target.value)}
                                    />
                                    {errors.email ? <p className="mt-1 text-xs font-semibold text-red-600">{errors.email}</p> : null}
                                </div>
                                <div>
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">Phone</label>
                                    <input
                                        className="input-shell h-11 w-full text-sm"
                                        value={data.phone}
                                        onChange={(event) => setData('phone', event.target.value)}
                                    />
                                    {errors.phone ? <p className="mt-1 text-xs font-semibold text-red-600">{errors.phone}</p> : null}
                                </div>
                            </div>
                            <div className="flex justify-end">
                                <button className="btn-primary" disabled={processing} type="submit">
                                    {processing ? 'Saving...' : 'Save changes'}
                                </button>
                            </div>
                        </form>

                        <div className="mt-8 grid gap-3 sm:grid-cols-2">
                            <Link href="/user/measurements" className="rounded-xl border border-slate-200 p-4 hover:bg-slate-50">
                                <p className="font-semibold text-slate-900">Measurements</p>
                                <p className="text-sm text-slate-600">Manage your body measurements for better fit suggestions.</p>
                            </Link>
                            <Link href="/user/addresses" className="rounded-xl border border-slate-200 p-4 hover:bg-slate-50">
                                <p className="font-semibold text-slate-900">Addresses</p>
                                <p className="text-sm text-slate-600">Add and edit delivery addresses.</p>
                            </Link>
                        </div>
                    </div>
                </div>
            </section>
        </MainLayout>
    );
}
