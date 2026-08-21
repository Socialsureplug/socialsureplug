<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\FinanceDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Response;

class DashController extends Controller
{
    public function __construct(protected FinanceDashboardService $financeDashboard)
    {
    }

    public function dashboard(Request $request)
    {
        $title = 'Dashboard';

        $period = $request->input('period', 'today');
        [$from, $to] = $this->financeDashboard->resolvePeriod(
            $period,
            $request->input('from'),
            $request->input('to')
        );

        $data = $this->financeDashboard->build($from, $to);

        $settings = Setting::first();

        $cronQueueWork    = 'curl -s "'.url('cron/queue-work').'"';
        $cronSendOrders   = 'curl -s "'.url('cron/smm/send-orders').'"';
        $cronSyncStatus   = 'curl -s "'.url('cron/smm/sync-status').'"';
        $cronSyncServices = 'curl -s "'.url('cron/smm/sync-services').'"';

        return view('backend.dashboard', array_merge($data, [
            'title'             => $title,
            'settings'          => $settings,
            'period'            => $period,
            'from'              => $from->toDateString(),
            'to'                => $to->toDateString(),
            'cronQueueWork'     => $cronQueueWork,
            'cronSendOrders'    => $cronSendOrders,
            'cronSyncStatus'    => $cronSyncStatus,
            'cronSyncServices'  => $cronSyncServices,
        ]));
    }

    /**
     * AJAX refresh: returns the same payload as dashboard() as JSON so the
     * filter bar can update cards/graphs/tables without a full reload.
     *
     * Renders via Blade::render() (inline template strings, defined in
     * bladeTemplates() below) rather than separate view files — this keeps
     * the initial page and the AJAX refresh from depending on any file that
     * could go missing/mis-cased during deployment.
     */
    public function filter(Request $request)
    {
        $period = $request->input('period', 'today');
        [$from, $to] = $this->financeDashboard->resolvePeriod(
            $period,
            $request->input('from'),
            $request->input('to')
        );

        $data = $this->financeDashboard->build($from, $to);
        $cur = optional(Setting::first())->website_currency ?? 'USD';
        $tpl = $this->bladeTemplates();

        return response()->json([
            'graphs'        => $data['graphs'],
            'notifications' => $data['notifications'],
            'html' => [
                'cardsTop'     => Blade::render($tpl['cardsTop'], ['cards' => $data['cards'], 'cur' => $cur]),
                'cardsBottom'  => Blade::render($tpl['cardsBottom'], ['cards' => $data['cards'], 'cur' => $cur]),
                'secondary'    => Blade::render($tpl['secondary'], [
                    'revenueByService'   => $data['revenueByService'],
                    'profitByService'    => $data['profitByService'],
                    'bestSellingService' => $data['bestSellingService'],
                    'bestSellingProduct' => $data['bestSellingProduct'],
                    'cur'                => $cur,
                ]),
                'quickStats'   => Blade::render($tpl['quickStats'], ['quickStats' => $data['quickStats'], 'cur' => $cur]),
                'signups'      => Blade::render($tpl['signups'], ['rows' => $data['recentSignups'], 'cur' => $cur]),
                'depositors'   => Blade::render($tpl['depositors'], ['rows' => $data['topDepositors'], 'cur' => $cur]),
                'transactions' => Blade::render($tpl['transactions'], ['rows' => $data['recentTransactions'], 'cur' => $cur]),
                'orders'       => Blade::render($tpl['orders'], ['rows' => $data['recentOrders'], 'cur' => $cur]),
            ],
        ]);
    }

    protected function periodFromRequest(Request $request): array
    {
        return $this->financeDashboard->resolvePeriod(
            $request->input('period', 'month'),
            $request->input('from'),
            $request->input('to')
        );
    }

