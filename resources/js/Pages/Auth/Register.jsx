import React, { useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Register({ prefillRole = 'renter' }) {
    const { data, setData, post, processing, errors } = useForm({
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        role: prefillRole,
        business_name: '',
        password: '',
        password_confirmation: '',
    });

    useEffect(() => {
        setData('role', prefillRole);
    }, [prefillRole]);

    const submit = (event) => {
        event.preventDefault();
        post('/register');
    };

    return (
        <>
            <Head title="Create Account" />
            <div className="flex min-h-screen items-center justify-center px-4 py-10">
                <div className="w-full max-w-2xl rounded-3xl border border-white/70 bg-white/90 p-8 shadow-floating backdrop-blur">
                    <Link href="/" className="inline-flex items-center gap-2 text-sm font-semibold text-brand-700">
                        ← Back to RentFit
                    </Link>

                    <h1 className="mt-4 text-3xl font-bold">Create your account</h1>
                    <p className="mt-1 text-sm text-slate-600">
                        Join as a renter or provider and start booking today.
                    </p>

                    <form className="mt-6 space-y-4" onSubmit={submit}>
                        <div>
                            <label className="mb-2 block text-sm font-semibold text-slate-700">I want to join as</label>
                            <div className="grid grid-cols-2 gap-3">
                                <button
                                    type="button"
                                    onClick={() => setData('role', 'renter')}
                                    className={`rounded-xl border px-4 py-2 text-sm font-semibold transition ${
                                        data.role === 'renter'
                                            ? 'border-brand-300 bg-brand-50 text-brand-700'
                                            : 'border-slate-200 text-slate-700 hover:border-brand-200'
                                    }`}
                                >
                                    Renter
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setData('role', 'provider')}
                                    className={`rounded-xl border px-4 py-2 text-sm font-semibold transition ${
                                        data.role === 'provider'
                                            ? 'border-brand-300 bg-brand-50 text-brand-700'
                                            : 'border-slate-200 text-slate-700 hover:border-brand-200'
                                    }`}
                                >
                                    Provider
                                </button>
                            </div>
                            {errors.role && <p className="mt-1 text-xs font-semibold text-red-600">{errors.role}</p>}
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label className="mb-1 block text-sm font-semibold text-slate-700" htmlFor="first_name">
                                    First name
                                </label>
                                <input
                                    id="first_name"
                                    className="input-shell h-11 w-full text-sm"
                                    value={data.first_name}
                                    onChange={(event) => setData('first_name', event.target.value)}
                                    required
                                />
                                {errors.first_name && <p className="mt-1 text-xs font-semibold text-red-600">{errors.first_name}</p>}
                            </div>
                            <div>
                                <label className="mb-1 block text-sm font-semibold text-slate-700" htmlFor="last_name">
                                    Last name
                                </label>
                                <input
                                    id="last_name"
                                    className="input-shell h-11 w-full text-sm"
                                    value={data.last_name}
                                    onChange={(event) => setData('last_name', event.target.value)}
                                    required
                                />
                                {errors.last_name && <p className="mt-1 text-xs font-semibold text-red-600">{errors.last_name}</p>}
                            </div>
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label className="mb-1 block text-sm font-semibold text-slate-700" htmlFor="email">
                                    Email
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    className="input-shell h-11 w-full text-sm"
                                    value={data.email}
                                    onChange={(event) => setData('email', event.target.value)}
                                    required
                                />
                                {errors.email && <p className="mt-1 text-xs font-semibold text-red-600">{errors.email}</p>}
                            </div>
                            <div>
                                <label className="mb-1 block text-sm font-semibold text-slate-700" htmlFor="phone">
                                    Phone
                                </label>
                                <input
                                    id="phone"
                                    className="input-shell h-11 w-full text-sm"
                                    value={data.phone}
                                    onChange={(event) => setData('phone', event.target.value)}
                                    placeholder="+63..."
                                />
                                {errors.phone && <p className="mt-1 text-xs font-semibold text-red-600">{errors.phone}</p>}
                            </div>
                        </div>

                        {data.role === 'provider' && (
                            <div>
                                <label className="mb-1 block text-sm font-semibold text-slate-700" htmlFor="business_name">
                                    Business name
                                </label>
                                <input
                                    id="business_name"
                                    className="input-shell h-11 w-full text-sm"
                                    value={data.business_name}
                                    onChange={(event) => setData('business_name', event.target.value)}
                                    required={data.role === 'provider'}
                                />
                                {errors.business_name && <p className="mt-1 text-xs font-semibold text-red-600">{errors.business_name}</p>}
                            </div>
                        )}

                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label className="mb-1 block text-sm font-semibold text-slate-700" htmlFor="password">
                                    Password
                                </label>
                                <input
                                    id="password"
                                    type="password"
                                    className="input-shell h-11 w-full text-sm"
                                    value={data.password}
                                    onChange={(event) => setData('password', event.target.value)}
                                    required
                                />
                                {errors.password && <p className="mt-1 text-xs font-semibold text-red-600">{errors.password}</p>}
                            </div>
                            <div>
                                <label className="mb-1 block text-sm font-semibold text-slate-700" htmlFor="password_confirmation">
                                    Confirm password
                                </label>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    className="input-shell h-11 w-full text-sm"
                                    value={data.password_confirmation}
                                    onChange={(event) => setData('password_confirmation', event.target.value)}
                                    required
                                />
                            </div>
                        </div>

                        <button type="submit" className="btn-primary h-11 w-full" disabled={processing}>
                            {processing ? 'Creating account...' : 'Create account'}
                        </button>
                    </form>

                    <p className="mt-5 text-center text-sm text-slate-600">
                        Already registered?{' '}
                        <Link href="/login" className="font-semibold text-brand-700 hover:text-brand-800">
                            Sign in
                        </Link>
                    </p>
                </div>
            </div>
        </>
    );
}

