import React from 'react';
import MainLayout from '../Layouts/MainLayout';

const endpoints = [
    { method: 'GET', path: '/api/v1/search', description: 'Search items and providers with filters.' },
    { method: 'GET', path: '/api/v1/items', description: 'Paginated catalog items.' },
    { method: 'GET', path: '/api/v1/items/{slug}', description: 'Single item details with variants and pricing.' },
    { method: 'GET', path: '/api/v1/categories', description: 'Top-level categories and nested children.' },
    { method: 'GET', path: '/api/v1/providers', description: 'Provider directory list.' },
    { method: 'POST', path: '/api/v1/auth/register', description: 'Create renter account and return token.' },
    { method: 'POST', path: '/api/v1/auth/login', description: 'Authenticate and return token.' },
];

export default function ApiDocs({ apiBase, currentDate }) {
    return (
        <MainLayout title="API Documentation">
            <section className="space-y-5">
                <div className="glass-card p-6">
                    <h1 className="text-3xl font-bold">RentFit API</h1>
                    <p className="mt-2 text-sm text-slate-600">
                        Base URL: <span className="font-semibold text-slate-800">{apiBase}</span>
                    </p>
                    <p className="mt-1 text-xs uppercase tracking-wider text-brand-700">
                        Snapshot updated {currentDate}
                    </p>
                </div>

                <div className="glass-card overflow-hidden">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">
                                    Method
                                </th>
                                <th className="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">
                                    Endpoint
                                </th>
                                <th className="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">
                                    Description
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 bg-white">
                            {endpoints.map((endpoint) => (
                                <tr key={endpoint.path}>
                                    <td className="px-4 py-3">
                                        <span className="rounded-full bg-brand-50 px-2 py-1 font-semibold text-brand-700">
                                            {endpoint.method}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 font-mono text-xs text-slate-800">{endpoint.path}</td>
                                    <td className="px-4 py-3 text-slate-600">{endpoint.description}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </section>
        </MainLayout>
    );
}
