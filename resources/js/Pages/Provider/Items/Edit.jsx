import React, { useMemo } from 'react';
import { useForm } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';
import ItemForm from './Form';

export default function ProviderItemsEdit({ item, categories, tags }) {
    const priceMap = useMemo(() => {
        const map = {};
        (item.pricing_tiers ?? []).forEach((tier) => {
            map[tier.duration_days] = tier.price;
        });
        return map;
    }, [item.pricing_tiers]);

    const { data, setData, put, processing, errors } = useForm({
        name: item.name ?? '',
        category_id: item.category_id ?? '',
        description: item.description ?? '',
        brand: item.brand ?? '',
        designer: item.designer ?? '',
        condition_rating: item.condition_rating ?? 'good',
        security_deposit: item.security_deposit ?? 0,
        late_fee_per_day: item.late_fee_per_day ?? 0,
        cleaning_policy: item.cleaning_policy ?? '',
        is_active: item.is_active ?? true,
        requires_approval: item.requires_approval ?? false,
        tag_ids: item.tag_ids ?? [],
        price_day: priceMap[1] ?? '',
        price_3_days: priceMap[3] ?? '',
        price_7_days: priceMap[7] ?? '',
        variants:
            item.variants?.map((variant) => ({
                id: variant.id,
                size_label: variant.size_label ?? '',
                color: variant.color ?? '',
                material: variant.material ?? '',
                quantity_available: variant.quantity_available ?? 0,
                chest_cm: variant.chest_cm ?? '',
                waist_cm: variant.waist_cm ?? '',
                length_cm: variant.length_cm ?? '',
                inseam_cm: variant.inseam_cm ?? '',
                shoulder_cm: variant.shoulder_cm ?? '',
            })) ?? [],
        photos: [],
        remove_photo_ids: [],
    });

    const submit = (event) => {
        event.preventDefault();
        put(`/provider/items/${item.id}`, { forceFormData: true });
    };

    return (
        <MainLayout title="Edit Item">
            <ItemForm
                mode="edit"
                data={data}
                setData={setData}
                errors={errors}
                processing={processing}
                categories={categories}
                tags={tags}
                variants={data.variants}
                setVariants={(variants) => setData('variants', variants)}
                photoFiles={data.photos}
                setPhotoFiles={(photos) => setData('photos', photos)}
                submit={submit}
                existingPhotos={item.photos ?? []}
                removePhotoIds={data.remove_photo_ids}
                setRemovePhotoIds={(ids) => setData('remove_photo_ids', ids)}
            />
        </MainLayout>
    );
}
