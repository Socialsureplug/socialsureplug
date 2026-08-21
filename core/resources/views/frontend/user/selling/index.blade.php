@extends('frontend.layout.dash')

@section('content')
    @include('frontend.components.accordion')

    <div class="card mp-20 social-logs-page">
        <div class="social-logs-header">
            <h2 class="title">{{ $title }}</h2>
            <p class="social-logs-desc">Browse and buy account credentials. Balance will be deducted on purchase.</p>
        </div>

        <div class="social-logs-search">
            <form id="selling-filter-form" method="GET" action="{{ route('user.selling.index') }}" class="table-search-form social-logs-search-form">
                <div class="table-search">
                    <input type="text" name="search" class="search-input" placeholder="Search by product name or category..." value="{{ $search ?? '' }}">
                    <i class="ti ti-search"></i>
                </div>
                <button type="submit" class="search-btn"><i class="ti ti-search"></i> Search</button>
                <select name="category_id" id="selling-category-filter" class="form-control social-logs-category-select" aria-label="Filter by category">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) ($category_id ?? '') === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <select name="subcategory_id" id="selling-subcategory-filter" class="form-control social-logs-category-select" aria-label="Filter by subcategory">
                    <option value="">Select category first</option>
                </select>
                <span>
                    <select name="price_sort" class="form-control social-logs-price-select" aria-label="Sort by price" onchange="this.form.submit()">
                        <option value="">Sort by Price</option>
                        <option value="asc" @selected(request('price_sort') === 'asc')>Price: Low to High</option>
                        <option value="desc" @selected(request('price_sort') === 'desc')>Price: High to Low</option>
                    </select>
                </span>
            </form>
        </div>

        <div class="products-catalog">
            @if($products->isNotEmpty())
                <div class="products-catalog-bar" aria-hidden="true">
                    <span class="products-catalog-bar-spacer"></span>
                    <span class="products-catalog-bar-product">Product</span>
                    <span class="products-catalog-bar-stock">In stock</span>
                    <span class="products-catalog-bar-price">Price</span>
                    <span class="products-catalog-bar-action"></span>
                </div>
            @endif

            @forelse($products as $p)
                <a href="{{ route('user.selling.product', $p->id) }}" class="products-catalog-row">
                    <div class="products-catalog-thumb">
                        @if($p->image)
                            <img src="{{ asset($p->image) }}" alt="{{ $p->title }}">
                        @else
                            <i class="ti ti-shopping-bag" aria-hidden="true"></i>
                        @endif
                    </div>
                    <div class="products-catalog-main">
                        <h3 class="products-catalog-title">{{ $p->title }}</h3>
                        <p class="products-catalog-category">
                            @if($p->sellingSubcategory)
                                {{ $p->sellingSubcategory->sellingCategory?->name }} - {{ $p->sellingSubcategory->name }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div class="products-catalog-stock js-product-stock"
                        data-product-id="{{ $p->id }}"
                        data-is-accszone="{{ $p->isAccsbulk() ? '1' : '0' }}"
                    >{{ $p->stock }} pcs.</div>
                    <div class="products-catalog-price-col">
                        <span class="products-catalog-price-label">Price per pc</span>
                        <span class="products-catalog-price-value">from {{ $currency }}{{ number_format((float) $p->price, 2) }}</span>
                    </div>
                    <span class="products-catalog-buy btn btn-primary"><i class="ti ti-shopping-cart" aria-hidden="true"></i> Buy</span>
                </a>
            @empty
                <div class="social-logs-empty products-catalog-empty">
                    <i class="ti ti-package-off"></i>
                    <p>No products available.</p>
                </div>
            @endforelse
        </div>

        @if($products->hasPages())
            <div class="social-logs-pagination">{{ $products->links('vendor.pagination.default') }}</div>
        @endif
    </div>
@endsection

@push('script')
<script>
(() => {
    const form = document.getElementById('selling-filter-form');
    const categorySelect = document.getElementById('selling-category-filter');
    const subcategorySelect = document.getElementById('selling-subcategory-filter');
    const subcategories = @json($subcategoriesForJs);
    const selectedSubcategoryId = @json($subcategory_id ?? '');

    if (!form || !categorySelect || !subcategorySelect) {
        return;
    }

    const fillSubcategories = (categoryId, selectedId = '') => {
        subcategorySelect.replaceChildren();

        const placeholder = new Option(
            categoryId ? 'All subcategories' : 'Select category first',
            ''
        );
        subcategorySelect.add(placeholder);

        if (!categoryId) {
            return;
        }

        const parentId = Number(categoryId);

        for (const sub of subcategories) {
            if (sub.categoryId !== parentId) {
                continue;
            }

            const option = new Option(sub.label, String(sub.id));
            if (String(sub.id) === String(selectedId)) {
                option.selected = true;
            }
            subcategorySelect.add(option);
        }
    };

    categorySelect.addEventListener('change', () => {
        fillSubcategories(categorySelect.value);
        if (!categorySelect.value) {
            form.submit();
        }
    });

    subcategorySelect.addEventListener('change', () => {
        form.submit();
    });

    fillSubcategories(categorySelect.value, selectedSubcategoryId);
})();
</script>
@endpush
