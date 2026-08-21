@extends('frontend.layout.dash')

@section('content')
    @include('frontend.components.accordion')

    <div class="card mp-20 product-detail-page">
        <a href="{{ route('user.selling.index') }}" class="product-detail-back">&larr; Back to products</a>

        <div class="product-detail-layout">
            <div class="product-detail-info">
                @if(!empty($product->image))
                    <div class="product-detail-image-wrap">
                        <img src="{{ asset($product->image) }}" alt="{{ $product->title }}">
                    </div>
                @else
                    <div class="product-detail-image-wrap product-detail-image-placeholder">
                        <i class="ti ti-shopping-bag"></i>
                    </div>
                @endif
                <h1 class="product-detail-title">{{ $product->title }}</h1>
                <p class="product-detail-category">
                    @if($product->sellingSubcategory)
                        {{ $product->sellingSubcategory->sellingCategory?->name }} - {{ $product->sellingSubcategory->name }}
                    @else
                        -
                    @endif
                </p>
                @if($product->description)
                    <div class="product-detail-description">{!! nl2br(e($product->description)) !!}</div>
                @endif
                <div class="product-detail-meta">
                    <span class="product-detail-price">{{ $currency }}{{ number_format((float) $product->price, 2) }} per account</span>
                    <span class="product-detail-stock">In stock: {{ $product->stock }}</span>
                </div>
            </div>

            <div class="product-detail-buy-wrap">
                <div class="product-detail-buy-card card">
                    <div class="card-body">
                        <h2 class="product-detail-buy-title">Buy now</h2>
                        @if($product->stock < 1)
                            <p class="product-detail-out-of-stock">Out of stock. Check back later.</p>
                        @else
                            <form id="selling-purchase-form" class="product-detail-buy-form">
                                @csrf
                                <input type="hidden" name="selling_product_id" value="{{ $product->id }}">
                                <div class="form-group">
                                    <label class="form-label" for="selling-qty">Quantity <span class="required">*</span></label>
                                    <input type="number" class="form-control" name="quantity" id="selling-qty" min="1" max="{{ $product->stock }}" value="1" required>
                                    <small class="text-muted">Max: {{ $product->stock }}</small>
                                </div>
                                <div class="product-detail-total-wrap">
                                    <span class="product-detail-total-label">Total</span>
                                    <span class="product-detail-total-amount" id="selling-total-amount">{{ $currency }}{{ number_format((float) $product->price, 2) }}</span>
                                </div>
                                <button type="submit" class="product-detail-purchase-btn" id="selling-btn-purchase">
                                    <i class="ti ti-shopping-cart"></i> Purchase
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($related->isNotEmpty())
        <div class="card mp-20 product-detail-related">
            <h2 class="product-detail-related-title">Related products</h2>
            <div class="product-detail-related-grid">
                @foreach($related as $r)
                    <a href="{{ route('user.selling.product', $r->id) }}" class="product-detail-related-card card">
                        @if(!empty($r->image))
                            <img src="{{ asset($r->image) }}" alt="{{ $r->title }}">
                        @else
                            <div class="product-detail-related-placeholder"><i class="ti ti-shopping-bag"></i></div>
                        @endif
                        <div class="card-body">
                            <h3 class="product-detail-related-name">{{ $r->title }}</h3>
                            <p class="product-detail-related-meta">{{ $currency }}{{ number_format((float) $r->price, 2) }} | Stock: {{ $r->stock }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
@endsection

@push('script')
<script>
(() => {
    const price = {{ (float) $product->price }};
    const currency = @json($currency);
    const purchaseUrl = @json(route('user.selling.purchase'));
    const afterPurchaseUrl = @json(route('user.selling.product', $product->id));

    const qtyEl = document.getElementById('selling-qty');
    const totalEl = document.getElementById('selling-total-amount');
    const updateTotal = () => {
        if (!qtyEl || !totalEl) return;
        const q = Math.max(1, Number.parseInt(qtyEl.value, 10) || 1);
        totalEl.textContent = `${currency}${(price * q).toFixed(2)}`;
    };
    qtyEl?.addEventListener('input', updateTotal);

    const form = document.getElementById('selling-purchase-form');
    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const btn = document.getElementById('selling-btn-purchase');
        if (!btn) return;

        btn.disabled = true;
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const response = await fetch(purchaseUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    Accept: 'application/json',
                },
                body: new FormData(form),
            });

            let data = {};
            try {
                data = await response.json();
            } catch {
                /* non-JSON body */
            }

            const toast = window.iziToast;
            if (!response.ok) {
                toast?.error?.({ message: data?.message ?? 'Request failed', position: 'topRight' });
                return;
            }
            if (data.status === 'success') {
                toast?.success?.({ message: data.message, position: 'topRight' });
                window.location.assign(afterPurchaseUrl);
                return;
            }
            toast?.error?.({ message: data?.message ?? 'Error', position: 'topRight' });
        } catch {
            window.iziToast?.error?.({ message: 'Request failed', position: 'topRight' });
        } finally {
            btn.disabled = false;
        }
    });
})();
</script>
@endpush
