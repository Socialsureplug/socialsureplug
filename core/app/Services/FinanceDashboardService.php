<?php

namespace App\Services;

use App\Models\NumberOrder;
use App\Models\NumberSetting;
use App\Models\Payment;
use App\Models\RentingOrder;
use App\Models\RentingSetting;
use App\Models\SellingOrder;
use App\Models\SellingProduct;
use App\Models\Setting;
use App\Models\SmmOrder;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Centralizes every query behind the admin Finance Dashboard.
 *
 * Status-bucket assumption note
 * ------------------------------
 * The four order tables (number_orders, renting_orders, selling_orders,
 * smm_orders) each use their own status vocabulary. The constants below map
 * each table's raw statuses into three buckets: COMPLETED (counts toward
 * revenue), PENDING (still in progress) and FAILED (cancelled/failed, never
 * counts toward revenue). "Refunded" is tracked separately since two tables
 * use a boolean `refunded` flag and one (selling_orders) uses a status
 * string. If your status naming drifts from this, adjust the constants only
 * — every method below reads from them, nothing is hard-coded elsewhere.
 */
class FinanceDashboardService
{
    public const NUMBER_COMPLETED = ['completed'];
    public const NUMBER_PENDING   = ['waiting_sms'];
    public const NUMBER_FAILED    = ['cancelled', 'failed', 'expired'];

    public const RENTING_COMPLETED = ['completed', 'expired', 'restored'];
    public const RENTING_PENDING   = ['active'];
    public const RENTING_FAILED    = ['failed', 'cancelled'];

    public const SELLING_COMPLETED = ['paid'];
    public const SELLING_PENDING   = ['pending', 'running'];
    public const SELLING_FAILED    = ['failed'];
    public const SELLING_REFUNDED  = ['refunded'];

    public const SMM_COMPLETED = ['completed', 'partial'];
    public const SMM_PENDING   = ['awaiting', 'pending', 'processing', 'inprogress'];
    public const SMM_FAILED    = ['canceled', 'cancelled'];

    /** Amounts at/above this are flagged as "large" in notifications. Tune to taste. */
    public const LARGE_DEPOSIT_THRESHOLD  = 50000;
    public const LARGE_PURCHASE_THRESHOLD = 20000;

    /** Stock at/below this triggers a low-stock notification. */
    public const LOW_STOCK_THRESHOLD = 5;

    /**
     * Resolve a (from, to) Carbon pair from a filter period key.
     */
    public function resolvePeriod(string $period, ?string $from = null, ?string $to = null): array
    {
        return match ($period) {
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            '7days'     => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'month'     => [now()->startOfMonth(), now()->endOfMonth()],
            'year'      => [now()->startOfYear(), now()->endOfYear()],
            'custom'    => [
                $from ? Carbon::parse($from)->startOfDay() : now()->startOfMonth(),
                $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay(),
            ],
            default => [now()->startOfDay(), now()->endOfDay()], // 'today'
        };
    }

    /**
     * Full payload for the dashboard: page load and the AJAX filter refresh
     * both call this so the two never drift apart.
     */
    public function build(Carbon $from, Carbon $to): array
    {
        return [
            'cards'             => $this->cards($from, $to),
            'revenueByService'  => $this->revenueByService($from, $to),
            'profitByService'   => $this->profitByService($from, $to),
            'bestSellingService'=> $this->bestSellingService($from, $to),
            'bestSellingProduct'=> $this->bestSellingProduct(),
            'recentSignups'     => $this->recentSignups(10, $from, $to),
            'topDepositors'     => $this->topDepositors(10),
            'recentTransactions'=> $this->recentTransactions(5, $from, $to),
            'recentOrders'      => $this->recentOrders(5, $from, $to),
            'graphs'            => $this->graphs($from, $to),
            'quickStats'        => $this->quickStats($from, $to),
            'notifications'     => $this->notifications(),
        ];
    }

    /* ----------------------------------------------------------------- *
     * Top cards
     * ----------------------------------------------------------------- */

