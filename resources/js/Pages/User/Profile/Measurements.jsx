import React from 'react';
import { Link, useForm } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';

export default function UserMeasurements({ measurements }) {
    const { data, setData, put, processing, errors } = useForm({
        height_cm: measurements?.height_cm || '',
        weight_kg: measurements?.weight_kg || '',
        chest_cm: measurements?.chest_cm || '',
        waist_cm: measurements?.waist_cm || '',
        hips_cm: measurements?.hips_cm || '',
        inseam_cm: measurements?.inseam_cm || '',
        shoulder_width_cm: measurements?.shoulder_width_cm || '',
        dress_size: measurements?.dress_size || '',
        pant_size: measurements?.pant_size || '',
        shirt_size: measurements?.shirt_size || '',
        shoe_size: measurements?.shoe_size || '',
    });

    const submit = (event) => {
        event.preventDefault();
        put('/user/measurements', { preserveScroll: true });
    };

    return (
        <MainLayout title="My Measurements">
            <section className="space-y-6">
                <div className="glass-card p-5">
                    <div className="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h1 className="text-2xl font-bold">Measurements</h1>
                            <p className="mt-1 text-sm text-slate-600">Save fit details to speed up booking decisions.</p>
                        </div>
                        <Link href="/user/profile" className="btn-soft">
                            Back to profile
                        </Link>
                    </div>
                </div>

                <form className="glass-card space-y-5 p-5" onSubmit={submit}>
                    <div className="grid gap-4 md:grid-cols-3">
                        <label className="text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">Height (cm)</span>
                            <input
                                className="input-shell h-10 w-full text-sm"
                                type="number"
                                step="0.01"
                                value={data.height_cm}
                                onChange={(event) => setData('height_cm', event.target.value)}
                            />
                        </label>
                        <label className="text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">Weight (kg)</span>
                            <input
                                className="input-shell h-10 w-full text-sm"
                                type="number"
                                step="0.01"
                                value={data.weight_kg}
                                onChange={(event) => setData('weight_kg', event.target.value)}
                            />
                        </label>
                        <label className="text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">Chest (cm)</span>
                            <input
                                className="input-shell h-10 w-full text-sm"
                                type="number"
                                step="0.01"
                                value={data.chest_cm}
                                onChange={(event) => setData('chest_cm', event.target.value)}
                            />
                        </label>
                        <label className="text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">Waist (cm)</span>
                            <input
                                className="input-shell h-10 w-full text-sm"
                                type="number"
                                step="0.01"
                                value={data.waist_cm}
                                onChange={(event) => setData('waist_cm', event.target.value)}
                            />
                        </label>
                        <label className="text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">Hips (cm)</span>
                            <input
                                className="input-shell h-10 w-full text-sm"
                                type="number"
                                step="0.01"
                                value={data.hips_cm}
                                onChange={(event) => setData('hips_cm', event.target.value)}
                            />
                        </label>
                        <label className="text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">Inseam (cm)</span>
                            <input
                                className="input-shell h-10 w-full text-sm"
                                type="number"
                                step="0.01"
                                value={data.inseam_cm}
                                onChange={(event) => setData('inseam_cm', event.target.value)}
                            />
                        </label>
                        <label className="text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">Shoulder width (cm)</span>
                            <input
                                className="input-shell h-10 w-full text-sm"
                                type="number"
                                step="0.01"
                                value={data.shoulder_width_cm}
                                onChange={(event) => setData('shoulder_width_cm', event.target.value)}
                            />
                        </label>
                        <label className="text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">Dress size</span>
                            <input
                                className="input-shell h-10 w-full text-sm"
                                value={data.dress_size}
                                onChange={(event) => setData('dress_size', event.target.value)}
                            />
                        </label>
                        <label className="text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">Pant size</span>
                            <input
                                className="input-shell h-10 w-full text-sm"
                                value={data.pant_size}
                                onChange={(event) => setData('pant_size', event.target.value)}
                            />
                        </label>
                        <label className="text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">Shirt size</span>
                            <input
                                className="input-shell h-10 w-full text-sm"
                                value={data.shirt_size}
                                onChange={(event) => setData('shirt_size', event.target.value)}
                            />
                        </label>
                        <label className="text-sm">
                            <span className="mb-1 block font-semibold text-slate-700">Shoe size</span>
                            <input
                                className="input-shell h-10 w-full text-sm"
                                value={data.shoe_size}
                                onChange={(event) => setData('shoe_size', event.target.value)}
                            />
                        </label>
                    </div>
                    {Object.keys(errors).length > 0 ? (
                        <div className="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                            Please review your measurement values and try again.
                        </div>
                    ) : null}
                    <div className="flex justify-end">
                        <button className="btn-primary" disabled={processing} type="submit">
                            {processing ? 'Saving...' : 'Save measurements'}
                        </button>
                    </div>
                </form>
            </section>
        </MainLayout>
    );
}
