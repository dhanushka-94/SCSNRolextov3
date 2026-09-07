import './bootstrap';
import L from 'leaflet';

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

document.querySelectorAll('[data-partner-nav]').forEach((nav) => {
    const toggle = nav.querySelector('[data-partner-menu-toggle]');
    const menu = nav.querySelector('[data-partner-mobile-menu]');
    const hero = document.querySelector('.partner-hero');

    const closeMenu = () => {
        menu?.classList.add('hidden');
        nav.classList.remove('is-menu-open');
        toggle?.setAttribute('aria-expanded', 'false');
        toggle?.setAttribute('aria-label', 'Open menu');
    };

    const openMenu = () => {
        menu?.classList.remove('hidden');
        nav.classList.add('is-menu-open');
        toggle?.setAttribute('aria-expanded', 'true');
        toggle?.setAttribute('aria-label', 'Close menu');
    };

    const syncScrollState = () => {
        const threshold = hero ? Math.max(hero.offsetHeight - 80, 48) : 48;
        nav.classList.toggle('is-scrolled', window.scrollY > threshold * 0.35);
    };

    toggle?.addEventListener('click', () => {
        if (menu?.classList.contains('hidden')) {
            openMenu();
        } else {
            closeMenu();
        }
    });

    menu?.querySelectorAll('a[href^="#"]').forEach((link) => {
        link.addEventListener('click', () => closeMenu());
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1280) {
            closeMenu();
        }
        syncScrollState();
    });

    window.addEventListener('scroll', syncScrollState, { passive: true });
    syncScrollState();
});

document.querySelectorAll('[data-partner-fab]').forEach((fab) => {
    const hideSelector = fab.getAttribute('data-partner-fab-hide');
    const target = hideSelector ? document.querySelector(hideSelector) : null;

    if (!target || !('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver(
        ([entry]) => {
            fab.classList.toggle('is-hidden', entry.isIntersecting);
        },
        { rootMargin: '0px 0px -12% 0px', threshold: 0.15 },
    );

    observer.observe(target);
});

const initSearchSelect = (root, { onChange } = {}) => {
    const valueInput = root.querySelector('[data-search-value]');
    const textInput = root.querySelector('[data-search-input]');
    const menu = root.querySelector('[data-search-menu]');
    const emptyText = root.getAttribute('data-search-empty') || 'No results found';

    if (!valueInput || !textInput || !menu) {
        return null;
    }

    const options = () => Array.from(menu.querySelectorAll('[data-value]'));

    const close = () => {
        menu.classList.add('hidden');
        root.classList.remove('is-open');
        textInput.setAttribute('aria-expanded', 'false');
    };

    const open = () => {
        if (textInput.disabled) {
            return;
        }

        menu.classList.remove('hidden');
        root.classList.add('is-open');
        textInput.setAttribute('aria-expanded', 'true');
        filter(textInput.value);
    };

    const setValue = (value, label, silent = false) => {
        valueInput.value = value || '';
        textInput.value = label || '';
        options().forEach((option) => {
            const selected = String(option.getAttribute('data-value')) === String(value);
            option.setAttribute('aria-selected', selected ? 'true' : 'false');
            option.classList.toggle('is-active', selected);
        });

        if (!silent) {
            valueInput.dispatchEvent(new Event('change', { bubbles: true }));
            onChange?.(value, label);
        }
    };

    const filter = (query) => {
        const needle = String(query || '').trim().toLowerCase();
        let visible = 0;

        menu.querySelectorAll('[data-search-empty-row]').forEach((row) => row.remove());

        options().forEach((option) => {
            const label = String(option.getAttribute('data-label') || option.textContent || '').toLowerCase();
            const match = !needle || label.includes(needle);
            option.classList.toggle('is-hidden', !match);
            if (match) {
                visible += 1;
            }
        });

        if (visible === 0) {
            const empty = document.createElement('li');
            empty.className = 'search-select-empty';
            empty.setAttribute('data-search-empty-row', '1');
            empty.textContent = emptyText;
            menu.appendChild(empty);
        }
    };

    textInput.addEventListener('focus', () => open());
    textInput.addEventListener('click', () => open());
    textInput.addEventListener('input', () => {
        open();
        filter(textInput.value);
        if (textInput.value.trim() === '') {
            setValue('', '', true);
            onChange?.('', '');
        }
    });

    menu.addEventListener('mousedown', (event) => {
        const option = event.target.closest('[data-value]');
        if (!option) {
            return;
        }

        event.preventDefault();
        setValue(option.getAttribute('data-value'), option.getAttribute('data-label'));
        close();
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            const selected = options().find((option) => option.getAttribute('aria-selected') === 'true');
            if (selected) {
                textInput.value = selected.getAttribute('data-label') || '';
            } else if (!valueInput.value) {
                textInput.value = '';
            }
            close();
        }
    });

    return { setValue, filter, open, close, menu, textInput, valueInput };
};

