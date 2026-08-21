@extends('backend.layout.app')

@section('content')
    <div class="container">
        <div class="container-header">
            <h1>{{ $title }}</h1>
            <a href="{{ route('backend.selling.products') }}" class="btn btn-primary">Back</a>
        </div>

        <div class="card product-card">
            <form action="{{ route('backend.selling.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row selling-product-image-row mb-25">
                    <div class="width-30">
                        <div class="selling-product-image">
                            <span class="selling-product-image-label">Product image</span>
                            <div class="selling-product-image-frame">
                                <img class="selling-product-image-preview" id="selling_product_preview_create"
                                    src="https://propertynir.softnir.com/white/asset/images/placeholder.png" alt="">
                            </div>
                            <div class="selling-product-image-actions">
                                <input type="file" name="image" id="selling_product_file_create" accept="image/*" hidden>
                                <button type="button" class="btn btn-primary selling-product-image-upload-btn" id="selling_product_upload_btn_create">
                                    <i class="las la-upload"></i>Upload image
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="width-70">
                        <div class="form-grid mb-25">
                            <div class="form-group">
                                <label class="form-label">Title<span class="required">*</span></label>
                                <input type="text" class="form-control" name="title" value="{{ old('title') }}" placeholder="Product title" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Slug<span class="required">*</span></label>
                                <input type="text" class="form-control" name="slug" value="{{ old('slug') }}" placeholder="unique-url-slug" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Subcategory<span class="required">*</span></label>
                                <select name="subcategory_id" class="form-control" required>
                                    <option value="">Select subcategory</option>
                                    @foreach ($subcategories as $sub)
                                        <option value="{{ $sub->id }}" @selected(old('subcategory_id') == $sub->id)>
                                            {{ $sub->sellingCategory?->name }} › {{ $sub->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Status<span class="required">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="1" @selected(old('status', '1') == '1')>Active</option>
                                    <option value="0" @selected(old('status', '1') == '0')>Inactive</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Price<span class="required">*</span></label>
                                <input type="text" class="form-control" name="price" value="{{ old('price') }}" placeholder="0.00" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Stock<span class="required">*</span></label>
                                <input type="number" class="form-control" name="stock" value="{{ old('stock', 0) }}" min="0" step="1" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Sold</label>
                                <input type="number" class="form-control" name="sold" value="{{ old('sold', 0) }}" min="0" step="1">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-25">
                    <label class="form-label">Description</label>
                    <textarea class="textarea" name="description" rows="4" placeholder="Optional">{{ old('description') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">Create product</button>
            </form>
        </div>
    </div>
@endsection

@push('script')
<script>
(function () {
    const btn = document.getElementById('selling_product_upload_btn_create');
    const input = document.getElementById('selling_product_file_create');
    const preview = document.getElementById('selling_product_preview_create');

    if (btn && input) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            input.click();
        });
    }

    if (input && preview) {
        input.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                if (typeof iziToast !== 'undefined') {
                    iziToast.error({ message: 'Please upload an image', position: 'topRight' });
                }
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                if (typeof iziToast !== 'undefined') {
                    iziToast.error({ message: 'File size must be less than 2MB', position: 'topRight' });
                }
                return;
            }
            const reader = new FileReader();
            reader.onload = function (ev) {
                preview.src = ev.target.result;
                preview.alt = 'Product image preview';
            };
            reader.readAsDataURL(file);
        });
    }
})();
</script>
@endpush
