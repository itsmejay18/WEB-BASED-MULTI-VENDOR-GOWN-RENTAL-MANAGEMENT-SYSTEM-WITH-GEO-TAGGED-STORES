import React from 'react';
import { Link } from '@inertiajs/react';

function VariantRows({ variants, setVariants }) {
    const update = (index, field, value) => {
        const next = [...variants];
        next[index] = { ...next[index], [field]: value };
        setVariants(next);
    };

    const add = () => {
        setVariants([
            ...variants,
            {
                id: null,
                size_label: '',
                color: '',
                material: '',
                quantity_available: 1,
                chest_cm: '',
                waist_cm: '',
                length_cm: '',
                inseam_cm: '',
                shoulder_cm: '',
            },
        ]);
    };

    const remove = (index) => {
        if (variants.length <= 1) {
            return;
        }

        setVariants(variants.filter((_, idx) => idx !== index));
    };

    return (
        <div className="space-y-3">
            <div className="flex items-center justify-between">
                <h3 className="text-lg font-bold">Variants</h3>
                <button type="button" className="btn-soft text-sm" onClick={add}>
                    Add Variant
                </button>
            </div>

            {variants.map((variant, index) => (
                <div key={variant.id ?? `new-${index}`} className="rounded-2xl border border-slate-200 p-4">
                    <div className="mb-3 flex items-center justify-between">
                        <p className="text-xs font-semibold uppercase tracking-widest text-slate-500">Variant {index + 1}</p>
                        {variants.length > 1 ? (
                            <button
                                type="button"
                                onClick={() => remove(index)}
                                className="text-xs font-semibold text-red-600 hover:text-red-700"
                            >
                                Remove
                            </button>
                        ) : null}
                    </div>

                    <div className="grid gap-3 md:grid-cols-4">
                        <input
                            className="input-shell h-10 text-sm"
                            placeholder="Size label"
                            value={variant.size_label ?? ''}
                            onChange={(event) => update(index, 'size_label', event.target.value)}
                        />
                        <input
                            className="input-shell h-10 text-sm"
                            placeholder="Color"
                            value={variant.color ?? ''}
                            onChange={(event) => update(index, 'color', event.target.value)}
                        />
                        <input
                            className="input-shell h-10 text-sm"
                            placeholder="Material"
                            value={variant.material ?? ''}
                            onChange={(event) => update(index, 'material', event.target.value)}
                        />
                        <input
                            className="input-shell h-10 text-sm"
                            placeholder="Qty"
                            type="number"
                            min="0"
                            value={variant.quantity_available ?? 0}
                            onChange={(event) => update(index, 'quantity_available', event.target.value)}
                        />
                    </div>
                </div>
            ))}
        </div>
    );
}

