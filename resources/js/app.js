import './bootstrap';

const sidebar = document.getElementById('app-sidebar');
const overlay = document.getElementById('sidebar-overlay');

document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        sidebar?.classList.toggle('-translate-x-full');
        overlay?.classList.toggle('hidden');
    });
});

overlay?.addEventListener('click', () => {
    sidebar?.classList.add('-translate-x-full');
    overlay?.classList.add('hidden');
});

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.getAttribute('data-password-toggle'));

        if (!input) {
            return;
        }

        input.type = input.type === 'password' ? 'text' : 'password';
    });
});

document.querySelectorAll('[data-delete-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        const name = form.getAttribute('data-delete-name') || 'this user';

        if (!window.confirm(`Delete ${name}? This action cannot be undone.`)) {
            event.preventDefault();
        }
    });
});

const flash = document.getElementById('flash-banner');

if (flash) {
    window.setTimeout(() => {
        flash.classList.add('opacity-0');
        window.setTimeout(() => flash.remove(), 300);
    }, 4200);
}
