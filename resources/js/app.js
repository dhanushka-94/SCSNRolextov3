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

document.querySelectorAll('[data-certification-form]').forEach((form) => {
    const syncPanels = (clearHidden = false) => {
        form.querySelectorAll('[data-panel]').forEach((panel) => {
            const group = panel.getAttribute('data-panel');
            const showWhen = panel.getAttribute('data-show-when');
            const checked = form.querySelector(`input[data-toggle-group="${group}"]:checked`);
            const visible = Boolean(checked && checked.value === showWhen);

            panel.classList.toggle('hidden', !visible);

            panel.querySelectorAll('input, textarea, select').forEach((field) => {
                field.disabled = !visible;

                if (clearHidden && !visible) {
                    if (field.type === 'radio' || field.type === 'checkbox') {
                        field.checked = false;
                    } else if (field.type === 'file') {
                        field.value = '';
                    } else {
                        field.value = '';
                    }
                }
            });
        });
    };

    form.querySelectorAll('[data-toggle-group]').forEach((input) => {
        input.addEventListener('change', () => syncPanels(true));
    });

    syncPanels(false);
});

document.querySelectorAll('[data-crop-repeater]').forEach((repeater) => {
    const list = repeater.querySelector('[data-crop-list]');
    const template = repeater.querySelector('[data-crop-template]');
    const addButton = repeater.querySelector('[data-crop-add]');
    const max = Number(repeater.getAttribute('data-max') || 10);
    const initiallyRequired = Boolean(list?.querySelector('input[name="crops_products[]"][required]'));

    const rows = () => Array.from(list.querySelectorAll('[data-crop-row]'));

    const refresh = () => {
        const current = rows();

        current.forEach((row, index) => {
            const indexLabel = row.querySelector('[data-crop-index]');
            const removeButton = row.querySelector('[data-crop-remove]');
            const input = row.querySelector('input[name="crops_products[]"]');

            if (indexLabel) {
                indexLabel.textContent = `${index + 1}.`;
            }

            if (removeButton) {
                removeButton.classList.toggle('invisible', current.length < 2);
            }

            if (input) {
                if (initiallyRequired && index === 0) {
                    input.setAttribute('required', 'required');
                } else {
                    input.removeAttribute('required');
                }
            }
        });

        if (addButton) {
            const atMax = current.length >= max;
            addButton.disabled = atMax;
            addButton.classList.toggle('opacity-50', atMax);
            addButton.classList.toggle('pointer-events-none', atMax);
        }
    };

    addButton?.addEventListener('click', () => {
        if (rows().length >= max || !template) {
            return;
        }

        list.appendChild(template.content.cloneNode(true));
        refresh();
        list.querySelector('[data-crop-row]:last-child input')?.focus();
    });

    list?.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-crop-remove]');

        if (!removeButton || rows().length < 2) {
            return;
        }

        removeButton.closest('[data-crop-row]')?.remove();
        refresh();
    });

    refresh();
});