export default function ItemForm({
    mode,
    data,
    setData,
    errors,
    processing,
    categories,
    tags,
    variants,
    setVariants,
    photoFiles,
    setPhotoFiles,
    submit,
    existingPhotos = [],
    removePhotoIds = [],
    setRemovePhotoIds = () => {},
}) {
    const handlePhotos = (event) => {
        const files = Array.from(event.target.files || []);
        setPhotoFiles([...photoFiles, ...files]);
    };

    const removeSelectedPhoto = (index) => {
        setPhotoFiles(photoFiles.filter((_, idx) => idx !== index));
    };

    return (
        <form
            onSubmit={submit}
            className="glass-card space-y-6 p-6"
            encType="multipart/form-data"
        >
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-bold">{mode === 'create' ? 'Create Item' : 'Edit Item'}</h1>
                <Link href="/provider/items" className="btn-soft">
                    Back
                </Link>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <label className="mb-1 block text-xs font-semibold uppercase tracking-widest text-slate-500">Name</label>
                    <input className="input-shell h-11 w-full text-sm" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                    {errors.name ? <p className="mt-1 text-xs font-semibold text-red-600">{errors.name}</p> : null}
                </div>
                <div>
                    <label className="mb-1 block text-xs font-semibold uppercase tracking-widest text-slate-500">Category</label>
                    <select className="input-shell h-11 w-full text-sm" value={data.category_id} onChange={(e) => setData('category_id', e.target.value)}>
                        <option value="">Choose category</option>
                        {categories.map((category) => (
                            <option key={category.id} value={category.id}>
                                {category.name}
                            </option>
                        ))}
                    </select>
                </div>
                <div>
                    <label className="mb-1 block text-xs font-semibold uppercase tracking-widest text-slate-500">Brand</label>
                    <input className="input-shell h-11 w-full text-sm" value={data.brand ?? ''} onChange={(e) => setData('brand', e.target.value)} />
                </div>
                <div>
                    <label className="mb-1 block text-xs font-semibold uppercase tracking-widest text-slate-500">Designer</label>
                    <input className="input-shell h-11 w-full text-sm" value={data.designer ?? ''} onChange={(e) => setData('designer', e.target.value)} />
                </div>
                <div>
                    <label className="mb-1 block text-xs font-semibold uppercase tracking-widest text-slate-500">Condition</label>
                    <select
                        className="input-shell h-11 w-full text-sm"
                        value={data.condition_rating}
                        onChange={(e) => setData('condition_rating', e.target.value)}
                    >
                        <option value="new">New</option>
                        <option value="like_new">Like New</option>
                        <option value="good">Good</option>
                        <option value="fair">Fair</option>
                    </select>
                </div>
                <div>
                    <label className="mb-1 block text-xs font-semibold uppercase tracking-widest text-slate-500">Security Deposit</label>
                    <input
                        className="input-shell h-11 w-full text-sm"
                        type="number"
                        min="0"
                        step="0.01"
                        value={data.security_deposit}
                        onChange={(e) => setData('security_deposit', e.target.value)}
                    />
                </div>
                <div>
                    <label className="mb-1 block text-xs font-semibold uppercase tracking-widest text-slate-500">Daily Price</label>
                    <input
                        className="input-shell h-11 w-full text-sm"
                        type="number"
                        min="1"
                        step="0.01"
                        value={data.price_day}
                        onChange={(e) => setData('price_day', e.target.value)}
                    />
                </div>
                <div>
                    <label className="mb-1 block text-xs font-semibold uppercase tracking-widest text-slate-500">3-Day Price</label>
                    <input
                        className="input-shell h-11 w-full text-sm"
                        type="number"
                        min="1"
                        step="0.01"
                        value={data.price_3_days}
                        onChange={(e) => setData('price_3_days', e.target.value)}
                    />
                </div>
                <div>
                    <label className="mb-1 block text-xs font-semibold uppercase tracking-widest text-slate-500">7-Day Price</label>
                    <input
                        className="input-shell h-11 w-full text-sm"
                        type="number"
                        min="1"
                        step="0.01"
                        value={data.price_7_days}
                        onChange={(e) => setData('price_7_days', e.target.value)}
                    />
                </div>
                <div className="md:col-span-2">
                    <label className="mb-1 block text-xs font-semibold uppercase tracking-widest text-slate-500">Description</label>
                    <textarea
                        className="input-shell w-full text-sm"
                        rows={3}
                        value={data.description ?? ''}
                        onChange={(e) => setData('description', e.target.value)}
                    />
                </div>
            </div>

            <div>
                <label className="mb-2 block text-xs font-semibold uppercase tracking-widest text-slate-500">Tags</label>
                <div className="flex flex-wrap gap-2">
                    {tags.map((tag) => {
                        const checked = data.tag_ids.includes(tag.id);
                        return (
                            <button
                                key={tag.id}
                                type="button"
                                onClick={() => {
                                    if (checked) {
                                        setData('tag_ids', data.tag_ids.filter((id) => id !== tag.id));
                                    } else {
                                        setData('tag_ids', [...data.tag_ids, tag.id]);
                                    }
                                }}
                                className={`rounded-full border px-3 py-1 text-xs font-semibold ${
                                    checked ? 'border-brand-300 bg-brand-50 text-brand-700' : 'border-slate-200 text-slate-700'
                                }`}
                            >
                                {tag.name}
                            </button>
                        );
                    })}
                </div>
            </div>

            <VariantRows variants={variants} setVariants={setVariants} />

            <div className="space-y-3">
                <h3 className="text-lg font-bold">Photos</h3>
                {existingPhotos.length > 0 ? (
                    <div className="grid gap-3 sm:grid-cols-3 lg:grid-cols-4">
                        {existingPhotos.map((photo) => (
                            <label key={photo.id} className="relative block overflow-hidden rounded-xl border border-slate-200">
                                <img src={photo.photo_url} alt="Existing" className="h-28 w-full object-cover" />
                                <span className="absolute bottom-1 left-1 rounded bg-white/90 px-2 py-0.5 text-xs font-semibold text-slate-700">
                                    Existing
                                </span>
                                <input
                                    type="checkbox"
                                    className="absolute right-2 top-2"
                                    checked={removePhotoIds.includes(photo.id)}
                                    onChange={(e) => {
                                        if (e.target.checked) {
                                            setRemovePhotoIds([...removePhotoIds, photo.id]);
                                        } else {
                                            setRemovePhotoIds(removePhotoIds.filter((id) => id !== photo.id));
                                        }
                                    }}
                                />
                            </label>
                        ))}
                    </div>
                ) : null}

                <input type="file" className="input-shell w-full text-sm" multiple accept="image/*" onChange={handlePhotos} />
                {photoFiles.length > 0 ? (
                    <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                        {photoFiles.map((file, index) => (
                            <div key={`${file.name}-${index}`} className="rounded-xl border border-slate-200 p-2 text-xs">
                                <p className="truncate font-semibold text-slate-800">{file.name}</p>
                                <button
                                    type="button"
                                    className="mt-2 text-red-600"
                                    onClick={() => removeSelectedPhoto(index)}
                                >
                                    Remove
                                </button>
                            </div>
                        ))}
                    </div>
                ) : null}
            </div>

            <div className="flex justify-end">
                <button disabled={processing} className="btn-primary" type="submit">
                    {processing ? 'Saving...' : mode === 'create' ? 'Create Item' : 'Update Item'}
                </button>
            </div>
        </form>
    );
}

