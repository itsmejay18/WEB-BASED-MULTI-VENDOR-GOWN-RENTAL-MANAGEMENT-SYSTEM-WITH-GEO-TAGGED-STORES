import React from 'react';
import { useForm } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';
import ItemForm from './Form';

export default function ProviderItemsCreate({ categories, tags }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        category_id: '',
        description: '',
        brand: '',
        designer: '',
        condition_rating: 'good',
        security_deposit: 0,
        late_fee_per_day: 0,
        cleaning_policy: '',
        is_active: true,
        requires_approval: false,
        tag_ids: [],
        price_day: '',
        price_3_days: '',
        price_7_days: '',
        variants: [
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
        ],
        photos: [],
    });

    const submit = (event) => {
        event.preventDefault();
        post('/provider/items', { forceFormData: true });
    };

    return (
        <MainLayout title="Create Item">
            <ItemForm
                mode="create"
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
            />
        </MainLayout>
    );
}
