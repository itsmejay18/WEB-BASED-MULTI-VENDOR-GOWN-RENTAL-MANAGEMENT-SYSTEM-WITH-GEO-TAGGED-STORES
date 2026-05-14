import React, { useMemo, useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

export default function ProviderProfileEdit({ provider }) {
    const [logoPreview, setLogoPreview] = useState(provider.logo || null);
    const [coverPreview, setCoverPreview] = useState(provider.cover_photo || null);
    const initials = useMemo(
        () => `${provider.user?.first_name?.[0] || ''}${provider.user?.last_name?.[0] || ''}`.toUpperCase(),
        [provider.user],
    );

    const { data, setData, post, processing, errors } = useForm({
        business_name: provider.business_name || '',
        business_registration: provider.business_registration || '',
        description: provider.description || '',
        first_name: provider.user?.first_name || '',
        last_name: provider.user?.last_name || '',
        email: provider.user?.email || '',
        phone: provider.user?.phone || '',
        logo: null,
        cover_photo: null,
        _method: 'PUT',
    });

    const onSubmit = (event) => {
        event.preventDefault();
        post('/provider/profile', { forceFormData: true });
    };

    return (
        <MainLayout title="Provider Profile">
            <section className="space-y-6">
                <div className="glass-card overflow-hidden">
                    <div className="relative h-48 bg-slate-100">
                        {coverPreview ? <img src={coverPreview} alt="Cover" className="h-full w-full object-cover" /> : null}
                        <label className="absolute bottom-3 right-3 cursor-pointer rounded-lg bg-white/90 px-3 py-1.5 text-xs font-semibold text-slate-800 shadow">
                            Change cover
                            <input
                                className="hidden"
                                type="file"
                                accept="image/*"
                                onChange={(event) => {
                                    const file = event.target.files?.[0];
                                    if (!file) {
                                        return;
                                    }
                                    setData('cover_photo', file);
                                    setCoverPreview(URL.createObjectURL(file));
                                }}
                            />
                        </label>
                    </div>

                    <div className="px-6 pb-6">
                        <div className="-mt-12 flex items-end gap-4">
                            <div className="relative h-24 w-24 overflow-hidden rounded-full border-4 border-white bg-white">
                                {logoPreview ? (
                                    <img src={logoPreview} alt={data.business_name} className="h-full w-full object-cover" />
                                ) : (
                                    <div className="grid h-full w-full place-items-center bg-brand-100 text-2xl font-bold text-brand-700">{initials}</div>
                                )}
                            </div>
                            <label className="cursor-pointer rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white">
                                Change logo
                                <input
                                    className="hidden"
                                    type="file"
                                    accept="image/*"
                                    onChange={(event) => {
                                        const file = event.target.files?.[0];
                                        if (!file) {
                                            return;
                                        }
                                        setData('logo', file);
                                        setLogoPreview(URL.createObjectURL(file));
                                    }}
                                />
                            </label>
                        </div>

                        <form className="mt-6 space-y-5" onSubmit={onSubmit}>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">Business name</label>
                                    <input
                                        className="input-shell h-11 w-full text-sm"
                                        value={data.business_name}
                                        onChange={(event) => setData('business_name', event.target.value)}
                                    />
                                    {errors.business_name ? <p className="mt-1 text-xs font-semibold text-red-600">{errors.business_name}</p> : null}
                                </div>
                                <div>
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">Business registration</label>
                                    <input
                                        className="input-shell h-11 w-full text-sm"
                                        value={data.business_registration}
                                        onChange={(event) => setData('business_registration', event.target.value)}
                                    />
                                    {errors.business_registration ? (
                                        <p className="mt-1 text-xs font-semibold text-red-600">{errors.business_registration}</p>
                                    ) : null}
                                </div>
                                <div>
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">Owner first name</label>
                                    <input
                                        className="input-shell h-11 w-full text-sm"
                                        value={data.first_name}
                                        onChange={(event) => setData('first_name', event.target.value)}
                                    />
                                    {errors.first_name ? <p className="mt-1 text-xs font-semibold text-red-600">{errors.first_name}</p> : null}
                                </div>
                                <div>
                                    <label className="mb-1 block text-sm font-semibold text-slate-700">Owner last name</label>
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
                            <div>
                                <label className="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                                <textarea
                                    className="input-shell min-h-[120px] w-full py-3 text-sm"
                                    value={data.description}
                                    onChange={(event) => setData('description', event.target.value)}
                                />
                                {errors.description ? <p className="mt-1 text-xs font-semibold text-red-600">{errors.description}</p> : null}
                            </div>

                            <div className="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">
                                <p className="font-semibold text-slate-700">Verification status: {provider.verification_status}</p>
                                {provider.is_suspended ? <p className="mt-1 text-red-700">This provider account is currently suspended.</p> : null}
                            </div>

                            <div className="flex flex-wrap justify-between gap-3">
                                <div className="flex flex-wrap gap-2">
                                    <Link href="/provider/locations" className="btn-soft">
                                        Manage locations
                                    </Link>
                                    <Link href="/provider/business-hours" className="btn-soft">
                                        Business hours
                                    </Link>
                                </div>
                                <button className="btn-primary" disabled={processing} type="submit">
                                    {processing ? 'Saving...' : 'Save changes'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </MainLayout>
    );
}
