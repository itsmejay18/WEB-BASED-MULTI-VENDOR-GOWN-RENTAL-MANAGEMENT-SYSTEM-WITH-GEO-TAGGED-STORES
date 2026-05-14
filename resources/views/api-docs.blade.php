<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentFit API Documentation</title>
    <style>
        :root {
            --bg: #f3f6fb;
            --panel: #ffffff;
            --ink: #1f2937;
            --muted: #6b7280;
            --accent: #0f766e;
            --line: #dbe3ef;
            --chip: #ecfeff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(900px 500px at 95% -10%, #c4f1f9 0%, transparent 60%),
                radial-gradient(700px 350px at -10% 10%, #c7d2fe 0%, transparent 55%),
                var(--bg);
        }
        .wrap {
            max-width: 980px;
            margin: 0 auto;
            padding: 40px 20px 64px;
        }
        .hero {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.07);
        }
        h1 {
            margin: 0 0 8px;
            font-size: 2rem;
            letter-spacing: 0.2px;
        }
        .sub {
            margin: 0;
            color: var(--muted);
            font-size: 1rem;
        }
        .grid {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 14px;
        }
        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px 16px;
        }
        .label {
            display: inline-block;
            margin-bottom: 10px;
            padding: 4px 8px;
            border-radius: 999px;
            background: var(--chip);
            color: var(--accent);
            font-size: 0.78rem;
            font-weight: 600;
        }
        code {
            display: block;
            margin-bottom: 6px;
            font-size: 0.87rem;
            padding: 6px 8px;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            overflow-x: auto;
            white-space: nowrap;
        }
        .desc {
            margin: 0;
            color: var(--muted);
            font-size: 0.9rem;
        }
        .meta {
            margin-top: 18px;
            color: var(--muted);
            font-size: 0.88rem;
        }
        a {
            color: #0b5cab;
            text-decoration: none;
        }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <main class="wrap">
        <section class="hero">
            <h1>RentFit API</h1>
            <p class="sub">Geo-tagged clothing rental marketplace backend documentation landing.</p>
            <p class="meta">Base URL: <code style="display:inline; padding:2px 6px;">{{ url('/api/v1') }}</code></p>
        </section>

        <section class="grid">
            <article class="card">
                <span class="label">Discovery</span>
                <code>GET /api/v1/search</code>
                <p class="desc">Search items/providers by keyword, size, color, price, dates, and location.</p>
            </article>
            <article class="card">
                <span class="label">Catalog</span>
                <code>GET /api/v1/items</code>
                <code>GET /api/v1/categories</code>
                <p class="desc">Browse item inventory and category hierarchy.</p>
            </article>
            <article class="card">
                <span class="label">Auth</span>
                <code>POST /api/v1/auth/register</code>
                <code>POST /api/v1/auth/login</code>
                <p class="desc">Register and authenticate users with Sanctum tokens.</p>
            </article>
            <article class="card">
                <span class="label">Bookings</span>
                <code>POST /api/v1/bookings</code>
                <code>GET /api/v1/bookings</code>
                <p class="desc">Create and manage rental bookings.</p>
            </article>
            <article class="card">
                <span class="label">Providers</span>
                <code>GET /api/v1/providers</code>
                <code>GET /api/v1/provider/dashboard</code>
                <p class="desc">Find providers and monitor provider-side analytics.</p>
            </article>
            <article class="card">
                <span class="label">Support</span>
                <code>GET /api/v1/conversations</code>
                <code>GET /api/v1/wishlist</code>
                <p class="desc">Messaging and renter personalization endpoints.</p>
            </article>
        </section>

        <p class="meta">
            RentFit v1.0.0 • Laravel v{{ Illuminate\Foundation\Application::VERSION }}
        </p>
    </main>
</body>
</html>
