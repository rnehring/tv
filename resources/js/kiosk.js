import '../css/kiosk.css';

/* ------------------------------------------------------------------ *
 *  Kiosk slideshow engine (replaces the legacy jQuery Cycle plugin).  *
 *  - crossfades slides using each slide's own duration               *
 *  - auto-fits the birthday/anniversary name panels                  *
 *  - scales the 1920x1080 stage to fit any TV                        *
 *  - reloads periodically to pick up admin changes / date rollover   *
 * ------------------------------------------------------------------ */

function scaleStage() {
    const stage = document.getElementById('stage');
    if (!stage) return;
    const scale = Math.min(window.innerWidth / 1920, window.innerHeight / 1080);
    stage.style.transform = `scale(${scale})`;
}

// Binary-search the largest font size at which a name panel's content fits.
function autofit(panel) {
    const lo0 = 10, hi0 = 60;
    let lo = lo0, hi = hi0, best = lo0;
    const fits = () =>
        panel.scrollHeight <= panel.clientHeight + 1 &&
        panel.scrollWidth <= panel.clientWidth + 1;
    for (let i = 0; i < 18; i++) {
        const mid = (lo + hi) / 2;
        panel.style.setProperty('--fs', mid + 'px');
        if (fits()) { best = mid; lo = mid; } else { hi = mid; }
    }
    panel.style.setProperty('--fs', best + 'px');
}

function autofitAll() {
    document.querySelectorAll('.gen .panel').forEach(autofit);
}

class Slideshow {
    constructor(root) {
        this.slides = Array.from(root.querySelectorAll('.kiosk-slide'));
        this.i = -1;
        this.timer = null;
    }

    start() {
        if (this.slides.length === 0) return;
        this.next();
    }

    next() {
        clearTimeout(this.timer);
        if (this.i >= 0) this.slides[this.i].classList.remove('is-active');
        this.i = (this.i + 1) % this.slides.length;
        const slide = this.slides[this.i];
        slide.classList.add('is-active');

        // Restart iframes so live embeds refresh each cycle.
        const frame = slide.querySelector('iframe[data-src]');
        if (frame) frame.src = frame.getAttribute('data-src');

        const duration = parseInt(slide.getAttribute('data-duration'), 10) || 8000;
        // Only advance automatically when there's more than one slide.
        if (this.slides.length > 1) {
            this.timer = setTimeout(() => this.next(), duration);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    scaleStage();
    window.addEventListener('resize', scaleStage);
    window.addEventListener('load', scaleStage);
    // When embedded (e.g. the admin preview) the viewport settles after layout,
    // so re-scale on any size change and on a couple of early ticks.
    if (window.ResizeObserver) {
        new ResizeObserver(scaleStage).observe(document.documentElement);
    }
    [100, 400, 1000].forEach((t) => setTimeout(scaleStage, t));

    autofitAll();
    window.addEventListener('resize', autofitAll);

    const deck = document.getElementById('deck');
    if (deck) new Slideshow(deck).start();

    // Periodic full reload (config comes from a data attribute).
    const body = document.body;
    const reloadSeconds = parseInt(body.getAttribute('data-reload'), 10) || 900;
    if (reloadSeconds > 0) {
        setTimeout(() => window.location.reload(), reloadSeconds * 1000);
    }
});
