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