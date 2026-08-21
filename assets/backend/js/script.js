const passwordIcon = document.getElementById('password-icon');

if(passwordIcon) {
    passwordIcon.addEventListener('click', () => {
        const passwordInput = document.getElementById('password');
    
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.classList.remove('la-eye');
            passwordIcon.classList.add('la-eye-slash');
        } else {
            passwordInput.type = 'password';
            passwordIcon.classList.remove('la-eye-slash');
            passwordIcon.classList.add('la-eye');
        }
    });
}

// Modal configuration
const modalConfig = [
    { button: 'add-user', modal: 'add-user-modal', type: 'id' },
    { button: 'send-email', modal: 'send-email-modal', type: 'id' },
    { button: 'add-number-api', modal: 'add-number-api-modal', type: 'id' },
    { button: 'add-number-country', modal: 'add-number-country-modal', type: 'id' },
    { button: 'add-number-service', modal: 'add-number-service-modal', type: 'id' },
    { button: 'add-selling-api', modal: 'add-selling-api-modal', type: 'id' },
    { button: 'add-selling-category', modal: 'add-selling-category-modal', type: 'id' },
    { button: 'import-selling-categories', modal: 'import-selling-categories-modal', type: 'id' },
    { button: 'add-selling-subcategory', modal: 'add-selling-subcategory-modal', type: 'id' },
    { button: 'import-selling-subcategories', modal: 'import-selling-subcategories-modal', type: 'id' },
    { button: 'import-selling-products', modal: 'import-selling-products-modal', type: 'id' },
    { button: 'add-selling-account', modal: 'add-selling-account-modal', type: 'id' },
    { button: 'import-selling-account', modal: 'import-selling-account-modal', type: 'id' },
];

// Initialize all modals
function initializeModals() {
    modalConfig.forEach(({ button, modal, type }) => {
        const modalEl = document.getElementById(modal);
        
        if (modalEl) {
            if (type === 'id') {
                const buttonEl = document.getElementById(button);
                if (buttonEl) {
                    buttonEl.addEventListener('click', () => {
                        modalEl.classList.add('active');
                    });
                }
            } else if (type === 'class') {
                const buttonEls = document.querySelectorAll(`.${button}`);
                buttonEls.forEach(buttonEl => {
                    buttonEl.addEventListener('click', () => {
                        modalEl.classList.add('active');
                    });
                });
            }
        }
    });
}

// Setup close handlers
function setupCloseHandlers() {
    const closeButtons = document.querySelectorAll('.close-modal');
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.modal.active').forEach(modal => {
                modal.classList.remove('active');
            });
        });
    });
}

// Close modal if click outside
document.addEventListener('click', (event) => {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
    }
});

// Initialize everything
initializeModals();
setupCloseHandlers();

const confirmModal = (function () {
    const modal = document.getElementById('confirm-modal');
    if (!modal) {
        return function (message) {
            return Promise.resolve(window.confirm(message || 'Are you sure?'));
        };
    }

    const messageEl = document.getElementById('confirm-modal-message');
    const titleEl = document.getElementById('confirm-modal-title');
    const okBtn = document.getElementById('confirm-modal-ok');
    const cancelBtn = document.getElementById('confirm-modal-cancel');
    let resolver = null;

    function settle(result) {
        modal.classList.remove('active');
        if (resolver) {
            const resolve = resolver;
            resolver = null;
            resolve(result);
        }
    }

    okBtn.addEventListener('click', () => settle(true));
    cancelBtn.addEventListener('click', () => settle(false));
    modal.querySelectorAll('.close-modal').forEach((btn) => {
        btn.addEventListener('click', () => settle(false));
    });
    modal.addEventListener('click', (event) => {
        if (event.target === modal) settle(false);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('active')) settle(false);
    });

    return function (message, options) {
        const opts = options || {};
        if (messageEl) messageEl.textContent = message || 'Are you sure?';
        if (titleEl) titleEl.textContent = opts.title || 'Please confirm';
        if (okBtn) okBtn.textContent = opts.confirmText || 'Confirm';
        if (cancelBtn) cancelBtn.textContent = opts.cancelText || 'Cancel';
        modal.classList.add('active');
        return new Promise((resolve) => {
            resolver = resolve;
        });
    };
})();
window.confirmModal = confirmModal;

const inputColor = document.querySelectorAll('.input-color input');
const inputColorValue = document.querySelectorAll('.input-color-value');

inputColor.forEach(colorInput => {
    colorInput.addEventListener('input', () => {
        // Copy the selected color value to all color value inputs
        inputColorValue.forEach(valueInput => {
            valueInput.value = colorInput.value;
        });
    });
});

const hasDropdown = document.querySelectorAll('.has-dropdown');

hasDropdown.forEach(item => {
    item.addEventListener('click', (e) => {
        e.preventDefault();
        let parent = item.parentElement;
        let dropdown = parent.querySelector('.sidebar-dropdown');

        dropdown.classList.toggle('active');

    })
});

// sidebar responsive
const sidebar = document.querySelector('.sidebar');
const sidebarMenuIcon = document.querySelector('.sidebar-menu-icon');

sidebarMenuIcon.addEventListener('click', () => {
    sidebar.classList.toggle('open');

    // if clicked outside
    document.addEventListener('click', (e) => {
        if(!sidebar.contains(e.target) && !sidebarMenuIcon.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
});

const headerHasDropdown = document.querySelectorAll('.header-has-dropdown');

headerHasDropdown.forEach(item => {
    item.addEventListener('click', (e) => {
        e.preventDefault();
        const parent = item.parentElement;
        const dropdown = parent.querySelector('.header-dropdown');
        
        // Close other dropdowns
        headerHasDropdown.forEach(otherItem => {
            if(otherItem !== item) {
                const otherDropdown = otherItem.parentElement.querySelector('.header-dropdown');
                otherDropdown.classList.remove('open');
            }
        });
        
        parent.classList.toggle('open');
        dropdown.classList.toggle('open');
    });
});

// if clicked outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('.header-has-dropdown')) {
        headerHasDropdown.forEach(item => {
            const parent = item.parentElement;
            const dropdown = parent.querySelector('.header-dropdown');
            dropdown.classList.remove('open');
        });
    }
});
