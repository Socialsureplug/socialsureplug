<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Jobs\SyncAccsbulkDescriptionsJob;
use App\Models\SellingAccount;
use App\Models\SellingApi;
use App\Models\SellingCategory;
use App\Models\SellingDescriptionSync;
use App\Models\SellingOrder;
use App\Models\SellingProduct;
use App\Models\SellingSubcategory;
use App\Models\Setting;
use App\Services\Selling\AccszoneService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SellingController extends Controller
{
    public function api(Request $request)
    {
        $data['title'] = 'API Settings';
        $query = SellingApi::query();

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('label', 'like', "%{$term}%")
                    ->orWhere('base_url', 'like', "%{$term}%");
            });
        }

        $data['apis'] = $query->orderBy('id')->paginate(10)->withQueryString();
        $data['search'] = $request->search ?? '';

        $balances = [];
        foreach ($data['apis'] as $api) {
            if (strtolower((string) $api->label) !== 'accsbulk') {
                $balances[$api->id] = '—';
                continue;
            }
            try {
                $balances[$api->id] = AccszoneService::fromSellingApi($api)->balance();
            } catch (\Throwable) {
                $balances[$api->id] = '—';
            }
        }
        $data['balances'] = $balances;

        return view('backend.selling.api', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'base_url' => 'required|url|max:512',
            'api_key' => 'required|string|max:512',
            'status' => 'required|in:0,1',
        ]);

        SellingApi::create($request->only(['name', 'label', 'base_url', 'api_key', 'status']));

        return redirect()->back()->with('success', 'Selling API added successfully');
    }

    public function update(Request $request, int $id)
    {
        $api = SellingApi::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'base_url' => 'required|url|max:512',
            'api_key' => 'required|string|max:512',
            'status' => 'required|in:0,1',
        ]);

        $api->update($request->only(['name', 'base_url', 'api_key', 'status']));

        return redirect()->back()->with('success', 'Selling API updated successfully');
    }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:selling_api,id',
            'status' => 'required|in:0,1',
        ]);

        $api = SellingApi::find($request->id);
        if (!$api) {
            return response()->json(['success' => false, 'message' => 'Not found']);
        }

        $api->status = (int) $request->status;
        $api->save();

        return response()->json(['success' => true, 'message' => 'Status updated successfully']);
    }

    public function categories(Request $request)
    {
        $data['title'] = 'Selling categories';
        $query = SellingCategory::query()->with('sellingApi')->orderBy('sort')->orderBy('id');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where('name', 'like', '%' . $term . '%');
        }

        $data['categories'] = $query->paginate(10)->withQueryString();
        $data['search'] = $request->search ?? '';
        $data['apis'] = SellingApi::query()->where('status', 1)->orderBy('id')->get();

        return view('backend.selling.categories', $data);
    }

    public function importCategories(Request $request)
    {
        $validated = $request->validate([
            'api_id' => 'required|integer|exists:selling_api,id',
        ]);

        $api = SellingApi::findOrFail($validated['api_id']);

        if (strtolower(trim((string) $api->label)) !== 'accsbulk') {
            return redirect()->back()->with('error', 'Category import is only supported for AccsBulk APIs.');
        }

        try {
            $rows = AccszoneService::fromSellingApi($api)->categories();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        $created = 0;
        $updated = 0;

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $remoteId = (int) ($row['id'] ?? 0);
            if ($remoteId === 0) {
                continue;
            }

            $name = (string) ($row['title'] ?? '');
            $category = SellingCategory::query()
                ->where('api_id', $api->id)
                ->where('category_id', $remoteId)
                ->first();

            if ($category) {
                $category->update(['name' => $name]);
                $updated++;
                continue;
            }

            SellingCategory::create([
                'api_id' => $api->id,
                'category_id' => $remoteId,
                'name' => $name,
                'slug' => (string) ($row['slug'] ?? Str::slug($name)),
                'sort' => 0,
            ]);
            $created++;
        }

        return redirect()->back()->with(
            'success',
            "Import finished: {$created} created, {$updated} updated."
        );
    }

    public function categoriesStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sort' => 'nullable|integer|min:0',
        ]);

        SellingCategory::create([
            'name' => $request->name,
            'sort' => $request->integer('sort', 0),
        ]);

        return redirect()->back()->with('success', 'Category added successfully');
    }

    public function categoriesUpdate(Request $request, int $id)
    {
        $category = SellingCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|min:0',
            'sort' => 'nullable|integer|min:0',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'category_id' => $request->filled('category_id') ? $request->integer('category_id') : null,
            'sort' => $request->integer('sort', 0),
        ]);

        return redirect()->back()->with('success', 'Category updated successfully');
    }

    public function categoriesDestroy(int $id)
    {
        SellingCategory::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Category deleted successfully');
    }

    public function subcategories(Request $request)
    {
        $data['title'] = 'Selling subcategories';
        $query = SellingSubcategory::query()
            ->with(['sellingCategory', 'sellingApi'])
            ->orderBy('selling_category_id')
            ->orderBy('sort')
            ->orderBy('id');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('selling_category_id')) {
            $query->where('selling_category_id', (int) $request->selling_category_id);
        }

        $data['subcategories'] = $query->paginate(15)->withQueryString();
        $data['search'] = $request->search ?? '';
        $data['categories'] = SellingCategory::query()->orderBy('sort')->orderBy('id')->get();
        $data['apis'] = SellingApi::query()->where('status', 1)->orderBy('id')->get();
        $data['selling_category_id'] = $request->input('selling_category_id');

        return view('backend.selling.subcategories', $data);
    }

    public function importSubcategories(Request $request)
    {
        $validated = $request->validate([
            'api_id' => 'required|integer|exists:selling_api,id',
        ]);

        $api = SellingApi::findOrFail($validated['api_id']);

        if (strtolower(trim((string) $api->label)) !== 'accsbulk') {
            return redirect()->back()->with('error', 'Subcategory import is only supported for AccsBulk APIs.');
        }

        $parents = SellingCategory::query()
            ->where('api_id', $api->id)
            ->whereNotNull('category_id')
            ->get();

        if ($parents->isEmpty()) {
            return redirect()->back()->with('error', 'Import categories for this API first.');
        }

        $service = AccszoneService::fromSellingApi($api);
        $created = 0;
        $updated = 0;

        foreach ($parents as $parent) {
            try {
                $rows = $service->subcategories((int) $parent->category_id);
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
            }

            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $remoteId = (int) ($row['id'] ?? 0);
                if ($remoteId === 0) {
                    continue;
                }

                $name = (string) ($row['title'] ?? '');
                $sub = SellingSubcategory::query()
                    ->where('api_id', $api->id)
                    ->where('subcategory_id', $remoteId)
                    ->first();

                if ($sub) {
                    $sub->update(['name' => $name]);
                    $updated++;
                    continue;
                }

                SellingSubcategory::create([
                    'selling_category_id' => $parent->id,
                    'api_id' => $api->id,
                    'subcategory_id' => $remoteId,
                    'name' => $name,
                    'slug' => (string) ($row['slug'] ?? Str::slug($name)),
                    'sort' => 0,
                ]);
                $created++;
            }
        }

        return redirect()->back()->with(
            'success',
            "Import finished: {$created} created, {$updated} updated."
        );
    }

    public function subcategoriesStore(Request $request)
    {
        $request->validate([
            'selling_category_id' => 'required|integer|exists:selling_categories,id',
            'name' => 'required|string|max:255',
            'sort' => 'nullable|integer|min:0',
        ]);

        SellingSubcategory::create([
            'selling_category_id' => $request->integer('selling_category_id'),
            'name' => $request->name,
            'sort' => $request->integer('sort', 0),
        ]);

        return redirect()->back()->with('success', 'Subcategory added successfully');
    }

    public function subcategoriesUpdate(Request $request, int $id)
    {
        $sub = SellingSubcategory::findOrFail($id);

        $request->validate([
            'selling_category_id' => 'required|integer|exists:selling_categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'subcategory_id' => 'nullable|integer|min:0',
            'sort' => 'nullable|integer|min:0',
        ]);

        $sub->update([
            'selling_category_id' => $request->integer('selling_category_id'),
            'name' => $request->name,
            'slug' => $request->slug,
            'subcategory_id' => $request->filled('subcategory_id') ? $request->integer('subcategory_id') : null,
            'sort' => $request->integer('sort', 0),
        ]);

        return redirect()->back()->with('success', 'Subcategory updated successfully');
    }

    public function subcategoriesDestroy(int $id)
    {
        SellingSubcategory::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Subcategory deleted successfully');
    }

    public function products(Request $request)
    {
        $data['title'] = 'All Products';
        $query = SellingProduct::query()->with(['sellingApi', 'sellingSubcategory.sellingCategory']);
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where('title', 'like', '%' . $term . '%');
        }

        if ($request->filled('category_id')) {
            $cid = (int) $request->input('category_id');
            if ($cid > 0) {
                $query->whereHas('sellingSubcategory', fn($q) => $q->where('selling_category_id', $cid));
            }
        }

        $data['products'] = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $data['search'] = $request->search ?? '';
        $data['category_id'] = $request->input('category_id');
        $data['apis'] = SellingApi::query()->where('status', 1)->orderBy('id')->get();
        $data['accsbulkApis'] = SellingApi::query()
            ->where('status', 1)
            ->whereRaw('LOWER(label) = ?', ['accsbulk'])
            ->orderBy('id')
            ->get();
        $data['categories'] = SellingCategory::query()->orderBy('sort')->orderBy('id')->get();
        $data['subcategories'] = SellingSubcategory::query()->with('sellingCategory')->orderBy('selling_category_id')->orderBy('sort')->get();
        $data['latestDescriptionSync'] = SellingDescriptionSync::query()->latest('id')->first();
        $data['activeDescriptionSync'] = SellingDescriptionSync::query()
            ->whereIn('status', ['pending', 'running'])
            ->latest('id')
            ->first();

        return view('backend.selling.products.index', $data);
    }

    public function syncDescriptions(Request $request)
    {
        $validated = $request->validate([
            'api_id' => 'required|integer|exists:selling_api,id',
        ]);

        $api = SellingApi::findOrFail($validated['api_id']);

        if (strtolower(trim((string) $api->label)) !== 'accsbulk') {
            return redirect()->back()->with('error', 'Description sync is only supported for AccsBulk APIs.');
        }

        $sync = SellingDescriptionSync::query()->create([
            'api_id' => $api->id,
            'status' => 'pending',
            'total_products' => 0,
            'processed_products' => 0,
            'success_count' => 0,
            'failed_count' => 0,
        ]);

        SyncAccsbulkDescriptionsJob::dispatch($sync->id);

        return redirect()->back()->with('success', 'Description sync started in background.');
    }

    public function syncDescriptionsStatus(int $syncId)
    {
        $sync = SellingDescriptionSync::query()->findOrFail($syncId);

        return response()->json([
            'success' => true,
            'sync' => [
                'id' => $sync->id,
                'status' => (string) $sync->status,
                'total_products' => (int) $sync->total_products,
                'processed_products' => (int) $sync->processed_products,
                'success_count' => (int) $sync->success_count,
                'failed_count' => (int) $sync->failed_count,
                'error_message' => $sync->error_message,
            ],
        ]);
    }

    public function importProducts(Request $request)
    {
        $validated = $request->validate([
            'api_id' => 'required|integer|exists:selling_api,id',
        ]);

        $api = SellingApi::findOrFail($validated['api_id']);

        if (strtolower(trim((string) $api->label)) !== 'accsbulk') {
            return redirect()->back()->with('error', 'Product import is only implemented for AccsBulk.');
        }

        try {
            $service = AccszoneService::fromSellingApi($api);
            $listings = $service->fetchAllListings();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        $setting = Setting::query()->first();
        $currencyRate = (float) ($setting->currency_rate ?? 1);
        if ($currencyRate <= 0) {
            $currencyRate = 1.0;
        }
        $priceIncreasePct = max(0.0, (float) ($setting->price_increase ?? 0));

        $created = 0;
        $updated = 0;

        foreach ($listings as $row) {
            if (!is_array($row)) {
                continue;
            }

            $providerId = (int) ($row['id'] ?? 0);
            if ($providerId === 0) {
                continue;
            }

            $remoteSubId = (int) data_get($row, 'subcategory.id', 0);
            $localSubId = null;
            if ($remoteSubId > 0) {
                $localSubId = SellingSubcategory::query()
                    ->where('api_id', $api->id)
                    ->where('subcategory_id', $remoteSubId)
                    ->value('id');
            }

            $product = SellingProduct::query()
                ->where('api_id', $api->id)
                ->where('provider_id', $providerId)
                ->first();

            $rawPrice = (float) ($row['price'] ?? 0);
            $providerPrice = $rawPrice;
            $price = round($rawPrice * $currencyRate * (1 + $priceIncreasePct / 100), 2);
            $stock = (int) ($row['available_stock'] ?? 0);
            $sold = (int) ($row['sold'] ?? 0);
            $title = (string) ($row['title'] ?? '');

            if ($product) {
                $updatePayload = [
                    'title' => $title,
                    'price' => $price,
                    'provider_price' => $providerPrice,
                    'stock' => $stock,
                    'sold' => $sold,
                ];
                if ($localSubId) {
                    $updatePayload['subcategory_id'] = $localSubId;
                }
                $product->update($updatePayload);
                $updated++;

                continue;
            }

            $slug = (string) ($row['slug'] ?? Str::slug($title));
            $baseSlug = $slug;
            $n = 0;
            while (SellingProduct::query()->where('slug', $slug)->exists()) {
                $n++;
                $slug = $baseSlug . '-' . $providerId . '-' . $n;
            }

            SellingProduct::create([
                'api_id' => $api->id,
                'provider_id' => $providerId,
                'title' => $title,
                'slug' => $slug,
                'description' => null,
                'provider_price' => $providerPrice,
                'price' => $price,
                'stock' => $stock,
                'sold' => $sold,
                'subcategory_id' => $localSubId,
                'status' => 1,
                'image' => null,
            ]);
            $created++;
        }

        return redirect()->back()->with(
            'success',
            "Import finished: {$created} created, {$updated} updated."
        );
    }

    public function createProduct()
    {
        $data['title'] = 'Create Product';
        $data['subcategories'] = SellingSubcategory::query()->with('sellingCategory')->orderBy('selling_category_id')->orderBy('sort')->get();

        return view('backend.selling.products.create', $data);
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:selling_product,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sold' => 'nullable|integer|min:0',
            'subcategory_id' => 'required|integer|exists:selling_subcategories,id',
            'status' => 'required|in:0,1',
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imagePath = 'assets/backend/images/selling-products/' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dest = base_path('../assets/backend/images/selling-products');
            if (!file_exists($dest)) {
                mkdir($dest, 0755, true);
            }
            $file->move($dest, basename($imagePath));
        }

        SellingProduct::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'provider_id' => null,
            'provider_price' => 0,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'sold' => $validated['sold'] ?? 0,
            'subcategory_id' => $validated['subcategory_id'],
            'api_id' => null,
            'status' => (int) $validated['status'],
            'image' => $imagePath,
        ]);

        return redirect()->route('backend.selling.products')->with('success', 'Product created successfully');
    }

    public function editProduct(int $id)
    {
        $product = SellingProduct::findOrFail($id);

        $data['title'] = 'Edit Product';
        $data['product'] = $product;
        $data['subcategories'] = SellingSubcategory::query()->with('sellingCategory')->orderBy('selling_category_id')->orderBy('sort')->get();

        return view('backend.selling.products.edit', $data);
    }

    public function productAccounts(Request $request, int $productId)
    {
        $product = SellingProduct::query()->findOrFail($productId);

        $query = SellingAccount::query()->where('product_id', $product->id);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where('name', 'like', '%' . $term . '%');
        }

        $data['title'] = 'Accounts — ' . $product->title;
        $data['product'] = $product;
        $data['accounts'] = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $data['search'] = $request->search ?? '';

        return view('backend.selling.accounts', $data);
    }

    public function storeSellingAccount(Request $request, int $productId)
    {
        $product = SellingProduct::query()->findOrFail($productId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'credentials' => 'required|string',
        ]);

        SellingAccount::create([
            'product_id' => $product->id,
            'name' => $validated['name'],
            'credentials' => $validated['credentials'],
            'is_sold' => $request->boolean('is_sold'),
        ]);

        return redirect()
            ->route('backend.selling.products.accounts', $product->id)
            ->with('success', 'Account added successfully');
    }

    public function updateProduct(Request $request, int $id)
    {
        $product = SellingProduct::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:512',
            'slug' => ['required', 'string', 'max:255', Rule::unique('selling_product', 'slug')->ignore($product->id)],
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sold' => 'nullable|integer|min:0',
            'subcategory_id' => 'required|integer|exists:selling_subcategories,id',
            'status' => 'required|in:0,1',
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $payload = [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'sold' => $validated['sold'] ?? 0,
            'subcategory_id' => $validated['subcategory_id'],
            'status' => (int) $validated['status'],
        ];

        if ($request->hasFile('image')) {
            if ($product->image && file_exists(base_path('../' . $product->image))) {
                unlink(base_path('../' . $product->image));
            }
            $file = $request->file('image');
            $payload['image'] = 'assets/backend/images/selling-products/' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dest = base_path('../assets/backend/images/selling-products');
            if (!file_exists($dest)) {
                mkdir($dest, 0755, true);
            }
            $file->move($dest, basename($payload['image']));
        }

        $product->update($payload);

        return redirect()->route('backend.selling.products')->with('success', 'Product updated successfully');
    }

    public function destroyProduct(int $id)
    {
        $product = SellingProduct::findOrFail($id);
        if ($product->image && file_exists(base_path('../' . $product->image))) {
            unlink(base_path('../' . $product->image));
        }
        $product->delete();

        return redirect()->route('backend.selling.products')->with('success', 'Product deleted successfully');
    }

    public function orders(Request $request)
    {
        $data['title'] = 'Selling orders';
        $query = SellingOrder::query()
            ->with(['sellingProduct', 'user'])
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $term = trim((string) $request->search);
            $query->where(function ($q) use ($term) {
                $q
                    ->where('id', 'like', '%' . $term . '%')
                    ->orWhereHas('sellingProduct', fn($p) => $p->where('title', 'like', '%' . $term . '%'))
                    ->orWhereHas('user', fn($u) => $u->where('email', 'like', '%' . $term . '%'));
            });
        }

        $data['orders'] = $query->paginate(20)->withQueryString();
        $data['search'] = $request->get('search', '');
        $data['currency'] = optional(Setting::query()->first())->website_currency ?? '';

        return view('backend.selling.orders', $data);
    }

    public function orderAccounts(int $order)
    {
        $orderModel = SellingOrder::query()
            ->with([
                'sellingProduct',
                'user',
                'sellingAccounts' => fn($q) => $q->orderBy('id'),
            ])
            ->findOrFail($order);

        $data['title'] = 'Order #' . $orderModel->id . ' — Accounts';
        $data['order'] = $orderModel;
        $data['currency'] = optional(Setting::query()->first())->website_currency ?? '';

        return view('backend.selling.order-accounts', $data);
    }

    public function importSellingAccounts(Request $request, int $productId)
    {
        $product = SellingProduct::findOrFail($productId);

        if ($product->api_id) {
            return back()->with('error', 'Cannot import accounts for API products.');
        }

        $request->validate([
            'file' => 'required|file|mimes:txt|max:2048',
        ]);

        $lines = file($request->file('file')->getRealPath(), FILE_IGNORE_NEW_LINES);
        $count = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = explode('|', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $username = trim($parts[0]);
            $password = trim($parts[1]);

            if ($username === '' || $password === '') {
                continue;
            }

            SellingAccount::create([
                'name' => $username,
                'product_id' => $product->id,
                'credentials' => $username . '|' . $password,
                'is_sold' => false,
            ]);

            $count++;
        }

        if ($count === 0) {
            return back()->with('error', 'No valid accounts found. Format: username|password');
        }

        $product->increment('stock', $count);

        return back()->with('success', $count . ' account(s) imported.');
    }

    public function updateSellingAccount(Request $request, int $productId, int $accountId)
    {
        $product = SellingProduct::findOrFail($productId);
        $account = SellingAccount::where('product_id', $product->id)->findOrFail($accountId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'credentials' => 'required|string',
            'is_sold' => 'required|in:0,1',
        ]);

        $wasSold = (bool) $account->is_sold;
        $isSold = (bool) $validated['is_sold'];

        $account->update([
            'name' => $validated['name'],
            'credentials' => $validated['credentials'],
            'is_sold' => $isSold,
        ]);

        if (!$wasSold && $isSold && $product->stock > 0) {
            $product->decrement('stock');
        }
        if ($wasSold && !$isSold) {
            $product->increment('stock');
        }

        return back()->with('success', 'Account updated successfully');
    }

    public function destroySellingAccount(int $productId, int $accountId)
    {
        $product = SellingProduct::findOrFail($productId);
        $account = SellingAccount::where('product_id', $product->id)->findOrFail($accountId);

        if (!$account->is_sold && $product->stock > 0) {
            $product->decrement('stock');
        }

        $account->delete();

        return back()->with('success', 'Account deleted successfully');
    }
}
