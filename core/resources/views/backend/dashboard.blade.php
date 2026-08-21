@extends('backend.layout.app')

@section('content')
    @php $cur = optional($settings)->website_currency ?? 'USD'; @endphp
    <div class="container finance-dashboard" id="finance-dashboard" data-currency="{{ $cur }}">

        <div class="fd-welcome">
            <h1>Welcome back</h1>
            <p>Here's how things are looking across deposits, orders, and profit.</p>
        </div>

        {{-- Notifications --}}
        @if (count($notifications))
            <div class="row fd-notifications" id="fd-notifications">
                @foreach ($notifications as $note)
                    <div class="fd-alert-col">
                        <div class="card card-stats fd-alert fd-alert-{{ $note['level'] }}">
                            <div class="card-stats-text">
                                <p>{{ $note['label'] }}</p>
                                <h2>{{ $note['count'] }}</h2>
                            </div>
                            <div class="card-stats-icon"><i class="las {{ $note['icon'] }}"></i></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="fd-notifications" id="fd-notifications"></div>
        @endif

        {{-- Overview (not affected by the period filter below) --}}
        <div id="fd-cards-top">
        <p class="fd-section-title">Overview <span class="fd-section-hint">(not affected by the filter below)</span></p>
        <div class="row">
            @php
                $overviewCards = [
                    ['label' => "Today's Deposits", 'value' => number_format($cards['todaysDeposits'], 2) . " $cur", 'icon' => 'la-calendar-day', 'trend' => $cards['depositsTrend']],
                    ['label' => 'Total Deposits', 'value' => number_format($cards['totalDeposits'], 2) . " $cur", 'icon' => 'la-money-bill-wave-alt', 'trend' => null],
                    ['label' => "Today's Revenue", 'value' => number_format($cards['todaysRevenue'], 2) . " $cur", 'icon' => 'la-chart-line', 'trend' => $cards['revenueTrend']],
                    ['label' => 'Total Revenue', 'value' => number_format($cards['totalRevenue'], 2) . " $cur", 'icon' => 'la-coins', 'trend' => null],
                ];
            @endphp
            @foreach ($overviewCards as $card)
                <div class="width-25">
                    <div class="card card-stats">
                        <div class="card-stats-text">
                            <p>{{ $card['label'] }}</p>
                            <h2>{{ $card['value'] }}</h2>
                            @if ($card['trend'])
                                <span class="fd-trend fd-trend-{{ $card['trend']['direction'] }}">
                                    <i class="las la-arrow-{{ $card['trend']['direction'] === 'down' ? 'down' : 'up' }}"></i>
                                    {{ $card['trend']['percent'] }}% vs yesterday
                                </span>
                            @endif
                        </div>
                        <div class="card-stats-icon"><i class="las {{ $card['icon'] }}"></i></div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="row">
            <div class="width-25">
                <div class="card card-stats fd-profit">
                    <div class="card-stats-text">
                        <p>Today's Est. Profit</p>
                        <h2>{{ number_format($cards['todaysProfit'], 2) }} {{ $cur }}</h2>
                        @if ($cards['profitTrend'])
                            <span class="fd-trend fd-trend-{{ $cards['profitTrend']['direction'] }}">
                                <i class="las la-arrow-{{ $cards['profitTrend']['direction'] === 'down' ? 'down' : 'up' }}"></i>
                                {{ $cards['profitTrend']['percent'] }}% vs yesterday
                            </span>
                        @endif
                    </div>
                    <div class="card-stats-icon"><i class="las la-piggy-bank"></i></div>
                </div>
            </div>
            <div class="width-25">
                <div class="card card-stats">
                    <div class="card-stats-text">
                        <p>User Wallet Balance</p>
                        <h2>{{ number_format($cards['userWalletBalance'], 2) }} {{ $cur }}</h2>
                    </div>
                    <div class="card-stats-icon"><i class="las la-wallet"></i></div>
                </div>
            </div>
            <div class="width-25">
                <div class="card card-stats">
                    <div class="card-stats-text">
                        <p>Total Customers</p>
                        <h2>{{ $cards['totalCustomers'] }}</h2>
                    </div>
                    <div class="card-stats-icon"><i class="las la-user"></i></div>
                </div>
            </div>
            <div class="width-25">
                <div class="card card-stats">
                    <div class="card-stats-text">
                        <p>New Customers Today</p>
                        <h2>{{ $cards['newCustomersToday'] }}</h2>
                    </div>
                    <div class="card-stats-icon"><i class="las la-user-plus"></i></div>
                </div>
            </div>
        </div>
        </div>{{-- /#fd-cards-top --}}

        {{-- Filter bar — sits right above the figures it actually changes (profit, order status, customers, revenue by service, charts) --}}
        <div class="card fd-filter-bar">
            <div class="fd-filter-controls">
                <select id="fd-period" class="form-control">
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="7days" {{ $period === '7days' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="year" {{ $period === 'year' ? 'selected' : '' }}>This Year</option>
                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
                <input type="date" id="fd-from" class="form-control" value="{{ $from }}" style="{{ $period === 'custom' ? '' : 'display:none;' }}">
                <input type="date" id="fd-to" class="form-control" value="{{ $to }}" style="{{ $period === 'custom' ? '' : 'display:none;' }}">
                <button type="button" class="btn btn-primary" id="fd-apply">Apply</button>
                <span class="fd-spinner" id="fd-spinner" style="display:none;"><i class="las la-spinner"></i></span>
            </div>
            <div class="fd-export-controls">
                <a href="{{ route('backend.dashboard.export.csv', ['period' => $period, 'from' => $from, 'to' => $to]) }}" class="btn btn-outline" id="fd-export-csv">
                    <i class="las la-file-csv"></i> CSV
                </a>
                <a href="{{ route('backend.dashboard.export.excel', ['period' => $period, 'from' => $from, 'to' => $to]) }}" class="btn btn-outline" id="fd-export-excel">
                    <i class="las la-file-excel"></i> Excel
                </a>
                <a href="{{ route('backend.dashboard.export.pdf', ['period' => $period, 'from' => $from, 'to' => $to]) }}" class="btn btn-outline" id="fd-export-pdf" target="_blank">
                    <i class="las la-file-pdf"></i> PDF
                </a>
            </div>
        </div>

        {{-- Profit, new customers, order status — everything below IS scoped to the filter above --}}
        <div id="fd-cards-bottom">
        <p class="fd-section-title">Profit & New Customers <span class="fd-section-hint">(for selected period)</span></p>
        <div class="row">
            <div class="width-50">
                <div class="card card-stats fd-profit">
                    <div class="card-stats-text">
                        <p>Est. Profit (selected range)</p>
                        <h2>{{ number_format($cards['rangeProfit'], 2) }} {{ $cur }}</h2>
                    </div>
                    <div class="card-stats-icon"><i class="las la-piggy-bank"></i></div>
                </div>
            </div>
            <div class="width-50">
                <div class="card card-stats">
                    <div class="card-stats-text">
                        <p>New Customers (selected period)</p>
                        <h2>{{ $cards['newCustomersInRange'] }}</h2>
                    </div>
                    <div class="card-stats-icon"><i class="las la-user-plus"></i></div>
                </div>
            </div>
        </div>
        <p class="fd-conversion-note">
            Profit is calculated per order as amount charged minus provider cost at the time of purchase
            &mdash; so it already accounts for any per-user service discount. Orders placed before this
            tracking was added fall back to an estimate (revenue &times; the service's markup %), which
            does not account for discounts.
        </p>

        {{-- Order status --}}
        <p class="fd-section-title">Order status</p>
        <div class="row">
            @php
                $orderCards = [
                    ['label' => 'Pending Orders', 'value' => $cards['pendingOrders'], 'icon' => 'la-clock'],
                    ['label' => 'Completed Orders', 'value' => $cards['completedOrders'], 'icon' => 'la-check-circle'],
                    ['label' => 'Failed Orders', 'value' => $cards['failedOrders'], 'icon' => 'la-times-circle'],
                    ['label' => 'Refunded Orders', 'value' => $cards['refundedOrders'], 'icon' => 'la-undo'],
                ];
            @endphp
            @foreach ($orderCards as $card)
                <div class="width-25">
                    <div class="card card-stats">
                        <div class="card-stats-text">
                            <p>{{ $card['label'] }}</p>
                            <h2>{{ $card['value'] }}</h2>
                        </div>
                        <div class="card-stats-icon"><i class="las {{ $card['icon'] }}"></i></div>
                    </div>
                </div>
            @endforeach
        </div>
        </div>{{-- /#fd-cards-bottom --}}

        {{-- Revenue by service + best sellers --}}
        <div class="row" id="fd-secondary">
            <div class="width-25">
                <div class="card card-stats">
                    <div class="card-stats-text">
                        <p>Account Logs Revenue</p>
                        <h2>{{ number_format($revenueByService['accountLogs'], 2) }} {{ $cur }}</h2>
                        <p class="fd-na">~{{ number_format($profitByService['accountLogs'], 2) }} {{ $cur }} profit</p>
                    </div>
                    <div class="card-stats-icon"><i class="las la-address-card"></i></div>
                </div>
            </div>
            <div class="width-25">
                <div class="card card-stats">
                    <div class="card-stats-text">
                        <p>Virtual Number Revenue</p>
                        <h2>{{ number_format($revenueByService['virtualNumber'], 2) }} {{ $cur }}</h2>
                        <p class="fd-na">~{{ number_format($profitByService['virtualNumber'], 2) }} {{ $cur }} profit</p>
                    </div>
                    <div class="card-stats-icon"><i class="las la-sim-card"></i></div>
                </div>
            </div>
            <div class="width-25">
                <div class="card card-stats">
                    <div class="card-stats-text">
                        <p>Rental Number Revenue</p>
                        <h2>{{ number_format($revenueByService['rentalNumber'], 2) }} {{ $cur }}</h2>
                        <p class="fd-na">~{{ number_format($profitByService['rentalNumber'], 2) }} {{ $cur }} profit</p>
                    </div>
                    <div class="card-stats-icon"><i class="las la-phone-volume"></i></div>
                </div>
            </div>
            <div class="width-25">
                <div class="card card-stats">
                    <div class="card-stats-text">
                        <p>Followers Revenue</p>
                        <h2>{{ number_format($revenueByService['followers'], 2) }} {{ $cur }}</h2>
                        <p class="fd-na">~{{ number_format($profitByService['followers'], 2) }} {{ $cur }} profit</p>
                    </div>
                    <div class="card-stats-icon"><i class="las la-users"></i></div>
                </div>
            </div>
            <div class="width-50">
                <div class="card card-stats">
                    <div class="card-stats-text">
                        <p>Best Selling Service</p>
                        <h2>{{ $bestSellingService['name'] }}</h2>
                        <p>{{ number_format($bestSellingService['amount'], 2) }} {{ $cur }} in revenue</p>
                    </div>
                    <div class="card-stats-icon"><i class="las la-trophy"></i></div>
                </div>
            </div>
            <div class="width-50">
                <div class="card card-stats">
                    <div class="card-stats-text">
                        <p>Best Selling Product</p>
                        @if ($bestSellingProduct)
                            <h2>{{ $bestSellingProduct['name'] }}</h2>
                            <p>{{ $bestSellingProduct['sold'] }} sold</p>
                        @else
                            <h2>N/A</h2>
                        @endif
                    </div>
                    <div class="card-stats-icon"><i class="las la-star"></i></div>
                </div>
            </div>
        </div>

        {{-- Quick statistics --}}
        <div class="card table-card" id="fd-quick-stats">
            <div class="table-header"><h2>Quick Statistics</h2></div>
            <div class="row">
                <div class="width-25">
                    <div class="card card-stats">
                        <div class="card-stats-text">
                            <p>Average Order Value</p>
                            <h2>{{ number_format($quickStats['avgOrderValue'], 2) }} {{ $cur }}</h2>
                        </div>
                        <div class="card-stats-icon"><i class="las la-receipt"></i></div>
                    </div>
                </div>
                <div class="width-25">
                    <div class="card card-stats">
                        <div class="card-stats-text">
                            <p>Total Products Sold</p>
                            <h2>{{ $quickStats['totalProductsSold'] }}</h2>
                        </div>
                        <div class="card-stats-icon"><i class="las la-box"></i></div>
                    </div>
                </div>
                <div class="width-25">
                    <div class="card card-stats">
                        <div class="card-stats-text">
                            <p>Total Transactions</p>
                            <h2>{{ $quickStats['totalTransactions'] }}</h2>
                        </div>
                        <div class="card-stats-icon"><i class="las la-exchange-alt"></i></div>
                    </div>
                </div>
                <div class="width-25">
                    <div class="card card-stats">
                        <div class="card-stats-text">
                            <p>Total Active Customers</p>
                            <h2>{{ $quickStats['totalActiveCustomers'] }}</h2>
                        </div>
                        <div class="card-stats-icon"><i class="las la-user-check"></i></div>
                    </div>
                </div>
            </div>
            <p class="fd-conversion-note">
                Conversion rate (paying customers &divide; registered customers):
                <strong>{{ $quickStats['conversionRate'] }}%</strong>
                &mdash; based on registered users, since no visitor/traffic tracking exists yet.
            </p>
        </div>

        {{-- Graphs --}}
        <div class="row fd-graphs">
            <div class="width-50">
                <div class="card fd-chart-card">
                    <h3>Revenue</h3>
                    <div class="fd-chart-canvas-wrap"><canvas id="fd-revenue-chart"></canvas></div>
                </div>
            </div>
            <div class="width-50">
                <div class="card fd-chart-card">
                    <h3>Deposits</h3>
                    <div class="fd-chart-canvas-wrap"><canvas id="fd-deposit-chart"></canvas></div>
                </div>
            </div>
            <div class="width-50">
                <div class="card fd-chart-card">
                    <h3>Orders</h3>
                    <div class="fd-chart-canvas-wrap"><canvas id="fd-orders-chart"></canvas></div>
                </div>
            </div>
            <div class="width-50">
                <div class="card fd-chart-card">
                    <h3>Revenue by Service</h3>
                    <div class="fd-chart-canvas-wrap"><canvas id="fd-service-chart"></canvas></div>
                </div>
            </div>
        </div>

        {{-- Recent signups --}}
        <div class="card table-card">
            <div class="table-header">
                <div class="fd-table-title">
                    <h2>Recent Signups <span class="fd-section-hint">(for selected period)</span></h2>
                    <a href="{{ route('backend.users.index') }}" class="fd-view-all">View all <i class="las la-arrow-right"></i></a>
                </div>
                <input type="text" class="form-control fd-search" id="fd-search-signups" placeholder="Search signups...">
            </div>
            <div class="table-responsive">
                <table class="table" id="fd-signups-table">
                    <thead>
                        <tr>
                            <th>Name</th><th>Email</th><th>Signed up</th><th>Total Deposited</th>
                        </tr>
                    </thead>
                    <tbody id="fd-signups-body">
                        @forelse ($recentSignups as $row)
                            <tr>
                                <td>{{ trim($row->fname . ' ' . $row->lname) ?: '—' }}</td>
                                <td>{{ $row->email }}</td>
                                <td>{{ $row->created_at?->format('M j, Y H:i') }}</td>
                                <td>{{ number_format((float) ($row->total_deposited ?? 0), 2) }} {{ $cur }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No signups found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top depositors --}}
        <div class="card table-card">
            <div class="table-header">
                <div class="fd-table-title">
                    <h2>Top Depositors <span class="fd-section-hint">(all-time)</span></h2>
                    <a href="{{ route('backend.users.index') }}" class="fd-view-all">View all <i class="las la-arrow-right"></i></a>
                </div>
                <input type="text" class="form-control fd-search" id="fd-search-depositors" placeholder="Search depositors...">
            </div>
            <div class="table-responsive">
                <table class="table" id="fd-depositors-table">
                    <thead>
                        <tr>
                            <th>Name</th><th>Email</th><th>Total Deposited</th><th>Total Spent</th><th>Available Balance</th>
                        </tr>
                    </thead>
                    <tbody id="fd-depositors-body">
                        @forelse ($topDepositors as $row)
                            <tr>
                                <td>{{ trim($row->fname . ' ' . $row->lname) ?: '—' }}</td>
                                <td>{{ $row->email }}</td>
                                <td>{{ number_format((float) ($row->total_deposited ?? 0), 2) }} {{ $cur }}</td>
                                <td>{{ number_format((float) ($row->total_spent ?? 0), 2) }} {{ $cur }}</td>
                                <td>{{ number_format((float) $row->balance, 2) }} {{ $cur }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No depositors found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent transactions --}}
        <div class="card table-card">
            <div class="table-header">
                <div class="fd-table-title">
                    <h2>Recent Transactions</h2>
                    <a href="{{ route('backend.payments.index') }}" class="fd-view-all">View all <i class="las la-arrow-right"></i></a>
                </div>
                <input type="text" class="form-control fd-search" id="fd-search-transactions" placeholder="Search transactions...">
            </div>
            <div class="table-responsive">
                <table class="table" id="fd-transactions-table">
                    <thead>
                        <tr>
                            <th>Customer</th><th>Amount</th><th>Product</th><th>Date</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="fd-transactions-body">
                        @forelse ($recentTransactions as $row)
                            <tr>
                                <td>{{ $row->customer }}</td>
                                <td>{{ number_format((float) $row->amount, 2) }} {{ $cur }}</td>
                                <td>{{ $row->product }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($row->created_at)->format('M j, Y H:i') }}</td>
                                <td><span class="badge {{ in_array($row->status, ['success','completed','paid','active','restored']) ? 'active' : (in_array($row->status, ['failed','cancelled','canceled']) ? 'inactive' : 'pending') }}">{{ ucfirst($row->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent orders --}}
        <div class="card table-card">
            <div class="table-header">
                <div class="fd-table-title">
                    <h2>Recent Orders</h2>
                    <a href="{{ route('backend.logs.transactions') }}" class="fd-view-all">View all <i class="las la-arrow-right"></i></a>
                </div>
                <input type="text" class="form-control fd-search" id="fd-search-orders" placeholder="Search orders...">
            </div>
            <div class="table-responsive">
                <table class="table" id="fd-orders-table">
                    <thead>
                        <tr>
                            <th>Customer</th><th>Product</th><th>Service</th><th>Amount</th><th>Status</th><th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="fd-orders-body">
                        @forelse ($recentOrders as $row)
                            <tr>
                                <td>{{ $row->customer }}</td>
                                <td>{{ $row->product }}</td>
                                <td>{{ $row->service }}</td>
                                <td>{{ number_format((float) $row->amount, 2) }} {{ $cur }}</td>
                                <td><span class="badge {{ in_array($row->status, ['completed','paid','active','restored']) ? 'active' : (in_array($row->status, ['failed','cancelled','canceled']) ? 'inactive' : 'pending') }}">{{ ucfirst($row->status) }}</span></td>
                                <td>{{ \Illuminate\Support\Carbon::parse($row->created_at)->format('M j, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No orders found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Cron jobs (unchanged) --}}
        <div class="cron-grid">
            <div class="card cron-card">
                <h3 class="cron-title">Number service imports and other queued jobs.</h3>
                <div class="input-group">
                    <input type="text" readonly class="form-control cron-input-field" value="{{ $cronQueueWork }}">
                    <div class="input-group-append cron-input">
                        <button type="button" class="js-copy-cron-url"><i class="las la-copy"></i> Copy url</button>
                    </div>
                </div>
            </div>

            @if (optional($settings)->boost_social)
                <div class="card cron-card">
                    <h3 class="cron-title">Send orders</h3>
                    <div class="input-group">
                        <input type="text" readonly class="form-control cron-input-field" value="{{ $cronSendOrders }}">
                        <div class="input-group-append cron-input">
                            <button type="button" class="js-copy-cron-url"><i class="las la-copy"></i> Copy url</button>
                        </div>
                    </div>
                </div>
            @endif

            @if (optional($settings)->boost_social)
                <div class="card cron-card">
                    <h3 class="cron-title">Sync status</h3>
                    <div class="input-group">
                        <input type="text" readonly class="form-control cron-input-field" value="{{ $cronSyncStatus }}">
                        <div class="input-group-append cron-input">
                            <button type="button" class="js-copy-cron-url"><i class="las la-copy"></i> Copy url</button>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card cron-card">
                <h3 class="cron-title">Sync services</h3>
                <div class="input-group">
                    <input type="text" readonly class="form-control cron-input-field" value="{{ $cronSyncServices }}">
                    <div class="input-group-append cron-input">
                        <button type="button" class="js-copy-cron-url"><i class="las la-copy"></i> Copy url</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('style')
        <link rel="stylesheet" href="{{ asset('assets/backend/css/finance-dashboard.css') }}?v={{ file_exists(base_path('../assets/backend/css/finance-dashboard.css')) ? filemtime(base_path('../assets/backend/css/finance-dashboard.css')) : time() }}">
    @endpush

    @push('script')
        <script>
            window.FD_ROUTES = {
                filter: "{{ route('backend.dashboard.filter') }}",
                exportCsv: "{{ route('backend.dashboard.export.csv') }}",
                exportExcel: "{{ route('backend.dashboard.export.excel') }}",
                exportPdf: "{{ route('backend.dashboard.export.pdf') }}",
            };
            window.FD_INITIAL_GRAPHS = @json($graphs);
            window.FD_CURRENCY = @json($cur);
        </script>
        <script>
            (function() {
                document.querySelectorAll('.js-copy-cron-url').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var card = this.closest('.cron-card');
                        var input = card ? card.querySelector('.cron-input-field') : null;
                        var url = input ? input.value : '';
                        if (!url) return;
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(url).then(function() {
                                if (typeof iziToast !== 'undefined') {
                                    iziToast.success({ message: 'URL copied to clipboard', position: 'topRight' });
                                } else {
                                    alert('URL copied.');
                                }
                            }).catch(function() { fallbackCopy(url); });
                        } else {
                            fallbackCopy(url);
                        }
                    });
                });

                function fallbackCopy(text) {
                    var ta = document.createElement('textarea');
                    ta.value = text;
                    ta.style.position = 'fixed';
                    ta.style.opacity = '0';
                    document.body.appendChild(ta);
                    ta.select();
                    try {
                        document.execCommand('copy');
                        if (typeof iziToast !== 'undefined') {
                            iziToast.success({ message: 'URL copied to clipboard', position: 'topRight' });
                        } else {
                            alert('URL copied.');
                        }
                    } catch (e) {}
                    document.body.removeChild(ta);
                }
            })();
        </script>
        <script src="{{ asset('assets/backend/js/finance-dashboard.js') }}?v={{ file_exists(base_path('../assets/backend/js/finance-dashboard.js')) ? filemtime(base_path('../assets/backend/js/finance-dashboard.js')) : time() }}"></script>
    @endpush
@endsection
