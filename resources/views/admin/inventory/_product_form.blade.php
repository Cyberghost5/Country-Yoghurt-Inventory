<div class="inv-modal-body">
  <div class="form-grid two-cols">

    <label class="span-2">
      <span>Product Name <span class="inv-required">*</span></span>
      <input type="text" name="name"
             value="{{ old('name', $product->name ?? '') }}" required />
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
      <span>Price (₦) <span class="inv-required">*</span></span>
      <input type="number" name="selling_price" step="0.01" min="0"
             value="{{ old('selling_price', $product->selling_price ?? '') }}"
             placeholder="0.00" required />
    </label>

  </div>
</div>
