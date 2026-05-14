import axios from 'axios';

const client = axios.create({
    baseURL: '/api/v1',
    headers: {
        Accept: 'application/json',
    },
});

export function listCategories() {
    return client.get('/categories').then((response) => response.data.categories ?? []);
}

export function listItems(params = {}) {
    return client.get('/items', { params }).then((response) => response.data);
}

export function getItem(slug) {
    return client.get(`/items/${slug}`).then((response) => response.data.item);
}

export function checkItemAvailability(slug, params) {
    return client.get(`/items/${slug}/availability`, { params }).then((response) => response.data);
}

export function listProviders(params = {}) {
    return client.get('/providers', { params }).then((response) => response.data);
}

export function search(params = {}) {
    return client.get('/search', { params }).then((response) => response.data);
}

export function toCollection(payload) {
    if (Array.isArray(payload)) {
        return payload;
    }

    if (Array.isArray(payload?.data)) {
        return payload.data;
    }

    return [];
}
