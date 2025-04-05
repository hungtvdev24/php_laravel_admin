@extends('layouts.admin')

@section('title', 'Thêm Sản phẩm')

@section('content')
    <div class="container-fluid py-4">
        <h1 class="h3 mb-4 text-warm-brown">Thêm Sản phẩm</h1>

        <!-- Hiển thị lỗi validation nếu có -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Hiển thị thông báo thành công nếu có -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form id="productForm" action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Danh mục -->
            <div class="mb-3">
                <label for="id_danhMuc" class="form-label">Danh mục</label>
                <select name="id_danhMuc" id="id_danhMuc" class="form-control @error('id_danhMuc') is-invalid @enderror" required>
                    @foreach($categories as $category)
                        <option value="{{ $category->id_danhMuc }}" {{ old('id_danhMuc') == $category->id_danhMuc ? 'selected' : '' }}>{{ $category->tenDanhMuc }}</option>
                    @endforeach
                </select>
                @error('id_danhMuc')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Tên sản phẩm -->
            <div class="mb-3">
                <label for="tenSanPham" class="form-label">Tên sản phẩm</label>
                <input type="text" name="tenSanPham" id="tenSanPham" class="form-control @error('tenSanPham') is-invalid @enderror" value="{{ old('tenSanPham') }}" required>
                @error('tenSanPham')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Thương hiệu -->
            <div class="mb-3">
                <label for="thuongHieu" class="form-label">Thương hiệu</label>
                <input type="text" name="thuongHieu" id="thuongHieu" class="form-control @error('thuongHieu') is-invalid @enderror" value="{{ old('thuongHieu') }}" required>
                @error('thuongHieu')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Mô tả -->
            <div class="mb-3">
                <label for="moTa" class="form-label">Mô tả</label>
                <textarea name="moTa" id="moTa" class="form-control @error('moTa') is-invalid @enderror" required>{{ old('moTa') }}</textarea>
                @error('moTa')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Trạng thái -->
            <div class="mb-3">
                <label for="trangThai" class="form-label">Trạng thái</label>
                <select name="trangThai" id="trangThai" class="form-control @error('trangThai') is-invalid @enderror" required>
                    <option value="active" {{ old('trangThai', 'active') == 'active' ? 'selected' : '' }}>Kích hoạt</option>
                    <option value="inactive" {{ old('trangThai') == 'inactive' ? 'selected' : '' }}>Không kích hoạt</option>
                </select>
                @error('trangThai')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Ẩn trường Giá sản phẩm (mặc định) -->
            <!--
            <div class="mb-3">
                <label for="gia" class="form-label">Giá sản phẩm (mặc định)</label>
                <input type="number" name="gia" id="gia" class="form-control @error('gia') is-invalid @enderror" step="0.01" min="0" max="99999999.99" value="{{ old('gia') }}">
                @error('gia')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            -->

            <!-- Biến thể -->
            <div class="mb-3">
                <label class="form-label">Biến thể (bắt buộc)</label>
                <div class="mb-3 input-group">
                    <label for="commonPrice" class="form-label me-2">Giá chung</label>
                    <input type="number" id="commonPrice" class="form-control" step="0.01" min="0" max="99999999.99" value="{{ old('commonPrice') }}" style="max-width: 150px;">
                    <button type="button" id="applyCommonPrice" class="btn btn-primary ms-2">Áp dụng</button>
                </div>
                <div id="variations-container">
                    <!-- Biến thể mặc định -->
                    <div class="variation-row mb-3 border p-3" data-index="0">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Màu</label>
                                <input type="text" name="variations[0][color]" class="form-control @error('variations.0.color') is-invalid @enderror" value="{{ old('variations.0.color') }}" required>
                                @error('variations.0.color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-9">
                                <label>Size và Tồn kho</label>
                                <table class="table table-bordered size-stock-table">
                                    <thead>
                                        <tr>
                                            <th>Chọn</th>
                                            <th>Size</th>
                                            <th>Tồn kho</th>
                                            <th>Giá</th>
                                        </tr>
                                    </thead>
                                    <tbody class="size-stock-tbody">
                                        @php
                                            $defaultSizes = ['S', 'M', 'L', 'XL'];
                                            $oldSizes = old('variations.0.sizes', []);
                                            $oldStocks = old('variations.0.stocks', []);
                                            $oldPrices = old('variations.0.prices', []);
                                            $customSizes = array_diff($oldSizes, $defaultSizes);
                                            $sizesToShow = !empty($oldSizes) ? array_unique(array_merge($defaultSizes, $customSizes)) : $defaultSizes;
                                        @endphp
                                        @foreach($sizesToShow as $index => $size)
                                            <tr class="size-stock-row">
                                                <td>
                                                    <input type="checkbox" name="variations[0][sizes][]" value="{{ $size }}" class="form-check-input size-checkbox" {{ in_array($size, $oldSizes) ? 'checked' : '' }}>
                                                </td>
                                                <td>{{ $size }}</td>
                                                <td>
                                                    <input type="number" name="variations[0][stocks][]" class="form-control stock-input" value="{{ $oldStocks[$index] ?? '' }}" min="0" {{ in_array($size, $oldSizes) ? 'required' : 'disabled' }}>
                                                </td>
                                                <td>
                                                    <input type="number" name="variations[0][prices][]" class="form-control price-input" step="0.01" value="{{ $oldPrices[$index] ?? '' }}" min="0" max="99999999.99" {{ in_array($size, $oldSizes) ? 'required' : 'disabled' }}>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-sm btn-info add-custom-size" data-index="0">Thêm size tùy chỉnh</button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label>Hình ảnh biến thể (chọn nhiều ảnh)</label>
                            <input type="file" name="variations[0][images][]" class="form-control @error('variations.0.images') is-invalid @enderror" multiple accept="image/*" required>
                            @error('variations.0.images')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Các biến thể khác (nếu có) -->
                    @if(old('variations'))
                        @foreach(array_slice(old('variations'), 1) as $index => $variation)
                            <div class="variation-row mb-3 border p-3" data-index="{{ $index + 1 }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Màu</label>
                                        <input type="text" name="variations[{{ $index + 1 }}][color]" class="form-control @error('variations.' . ($index + 1) . '.color') is-invalid @enderror" value="{{ $variation['color'] }}" required>
                                        @error('variations.' . ($index + 1) . '.color')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-9">
                                        <label>Size và Tồn kho</label>
                                        <table class="table table-bordered size-stock-table">
                                            <thead>
                                                <tr>
                                                    <th>Chọn</th>
                                                    <th>Size</th>
                                                    <th>Tồn kho</th>
                                                    <th>Giá</th>
                                                </tr>
                                            </thead>
                                            <tbody class="size-stock-tbody">
                                                @php
                                                    $defaultSizes = ['S', 'M', 'L', 'XL'];
                                                    $oldSizes = $variation['sizes'] ?? [];
                                                    $oldStocks = $variation['stocks'] ?? [];
                                                    $oldPrices = $variation['prices'] ?? [];
                                                    $customSizes = array_diff($oldSizes, $defaultSizes);
                                                    $sizesToShow = !empty($oldSizes) ? array_unique(array_merge($defaultSizes, $customSizes)) : $defaultSizes;
                                                @endphp
                                                @foreach($sizesToShow as $subIndex => $size)
                                                    <tr class="size-stock-row">
                                                        <td>
                                                            <input type="checkbox" name="variations[{{ $index + 1 }}][sizes][]" value="{{ $size }}" class="form-check-input size-checkbox" {{ in_array($size, $oldSizes) ? 'checked' : '' }}>
                                                        </td>
                                                        <td>{{ $size }}</td>
                                                        <td>
                                                            <input type="number" name="variations[{{ $index + 1 }}][stocks][]" class="form-control stock-input" value="{{ $oldStocks[$subIndex] ?? '' }}" min="0" {{ in_array($size, $oldSizes) ? 'required' : 'disabled' }}>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="variations[{{ $index + 1 }}][prices][]" class="form-control price-input" step="0.01" value="{{ $oldPrices[$subIndex] ?? '' }}" min="0" max="99999999.99" {{ in_array($size, $oldSizes) ? 'required' : 'disabled' }}>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <button type="button" class="btn btn-sm btn-info add-custom-size" data-index="{{ $index + 1 }}">Thêm size tùy chỉnh</button>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label>Hình ảnh biến thể (chọn nhiều ảnh)</label>
                                    <input type="file" name="variations[{{ $index + 1 }}][images][]" class="form-control @error('variations.' . ($index + 1) . '.images') is-invalid @enderror" multiple accept="image/*" required>
                                    @error('variations.' . ($index + 1) . '.images')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="button" class="btn btn-danger btn-sm mt-2 remove-variation">Xóa biến thể</button>
                            </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" id="add-variation" class="btn btn-primary mt-2">Thêm biến thể</button>
            </div>

            <button type="submit" class="btn btn-warm-orange shadow-sm">Lưu</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary shadow-sm">Hủy</a>
        </form>

        <!-- Dialog thêm size tùy chỉnh -->
        <div class="modal fade" id="customSizeModal" tabindex="-1" aria-labelledby="customSizeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="customSizeModalLabel">Thêm size tùy chỉnh</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" id="customSizeInput" class="form-control" placeholder="Nhập size mới">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-primary" id="saveCustomSize">Lưu</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Debug: Log toàn bộ dữ liệu form khi submit
        document.getElementById('productForm').addEventListener('submit', function(e) {
            console.log('Submitting product form...');
            let formData = new FormData(this);
            for (let pair of formData.entries()) {
                console.log(pair[0] + ':', pair[1]);
            }
        });

        let variationIndex = {{ old('variations') ? count(old('variations')) : 1 }};
        let currentIndex = 0;

        document.getElementById('add-variation').addEventListener('click', function() {
            const container = document.getElementById('variations-container');
            const html = `
                <div class="variation-row mb-3 border p-3" data-index="${variationIndex}">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Màu</label>
                            <input type="text" name="variations[${variationIndex}][color]" class="form-control" required>
                        </div>
                        <div class="col-md-9">
                            <label>Size và Tồn kho</label>
                            <table class="table table-bordered size-stock-table">
                                <thead>
                                    <tr>
                                        <th>Chọn</th>
                                        <th>Size</th>
                                        <th>Tồn kho</th>
                                        <th>Giá</th>
                                    </tr>
                                </thead>
                                <tbody class="size-stock-tbody">
                                    <tr class="size-stock-row">
                                        <td><input type="checkbox" name="variations[${variationIndex}][sizes][]" value="S" class="form-check-input size-checkbox"></td>
                                        <td>S</td>
                                        <td><input type="number" name="variations[${variationIndex}][stocks][]" class="form-control stock-input" min="0" disabled></td>
                                        <td><input type="number" name="variations[${variationIndex}][prices][]" class="form-control price-input" step="0.01" min="0" max="99999999.99" disabled></td>
                                    </tr>
                                    <tr class="size-stock-row">
                                        <td><input type="checkbox" name="variations[${variationIndex}][sizes][]" value="M" class="form-check-input size-checkbox"></td>
                                        <td>M</td>
                                        <td><input type="number" name="variations[${variationIndex}][stocks][]" class="form-control stock-input" min="0" disabled></td>
                                        <td><input type="number" name="variations[${variationIndex}][prices][]" class="form-control price-input" step="0.01" min="0" max="99999999.99" disabled></td>
                                    </tr>
                                    <tr class="size-stock-row">
                                        <td><input type="checkbox" name="variations[${variationIndex}][sizes][]" value="L" class="form-check-input size-checkbox"></td>
                                        <td>L</td>
                                        <td><input type="number" name="variations[${variationIndex}][stocks][]" class="form-control stock-input" min="0" disabled></td>
                                        <td><input type="number" name="variations[${variationIndex}][prices][]" class="form-control price-input" step="0.01" min="0" max="99999999.99" disabled></td>
                                    </tr>
                                    <tr class="size-stock-row">
                                        <td><input type="checkbox" name="variations[${variationIndex}][sizes][]" value="XL" class="form-check-input size-checkbox"></td>
                                        <td>XL</td>
                                        <td><input type="number" name="variations[${variationIndex}][stocks][]" class="form-control stock-input" min="0" disabled></td>
                                        <td><input type="number" name="variations[${variationIndex}][prices][]" class="form-control price-input" step="0.01" min="0" max="99999999.99" disabled></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-sm btn-info add-custom-size" data-index="${variationIndex}">Thêm size tùy chỉnh</button>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label>Hình ảnh biến thể (chọn nhiều ảnh)</label>
                        <input type="file" name="variations[${variationIndex}][images][]" class="form-control" multiple accept="image/*" required>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm mt-2 remove-variation">Xóa biến thể</button>
                </div>`;
            container.insertAdjacentHTML('beforeend', html);
            attachCheckboxListeners(container.querySelector(`.variation-row[data-index="${variationIndex}"]`));
            variationIndex++;
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-variation')) {
                e.target.closest('.variation-row').remove();
            }
            if (e.target.classList.contains('add-custom-size')) {
                currentIndex = e.target.getAttribute('data-index');
                $('#customSizeModal').modal('show');
            }
        });

        document.getElementById('saveCustomSize').addEventListener('click', function() {
            const customSize = document.getElementById('customSizeInput').value.trim();
            if (customSize) {
                const container = document.querySelector(`.variation-row[data-index="${currentIndex}"] .size-stock-tbody`);
                const html = `
                    <tr class="size-stock-row">
                        <td>
                            <input type="checkbox" name="variations[${currentIndex}][sizes][]" value="${customSize}" class="form-check-input size-checkbox" checked>
                        </td>
                        <td>${customSize}</td>
                        <td>
                            <input type="number" name="variations[${currentIndex}][stocks][]" class="form-control stock-input" min="0" required>
                        </td>
                        <td>
                            <input type="number" name="variations[${currentIndex}][prices][]" class="form-control price-input" step="0.01" min="0" max="99999999.99" required>
                        </td>
                    </tr>`;
                container.insertAdjacentHTML('beforeend', html);
                attachCheckboxListeners(container.querySelector(`tr:last-child`));
                document.getElementById('customSizeInput').value = '';
                $('#customSizeModal').modal('hide');
            }
        });

        document.getElementById('applyCommonPrice').addEventListener('click', function() {
            const commonPrice = document.getElementById('commonPrice').value;
            if (commonPrice) {
                const priceInputs = document.querySelectorAll('.price-input');
                priceInputs.forEach(input => {
                    if (!input.disabled && (!input.value || input.value === '')) {
                        input.value = commonPrice;
                    }
                });
            }
        });

        function attachCheckboxListeners(container) {
            const checkboxes = container.querySelectorAll('.size-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const row = this.closest('.size-stock-row');
                    const stockInput = row.querySelector('.stock-input');
                    const priceInput = row.querySelector('.price-input');

                    if (this.checked) {
                        stockInput.disabled = false;
                        stockInput.required = true;
                        priceInput.disabled = false;
                        priceInput.required = true;
                    } else {
                        stockInput.disabled = true;
                        stockInput.required = false;
                        priceInput.disabled = true;
                        priceInput.required = false;
                        stockInput.value = '';
                        priceInput.value = '';
                    }
                });
            });
        }

        document.querySelectorAll('.variation-row').forEach(container => {
            attachCheckboxListeners(container);
        });
    </script>
@endsection