document.querySelectorAll('[data-district-rdo]').forEach((wrapper) => {
    const districtRoot = wrapper.querySelector('[data-search-select]:not([data-rdo-search])');
    const rdoRoot = wrapper.querySelector('[data-rdo-search]');
    const districtValue = wrapper.querySelector('[data-district-select]');
    const rdoValue = wrapper.querySelector('[data-rdo-select]');
    const rdoInput = wrapper.querySelector('[data-rdo-input]');
    const rdoMenu = rdoRoot?.querySelector('[data-search-menu]');
    const mapNode = wrapper.querySelector('[data-rdo-map]');

    if (!districtRoot || !rdoRoot || !districtValue || !rdoValue || !rdoInput || !rdoMenu || !mapNode) {
        return;
    }

    let map = {};

    try {
        map = JSON.parse(mapNode.textContent || '{}');
    } catch (error) {
        map = {};
    }

    let rdoApi = null;

    const renderDivisions = (districtId, preferredId = '') => {
        const divisions = map[String(districtId)] || [];
        rdoMenu.innerHTML = '';

            divisions.forEach((division) => {
                const label = division.code ? `${division.name} (${division.code})` : division.name;
                const option = document.createElement('li');
                option.className = 'search-select-option';
                option.setAttribute('role', 'option');
                option.setAttribute('data-value', String(division.id));
                option.setAttribute('data-label', label);

                const nameSpan = document.createElement('span');
                nameSpan.textContent = division.name;
                const metaSpan = document.createElement('span');
                metaSpan.className = 'search-select-meta';
                metaSpan.textContent = division.code || '';
                option.append(nameSpan, metaSpan);
                rdoMenu.appendChild(option);
            });

        const preferred = divisions.find((division) => String(division.id) === String(preferredId));
        const enabled = Boolean(districtId);

        rdoInput.disabled = !enabled;
        rdoRoot.setAttribute('data-search-placeholder', enabled ? 'Search division...' : 'Select district first');
        rdoInput.placeholder = enabled ? 'Search division...' : 'Select district first';

        if (preferred) {
            const label = preferred.code ? `${preferred.name} (${preferred.code})` : preferred.name;
            rdoApi?.setValue(String(preferred.id), label, true);
        } else {
            rdoApi?.setValue('', '', true);
        }
    };

    initSearchSelect(districtRoot, {
        onChange: (value) => {
            rdoValue.removeAttribute('data-selected');
            renderDivisions(value, '');
        },
    });

    rdoApi = initSearchSelect(rdoRoot);

    const initialDistrict = String(districtValue.value || '');
    const initialDivision = String(rdoValue.getAttribute('data-selected') || rdoValue.value || '');
    renderDivisions(initialDistrict, initialDivision);
});

document.querySelectorAll('[data-search-select]').forEach((root) => {
    if (root.closest('[data-district-rdo]')) {
        return;
    }

    initSearchSelect(root);
});

const farmPinIcon = L.divIcon({
    className: 'map-pin-marker',
    html: '<span></span>',
    iconSize: [32, 32],
    iconAnchor: [16, 16],
});