    public function exportCsv(Request $request)
    {
        [$from, $to] = $this->periodFromRequest($request);
        $rows = $this->financeDashboard->transactionsForExport($from, $to);

        $filename = 'finance-report-' . $from->toDateString() . '-to-' . $to->toDateString() . '.csv';

        return Response::streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Customer', 'Amount', 'Product', 'Date', 'Status']);
            foreach ($rows as $row) {
                fputcsv($out, [$row->customer, $row->amount, $row->product, $row->created_at, $row->status]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * "Excel" export. No composer package (PhpSpreadsheet / Maatwebsite
     * Excel) is installed in this project, so this ships an HTML table
     * served with an .xls extension and the Excel MIME type — Excel and
     * Google Sheets both open this correctly. For a true native .xlsx,
     * run `composer require maatwebsite/excel` and swap this method to
     * use it; the query (transactionsForExport) is already package-agnostic.
     */
    public function exportExcel(Request $request)
    {
        [$from, $to] = $this->periodFromRequest($request);
        $rows = $this->financeDashboard->transactionsForExport($from, $to);

        $filename = 'finance-report-' . $from->toDateString() . '-to-' . $to->toDateString() . '.xls';

        $html = Blade::render($this->bladeTemplates()['exportTable'], ['rows' => $rows]);

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * PDF export. Requires `composer require barryvdh/laravel-dompdf`
     * (not installed in this project — packagist wasn't reachable from
     * the build sandbox, so install it before deploying). Until then this
     * falls back to a print-friendly HTML page the admin can "Print > Save
     * as PDF" from the browser.
     */
    public function exportPdf(Request $request)
    {
        [$from, $to] = $this->periodFromRequest($request);
        $rows = $this->financeDashboard->transactionsForExport($from, $to);
        $cards = $this->financeDashboard->cards($from, $to);

        $printFallback = !class_exists(\Barryvdh\DomPDF\Facade\Pdf::class);
        $html = Blade::render($this->bladeTemplates()['exportPdf'], [
            'rows' => $rows, 'cards' => $cards, 'from' => $from, 'to' => $to, 'printFallback' => $printFallback,
        ]);

        if (!$printFallback) {
            return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                ->download('finance-report-' . $from->toDateString() . '-to-' . $to->toDateString() . '.pdf');
        }

        return response($html);
    }

    /**
     * Inline Blade template strings, compiled on the fly via Blade::render().
     * Kept here (rather than as separate resources/views files) so nothing
     * on the AJAX filter or export endpoints can fail with a
     * "view not found" error due to a missing/mis-deployed file.
     */
    protected function bladeTemplates(): array
    {
        return [
            'cardsTop' => <<<'BLADE'
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
BLADE,

            'cardsBottom' => <<<'BLADE'
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
BLADE,

            'secondary' => <<<'BLADE'
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
BLADE,

            'quickStats' => <<<'BLADE'
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
BLADE,

            'signups' => <<<'BLADE'
@forelse ($rows as $row)
    <tr>
        <td>{{ trim($row->fname . ' ' . $row->lname) ?: '—' }}</td>
        <td>{{ $row->email }}</td>
        <td>{{ $row->created_at?->format('M j, Y H:i') }}</td>
        <td>{{ number_format((float) ($row->total_deposited ?? 0), 2) }} {{ $cur }}</td>
    </tr>
@empty
    <tr><td colspan="4" class="text-center">No signups found.</td></tr>
@endforelse
BLADE,

            'depositors' => <<<'BLADE'
@forelse ($rows as $row)
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
BLADE,

            'transactions' => <<<'BLADE'
@forelse ($rows as $row)
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
BLADE,

            'orders' => <<<'BLADE'
@forelse ($rows as $row)
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
BLADE,

            'exportTable' => <<<'BLADE'
<table border="1">
    <thead>
        <tr><th>Customer</th><th>Amount</th><th>Product</th><th>Date</th><th>Status</th></tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            <tr>
                <td>{{ $row->customer }}</td>
                <td>{{ number_format((float) $row->amount, 2) }}</td>
                <td>{{ $row->product }}</td>
                <td>{{ $row->created_at }}</td>
                <td>{{ ucfirst($row->status) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
BLADE,

            'exportPdf' => <<<'BLADE'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Finance Report {{ $from->toDateString() }} to {{ $to->toDateString() }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #1e293b; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .subtitle { color: #64748b; margin-bottom: 16px; }
        .cards { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .cards td { padding: 6px 10px; border: 1px solid #e2e8f0; }
        table.transactions { width: 100%; border-collapse: collapse; }
        table.transactions th, table.transactions td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; }
        table.transactions th { background: #f1f5f9; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    @if (!empty($printFallback))
        <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>
        <p class="print-btn" style="color:#dc2626;">
            barryvdh/laravel-dompdf isn't installed, so this is a print-friendly
            fallback &mdash; run <code>composer require barryvdh/laravel-dompdf</code>
            for a direct PDF download instead.
        </p>
    @endif

    <h1>Finance Report</h1>
    <div class="subtitle">{{ $from->toFormattedDateString() }} &ndash; {{ $to->toFormattedDateString() }}</div>

    <table class="cards">
        <tr>
            <td>Total Deposits</td><td>{{ number_format($cards['totalDeposits'], 2) }}</td>
            <td>Total Revenue</td><td>{{ number_format($cards['totalRevenue'], 2) }}</td>
        </tr>
        <tr>
            <td>Completed Orders</td><td>{{ $cards['completedOrders'] }}</td>
            <td>Failed Orders</td><td>{{ $cards['failedOrders'] }}</td>
        </tr>
        <tr>
            <td>Pending Orders</td><td>{{ $cards['pendingOrders'] }}</td>
            <td>Refunded Orders</td><td>{{ $cards['refundedOrders'] }}</td>
        </tr>
    </table>

    <table class="transactions">
        <thead>
            <tr><th>Customer</th><th>Amount</th><th>Product</th><th>Date</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->customer }}</td>
                    <td>{{ number_format((float) $row->amount, 2) }}</td>
                    <td>{{ $row->product }}</td>
                    <td>{{ $row->created_at }}</td>
                    <td>{{ ucfirst($row->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
BLADE,
        ];
    }
}