    public function cards(Carbon $from, Carbon $to): array
    {
        $todayStart = now()->startOfDay();
        $todayEnd   = now()->endOfDay();
        $yesterdayStart = now()->subDay()->startOfDay();
        $yesterdayEnd   = now()->subDay()->endOfDay();

        $todaysDeposits = (float) Payment::where('status', 'success')
            ->whereBetween('created_at', [$todayStart, $todayEnd])->sum('amount');
        $yesterdaysDeposits = (float) Payment::where('status', 'success')
            ->whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])->sum('amount');

        $totalDeposits = (float) Payment::where('status', 'success')->sum('amount');

        $todaysRevenue = $this->revenueBetween($todayStart, $todayEnd);
        $yesterdaysRevenue = $this->revenueBetween($yesterdayStart, $yesterdayEnd);
        $totalRevenue  = $this->revenueBetween(null, null);

        $todaysProfit = $this->estimatedProfit($todayStart, $todayEnd);
        $yesterdaysProfit = $this->estimatedProfit($yesterdayStart, $yesterdayEnd);
        $rangeProfit  = $this->estimatedProfit($from, $to);

        $userWalletBalance = (float) User::sum('balance');

        $statusCounts = $this->orderStatusCounts($from, $to);

        return [
            'todaysDeposits'     => $todaysDeposits,
            'depositsTrend'      => $this->trend($todaysDeposits, $yesterdaysDeposits),
            'totalDeposits'      => $totalDeposits,
            'todaysRevenue'      => $todaysRevenue,
            'revenueTrend'       => $this->trend($todaysRevenue, $yesterdaysRevenue),
            'totalRevenue'       => $totalRevenue,
            'todaysProfit'       => $todaysProfit,
            'profitTrend'        => $this->trend($todaysProfit, $yesterdaysProfit),
            'rangeProfit'        => $rangeProfit,
            'userWalletBalance'  => $userWalletBalance,
            'pendingOrders'      => $statusCounts['pending'],
            'completedOrders'    => $statusCounts['completed'],
            'failedOrders'       => $statusCounts['failed'],
            'refundedOrders'     => $statusCounts['refunded'],
            'totalCustomers'     => User::count(),
            'newCustomersToday'  => User::whereBetween('created_at', [$todayStart, $todayEnd])->count(),
            'newCustomersInRange'=> User::whereBetween('created_at', [$from, $to])->count(),
        ];
    }

    /**
     * Simple up/down/flat comparison against the prior equivalent value —
     * used for the trend arrows on the three "Today's X" cards (compared
     * to the same metric yesterday). Returns null when there's nothing
     * meaningful to compare against (yesterday was zero).
     */
    protected function trend(float $current, float $previous): ?array
    {
        if ($previous <= 0.0) {
            return null;
        }

        $percent = round((($current - $previous) / $previous) * 100, 1);

        return [
            'direction' => $percent > 0.05 ? 'up' : ($percent < -0.05 ? 'down' : 'flat'),
            'percent'   => abs($percent),
        ];
    }

    /* ----------------------------------------------------------------- *
     * Revenue helpers (shared by cards, graphs, revenue-by-service)
     * ----------------------------------------------------------------- */

    /**
     * Sum of all completed-order revenue across the four service tables.
     * Pass null/null for all-time.
     */
    public function revenueBetween(?Carbon $from, ?Carbon $to): float
    {
        $sum = 0.0;
        $sum += (float) $this->scoped(NumberOrder::query(), $from, $to)
            ->whereIn('status', self::NUMBER_COMPLETED)->where('refunded', 0)->sum('price');
        $sum += (float) $this->scoped(RentingOrder::query(), $from, $to)
            ->whereIn('status', self::RENTING_COMPLETED)->where('refunded', 0)->sum('price');
        $sum += (float) $this->scoped(SellingOrder::query(), $from, $to)
            ->whereIn('status', self::SELLING_COMPLETED)->sum('total_amount');
        $sum += (float) $this->scoped(SmmOrder::query(), $from, $to)
            ->whereIn('status', self::SMM_COMPLETED)->where('refunded', 0)->sum('charge');

        return $sum;
    }

    public function revenueByService(Carbon $from, Carbon $to): array
    {
        return [
            'accountLogs'    => (float) $this->scoped(SellingOrder::query(), $from, $to)
                ->whereIn('status', self::SELLING_COMPLETED)->sum('total_amount'),
            'virtualNumber'  => (float) $this->scoped(NumberOrder::query(), $from, $to)
                ->whereIn('status', self::NUMBER_COMPLETED)->where('refunded', 0)->sum('price'),
            'rentalNumber'   => (float) $this->scoped(RentingOrder::query(), $from, $to)
                ->whereIn('status', self::RENTING_COMPLETED)->where('refunded', 0)->sum('price'),
            'followers'      => (float) $this->scoped(SmmOrder::query(), $from, $to)
                ->whereIn('status', self::SMM_COMPLETED)->where('refunded', 0)->sum('charge'),
        ];
    }

    public function bestSellingService(Carbon $from, Carbon $to): array
    {
        $revenue = $this->revenueByService($from, $to);
        $labels = [
            'accountLogs'   => 'Account Logs',
            'virtualNumber' => 'Virtual Number',
            'rentalNumber'  => 'Rental Number',
            'followers'     => 'Followers / SMM',
        ];
        $topKey = array_keys($revenue, max($revenue))[0] ?? null;

        return [
            'name'   => $topKey ? $labels[$topKey] : 'N/A',
            'amount' => $topKey ? $revenue[$topKey] : 0.0,
        ];
    }

    public function bestSellingProduct(): ?array
    {
        $product = SellingProduct::orderByDesc('sold')->first();
        if (!$product) {
            return null;
        }

        return [
            'name'  => $product->title,
            'sold'  => (int) $product->sold,
            'price' => (float) $product->price,
        ];
    }

    /* ----------------------------------------------------------------- *
     * Signups / depositors
     * ----------------------------------------------------------------- */

    /**
     * The most recently created accounts, newest first, scoped to the
     * selected period (pass null/null for all-time). Each row carries its
     * all-time total successful deposit amount, not scoped to the period —
     * "how much they've deposited" reads as their running total, not just
     * what happened to land inside the filter window.
     */
    public function recentSignups(int $limit = 10, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        return User::query()
            ->when($from && $to, fn ($q) => $q->whereBetween('created_at', [$from, $to]))
            ->withSum(['payments as total_deposited' => fn ($q) => $q->where('status', 'success')], 'amount')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * The users with the highest all-time successful deposit total, along
     * with their all-time total spend (every debit transaction, across all
     * four services) and their current live wallet balance. This is a
     * leaderboard, not a period-scoped figure — it always covers all-time
     * regardless of the dashboard's date filter.
     */
    public function topDepositors(int $limit = 10): Collection
    {
        return User::query()
            ->withSum(['payments as total_deposited' => fn ($q) => $q->where('status', 'success')], 'amount')
            ->withSum(['transactions as total_spent' => fn ($q) => $q->where('type', 'debit')], 'amount')
            ->havingRaw('total_deposited > 0')
            ->orderByDesc('total_deposited')
            ->limit($limit)
            ->get();
    }

    /* ----------------------------------------------------------------- *
     * Profit
     * ----------------------------------------------------------------- */

    /**
     * Profit per service, computed per order wherever possible:
     *
     *   profit = amount_charged - cost
     *
     * `amount_charged` is whatever the customer actually paid (already net
     * of any per-user service discount — see UserServiceDiscount), and
     * `cost` is a provider-cost snapshot taken at purchase time (in site
     * currency), stored on the order row itself. This makes profit exact
     * per order and automatically correct when a discounted user pays
     * less than list price — a flat "revenue x margin%" estimate would
     * overstate profit on discounted orders since it has no way to know
     * a discount was applied.
     *
     * Orders placed before the `cost` column existed have cost = NULL.
     * For those legacy rows only, this falls back to the old estimate
     * (revenue x current markup%) so historical reporting doesn't just
     * drop to zero — that fallback is still an approximation and doesn't
     * account for discounts, but it only ever applies to pre-existing data.
     */
    public function profitByService(Carbon $from, Carbon $to): array
    {
        $numberIncrease  = (float) NumberSetting::current()->price_increase;
        $rentingIncrease = (float) RentingSetting::current()->price_increase;
        $globalIncrease  = (float) (Setting::first()->price_increase ?? 0);

        // PHP's number_format(-0.0, 2) prints "-0.00" — snap near-zero to a clean 0.0.
        $clean = fn (float $v) => abs($v) < 0.005 ? 0.0 : round($v, 2);

        return [
            'accountLogs'   => $clean($this->serviceProfit(SellingOrder::query(), $from, $to, self::SELLING_COMPLETED, 'total_amount', null, $globalIncrease)),
            'virtualNumber' => $clean($this->serviceProfit(NumberOrder::query(), $from, $to, self::NUMBER_COMPLETED, 'price', 'refunded', $numberIncrease)),
            'rentalNumber'  => $clean($this->serviceProfit(RentingOrder::query(), $from, $to, self::RENTING_COMPLETED, 'price', 'refunded', $rentingIncrease)),
            'followers'     => $clean($this->serviceProfit(SmmOrder::query(), $from, $to, self::SMM_COMPLETED, 'charge', 'refunded', $globalIncrease)),
        ];
    }

    /**
     * Sums exact per-order profit (amount - cost) for rows with a cost
     * snapshot, and the legacy revenue x margin% estimate for rows without
     * one (cost IS NULL), in a single query.
     */
    protected function serviceProfit(
        $query,
        Carbon $from,
        Carbon $to,
        array $statuses,
        string $amountColumn,
        ?string $refundedColumn,
        float $fallbackMarkupPct
    ): float {
        $marginFraction = $fallbackMarkupPct > 0 ? $fallbackMarkupPct / (100 + $fallbackMarkupPct) : 0.0;

        $q = $this->scoped($query, $from, $to)->whereIn('status', $statuses);
        if ($refundedColumn) {
            $q->where($refundedColumn, 0);
        }

        $profit = $q->selectRaw(
            "SUM(CASE WHEN cost IS NOT NULL THEN ({$amountColumn} - cost) ELSE ({$amountColumn} * ?) END) as profit",
            [$marginFraction]
        )->value('profit');

        return (float) ($profit ?? 0);
    }

    public function estimatedProfit(Carbon $from, Carbon $to): float
    {
        return array_sum($this->profitByService($from, $to));
    }

    /* ----------------------------------------------------------------- *
     * Order status counts (Pending / Completed / Failed / Refunded)
     * ----------------------------------------------------------------- */

    public function orderStatusCounts(Carbon $from, Carbon $to): array
    {
        $pending = $this->scoped(NumberOrder::query(), $from, $to)->whereIn('status', self::NUMBER_PENDING)->count()
            + $this->scoped(RentingOrder::query(), $from, $to)->whereIn('status', self::RENTING_PENDING)->count()
            + $this->scoped(SellingOrder::query(), $from, $to)->whereIn('status', self::SELLING_PENDING)->count()
            + $this->scoped(SmmOrder::query(), $from, $to)->whereIn('status', self::SMM_PENDING)->count();

        $completed = $this->scoped(NumberOrder::query(), $from, $to)->whereIn('status', self::NUMBER_COMPLETED)->count()
            + $this->scoped(RentingOrder::query(), $from, $to)->whereIn('status', self::RENTING_COMPLETED)->count()
            + $this->scoped(SellingOrder::query(), $from, $to)->whereIn('status', self::SELLING_COMPLETED)->count()
            + $this->scoped(SmmOrder::query(), $from, $to)->whereIn('status', self::SMM_COMPLETED)->count();

        $failed = $this->scoped(NumberOrder::query(), $from, $to)->whereIn('status', self::NUMBER_FAILED)->count()
            + $this->scoped(RentingOrder::query(), $from, $to)->whereIn('status', self::RENTING_FAILED)->count()
            + $this->scoped(SellingOrder::query(), $from, $to)->whereIn('status', self::SELLING_FAILED)->count()
            + $this->scoped(SmmOrder::query(), $from, $to)->whereIn('status', self::SMM_FAILED)->count();

        $refunded = $this->scoped(NumberOrder::query(), $from, $to)->where('refunded', 1)->count()
            + $this->scoped(RentingOrder::query(), $from, $to)->where('refunded', 1)->count()
            + $this->scoped(SellingOrder::query(), $from, $to)->whereIn('status', self::SELLING_REFUNDED)->count()
            + $this->scoped(SmmOrder::query(), $from, $to)->where('refunded', 1)->count();

        return compact('pending', 'completed', 'failed', 'refunded');
    }

    /* ----------------------------------------------------------------- *
     * Recent transactions / orders (union across tables)
     * ----------------------------------------------------------------- */

    /**
     * Deposits + purchases, newest first, scoped to the selected period
     * (pass null/null for all-time). Built as a UNION so it's one query
     * instead of four fetch-then-merge-in-PHP round trips.
     */
    public function recentTransactions(int $limit = 12, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $deposits = DB::table('payments')
            ->join('users', 'users.id', '=', 'payments.user_id')
            ->when($from && $to, fn ($q) => $q->whereBetween('payments.created_at', [$from, $to]))
            ->selectRaw("CONCAT(users.fname, ' ', users.lname) as customer, payments.amount as amount, 'Wallet Deposit' as product, payments.created_at as created_at, payments.status as status, 'deposit' as kind");

        $numbers = DB::table('number_orders')
            ->join('users', 'users.id', '=', 'number_orders.user_id')
            ->when($from && $to, fn ($q) => $q->whereBetween('number_orders.created_at', [$from, $to]))
            ->selectRaw("CONCAT(users.fname, ' ', users.lname) as customer, number_orders.price as amount, number_orders.service_name as product, number_orders.created_at as created_at, number_orders.status as status, 'virtual_number' as kind");

        $rentals = DB::table('renting_orders')
            ->join('users', 'users.id', '=', 'renting_orders.user_id')
            ->when($from && $to, fn ($q) => $q->whereBetween('renting_orders.created_at', [$from, $to]))
            ->selectRaw("CONCAT(users.fname, ' ', users.lname) as customer, renting_orders.price as amount, renting_orders.service_name as product, renting_orders.created_at as created_at, renting_orders.status as status, 'rental_number' as kind");

        $selling = DB::table('selling_orders')
            ->join('users', 'users.id', '=', 'selling_orders.user_id')
            ->join('selling_product', 'selling_product.id', '=', 'selling_orders.selling_product_id')
            ->when($from && $to, fn ($q) => $q->whereBetween('selling_orders.created_at', [$from, $to]))
            ->selectRaw("CONCAT(users.fname, ' ', users.lname) as customer, selling_orders.total_amount as amount, selling_product.title as product, selling_orders.created_at as created_at, selling_orders.status as status, 'account_logs' as kind");

        $smm = DB::table('smm_orders')
            ->join('users', 'users.id', '=', 'smm_orders.user_id')
            ->join('smm_services', 'smm_services.id', '=', 'smm_orders.smm_service_id')
            ->when($from && $to, fn ($q) => $q->whereBetween('smm_orders.created_at', [$from, $to]))
            ->selectRaw("CONCAT(users.fname, ' ', users.lname) as customer, smm_orders.charge as amount, smm_services.name as product, smm_orders.created_at as created_at, smm_orders.status as status, 'followers' as kind");

        $union = $deposits->unionAll($numbers)->unionAll($rentals)->unionAll($selling)->unionAll($smm);

        return DB::query()->fromSub($union, 't')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * The four service order tables only (no deposits), newest first,
     * scoped to the selected period (pass null/null for all-time).
     */
    public function recentOrders(int $limit = 12, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $numbers = DB::table('number_orders')
            ->join('users', 'users.id', '=', 'number_orders.user_id')
            ->when($from && $to, fn ($q) => $q->whereBetween('number_orders.created_at', [$from, $to]))
            ->selectRaw("CONCAT(users.fname, ' ', users.lname) as customer, number_orders.service_name as product, number_orders.price as amount, number_orders.status as status, number_orders.created_at as created_at, 'Virtual Number' as service");

        $rentals = DB::table('renting_orders')
            ->join('users', 'users.id', '=', 'renting_orders.user_id')
            ->when($from && $to, fn ($q) => $q->whereBetween('renting_orders.created_at', [$from, $to]))
            ->selectRaw("CONCAT(users.fname, ' ', users.lname) as customer, renting_orders.service_name as product, renting_orders.price as amount, renting_orders.status as status, renting_orders.created_at as created_at, 'Rental Number' as service");

        $selling = DB::table('selling_orders')
            ->join('users', 'users.id', '=', 'selling_orders.user_id')
            ->join('selling_product', 'selling_product.id', '=', 'selling_orders.selling_product_id')
            ->when($from && $to, fn ($q) => $q->whereBetween('selling_orders.created_at', [$from, $to]))
            ->selectRaw("CONCAT(users.fname, ' ', users.lname) as customer, selling_product.title as product, selling_orders.total_amount as amount, selling_orders.status as status, selling_orders.created_at as created_at, 'Account Logs' as service");

        $smm = DB::table('smm_orders')
            ->join('users', 'users.id', '=', 'smm_orders.user_id')
            ->join('smm_services', 'smm_services.id', '=', 'smm_orders.smm_service_id')
            ->when($from && $to, fn ($q) => $q->whereBetween('smm_orders.created_at', [$from, $to]))
            ->selectRaw("CONCAT(users.fname, ' ', users.lname) as customer, smm_services.name as product, smm_orders.charge as amount, smm_orders.status as status, smm_orders.created_at as created_at, 'Followers / SMM' as service");

        $union = $numbers->unionAll($rentals)->unionAll($selling)->unionAll($smm);

        return DB::query()->fromSub($union, 't')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Same union as recentTransactions() but unlimited, for exports.
     */
    public function transactionsForExport(Carbon $from, Carbon $to)
    {
        $deposits = DB::table('payments')
            ->join('users', 'users.id', '=', 'payments.user_id')
            ->whereBetween('payments.created_at', [$from, $to])
            ->selectRaw("CONCAT(users.fname, ' ', users.lname) as customer, payments.amount as amount, 'Wallet Deposit' as product, payments.created_at as created_at, payments.status as status");

        $numbers = DB::table('number_orders')
            ->join('users', 'users.id', '=', 'number_orders.user_id')
            ->whereBetween('number_orders.created_at', [$from, $to])
            ->selectRaw("CONCAT(users.fname, ' ', users.lname) as customer, number_orders.price as amount, number_orders.service_name as product, number_orders.created_at as created_at, number_orders.status as status");

        $rentals = DB::table('renting_orders')
            ->join('users', 'users.id', '=', 'renting_orders.user_id')
            ->whereBetween('renting_orders.created_at', [$from, $to])
            ->selectRaw("CONCAT(users.fname, ' ', users.lname) as customer, renting_orders.price as amount, renting_orders.service_name as product, renting_orders.created_at as created_at, renting_orders.status as status");

        $selling = DB::table('selling_orders')
            ->join('users', 'users.id', '=', 'selling_orders.user_id')
            ->join('selling_product', 'selling_product.id', '=', 'selling_orders.selling_product_id')
            ->whereBetween('selling_orders.created_at', [$from, $to])
            ->selectRaw("CONCAT(users.fname, ' ', users.lname) as customer, selling_orders.total_amount as amount, selling_product.title as product, selling_orders.created_at as created_at, selling_orders.status as status");

        $smm = DB::table('smm_orders')
            ->join('users', 'users.id', '=', 'smm_orders.user_id')
            ->join('smm_services', 'smm_services.id', '=', 'smm_orders.smm_service_id')
            ->whereBetween('smm_orders.created_at', [$from, $to])
            ->selectRaw("CONCAT(users.fname, ' ', users.lname) as customer, smm_orders.charge as amount, smm_services.name as product, smm_orders.created_at as created_at, smm_orders.status as status");

        $union = $deposits->unionAll($numbers)->unionAll($rentals)->unionAll($selling)->unionAll($smm);

        return DB::query()->fromSub($union, 't')->orderByDesc('created_at')->get();
    }

    /* ----------------------------------------------------------------- *
     * Graphs
     * ----------------------------------------------------------------- */

    /**
     * Daily series for revenue, deposits, and order counts across the
     * filter range, plus a revenue-by-service breakdown for the pie/bar.
     */
    public function graphs(Carbon $from, Carbon $to): array
    {
        $days = CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay());
        $labels = [];
        $revenueSeries = [];
        $depositSeries = [];
        $orderSeries = [];

        foreach ($days as $day) {
            $dayStart = $day->copy()->startOfDay();
            $dayEnd   = $day->copy()->endOfDay();

            $labels[] = $day->format('M j');
            $revenueSeries[] = round($this->revenueBetween($dayStart, $dayEnd), 2);
            $depositSeries[] = round((float) Payment::where('status', 'success')
                ->whereBetween('created_at', [$dayStart, $dayEnd])->sum('amount'), 2);

            $orderSeries[] = $this->scoped(NumberOrder::query(), $dayStart, $dayEnd)->count()
                + $this->scoped(RentingOrder::query(), $dayStart, $dayEnd)->count()
                + $this->scoped(SellingOrder::query(), $dayStart, $dayEnd)->count()
                + $this->scoped(SmmOrder::query(), $dayStart, $dayEnd)->count();
        }

        return [
            'labels'            => $labels,
            'revenue'           => $revenueSeries,
            'deposits'          => $depositSeries,
            'orders'            => $orderSeries,
            'revenueByService'  => $this->revenueByService($from, $to),
        ];
    }

    /* ----------------------------------------------------------------- *
     * Quick statistics
     * ----------------------------------------------------------------- */

    public function quickStats(Carbon $from, Carbon $to): array
    {
        $statusCounts = $this->orderStatusCounts($from, $to);
        $totalOrders = $statusCounts['pending'] + $statusCounts['completed'] + $statusCounts['failed'];
        $revenue = $this->revenueBetween($from, $to);

        $avgOrderValue = $statusCounts['completed'] > 0
            ? $revenue / $statusCounts['completed']
            : 0.0;

        $totalProductsSold = (int) $this->scoped(SellingOrder::query(), $from, $to)
            ->whereIn('status', self::SELLING_COMPLETED)->sum('quantity');

        $totalTransactions = Payment::whereBetween('created_at', [$from, $to])->count() + $totalOrders;

        $totalActiveCustomers = User::where('status', 1)->count();

        // No visitor/analytics tracking exists in this schema yet, so
        // "conversion rate" is approximated as paying customers ÷ all
        // registered customers. Wire this to real traffic data (e.g. GA4)
        // once that's available for a true visitor-based rate.
        $totalCustomers = User::count();
        $payingCustomers = DB::table('payments')->where('status', 'success')->distinct('user_id')->count('user_id');
        $conversionRate = $totalCustomers > 0 ? round(($payingCustomers / $totalCustomers) * 100, 2) : 0.0;

        return [
            'avgOrderValue'        => $avgOrderValue,
            'totalProductsSold'    => $totalProductsSold,
            'totalTransactions'    => $totalTransactions,
            'totalActiveCustomers' => $totalActiveCustomers,
            'conversionRate'       => $conversionRate,
        ];
    }

    /* ----------------------------------------------------------------- *
     * Notifications
     * ----------------------------------------------------------------- */

    public function notifications(): array
    {
        $notifications = [];

        [$todayStart, $todayEnd] = [now()->startOfDay(), now()->endOfDay()];
        $statusCounts = $this->orderStatusCounts($todayStart, $todayEnd);

        if ($statusCounts['pending'] > 0) {
            $notifications[] = [
                'type'    => 'pending_orders',
                'level'   => 'warning',
                'count'   => $statusCounts['pending'],
                'label'   => 'Orders Awaiting Processing',
                'icon'    => 'la-clock',
                'message' => "{$statusCounts['pending']} order(s) awaiting processing.",
            ];
        }

        if ($statusCounts['failed'] > 0) {
            $notifications[] = [
                'type'    => 'failed_orders',
                'level'   => 'danger',
                'count'   => $statusCounts['failed'],
                'label'   => 'Failed / Cancelled Today',
                'icon'    => 'la-times-circle',
                'message' => "{$statusCounts['failed']} order(s) failed or were cancelled today.",
            ];
        }

        $lowStockCount = SellingProduct::where('status', 1)
            ->where('stock', '<=', self::LOW_STOCK_THRESHOLD)->count();
        if ($lowStockCount > 0) {
            $notifications[] = [
                'type'    => 'low_stock',
                'level'   => 'warning',
                'count'   => $lowStockCount,
                'label'   => 'Products Low On Stock',
                'icon'    => 'la-box-open',
                'message' => "{$lowStockCount} product(s) low on stock.",
            ];
        }

        $largeDeposits = Payment::where('status', 'success')
            ->where('amount', '>=', self::LARGE_DEPOSIT_THRESHOLD)
            ->whereBetween('created_at', [$todayStart, $todayEnd])->count();
        if ($largeDeposits > 0) {
            $notifications[] = [
                'type'    => 'large_deposit',
                'level'   => 'info',
                'count'   => $largeDeposits,
                'label'   => 'Large Deposits Today',
                'icon'    => 'la-hand-holding-usd',
                'message' => "{$largeDeposits} large deposit(s) today (over " . number_format(self::LARGE_DEPOSIT_THRESHOLD) . ').',
            ];
        }

        $largePurchases = $this->scoped(SmmOrder::query(), $todayStart, $todayEnd)->where('charge', '>=', self::LARGE_PURCHASE_THRESHOLD)->count()
            + $this->scoped(SellingOrder::query(), $todayStart, $todayEnd)->where('total_amount', '>=', self::LARGE_PURCHASE_THRESHOLD)->count()
            + $this->scoped(RentingOrder::query(), $todayStart, $todayEnd)->where('price', '>=', self::LARGE_PURCHASE_THRESHOLD)->count()
            + $this->scoped(NumberOrder::query(), $todayStart, $todayEnd)->where('price', '>=', self::LARGE_PURCHASE_THRESHOLD)->count();
        if ($largePurchases > 0) {
            $notifications[] = [
                'type'    => 'large_purchase',
                'level'   => 'info',
                'count'   => $largePurchases,
                'label'   => 'Large Purchases Today',
                'icon'    => 'la-shopping-cart',
                'message' => "{$largePurchases} large purchase(s) today (over " . number_format(self::LARGE_PURCHASE_THRESHOLD) . ').',
            ];
        }

        return $notifications;
    }

    /**
     * A cheap count for the header bell badge (shown on every admin page,
     * not just the dashboard) — deliberately just 2 fast queries rather
     * than the full notifications() computation above, since this runs
     * sitewide on every request.
     */
    public function alertCount(): int
    {
        $todayStart = now()->startOfDay();
        $todayEnd   = now()->endOfDay();

        $pending = $this->scoped(NumberOrder::query(), $todayStart, $todayEnd)->whereIn('status', self::NUMBER_PENDING)->count()
            + $this->scoped(RentingOrder::query(), $todayStart, $todayEnd)->whereIn('status', self::RENTING_PENDING)->count()
            + $this->scoped(SellingOrder::query(), $todayStart, $todayEnd)->whereIn('status', self::SELLING_PENDING)->count()
            + $this->scoped(SmmOrder::query(), $todayStart, $todayEnd)->whereIn('status', self::SMM_PENDING)->count();

        $lowStock = SellingProduct::where('status', 1)->where('stock', '<=', self::LOW_STOCK_THRESHOLD)->count();

        return $pending + ($lowStock > 0 ? 1 : 0);
    }

    /* ----------------------------------------------------------------- *
     * Internal helper
     * ----------------------------------------------------------------- */

    protected function scoped($query, ?Carbon $from, ?Carbon $to)
    {
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        return $query;
    }
}
