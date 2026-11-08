<form action="{{ $action }}" method="POST" class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
    @csrf
    @if ($method !== 'POST')@method($method)@endif
    @if ($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><p class="font-semibold">Revisa los datos ingresados:</p><ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="grid gap-5 md:grid-cols-2">
        <label class="text-sm font-medium text-zinc-700">Nombre de la prenda *
            <input name="name" required maxlength="180" value="{{ old('name', $product?->name) }}" class="mt-1.5 block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30">
        </label>
        <label class="text-sm font-medium text-zinc-700">Categoría *
            <select name="category_id" required class="mt-1.5 block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30"><option value="">Selecciona una categoría</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id) == $category->id)>{{ $category->name }}{{ $category->is_active ? '' : ' (inactiva)' }}</option>@endforeach</select>
        </label>
        <label class="text-sm font-medium text-zinc-700">Talla
            <input name="size" maxlength="40" value="{{ old('size', $product?->size) }}" placeholder="Ej. S, M, 8" class="mt-1.5 block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30">
        </label>
        <label class="text-sm font-medium text-zinc-700">Color
            <input name="color" maxlength="80" value="{{ old('color', $product?->color) }}" placeholder="Ej. Negro" class="mt-1.5 block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30">
        </label>
        <label class="text-sm font-medium text-zinc-700">Precio de compra ({{ $currencySymbol }}) *
            <input type="number" step="0.01" min="0" name="purchase_price" required value="{{ old('purchase_price', $product?->purchase_price) }}" class="mt-1.5 block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30">
        </label>
        <label class="text-sm font-medium text-zinc-700">Precio de venta ({{ $currencySymbol }}) *
            <input type="number" step="0.01" min="0.01" name="sale_price" required value="{{ old('sale_price', $product?->sale_price) }}" class="mt-1.5 block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30">
        </label>
        @if (!$product)
            <label class="text-sm font-medium text-zinc-700">Existencias iniciales *
                <input type="number" min="0" max="1000000" name="stock" required value="{{ old('stock', 0) }}" class="mt-1.5 block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30">
            </label>
        @else
            <div class="rounded-xl bg-zinc-50 px-4 py-3 text-sm text-zinc-600"><span class="font-medium text-zinc-800">Existencias actuales:</span> {{ $product->stock }}<p class="mt-1 text-xs">Utiliza Movimientos para ajustar el inventario.</p></div>
        @endif
        <label class="text-sm font-medium text-zinc-700">Fecha de ingreso
            <input type="date" name="received_at" value="{{ old('received_at', $product?->received_at?->format('Y-m-d') ?? today()->toDateString()) }}" class="mt-1.5 block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30">
        </label>
        @if ($product)
            <label class="flex items-center gap-3 self-end rounded-xl bg-zinc-50 p-4 text-sm text-zinc-700"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active)) class="size-4 rounded border-zinc-300 text-amber-700 focus:ring-amber-500"> Prenda activa y disponible para venta</label>
        @endif
        <label class="text-sm font-medium text-zinc-700 md:col-span-2">Descripción
            <textarea name="description" rows="3" maxlength="2000" class="mt-1.5 block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30">{{ old('description', $product?->description) }}</textarea>
        </label>
    </div>
    <div class="flex justify-end gap-3 border-t border-zinc-100 pt-5">
        <a href="{{ route('products.index') }}" class="rounded-xl border border-zinc-200 px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Cancelar</a>
        <button class="rounded-xl bg-zinc-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-zinc-700">{{ $product ? 'Guardar cambios' : 'Guardar prenda' }}</button>
    </div>
</form>
