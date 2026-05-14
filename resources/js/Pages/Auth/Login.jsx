import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (event) => {
        event.preventDefault();
        post('/login');
    };

    return (
        <>
            <Head title="Sign In" />
            <div className="flex min-h-screen items-center justify-center px-4 py-10">
                <div className="w-full max-w-md rounded-3xl border border-white/70 bg-white/90 p-8 shadow-floating backdrop-blur">
                    <Link href="/" className="inline-flex items-center gap-2 text-sm font-semibold text-brand-700">
                        ← Back to RentFit
                    </Link>

                    <h1 className="mt-4 text-3xl font-bold">Sign in</h1>
                    <p className="mt-1 text-sm text-slate-600">
                        Access your rentals, bookings, and provider dashboard.
                    </p>

                    <form className="mt-6 space-y-4" onSubmit={submit}>
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
                                autoComplete="email"
                                required
                            />
                            {errors.email && <p className="mt-1 text-xs font-semibold text-red-600">{errors.email}</p>}
                        </div>

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
                                autoComplete="current-password"
                                required
                            />
                            {errors.password && <p className="mt-1 text-xs font-semibold text-red-600">{errors.password}</p>}
                        </div>

                        <label className="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input
                                type="checkbox"
                                checked={data.remember}
                                onChange={(event) => setData('remember', event.target.checked)}
                                className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            />
                            Keep me signed in
                        </label>

                        <button type="submit" className="btn-primary h-11 w-full" disabled={processing}>
                            {processing ? 'Signing in...' : 'Sign in'}
                        </button>
                    </form>

                    <p className="mt-5 text-center text-sm text-slate-600">
                        New to RentFit?{' '}
                        <Link href="/register" className="font-semibold text-brand-700 hover:text-brand-800">
                            Create an account
                        </Link>
                    </p>
                </div>
            </div>
        </>
    );
}

