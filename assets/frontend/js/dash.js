// =========== toggle theme mode
const root = document.documentElement;
const toggle = document.getElementById('toggle');

const setTheme = (dark) => {
    root.setAttribute('data-theme', dark ? 'dark' : 'light');
    if (toggle) toggle.checked = dark;
    try { localStorage.setItem('theme', dark ? 'dark' : 'light'); } catch (_) { }
};

const saved = localStorage.getItem('theme');
const dark = saved ? saved === 'dark' : toggle?.checked;
setTheme(dark);

toggle?.addEventListener('change', () => setTheme(toggle.checked));

// ======== toggle sidebar

const sidebar = document.querySelector('.sidebar');
const menuBtn = document.querySelector('.menu-icon');

menuBtn?.addEventListener('click', () => sidebar?.classList.toggle('open'));

document.addEventListener('click', (event) => {
    const sidebar = document.querySelector('.sidebar');
    const menuBtn = document.querySelector('.menu-icon');

    if (!sidebar || !sidebar.classList.contains('open')) return;
    if (sidebar.contains(event.target) || menuBtn.contains(event.target)) return;

    if (window.innerWidth <= 768) {
        sidebar.classList.remove('open');
    }
});

// Header profile dropdown
const headerProfile = document.querySelector('.header-profile');
const headerProfileTop = document.querySelector('.header-profile-top');

if (headerProfileTop && headerProfile) {
    headerProfileTop.addEventListener('click', function(e) {
        e.stopPropagation();
        headerProfile.classList.toggle('open');
    });
}

document.addEventListener('click', function(e) {
    if (headerProfile && !headerProfile.contains(e.target)) {
        headerProfile.classList.remove('open');
    }
});

// ======== accordion toggle (all accordion cards)
document.querySelectorAll('.accordion-card').forEach(card => {
    const header = card.querySelector('.accordion-header');
    const body = card.querySelector('.accordion-body');
    const caret = card.querySelector('.accordion-caret');

    if (!header || !body) return;

    header.addEventListener('click', () => {
        const isOpen = body.classList.toggle('open');
        header.setAttribute('aria-expanded', isOpen);

        if (caret) {
            caret.classList.toggle('rotated', isOpen);
        }
    });
});


// Single Select
const selects = document.querySelectorAll('.select');
selects.forEach(select => {
    const selectPreview = select.querySelector('.select-preview');
    const selectDropdown = select.querySelector('.select-dropdown');
    const selectIcon = select.querySelector('.select-icon');
    const selectList = select.querySelector('.select-list');
    const searchInput = select.querySelector('.select-search input');
    const customInput = select.querySelector('.custom-input');
    const addCustomBtn = select.querySelector('.add-custom-btn');


    // Toggle dropdown
    if (selectPreview) {
        selectPreview.addEventListener('click', () => {
            if (selectDropdown) {
                selectDropdown.classList.toggle('show');
            }
            if (select) {
                select.classList.toggle('active');
            }
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (select && !select.contains(e.target)) {
            if (selectDropdown) {
                selectDropdown.classList.remove('show');
            }
            if (select) {
                select.classList.remove('active');
            }
        }
    });

    // Keep dropdown open when clicking inside search (prevent accidental close)
    if (searchInput) {
        searchInput.addEventListener('click', (e) => e.stopPropagation());
    }

    // Search filter – works for static and dynamically loaded items
    if (searchInput && selectList) {
        searchInput.addEventListener('input', (e) => {
            const searchTerm = (e.target.value || '').trim().toLowerCase();
            const items = selectList.querySelectorAll('.select-item');
            items.forEach(item => {
                const isSelectable = !!(
                    item.dataset.apiId ||
                    item.dataset.countryId ||
                    item.dataset.serviceId ||
                    item.dataset.categoryId ||
                    item.dataset.value
                );
                const text = (item.dataset.name || item.textContent || '').trim().toLowerCase();
                const matches = searchTerm === '' || text.includes(searchTerm);
                item.classList.toggle('hidden', isSelectable ? !matches : searchTerm !== '');
            });
        });
    }

    // Select item function
    function selectItem(item) {
        if (!item || !selectPreview) return;

        const previewSpan = selectPreview.querySelector('span');
        if (previewSpan) {
            // Prefer a dedicated label element or data-name, fall back to full text
            const nameEl = item.querySelector('.select-item-name');
            const label =
                (nameEl && nameEl.textContent.trim()) ||
                (item.dataset && (item.dataset.name || '').trim()) ||
                (item.textContent || '').trim();

            previewSpan.textContent = label;
            previewSpan.title = label;
        }

        if (selectList) {
            selectList.querySelectorAll('.select-item').forEach(i => {
                i.classList.remove('selected');
            });
        }

        item.classList.add('selected');

        if (selectDropdown) {
            selectDropdown.classList.remove('show');
        }
        if (select) {
            select.classList.remove('active');
        }
    }

    // Handle existing and dynamically added items (use closest so clicking price span works)
    if (selectList) {
        selectList.addEventListener('click', (e) => {
            const item = e.target.closest('.select-item');
            if (item) {
                selectItem(item);
            }
        });
    }
});