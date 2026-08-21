(function () {
    'use strict';

    var charts = {};

    function currencyFmt(v) {
        return Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function chartColors() {
        var style = getComputedStyle(document.documentElement);
        return {
            primary: style.getPropertyValue('--primary-color').trim() || '#4c45e0',
            grid: 'rgba(148, 163, 184, 0.15)',
            text: '#94a3b8',
        };
    }

    function renderLineChart(canvasId, labels, data, label) {
        var el = document.getElementById(canvasId);
        if (!el || typeof Chart === 'undefined') return;
        var colors = chartColors();

        if (charts[canvasId]) {
            charts[canvasId].data.labels = labels;
            charts[canvasId].data.datasets[0].data = data;
            charts[canvasId].update();
            return;
        }

        charts[canvasId] = new Chart(el.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: data,
                    borderColor: colors.primary,
                    backgroundColor: colors.primary + '33',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: colors.text }, grid: { color: colors.grid } },
                    y: { ticks: { color: colors.text }, grid: { color: colors.grid }, beginAtZero: true },
                },
            },
        });
    }

    function hexToRgb(hex) {
        hex = hex.replace('#', '');
        if (hex.length === 3) hex = hex.split('').map(function (c) { return c + c; }).join('');
        var num = parseInt(hex, 16);
        return { r: (num >> 16) & 255, g: (num >> 8) & 255, b: num & 255 };
    }

    function renderServiceChart(revenueByService) {
        var el = document.getElementById('fd-service-chart');
        if (!el || typeof Chart === 'undefined') return;
        var colors = chartColors();
        var labels = ['Account Logs', 'Virtual Number', 'Rental Number', 'Followers'];
        var data = [
            revenueByService.accountLogs || 0,
            revenueByService.virtualNumber || 0,
            revenueByService.rentalNumber || 0,
            revenueByService.followers || 0,
        ];

        // Shades of the site's own accent color rather than introducing new
        // colors — same brand, just varying opacity per slice.
        var rgb = hexToRgb(colors.primary.indexOf('#') === 0 ? colors.primary : '#4c45e0');
        var shade = function (alpha) { return 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',' + alpha + ')'; };
        var palette = [shade(1), shade(0.75), shade(0.5), shade(0.3)];

        var total = data.reduce(function (a, b) { return a + b; }, 0);

        if (charts['fd-service-chart']) {
            charts['fd-service-chart'].data.datasets[0].data = data;
            charts['fd-service-chart'].update();
            return;
        }

        charts['fd-service-chart'] = new Chart(el.getContext('2d'), {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: data, backgroundColor: palette }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: colors.text,
                            generateLabels: function (chart) {
                                var ds = chart.data.datasets[0];
                                return chart.data.labels.map(function (label, i) {
                                    var value = ds.data[i];
                                    var pct = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return {
                                        text: label + ' — ' + pct + '%',
                                        fillStyle: ds.backgroundColor[i],
                                        strokeStyle: ds.backgroundColor[i],
                                        index: i,
                                    };
                                });
                            },
                        },
                    },
                },
            },
        });
    }

    function renderAllCharts(graphs) {
        renderLineChart('fd-revenue-chart', graphs.labels, graphs.revenue, 'Revenue');
        renderLineChart('fd-deposit-chart', graphs.labels, graphs.deposits, 'Deposits');
        renderLineChart('fd-orders-chart', graphs.labels, graphs.orders, 'Orders');
        renderServiceChart(graphs.revenueByService || {});
    }

    function renderNotifications(notifications) {
        var wrap = document.getElementById('fd-notifications');
        if (!wrap) return;
        wrap.innerHTML = '';
        wrap.className = (notifications && notifications.length) ? 'row fd-notifications' : 'fd-notifications';
        (notifications || []).forEach(function (n) {
            var col = document.createElement('div');
            col.className = 'fd-alert-col';
            col.innerHTML =
                '<div class="card card-stats fd-alert fd-alert-' + n.level + '">' +
                    '<div class="card-stats-text">' +
                        '<p>' + n.label + '</p>' +
                        '<h2>' + n.count + '</h2>' +
                    '</div>' +
                    '<div class="card-stats-icon"><i class="las ' + n.icon + '"></i></div>' +
                '</div>';
            wrap.appendChild(col);
        });
    }

    function updateExportLinks(period, from, to) {
        ['fd-export-csv', 'fd-export-excel', 'fd-export-pdf'].forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            var base = el.getAttribute('data-base') || el.href.split('?')[0];
            el.setAttribute('data-base', base);
            el.href = base + '?period=' + encodeURIComponent(period) + '&from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to);
        });
    }

    function applyFilter() {
        var period = document.getElementById('fd-period').value;
        var from = document.getElementById('fd-from').value;
        var to = document.getElementById('fd-to').value;
        var spinner = document.getElementById('fd-spinner');
        var url = window.FD_ROUTES.filter + '?period=' + encodeURIComponent(period);
        if (period === 'custom') {
            url += '&from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to);
        }

        if (spinner) spinner.style.display = 'inline-block';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                document.getElementById('fd-cards-top').innerHTML = data.html.cardsTop;
                document.getElementById('fd-cards-bottom').innerHTML = data.html.cardsBottom;
                document.getElementById('fd-secondary').innerHTML = data.html.secondary;
                var qsContainer = document.getElementById('fd-quick-stats');
                qsContainer.innerHTML = '<div class="table-header"><h2>Quick Statistics</h2></div>' + data.html.quickStats;
                document.getElementById('fd-signups-body').innerHTML = data.html.signups;
                document.getElementById('fd-depositors-body').innerHTML = data.html.depositors;
                document.getElementById('fd-transactions-body').innerHTML = data.html.transactions;
                document.getElementById('fd-orders-body').innerHTML = data.html.orders;
                renderAllCharts(data.graphs);
                renderNotifications(data.notifications);
                updateExportLinks(period, period === 'custom' ? from : '', period === 'custom' ? to : '');
            })
            .catch(function () {
                if (typeof iziToast !== 'undefined') {
                    iziToast.error({ message: 'Could not refresh dashboard data.', position: 'topRight' });
                }
            })
            .finally(function () {
                if (spinner) spinner.style.display = 'none';
            });
    }

    function wireSearch(inputId, tbodyId) {
        var input = document.getElementById(inputId);
        var tbody = document.getElementById(tbodyId);
        if (!input || !tbody) return;
        input.addEventListener('input', function () {
            var term = this.value.trim().toLowerCase();
            Array.prototype.forEach.call(tbody.querySelectorAll('tr'), function (row) {
                row.style.display = row.textContent.toLowerCase().indexOf(term) !== -1 ? '' : 'none';
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (window.FD_INITIAL_GRAPHS) {
            renderAllCharts(window.FD_INITIAL_GRAPHS);
        }

        var periodSelect = document.getElementById('fd-period');
        var fromInput = document.getElementById('fd-from');
        var toInput = document.getElementById('fd-to');

        if (periodSelect) {
            periodSelect.addEventListener('change', function () {
                var isCustom = this.value === 'custom';
                if (fromInput) fromInput.style.display = isCustom ? '' : 'none';
                if (toInput) toInput.style.display = isCustom ? '' : 'none';
            });
        }

        var applyBtn = document.getElementById('fd-apply');
        if (applyBtn) applyBtn.addEventListener('click', applyFilter);

        wireSearch('fd-search-signups', 'fd-signups-body');
        wireSearch('fd-search-depositors', 'fd-depositors-body');
        wireSearch('fd-search-transactions', 'fd-transactions-body');
        wireSearch('fd-search-orders', 'fd-orders-body');
    });
})();