const initMapPin = (root) => {
    const canvas = root.querySelector('[data-map-canvas]');
    const latInput = root.querySelector('[data-map-lat]');
    const lngInput = root.querySelector('[data-map-lng]');
    const status = root.querySelector('[data-map-status]');
    const locateButton = root.querySelector('[data-map-locate]');
    const clearButton = root.querySelector('[data-map-clear]');
    const readonly = root.getAttribute('data-readonly') === '1';

    if (!canvas || !latInput || !lngInput) {
        return;
    }

    const defaultLat = Number(root.getAttribute('data-default-lat') || 7.8731);
    const defaultLng = Number(root.getAttribute('data-default-lng') || 80.7718);
    const defaultZoom = Number(root.getAttribute('data-default-zoom') || 7);
    const pinnedZoom = Number(root.getAttribute('data-pinned-zoom') || 14);
    const minLat = 5.8;
    const maxLat = 10.0;
    const minLng = 79.4;
    const maxLng = 82.1;

    const parseCoord = (value) => {
        if (value === null || value === undefined || String(value).trim() === '') {
            return null;
        }

        const number = Number(value);

        return Number.isFinite(number) ? number : null;
    };

    const withinSriLanka = (lat, lng) => (
        lat >= minLat && lat <= maxLat && lng >= minLng && lng <= maxLng
    );

    const formatCoord = (value) => value.toFixed(7);

    let marker = null;

    const setStatus = (message) => {
        if (status) {
            status.textContent = message;
        }
    };

    const writeInputs = (lat, lng) => {
        latInput.value = formatCoord(lat);
        lngInput.value = formatCoord(lng);
    };

    const clearInputs = () => {
        latInput.value = '';
        lngInput.value = '';
    };

    const placeMarker = (lat, lng, { pan = true, zoom = false } = {}) => {
        if (!withinSriLanka(lat, lng)) {
            setStatus('Choose a point inside Sri Lanka.');
            return false;
        }

        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], {
                icon: farmPinIcon,
                draggable: !readonly,
            }).addTo(map);

            if (!readonly) {
                marker.on('dragend', () => {
                    const position = marker.getLatLng();
                    if (!withinSriLanka(position.lat, position.lng)) {
                        setStatus('Pin must stay inside Sri Lanka.');
                        const lastLat = parseCoord(latInput.value);
                        const lastLng = parseCoord(lngInput.value);
                        if (lastLat !== null && lastLng !== null) {
                            marker.setLatLng([lastLat, lastLng]);
                        }
                        return;
                    }

                    writeInputs(position.lat, position.lng);
                    setStatus('Pin updated.');
                });
            }
        }

        writeInputs(lat, lng);

        if (pan) {
            map.setView([lat, lng], zoom ? pinnedZoom : Math.max(map.getZoom(), pinnedZoom - 2));
        }

        setStatus('Pin set.');
        return true;
    };

    const clearMarker = () => {
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }

        clearInputs();
        map.setView([defaultLat, defaultLng], defaultZoom);
        setStatus(readonly ? 'No map pin recorded.' : 'No pin yet — click the map to set one.');
    };

    const initialLat = parseCoord(latInput.value);
    const initialLng = parseCoord(lngInput.value);
    const hasInitial = initialLat !== null && initialLng !== null && withinSriLanka(initialLat, initialLng);

    const map = L.map(canvas, {
        scrollWheelZoom: !readonly,
        dragging: true,
        doubleClickZoom: !readonly,
        boxZoom: !readonly,
        keyboard: !readonly,
    }).setView(
        hasInitial ? [initialLat, initialLng] : [defaultLat, defaultLng],
        hasInitial ? pinnedZoom : defaultZoom,
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    if (hasInitial) {
        placeMarker(initialLat, initialLng, { pan: false });
    }

    window.setTimeout(() => map.invalidateSize(), 80);

    if (!readonly) {
        map.on('click', (event) => {
            placeMarker(event.latlng.lat, event.latlng.lng, { pan: true, zoom: true });
        });

        const syncFromInputs = () => {
            const lat = parseCoord(latInput.value);
            const lng = parseCoord(lngInput.value);

            if (lat === null && lng === null) {
                clearMarker();
                return;
            }

            if (lat === null || lng === null) {
                setStatus('Enter both latitude and longitude, or click the map.');
                return;
            }

            placeMarker(lat, lng, { pan: true, zoom: true });
        };

        latInput.addEventListener('change', syncFromInputs);
        lngInput.addEventListener('change', syncFromInputs);

        clearButton?.addEventListener('click', (event) => {
            event.preventDefault();
            clearMarker();
        });

        locateButton?.addEventListener('click', (event) => {
            event.preventDefault();

            if (!navigator.geolocation) {
                setStatus('Location is not supported in this browser.');
                return;
            }

            setStatus('Finding your location…');
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    placeMarker(position.coords.latitude, position.coords.longitude, {
                        pan: true,
                        zoom: true,
                    });
                },
                () => setStatus('Could not read your location. Click the map instead.'),
                { enableHighAccuracy: true, timeout: 12000 },
            );
        });
    }
};

document.querySelectorAll('[data-map-pin]').forEach((root) => initMapPin(root));
