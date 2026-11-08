<x-layouts.app :title="__('Registrar venta')">
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div><a href="{{ route('sales.index') }}" class="text-sm font-medium text-amber-800 hover:underline">← Historial de ventas</a><h1 class="mt-3 text-3xl font-semibold tracking-tight text-zinc-900">Registrar venta</h1><p class="mt-1 text-sm text-zinc-500">Busca prendas, agrega cantidades y confirma el cobro.</p></div>
        @if ($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><p class="font-semibold">No se pudo completar la venta:</p><ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <form action="{{ route('sales.store') }}" method="POST" id="sale-form" class="space-y-6">
            @csrf
            <section class="space-y-5">
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                    <label for="product-search" class="text-sm font-semibold text-zinc-800">Buscar prenda por nombre</label>
                    <div class="relative mt-2">
                        <input id="product-search" type="search" autocomplete="off" placeholder="Escribe al menos 2 caracteres..." class="block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 shadow-sm outline-none transition placeholder:text-zinc-400 focus:border-black-500 focus:ring-">
                        <div id="product-results" class="absolute z-20 mt-2 hidden max-h-80 w-full overflow-y-auto rounded-xl border border-zinc-200 bg-white shadow-xl"></div>
                    </div>
                    <p class="mt-2 text-xs text-zinc-500">Solo se muestran prendas activas con existencias disponibles.</p>
                </div>

                <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                    <div class="border-b border-zinc-100 px-5 py-4"><h2 class="font-semibold text-zinc-900">Prendas de esta venta</h2><p class="mt-1 text-xs text-zinc-500">El costo de compra es información interna y no forma parte del cobro.</p></div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500"><tr><th class="px-4 py-3">Producto</th><th class="px-4 py-3">Precio / costo</th><th class="px-4 py-3">Cantidad</th><th class="px-4 py-3">Descuento/u.</th><th class="px-4 py-3">Total</th><th class="px-4 py-3"></th></tr></thead>
                            <tbody id="sale-items" class="divide-y divide-zinc-100"><tr id="empty-row"><td colspan="6" class="px-4 py-12 text-center text-sm text-zinc-500">Busca y agrega una prenda para comenzar.</td></tr></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <div class="border-b border-zinc-100 bg-zinc-50 px-6 py-4"><h2 class="font-semibold text-zinc-900">Resumen del cobro</h2><p class="mt-0.5 text-xs text-zinc-500">Revisa los montos y elige cómo pagó el cliente.</p></div>
                <div class="grid lg:grid-cols-2">
                    <div class="space-y-4 p-6 lg:border-r lg:border-zinc-100">
                        <label class="block text-sm font-medium text-zinc-700">Forma de pago <span class="font-normal text-zinc-400">(opcional)</span>
                            <select name="payment_method" class="mt-1.5 block w-full rounded-xl border border-zinc-900 bg-white px-4 py-3 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-900/20"><option value="">No especificada</option><option value="Efectivo">Efectivo</option><option value="Tarjeta">Tarjeta</option><option value="Transferencia">Transferencia</option><option value="Otro">Otro</option></select>
                        </label>
                        <p class="rounded-xl bg-amber-50 p-3 text-xs leading-5 text-amber-900">El descuento se aplica a cada prenda. Al confirmar la venta, las existencias bajan automáticamente.</p>
                    </div>
                    <div class="p-6">
                        <dl class="space-y-3 text-sm">
                            <div class="flex items-center justify-between text-zinc-600"><dt>Subtotal</dt><dd id="subtotal" class="font-medium text-zinc-800">{{ $currencySymbol }} 0.00</dd></div>
                            <div class="flex items-center justify-between text-zinc-600"><dt>Descuentos</dt><dd id="discount-total" class="font-medium text-red-600">{{ $currencySymbol }} 0.00</dd></div>
                        </dl>
                        <div class="mt-4 flex items-end justify-between border-t border-dashed border-zinc-300 pt-4">
                            <span class="text-sm font-medium uppercase tracking-wide text-zinc-500">Total a pagar</span>
                            <span id="sale-total" class="text-3xl font-semibold tracking-tight text-zinc-900">{{ $currencySymbol }} 0.00</span>
                        </div>
                        <button id="submit-sale" class="mt-5 w-full rounded-xl bg-amber-600 px-8 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700">Confirmar venta</button>
                    </div>
                </div>
            </section>
        </form>
    </div>

    <script>
        (() => {
            const search = document.getElementById('product-search');
            const results = document.getElementById('product-results');
            const itemsBody = document.getElementById('sale-items');
            const form = document.getElementById('sale-form');
            const cart = new Map();
            let timer;

            const currency = @js($currencySymbol);
            const money = (value) => `${currency} ${Number(value).toFixed(2)}`;
            const cents = (value) => Math.round(Number(value) * 100);

            function renderCart() {
                itemsBody.replaceChildren();
                let subtotal = 0;
                let discountTotal = 0;
                if (!cart.size) {
                    const row = document.createElement('tr');
                    row.innerHTML = '<td colspan="6" class="px-4 py-12 text-center text-sm text-zinc-500">Busca y agrega una prenda para comenzar.</td>';
                    itemsBody.append(row);
                }

                [...cart.values()].forEach((item, index) => {
                    const quantity = Math.max(1, Number(item.quantity) || 1);
                    const price = cents(item.sale_price);
                    const discount = cents(item.discount);
                    subtotal += price * quantity;
                    discountTotal += discount * quantity;
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-4 py-4"><p class="font-medium text-zinc-900"></p><p class="mt-1 text-xs text-zinc-500"></p><input type="hidden" name="items[${index}][product_id]"></td>
                        <td class="whitespace-nowrap px-4 py-4 text-zinc-700">${money(item.sale_price)}<p class="mt-1 text-xs text-zinc-400">Costo: ${money(item.purchase_price)}</p></td>
                        <td class="px-4 py-4"><input type="number" min="1" max="${item.stock}" name="items[${index}][quantity]" value="${quantity}" class="w-20 rounded-lg border border-zinc-900 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-900/20" aria-label="Cantidad"></td>
                        <td class="px-4 py-4"><input type="number" min="0" max="${item.max_discount}" step="0.01" name="items[${index}][discount]" value="${item.discount}" class="w-28 rounded-lg border border-zinc-900 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:ring-2 focus:ring-zinc-900/20" aria-label="Descuento por unidad"><p class="mt-1 text-[11px] text-zinc-400">Máx. ${money(item.max_discount)}</p></td>
                        <td class="whitespace-nowrap px-4 py-4 font-medium text-zinc-900">${money((price - discount) * quantity / 100)}</td>
                        <td class="px-4 py-4"><button type="button" class="remove-item rounded-lg px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50">Quitar</button></td>`;
                    row.querySelector('td p').textContent = item.name;
                    row.querySelector('td p + p').textContent = `${item.category} · Existencia: ${item.stock}`;
                    row.querySelector('input[type="hidden"]').value = item.id;
                    const [quantityInput, discountInput] = row.querySelectorAll('input[type="number"]');
                    quantityInput.addEventListener('change', () => {
                        item.quantity = Math.min(item.stock, Math.max(1, Number(quantityInput.value) || 1));
                        renderCart();
                    });
                    discountInput.addEventListener('change', () => {
                        item.discount = Math.min(Number(item.max_discount), Math.max(0, Number(discountInput.value) || 0)).toFixed(2);
                        renderCart();
                    });
                    row.querySelector('.remove-item').addEventListener('click', () => {
                        cart.delete(item.id);
                        renderCart();
                    });
                    itemsBody.append(row);
                });

                document.getElementById('subtotal').textContent = money(subtotal / 100);
                document.getElementById('discount-total').textContent = money(discountTotal / 100);
                document.getElementById('sale-total').textContent = money((subtotal - discountTotal) / 100);
            }

            function addProduct(product) {
                if (cart.has(product.id)) return;
                cart.set(product.id, { ...product, quantity: 1, discount: '0.00' });
                search.value = '';
                results.classList.add('hidden');
                renderCart();
            }

            search.addEventListener('input', () => {
                clearTimeout(timer);
                const term = search.value.trim();
                if (term.length < 2) {
                    results.classList.add('hidden');
                    return;
                }
                timer = setTimeout(async () => {
                    results.replaceChildren();
                    const loading = document.createElement('p');
                    loading.className = 'px-4 py-3 text-sm text-zinc-500';
                    loading.textContent = 'Buscando...';
                    results.append(loading);
                    results.classList.remove('hidden');

                    try {
                        const response = await fetch(`{{ route('sales.products') }}?search=${encodeURIComponent(term)}`, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (!response.ok) throw new Error('No fue posible buscar las prendas.');
                        const products = await response.json();
                        results.replaceChildren();
                        if (!products.length) {
                            const empty = document.createElement('p');
                            empty.className = 'px-4 py-3 text-sm text-zinc-500';
                            empty.textContent = 'No hay prendas disponibles con esa búsqueda.';
                            results.append(empty);
                        }
                        products.forEach((product) => {
                            const button = document.createElement('button');
                            button.type = 'button';
                            button.className = 'block w-full border-b border-zinc-100 px-4 py-3 text-left hover:bg-amber-50';
                            const title = document.createElement('span');
                            title.className = 'block text-sm font-medium text-zinc-900';
                            title.textContent = product.name;
                            const detail = document.createElement('span');
                            detail.className = 'mt-1 block text-xs text-zinc-500';
                            detail.textContent = `${product.category} · ${product.size || 'Sin talla'} · ${product.stock} disponibles · ${money(product.sale_price)}`;
                            button.append(title, detail);
                            button.addEventListener('click', () => addProduct(product));
                            results.append(button);
                        });
                    } catch (error) {
                        results.replaceChildren();
                        const message = document.createElement('p');
                        message.className = 'px-4 py-3 text-sm text-red-600';
                        message.textContent = error.message;
                        results.append(message);
                    }
                }, 250);
            });

            document.addEventListener('click', (event) => {
                if (!results.contains(event.target) && event.target !== search) results.classList.add('hidden');
            });
            form.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' || event.target.tagName === 'TEXTAREA' || event.target.tagName === 'BUTTON') return;
                event.preventDefault();
                if (event.target === search) {
                    const first = results.querySelector('button');
                    if (first) first.click();
                }
            });
            form.addEventListener('submit', (event) => {
                if (!cart.size) {
                    event.preventDefault();
                    alert('Agrega al menos una prenda antes de confirmar la venta.');
                }
            });
        })();
    </script>
</x-layouts.app>
