<?php

return [
    'earth_radius_km' => 6371,
    'default_radius_km' => (float) env('GEO_DEFAULT_RADIUS', env('GEO_DEFAULT_SEARCH_RADIUS_KM', 10)),
    'default_search_radius_km' => (float) env('GEO_DEFAULT_SEARCH_RADIUS_KM', env('GEO_DEFAULT_RADIUS', 10)),
    'max_search_radius_km' => (float) env('GEO_MAX_SEARCH_RADIUS_KM', 100),
    'default_latitude' => (float) env('GEO_DEFAULT_LATITUDE', 14.5995),
    'default_longitude' => (float) env('GEO_DEFAULT_LONGITUDE', 120.9842),
];
