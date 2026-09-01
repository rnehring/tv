import './bootstrap';
import { initFlowbite } from 'flowbite';

// (Re)initialise Flowbite interactive components on load.
document.addEventListener('DOMContentLoaded', () => initFlowbite());

// Confirm destructive actions.
document.addEventListener('submit', (e) => {
    const form = e.target;
    if (form.matches('[data-confirm]')) {
        if (!window.confirm(form.getAttribute('data-confirm'))) {
            e.preventDefault();
        }
    }
});

// Colour-picker mirroring: keep a text input in sync with an <input type=color>.
document.querySelectorAll('[data-color-sync]').forEach((picker) => {
    const target = document.querySelector(picker.getAttribute('data-color-sync'));
    if (!target) return;
    const push = (from, to) => { to.value = from.value; };
    picker.addEventListener('input', () => push(picker, target));
    target.addEventListener('input', () => {
        if (/^#[0-9a-fA-F]{6}$/.test(target.value)) push(target, picker);
    });
});
