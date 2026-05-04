<div class="inv-modal-body">
  <div class="form-grid two-cols">

    <label class="{{ isset($edit) ? '' : '' }}">
      <span>Product Name <span class="inv-required">*</span></span>
      <input type="text" name="name"
             value="{{ old('name', $product->name ?? '') }}" required />
    </label>

    <label>
      SKU <span class="inv-hint-text">(auto-generated if blank)</span>
      <input type="text" name="sku"
             value="{{ old('sku', $product->sku ?? '') }}"
             placeholder="e.g. YOG-STRW-A1B2" />
    </label>

    <label>
      <span>Category <span class="inv-required">*</span></span>
      <select name="category" required>
        <option value="yoghurt"     {{ old('category', $product->category ?? 'yoghurt') === 'yoghurt'     ? 'selected' : '' }}>Yoghurt</option>
        <option value="accessories" {{ old('category', $product->category ?? '') === 'accessories' ? 'selected' : '' }}>Accessories</option>
        <option value="packaging"   {{ old('category', $product->category ?? '') === 'packaging'   ? 'selected' : '' }}>Packaging</option>
        <option value="others"      {{ old('category', $product->category ?? '') === 'others'      ? 'selected' : '' }}>Others</option>
      </select>
    </label>

    <label>
      Flavor
      <input type="text" name="flavor"
             value="{{ old('flavor', $product->flavor ?? '') }}"
             placeholder="e.g. Strawberry, Plain, Mango" />
    </label>

    <label>
      Size / Volume
      <input type="text" name="size_label"
             value="{{ old('size_label', $product->size_label ?? '') }}"
             placeholder="e.g. 200ml, 1L, 500g" />
    </label>

    <label>
      <span>Unit <span class="inv-required">*</span></span>
      <select name="unit" required>
        <option value="carton" {{ old('unit', $product->unit ?? 'carton') === 'carton' ? 'selected' : '' }}>Carton</option>
        <option value="pack"   {{ old('unit', $product->unit ?? '') === 'pack'   ? 'selected' : '' }}>Pack</option>
        <option value="piece"  {{ old('unit', $product->unit ?? '') === 'piece'  ? 'selected' : '' }}>Piece</option>
        <option value="litre"  {{ old('unit', $product->unit ?? '') === 'litre'  ? 'selected' : '' }}>Litre</option>
      </select>
    </label>

    <label>
      <span>Cost Price (₦) <span class="inv-required">*</span></span>
      <input type="number" name="cost_price" step="0.01" min="0"
             value="{{ old('cost_price', $product->cost_price ?? '') }}"
             placeholder="0.00" required />
    </label>

    <label>
      <span>Selling Price (₦) <span class="inv-required">*</span></span>
      <input type="number" name="selling_price" step="0.01" min="0"
             value="{{ old('selling_price', $product->selling_price ?? '') }}"
             placeholder="0.00" required />
    </label>

    <label>
      <span>Quantity in Stock <span class="inv-required">*</span></span>
      <input type="number" name="quantity" min="0"
             value="{{ old('quantity', $product->quantity ?? 0) }}" required />
    </label>

    <label>
      <span>Reorder Level <span class="inv-required">*</span></span>
      <input type="number" name="reorder_level" min="0"
             value="{{ old('reorder_level', $product->reorder_level ?? 10) }}" required />
    </label>

    <label class="span-2">
      Supplier Name
      <input type="text" name="supplier_name"
             value="{{ old('supplier_name', $product->supplier_name ?? 'Country Yoghurt Ltd') }}"
             placeholder="e.g. Bauchi Dairy Ltd" />
    </label>

    <label class="span-2">
      Notes
      <textarea name="notes" rows="2"
                placeholder="Optional product notes">{{ old('notes', $product->notes ?? '') }}</textarea>
    </label>

  </div>
</div>
