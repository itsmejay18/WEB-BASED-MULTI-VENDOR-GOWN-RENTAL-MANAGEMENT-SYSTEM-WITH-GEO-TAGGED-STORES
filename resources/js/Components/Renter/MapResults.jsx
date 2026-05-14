import React, { useMemo } from 'react';
import { Link } from '@inertiajs/react';
import { MapPinIcon } from '@heroicons/react/24/outline';
import {
    Circle,
    MapContainer,
    Marker,
    Popup,
    TileLayer,
    useMap,
} from 'react-leaflet';
import L from 'leaflet';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

const toNumberOrNull = (value) => {
    const castValue = Number(value);

    return Number.isFinite(castValue) ? castValue : null;
};

function FitMapBounds({ markers, center }) {
    const map = useMap();

    React.useEffect(() => {
        if (markers.length > 0) {
            const bounds = L.latLngBounds(markers.map((marker) => [marker.lat, marker.lng]));
            map.fitBounds(bounds.pad(0.2), { maxZoom: 14 });
            return;
        }

        if (Number.isFinite(center?.lat) && Number.isFinite(center?.lng)) {
            map.setView([center.lat, center.lng], 13);
        }
    }, [map, markers, center]);

    return null;
}

/**
 * Leaflet map results and nearby list.
 * @param {object} props
 * @param {Array<object>} props.items
 * @param {{lat:number,lng:number}|null} props.center
 * @param {number} props.radiusKm
 */
export default function MapResults({ items, center, radiusKm }) {
    const markerItems = useMemo(
        () =>
            items
                .map((item) => {
                    const providerLocation = item.provider?.main_location;
                    const lat = toNumberOrNull(providerLocation?.latitude ?? item.latitude);
                    const lng = toNumberOrNull(providerLocation?.longitude ?? item.longitude);

                    if (lat === null || lng === null) {
                        return null;
                    }

                    return {
                        ...item,
                        lat,
                        lng,
                    };
                })
                .filter(Boolean),
        [items],
    );

    const mapCenter = useMemo(() => {
        if (Number.isFinite(center?.lat) && Number.isFinite(center?.lng)) {
            return center;
        }

        if (markerItems.length > 0) {
            return { lat: markerItems[0].lat, lng: markerItems[0].lng };
        }

        return null;
    }, [center, markerItems]);

    if (!mapCenter) {
        return (
            <div className="glass-card p-8 text-center">
                <MapPinIcon className="mx-auto h-12 w-12 text-slate-400" />
                <h3 className="mt-2 text-sm font-semibold text-slate-900">No map coordinates available</h3>
                <p className="mt-1 text-sm text-slate-600">Try adding a location or pick items with tagged provider locations.</p>
            </div>
        );
    }

    return (
        <div className="glass-card overflow-hidden">
            <div className="h-96 w-full">
                <MapContainer
                    center={[mapCenter.lat, mapCenter.lng]}
                    zoom={13}
                    scrollWheelZoom
                    className="h-full w-full"
                >
                    <TileLayer
                        attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                    />

                    {Number.isFinite(center?.lat) && Number.isFinite(center?.lng) ? (
                        <Circle
                            center={[center.lat, center.lng]}
                            radius={(Number(radiusKm) || 10) * 1000}
                            pathOptions={{ color: '#1d97f5', weight: 2, fillOpacity: 0.08 }}
                        />
                    ) : null}

                    {markerItems.map((item) => (
                        <Marker key={`${item.id}-${item.lat}-${item.lng}`} position={[item.lat, item.lng]}>
                            <Popup>
                                <div className="space-y-1 text-sm">
                                    <p className="font-semibold text-slate-900">{item.name}</p>
                                    <p className="text-slate-600">{item.provider?.business_name || 'Unknown provider'}</p>
                                    {Array.isArray(item.tags) && item.tags.length > 0 ? (
                                        <p className="text-xs text-slate-500">Tags: {item.tags.slice(0, 3).map((tag) => tag.name).join(', ')}</p>
                                    ) : null}
                                    <a href={`/renter/items/${item.slug}`} className="text-xs font-semibold text-brand-700">
                                        View details
                                    </a>
                                </div>
                            </Popup>
                        </Marker>
                    ))}

                    <FitMapBounds markers={markerItems} center={mapCenter} />
                </MapContainer>
            </div>

            <div className="max-h-64 divide-y overflow-y-auto border-t">
                {markerItems.length === 0 ? (
                    <div className="p-4 text-sm text-slate-600">No mappable items found for the current filters.</div>
                ) : null}
                {markerItems.map((item) => (
                    <Link key={item.id} href={`/renter/items/${item.slug}`} className="flex items-center gap-3 p-3 hover:bg-slate-50">
                        <img
                            src={item.primary_photo_url || item.primary_photo?.photo_url || 'https://via.placeholder.com/40'}
                            alt={item.name}
                            className="h-10 w-10 rounded-lg object-cover"
                        />
                        <div className="min-w-0 flex-1">
                            <p className="truncate text-sm font-semibold text-slate-900">{item.name}</p>
                            <p className="truncate text-xs text-slate-500">{item.provider?.business_name}</p>
                            {Array.isArray(item.tags) && item.tags.length > 0 ? (
                                <p className="truncate text-xs text-slate-500">#{item.tags[0].name}</p>
                            ) : null}
                        </div>
                        {item.distance !== null && item.distance !== undefined ? (
                            <p className="text-xs text-slate-500">{Number(item.distance).toFixed(1)} km</p>
                        ) : null}
                    </Link>
                ))}
            </div>
        </div>
    );
}
