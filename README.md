# Andronaco TV — Factory Kiosk Slideshow

A Laravel 12 rewrite of the legacy `wwwtv` PHP app. It drives the fullscreen
slideshow shown on factory TVs and gives HR/marketing a clean admin to manage it.

- **Stack:** Laravel 12, Tailwind CSS v4, Flowbite UI, MySQL (`ai_tv`).
- **Public kiosk:** `GET /` — a self-scaling 1920×1080 slideshow with crossfades.
- **Admin:** `GET /admin` — login `rnehring` / `abc123`.

## What it does

- **Static slides** — upload graphics or embed a page (iframe), set on-screen
  duration, a start/end display window, active on/off, ordering, and which
  location(s) they show at.
- **Monthly slides** — birthdays and work anniversaries are rendered **live** as
  HTML/CSS over a per-month background image (no image library). Names auto-lay
  out into balanced columns and auto-fit to the panel; anyone whose day is
  *today* is highlighted. Each slide hides itself automatically when the current
  month has nobody.
  - Import the ADP payroll export (CSV) for birthdays and anniversaries.
  - Edit any row inline afterwards, or add/remove people by hand.
  - Upload/replace the background graphic per month (separate birthday and
    anniversary art), and tweak name colour, "today" colour, heading and
    alignment.
- **Locations** — display zones replace the legacy IP-octet targeting. A TV can
  auto-select a zone by its IP prefix, or you point it at `/?location=<slug>`.

## First-time setup (on the machine with Herd + MySQL)

```bash
cd ~/Herd/tv
composer install
php artisan storage:link        # serves uploaded images + backgrounds
php artisan migrate --seed      # builds ai_tv and loads seed data (admin user,
                                # locations, month backgrounds, sample August data)
npm install && npm run build    # optional — compiled assets are already bundled
```

Herd serves the folder at **http://tv.test**. If your MySQL isn't root / no
password, edit `DB_USERNAME` / `DB_PASSWORD` in `.env` first. `APP_KEY` is
already set.

## Day-to-day

- **New month of birthdays/anniversaries:** Admin → *Monthly slides* → pick the
  month → *Import CSV*. Sample files are in `docs/`.
- **Swap a month's artwork:** Admin → *Monthly slides* → *Backgrounds* tab.
- **Add a promo slide:** Admin → *Static slides* → *New slide*.
- **Point a TV somewhere specific:** Admin → *Locations* for the URL of each zone.

## Notes on the port

- The legacy per-month, per-slide hard-coded pixel positions are gone; names are
  laid out automatically over whatever background you upload.
- Unrelated legacy modules (gift/gas cards, OTIF backlog graphs, recycling,
  holidays) were intentionally left out of scope.
- Employee data model is clean: `employee_birthdays` and
  `employee_anniversaries` store month/day (+ hire date for years of service);
  re-importing a CSV updates matching people instead of duplicating them.
