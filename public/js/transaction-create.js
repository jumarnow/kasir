/**
 * Transaction Create Page - Main JavaScript
 * ==========================================
 * Modular JavaScript for transaction creation functionality
 */

// =========================================
// Configuration & State
// =========================================
const TransactionApp = {
    // State
    cart: [],
    currentPriceTier: 1,
    printChoiceConfirmed: false,
    pendingSubmitForm: null,
    preOpenedPrintWindow: null,
    isInitializing: false, // New flag to track initialization state

    // Constants
    printWindowFeatures: 'width=360,height=600,menubar=no,toolbar=no,location=no,status=no,scrollbars=yes',

    // DOM References (initialized in init())
    $elements: {},

    // Data (set from server)
    productsData: [],
    customersData: [],
    finishingsData: [],
    displaysData: [],
    materialsData: [],
    routes: {},
    csrfToken: '',
    productSlimSelect: null
};

// =========================================
// Utility Functions
// =========================================
const Utils = {
    formatCurrency(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
    },

    parseCurrency(value) {
        if (value === null || value === undefined) {
            return 0;
        }

        if (typeof value !== 'string') {
            value = String(value);
        }

        const normalized = value
            .replace(/[^\d,.-]/g, '')
            .replace(/\.(?=\d{3}(?:[\.,]|$))/g, '')
            .replace(',', '.');

        const parsed = parseFloat(normalized);

        return Number.isFinite(parsed) ? parsed : 0;
    }
};

// =========================================
// Quick Customer Modal
// =========================================
const QuickCustomerModal = {
    init() {
        const app = TransactionApp;

        app.$elements.$btnQuickCustomer.on('click', () => this.toggle(true));
        app.$elements.$btnCloseQuickCustomer.on('click', () => this.toggle(false));
        app.$elements.$btnCancelQuickCustomer.on('click', () => this.toggle(false));
        app.$elements.$quickCustomerForm.on('submit', (e) => this.handleSubmit(e));
    },

    toggle(show) {
        const $modal = TransactionApp.$elements.$quickCustomerModal;
        const $form = TransactionApp.$elements.$quickCustomerForm;

        if (show) {
            $modal.removeClass('hidden').addClass('flex');
        } else {
            $modal.addClass('hidden').removeClass('flex');
            $form[0].reset();
        }
    },

    handleSubmit(e) {
        e.preventDefault();
        const app = TransactionApp;
        const $form = app.$elements.$quickCustomerForm;
        const formData = $form.serialize();
        const $submitBtn = $form.find('button[type="submit"]');

        $submitBtn.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: app.routes.customersStore,
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': app.csrfToken,
                'Accept': 'application/json'
            },
            success: (response) => {
                // Add to dropdown
                const newOption = new Option(response.name, response.id, true, true);
                $(newOption).data('price-tier', response.price_tier || 1);
                $('#customer-select').append(newOption).trigger('change');

                // Close modal
                this.toggle(false);

                // Update tier
                app.currentPriceTier = response.price_tier || 1;
                if (app.cart.length > 0) Cart.updatePrices(app.currentPriceTier);

                alert('Pelanggan berhasil ditambahkan!');
            },
            error: (xhr) => {
                let msg = 'Terjadi kesalahan.';
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    msg = Object.values(errors).flat().join('\n');
                }
                alert(msg);
            },
            complete: () => {
                $submitBtn.prop('disabled', false).text('Simpan');
            }
        });
    }
};

// =========================================
// Cart Management
// =========================================
const Cart = {
    render() {
        const app = TransactionApp;
        const tbody = $('#cart-items');
        const mobileList = $('#cart-items-mobile');
        const emptyState = $('#empty-cart');
        const inputsWrapper = $('#items-inputs');

        tbody.empty();
        mobileList.empty();
        inputsWrapper.empty();

        if (app.cart.length === 0) {
            emptyState.show();
        } else {
            emptyState.hide();
        }

        app.cart.forEach((item, index) => {
            const subtotal = item.quantity * item.price;

            // Format Dimensions & Details for display
            let detailsText = '';
            if (item.width > 0 && item.length > 0) {
                detailsText += `<div class="text-xs text-slate-500">Dimensi: ${item.length} x ${item.width} cm</div>`;
            }

            const finishingName = app.finishingsData.find(f => f.id == item.finishing_id)?.name || null;
            const materialName = app.materialsData.find(m => m.id == item.material_id)?.name || null;
            const displayName = app.displaysData.find(d => d.id == item.display_id)?.name || null;

            let extras = [];
            if (finishingName) extras.push(`F: ${finishingName}`);
            if (materialName) extras.push(`M: ${materialName}`);
            if (displayName) extras.push(`D: ${displayName}`);

            if (extras.length > 0) {
                detailsText += `<div class="text-xs text-slate-500 mt-1">${extras.join(' | ')}</div>`;
            }

            // Determine badge and stock display
            const isCustom = item.is_custom;
            const stockText = isCustom ? '<span class="text-emerald-500">Manual</span>' : `Stok: ${item.stock}`;
            let badgeHtml;
            if (isCustom) {
                badgeHtml = '<span class="inline-flex items-center rounded-md bg-emerald-50 text-emerald-700 ring-emerald-600/10 px-2 py-1 text-[10px] font-medium ring-1 ring-inset">Manual</span>';
            } else if (item.pricing_type === 'per_dimension') {
                badgeHtml = '<span class="inline-flex items-center rounded-md bg-blue-50 text-blue-700 ring-blue-700/10 px-2 py-1 text-[10px] font-medium ring-1 ring-inset">Per Dimensi</span>';
            } else {
                badgeHtml = '<span class="inline-flex items-center rounded-md bg-slate-50 text-slate-600 ring-slate-200 px-2 py-1 text-[10px] font-medium ring-1 ring-inset">Unit/Pcs</span>';
            }

            const editBtnHtml = isCustom
                ? `<button type="button" class="bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-md px-2 py-1 text-xs font-medium transition-colors btn-edit-custom" data-index="${index}">Edit</button>`
                : `<button type="button" class="bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-md px-2 py-1 text-xs font-medium transition-colors btn-edit-item" data-index="${index}">Edit</button>`;

            // Desktop Row
            const row = $(`
                <tr>
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-700">${item.name}</div>
                        <div class="text-xs text-slate-400">${stockText}</div>
                        ${detailsText}
                    </td>
                    <td class="px-4 py-3 text-center">
                        ${badgeHtml}
                    </td>
                    <td class="px-4 py-3 text-center">
                        ${Utils.formatCurrency(item.price)}
                    </td>
                    <td class="px-4 py-3 text-center">
                        ${item.quantity}
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-700">
                        ${Utils.formatCurrency(subtotal)}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-2">
                            ${editBtnHtml}
                            <button type="button" class="bg-red-50 text-red-600 hover:bg-red-100 rounded-md px-2 py-1 text-xs font-medium transition-colors remove-item" data-index="${index}">Hapus</button>
                        </div>
                    </td>
                </tr>
            `);
            tbody.append(row);

            // Mobile edit button
            const mobileEditBtnHtml = isCustom
                ? `<button type="button" class="text-xs text-emerald-600 font-medium btn-edit-custom" data-index="${index}">Edit</button>`
                : `<button type="button" class="text-xs text-indigo-600 font-medium btn-edit-item" data-index="${index}">Edit</button>`;

            // Mobile type label
            let mobileTypeLabel, mobileTypeClass;
            if (isCustom) {
                mobileTypeLabel = 'Manual';
                mobileTypeClass = 'text-emerald-600';
            } else if (item.pricing_type === 'per_dimension') {
                mobileTypeLabel = 'Per Dimensi';
                mobileTypeClass = 'text-blue-600';
            } else {
                mobileTypeLabel = 'Unit/Pcs';
                mobileTypeClass = 'text-slate-600';
            }

            // Mobile list
            const mobileItem = $(`
                <div class="p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-medium text-slate-700">${item.name}</p>
                            ${detailsText}
                        </div>
                        <div class="flex gap-3">
                            ${mobileEditBtnHtml}
                            <button type="button" class="remove-item text-xs text-red-500 font-medium" data-index="${index}">Hapus</button>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Tipe Harga</span>
                            <span class="font-medium ${mobileTypeClass}">${mobileTypeLabel}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Harga</span>
                            <span>${Utils.formatCurrency(item.price)}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Qty</span>
                            <span>${item.quantity}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-slate-50">
                            <span class="text-xs font-semibold text-slate-500">Subtotal</span>
                            <span class="font-semibold text-slate-700">${Utils.formatCurrency(subtotal)}</span>
                        </div>
                    </div>
                </div>
            `);
            mobileList.append(mobileItem);

            if (item.is_custom) {
                inputsWrapper.append(`
                    <input type="hidden" name="items[${index}][custom_name]" value="${item.custom_name}">
                    <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
                    <input type="hidden" name="items[${index}][price]" value="${item.price}">
                    <input type="hidden" name="items[${index}][cost_price]" value="${item.cost_price}">
                    <input type="hidden" name="items[${index}][width]" value="0">
                    <input type="hidden" name="items[${index}][length]" value="0">
                `);
            } else {
                inputsWrapper.append(`
                    <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                    <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
                    <input type="hidden" name="items[${index}][price]" value="${item.price}">
                    <input type="hidden" name="items[${index}][cost_price]" value="${item.cost_price}">
                    <input type="hidden" name="items[${index}][width]" value="${item.width || 0}">
                    <input type="hidden" name="items[${index}][length]" value="${item.length || 0}">
                    <input type="hidden" name="items[${index}][finishing_id]" value="${item.finishing_id || ''}">
                    <input type="hidden" name="items[${index}][material_id]" value="${item.material_id || ''}">
                    <input type="hidden" name="items[${index}][material_price_tier]" value="${item.material_price_tier || '1'}">
                    <input type="hidden" name="items[${index}][product_price_tier]" value="${item.product_price_tier || '1'}">
                    <input type="hidden" name="items[${index}][display_id]" value="${item.display_id || ''}">
                `);
            }
        });

        Summary.update();
    },

    addProduct(product) {
        const app = TransactionApp;
        const stockAlert = Number(product.stock_alert || 0);

        if (product.stock < 1) {
            alert('Stok produk habis.');
            return;
        }

        let selectedPrice = Number(product.price);
        if (app.currentPriceTier === 2 && product.price_2 > 0) {
            selectedPrice = Number(product.price_2);
        } else if (app.currentPriceTier === 3 && product.price_3 > 0) {
            selectedPrice = Number(product.price_3);
        }

        if (product.pricing_type === 'per_dimension') {
            selectedPrice = 0;
        }

        app.cart.push({
            id: product.id,
            name: product.name,
            price: selectedPrice,
            price_1: Number(product.price),
            price_2: Number(product.price_2 || 0),
            price_3: Number(product.price_3 || 0),
            cost_price: Number(product.cost_price ?? product.price),
            stock: product.stock,
            stock_alert: stockAlert,
            quantity: 1,
            pricing_type: product.pricing_type,
            price_per_meter: Number(product.price_per_meter || product.price),
            price_unit: product.price_unit || 'per_m2',
            width: 0,
            length: 0,
            area: 0,
            finishing_id: null,
            material_id: null,
            material_price_tier: '1',
            product_price_tier: '1',
            display_id: null
        });

        if (stockAlert > 0 && (product.stock - 1) <= stockAlert) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: `Stok menipis: ${product.name}`,
                showConfirmButton: false,
                timer: 3000
            });
        }

        this.render();
        ItemDetailModal.open(app.cart.length - 1);
    },

    updatePrices(tier) {
        const app = TransactionApp;
        app.cart.forEach(item => {
            if (item.is_custom) return; // Skip custom items
            if (tier === 2 && item.price_2 > 0) {
                item.price = item.price_2;
            } else if (tier === 3 && item.price_3 > 0) {
                item.price = item.price_3;
            } else {
                item.price = item.price_1;
            }
        });
        this.render();
    }
};

// =========================================
// Summary Calculation
// =========================================
const Summary = {
    calculate() {
        const app = TransactionApp;
        const subtotal = app.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const discountPercent = parseFloat($('#discount-percent').val()) || 0;
        const discountAmountInput = Utils.parseCurrency($('#discount-amount').val());
        const discountFromPercent = subtotal * (discountPercent / 100);
        const totalDiscount = Math.min(subtotal, discountAmountInput + discountFromPercent);
        const shippingCost = Utils.parseCurrency($('#shipping-cost').val());
        const total = Math.max(subtotal - totalDiscount + shippingCost, 0);
        const amountPaid = Utils.parseCurrency($('#amount-paid').val());
        const change = Math.max(amountPaid - total, 0);
        return { subtotal, totalDiscount, total, amountPaid, change };
    },

    update() {
        const app = TransactionApp;
        const results = this.calculate();
        const { subtotal, totalDiscount, total, change } = results;
        let amountPaid = results.amountPaid;

        const paymentMethod = $('select[name="payment_method"]').val();

        // Auto-fill amount_paid if lunas
        if (paymentMethod === 'lunas') {
            $('#amount-paid').val(Utils.formatCurrency(total));
            amountPaid = total;
        } else if (paymentMethod === 'pending') {
            $('#amount-paid').val(Utils.formatCurrency(0));
            amountPaid = 0;
        }

        const shippingCost = Utils.parseCurrency($('#shipping-cost').val());

        $('#summary-subtotal').text(Utils.formatCurrency(subtotal));
        $('#summary-discount').text(Utils.formatCurrency(totalDiscount));
        $('#summary-shipping').text(Utils.formatCurrency(shippingCost));
        $('#summary-total').text(Utils.formatCurrency(total));
        $('#summary-change').text(Utils.formatCurrency(Math.max(amountPaid - total, 0)));

        this.updateSubmitButton(total, amountPaid);
    },

    updateSubmitButton(total, amountPaid) {
        const app = TransactionApp;
        if (!app.$elements.$submitButton) return;
        const canSubmit = app.cart.length > 0;
        app.$elements.$submitButton.prop('disabled', !canSubmit);
    }
};

// =========================================
// Item Detail Modal
// =========================================
const ItemDetailModal = {
    init() {
        const app = TransactionApp;

        app.$elements.$closeItemDetail.on('click', () => this.close());
        app.$elements.$cancelItemDetail.on('click', () => this.close());

        $('#item-detail-form input, #item-detail-form select').on('input change', () => this.calculatePrice());
        $('#modal-material').on('change', () => this.updateMaterialTierLabels());

        app.$elements.$itemDetailForm.on('submit', (e) => this.handleSubmit(e));
    },

    open(index) {
        const app = TransactionApp;
        const item = app.cart[index];
        if (!item) return;

        $('#modal-item-index').val(index);
        $('#modal-item-name').text(item.name);
        $('#modal-qty').val(item.quantity);
        $('#modal-length').val(item.length || 0);
        $('#modal-width').val(item.width || 0);
        $('#modal-finishing').val(item.finishing_id || '');
        $('#modal-material').val(item.material_id || '');
        $('#modal-display').val(item.display_id || '');

        this.updateMaterialTierLabels();
        $('#modal-material-tier').val(item.material_price_tier || '1');

        // Toggle Dimensions based on pricing type
        // Finishing selalu muncul untuk semua tipe produk
        $('#modal-finishing-wrapper').removeClass('hidden');

        if (item.pricing_type === 'per_dimension') {
            $('#modal-dimensions-wrapper').removeClass('hidden').addClass('grid');
            $('#modal-options-wrapper').removeClass('hidden').addClass('grid');
            $('#modal-product-price-tier-wrapper').addClass('hidden');

            if ($('#modal-material').val()) {
                $('#modal-material-tier-wrapper').removeClass('hidden');
            } else {
                $('#modal-material-tier-wrapper').addClass('hidden');
            }
        } else {
            $('#modal-dimensions-wrapper').addClass('hidden').removeClass('grid');
            $('#modal-options-wrapper').addClass('hidden').removeClass('grid');
            $('#modal-material-tier-wrapper').addClass('hidden');

            // Show Product Price Tier
            $('#modal-product-price-tier-wrapper').removeClass('hidden');

            // Populate options
            const $tierSelect = $('#modal-product-price-tier');
            $tierSelect.empty();

            const p1 = parseFloat(item.price_1 || 0);
            const p2 = parseFloat(item.price_2 || 0);
            const p3 = parseFloat(item.price_3 || 0);

            $tierSelect.append(new Option(`Harga Utama (${Utils.formatCurrency(p1)})`, '1'));

            if (p2 > 0) $tierSelect.append(new Option(`Harga 2 (${Utils.formatCurrency(p2)})`, '2'));
            if (p3 > 0) $tierSelect.append(new Option(`Harga 3 (${Utils.formatCurrency(p3)})`, '3'));

            let selectedTier = item.product_price_tier || '1';

            if (!item.product_price_tier) {
                if (item.price == p2) selectedTier = '2';
                else if (item.price == p3) selectedTier = '3';
                else if (app.currentPriceTier == 2 && p2 > 0) selectedTier = '2';
                else if (app.currentPriceTier == 3 && p3 > 0) selectedTier = '3';
            }

            $tierSelect.val(selectedTier);
        }

        this.calculatePrice();

        app.$elements.$itemDetailModal.removeClass('hidden').addClass('flex');
        $('#modal-qty').focus();
    },

    close() {
        const app = TransactionApp;
        app.$elements.$itemDetailModal.addClass('hidden').removeClass('flex');
        app.$elements.$itemDetailForm[0].reset();
        $('#modal-material-tier-wrapper').addClass('hidden');
    },

    updateMaterialTierLabels() {
        const app = TransactionApp;
        const materialId = $('#modal-material').val();
        const $tierSelect = $('#modal-material-tier');

        if (!materialId) {
            $tierSelect.find('option[value="1"]').text('Harga Utama');
            $tierSelect.find('option[value="2"]').text('Harga 2');
            $tierSelect.find('option[value="3"]').text('Harga 3');
            return;
        }

        const material = app.materialsData.find(m => m.id == materialId);
        if (material) {
            $tierSelect.find('option[value="1"]').text(Utils.formatCurrency(material.selling_price || 0));

            const price2 = parseFloat(material.price_2 || 0);
            if (price2 > 0) {
                $tierSelect.find('option[value="2"]').text(Utils.formatCurrency(price2)).show();
            } else {
                $tierSelect.find('option[value="2"]').text('-').hide();
            }

            const price3 = parseFloat(material.price_3 || 0);
            if (price3 > 0) {
                $tierSelect.find('option[value="3"]').text(Utils.formatCurrency(price3)).show();
            } else {
                $tierSelect.find('option[value="3"]').text('-').hide();
            }
        }
    },

    calculatePrice() {
        const app = TransactionApp;
        const index = $('#modal-item-index').val();
        const item = app.cart[index];
        if (!item) return;

        const w = parseFloat($('#modal-width').val()) || 0;
        const l = parseFloat($('#modal-length').val()) || 0;
        const qty = parseInt($('#modal-qty').val()) || 1;

        // Calculate area based on price_unit
        // per_m2: convert cm to m (divide by 100), then calculate m²
        // per_cm2: use cm directly as cm²
        let area;
        if (item.price_unit === 'per_cm2') {
            area = w * l; // cm * cm = cm²
        } else {
            area = (w / 100) * (l / 100); // m * m = m²
        }

        let productPrice = item.price_1;

        if (item.pricing_type !== 'per_dimension') {
            const selectedTier = $('#modal-product-price-tier').val();
            if (selectedTier == '2' && item.price_2 > 0) productPrice = item.price_2;
            else if (selectedTier == '3' && item.price_3 > 0) productPrice = item.price_3;
        } else {
            if (app.currentPriceTier === 2 && item.price_2 > 0) productPrice = item.price_2;
            if (app.currentPriceTier === 3 && item.price_3 > 0) productPrice = item.price_3;
            productPrice = item.price_per_meter || productPrice;
        }

        let unitPrice = 0;
        if (item.pricing_type === 'per_dimension') {
            unitPrice = productPrice * area;
        } else {
            unitPrice = productPrice;
        }

        // Add Material Price
        const materialId = $('#modal-material').val();
        const materialTier = $('#modal-material-tier').val();

        if (materialId) {
            if (item.pricing_type === 'per_dimension') {
                $('#modal-material-tier-wrapper').removeClass('hidden');
            }

            const material = app.materialsData.find(m => m.id == materialId);
            if (material) {
                let matPrice = parseFloat(material.selling_price) || 0;
                if (materialTier == '2' && material.price_2 > 0) matPrice = parseFloat(material.price_2);
                if (materialTier == '3' && material.price_3 > 0) matPrice = parseFloat(material.price_3);

                if (item.pricing_type === 'per_dimension') {
                    unitPrice += (matPrice * area);
                } else {
                    unitPrice += matPrice;
                }
            }
        } else {
            $('#modal-material-tier-wrapper').addClass('hidden');
        }

        // Add Finishing Price
        const finishingId = $('#modal-finishing').val();
        if (finishingId) {
            const finishing = app.finishingsData.find(f => f.id == finishingId);
            if (finishing) {
                const fPrice = parseFloat(finishing.price) || 0;
                // Untuk produk per pcs (bukan per_dimension), finishing langsung flat price
                if (item.pricing_type !== 'per_dimension') {
                    unitPrice += fPrice;
                } else if (finishing.pricing_type === 'per_meter') {
                    unitPrice += (fPrice * (l / 100));
                } else if (finishing.pricing_type === 'per_dimension') {
                    unitPrice += (fPrice * area);
                } else {
                    unitPrice += fPrice;
                }
            }
        }

        const total = Math.round(unitPrice) * qty;
        $('#modal-price-display').text(Utils.formatCurrency(total));
    },

    handleSubmit(e) {
        e.preventDefault();
        const app = TransactionApp;
        const index = $('#modal-item-index').val();
        const item = app.cart[index];
        if (!item) return;

        const qty = parseInt($('#modal-qty').val()) || 1;
        const w = parseFloat($('#modal-length').val()) || 0;
        const l = parseFloat($('#modal-width').val()) || 0;

        // Calculate area based on price_unit
        let area;
        if (item.price_unit === 'per_cm2') {
            area = w * l; // cm * cm = cm²
        } else {
            area = (w / 100) * (l / 100); // m * m = m²
        }

        // Update basic specs
        item.quantity = qty;
        item.length = w;
        item.width = l;
        item.area = area;
        item.finishing_id = $('#modal-finishing').val() || null;
        item.material_id = $('#modal-material').val() || null;
        item.material_price_tier = $('#modal-material-tier').val() || '1';
        item.display_id = $('#modal-display').val() || null;
        item.product_price_tier = $('#modal-product-price-tier').val() || '1';

        // Final Unit Price Calculation
        let productPrice = item.price_1;

        if (item.pricing_type !== 'per_dimension') {
            const selectedTier = item.product_price_tier;
            if (selectedTier == '2' && item.price_2 > 0) productPrice = item.price_2;
            else if (selectedTier == '3' && item.price_3 > 0) productPrice = item.price_3;
        } else {
            if (app.currentPriceTier === 2 && item.price_2 > 0) productPrice = item.price_2;
            if (app.currentPriceTier === 3 && item.price_3 > 0) productPrice = item.price_3;
            productPrice = item.price_per_meter || productPrice;
        }

        let unitPrice = 0;
        if (item.pricing_type === 'per_dimension') {
            unitPrice = productPrice * area;
        } else {
            unitPrice = productPrice;
        }

        // Material
        if (item.material_id) {
            const material = app.materialsData.find(m => m.id == item.material_id);
            if (material) {
                let matPrice = parseFloat(material.selling_price) || 0;
                if (item.material_price_tier == '2' && material.price_2 > 0) matPrice = parseFloat(material.price_2);
                if (item.material_price_tier == '3' && material.price_3 > 0) matPrice = parseFloat(material.price_3);

                unitPrice += (item.pricing_type === 'per_dimension') ? (matPrice * area) : matPrice;
            }
        }

        // Finishing
        if (item.finishing_id) {
            const finishing = app.finishingsData.find(f => f.id == item.finishing_id);
            if (finishing) {
                const fPrice = parseFloat(finishing.price) || 0;
                // Untuk produk per pcs (bukan per_dimension), finishing langsung flat price
                if (item.pricing_type !== 'per_dimension') {
                    unitPrice += fPrice;
                } else if (finishing.pricing_type === 'per_meter') {
                    unitPrice += (fPrice * (item.length / 100));
                } else if (finishing.pricing_type === 'per_dimension') {
                    unitPrice += (fPrice * area);
                } else {
                    unitPrice += fPrice;
                }
            }
        }

        item.price = Math.round(unitPrice);

        Cart.render();
        this.close();
    }
};

// =========================================
// Print Modal
// =========================================
const PrintModal = {
    init() {
        const app = TransactionApp;

        app.$elements.$printModalConfirm.on('click', () => this.handleConfirm('invoice'));
        app.$elements.$printModalShipping.on('click', () => this.handleConfirm('shipping'));
        app.$elements.$printModalSkip.on('click', () => this.handleConfirm('skip'));
        app.$elements.$printModalClose.on('click', () => this.hide());

        app.$elements.$printModal.on('click', function (event) {
            if (event.target === this) {
                PrintModal.hide();
            }
        });

        $(document).on('keydown', function (event) {
            if (event.key === 'Escape' && !app.$elements.$printModal.hasClass('hidden')) {
                PrintModal.hide();
            }
        });
    },

    show() {
        const app = TransactionApp;
        if (!app.$elements.$printModal) return;
        app.$elements.$printModal.removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden');
    },

    hide() {
        const app = TransactionApp;
        if (!app.$elements.$printModal) return;
        app.$elements.$printModal.addClass('hidden').removeClass('flex');
        $('body').removeClass('overflow-hidden');
    },

    handleConfirm(type) {
        const app = TransactionApp;

        if (type === 'invoice') {
            app.$elements.$printInvoiceInput.val('1');
            app.$elements.$printShippingInput.val('0');
            try {
                sessionStorage.setItem('kasirInvoicePrintRequested', '1');
                sessionStorage.removeItem('kasirShippingLabelPrintRequested');
            } catch (error) { }
        } else if (type === 'shipping') {
            app.$elements.$printInvoiceInput.val('0');
            app.$elements.$printShippingInput.val('1');
            try {
                sessionStorage.removeItem('kasirInvoicePrintRequested');
                sessionStorage.setItem('kasirShippingLabelPrintRequested', '1');
            } catch (error) { }
        } else {
            app.$elements.$printInvoiceInput.val('0');
            app.$elements.$printShippingInput.val('0');
            try {
                sessionStorage.removeItem('kasirInvoicePrintRequested');
                sessionStorage.removeItem('kasirShippingLabelPrintRequested');
            } catch (error) { }
        }

        app.printChoiceConfirmed = true;
        this.hide();

        if (app.pendingSubmitForm) {
            $(app.pendingSubmitForm).trigger('submit');
        }
    }
};

// =========================================
// Form Handler
// =========================================
const FormHandler = {
    init() {
        const app = TransactionApp;

        // Product selection
        app.$elements.$addProductButton.on('click', () => this.handleAddProduct());

        // Custom/Manual item
        $('#add-custom-product').on('click', () => this.handleAddCustomProduct());

        // Custom input validation - toggle add button
        $('#custom-product-name, #custom-price').on('input', () => this.toggleCustomAddButton());

        // Mode toggle
        $('.item-mode-btn').on('click', function () {
            const mode = $(this).data('mode');
            $('.item-mode-btn').removeClass('active');
            $(this).addClass('active');

            if (mode === 'custom') {
                $('#product-mode-inputs').addClass('hidden');
                $('#custom-mode-inputs').removeClass('hidden');
                $('#custom-product-name').focus();
            } else {
                $('#custom-mode-inputs').addClass('hidden');
                $('#product-mode-inputs').removeClass('hidden');
                $('#barcode-input').focus();
            }
        });

        // Allow Enter key to add custom item
        $('#custom-product-name, #custom-qty, #custom-price').on('keypress', (e) => {
            if (e.which === 13) {
                e.preventDefault();
                this.handleAddCustomProduct();
            }
        });

        // Barcode input
        $('#barcode-input').on('keypress', (e) => {
            if (e.which === 13) {
                e.preventDefault();
                this.handleBarcodeScan();
            }
        });

        // Cart actions
        $('#cart-items, #cart-items-mobile').on('click', '.btn-edit-item', function () {
            const index = $(this).data('index');
            ItemDetailModal.open(index);
        });

        // Edit custom item inline
        $('#cart-items, #cart-items-mobile').on('click', '.btn-edit-custom', function () {
            const index = $(this).data('index');
            FormHandler.editCustomItem(index);
        });

        $('#cart-items, #cart-items-mobile').on('click', '.remove-item', function () {
            const index = $(this).data('index');
            app.cart.splice(index, 1);
            Cart.render();
        });

        // Summary updates
        $(document).on('input', '#discount-percent, #discount-amount, #amount-paid, #shipping-cost', function () {
            const raf = window.requestAnimationFrame || function (cb) { return setTimeout(cb, 0); };
            raf(() => Summary.update());
        });

        // Payment method change
        $('#payment-method').on('change', () => this.handlePaymentMethodChange());

        // Customer change
        $('#customer-select').on('change', () => this.handleCustomerChange());

        // Customer section is now always visible

        // Form submit
        $('#transaction-form').on('submit', (e) => this.handleSubmit(e));

        // Print Preview close
        $('#close-print-preview').on('click', () => {
            window.location.reload();
        });

        // Initial triggers
        $('#payment-method').trigger('change');
        Summary.update();
    },

    handleAddProduct() {
        const app = TransactionApp;
        const productId = app.$elements.$productSelect.val();

        if (!productId) {
            alert('Pilih produk terlebih dahulu.');
            return;
        }

        const product = app.productsData.find(p => p.id == productId);

        if (product) {
            Cart.addProduct(product);
            app.productSlimSelect.setSelected('');
            this.toggleAddButton();
        }
    },

    handleAddCustomProduct() {
        const name = $('#custom-product-name').val().trim();
        const qty = parseInt($('#custom-qty').val()) || 1;
        const price = Utils.parseCurrency($('#custom-price').val());

        if (!name) {
            alert('Masukkan nama produk.');
            $('#custom-product-name').focus();
            return;
        }

        if (price <= 0) {
            alert('Masukkan harga yang valid.');
            $('#custom-price').focus();
            return;
        }

        TransactionApp.cart.push({
            id: null,
            is_custom: true,
            custom_name: name,
            name: name,
            price: price,
            price_1: price,
            price_2: 0,
            price_3: 0,
            cost_price: price,
            stock: 0,
            stock_alert: 0,
            quantity: qty,
            pricing_type: 'per_unit',
            price_per_meter: 0,
            price_unit: 'per_m2',
            width: 0,
            length: 0,
            area: 0,
            finishing_id: null,
            material_id: null,
            material_price_tier: '1',
            product_price_tier: '1',
            display_id: null
        });

        // Reset form
        $('#custom-product-name').val('');
        $('#custom-qty').val(1);
        $('#custom-price').val('');
        this.toggleCustomAddButton();

        Cart.render();
        $('#custom-product-name').focus();
    },

    editCustomItem(index) {
        const app = TransactionApp;
        const item = app.cart[index];
        if (!item || !item.is_custom) return;

        const newName = prompt('Nama Produk:', item.name);
        if (newName === null) return; // cancelled
        if (newName.trim() === '') {
            alert('Nama produk tidak boleh kosong.');
            return;
        }

        const newQty = prompt('Qty:', item.quantity);
        if (newQty === null) return;
        const parsedQty = parseInt(newQty);
        if (isNaN(parsedQty) || parsedQty < 1) {
            alert('Qty harus minimal 1.');
            return;
        }

        const newPrice = prompt('Harga:', item.price);
        if (newPrice === null) return;
        const parsedPrice = Utils.parseCurrency(newPrice);
        if (parsedPrice < 0) {
            alert('Harga tidak valid.');
            return;
        }

        item.name = newName.trim();
        item.custom_name = newName.trim();
        item.quantity = parsedQty;
        item.price = parsedPrice;
        item.price_1 = parsedPrice;
        item.cost_price = parsedPrice;

        Cart.render();
    },

    toggleCustomAddButton() {
        const name = $('#custom-product-name').val().trim();
        const price = $('#custom-price').val().trim();
        const canAdd = name.length > 0 && price.length > 0;
        $('#add-custom-product').prop('disabled', !canAdd);
    },

    handleBarcodeScan() {
        const barcode = $('#barcode-input').val().trim();
        if (!barcode) return;

        $.get(TransactionApp.routes.lookup, { barcode })
            .done((data) => {
                Cart.addProduct(data);
                $('#barcode-input').val('');
            })
            .fail(() => {
                alert('Produk tidak ditemukan.');
            });
    },

    handlePaymentMethodChange() {
        const method = $('#payment-method').val();

        if (method === 'dp' || method === 'pending') {
            $('#due-date-container').removeClass('hidden');
            if (!$('#due-date').val()) {
                const date = new Date();
                // date.setDate(date.getDate() + 7); // Default ke hari ini
                let month = (date.getMonth() + 1).toString().padStart(2, '0');
                let day = date.getDate().toString().padStart(2, '0');
                $('#due-date').val(`${date.getFullYear()}-${month}-${day}`);
            }
        } else {
            $('#due-date-container').addClass('hidden');
            $('#due-date').val('');
        }

        Summary.update();
    },

    handleCustomerChange() {
        const selectedOption = $('#customer-select').find('option:selected');
        const priceTier = parseInt(selectedOption.data('price-tier')) || 1;
        TransactionApp.currentPriceTier = priceTier;

        // Prevent price update during initialization
        if (TransactionApp.isInitializing) return;

        if (TransactionApp.cart.length > 0) {
            Cart.updatePrices(priceTier);
        }
    },

    // initCustomerSectionToggle removed as section is always visible

    toggleAddButton() {
        const app = TransactionApp;
        const hasSelection = Boolean(app.$elements.$productSelect.val());
        app.$elements.$addProductButton.prop('disabled', !hasSelection);
    },

    handleSubmit(event) {
        event.preventDefault();
        const app = TransactionApp;
        const { total, amountPaid } = Summary.calculate();
        const paymentMethod = $('#payment-method').val();

        if (app.cart.length === 0) {
            alert('Tambahkan minimal satu produk.');
            return false;
        }

        const formData = $('#transaction-form').serialize();
        app.$elements.$submitButton.prop('disabled', true).text('Memproses...');

        $.ajax({
            url: app.routes.store,
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': app.csrfToken,
                'Accept': 'application/json'
            },
            success: (response) => {
                if (response.status === 'success') {
                    const txId = response.transaction_id;
                    let printUrl = '';

                    if (paymentMethod === 'lunas') {
                        printUrl = `/transactions/${txId}/receipt`;
                    } else {
                        printUrl = `/transactions/${txId}/invoice-a5`;
                    }

                    $('#print-preview-frame').attr('src', printUrl);
                    $('#print-preview-modal').removeClass('hidden').addClass('flex');

                    Swal.fire({
                        icon: 'success',
                        title: 'Transaksi Berhasil',
                        text: 'Silakan cetak dokumen pada jendela preview.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            },
            error: (xhr) => {
                app.$elements.$submitButton.prop('disabled', false).text('Simpan & Cetak');
                let msg = 'Gagal menyimpan transaksi.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                if (xhr.status === 422) {
                    const errs = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    msg += '\n' + errs;
                }
                Swal.fire('Error', msg, 'error');
            }
        });
    }
};

// =========================================
// Initialization
// =========================================
function initTransactionApp(config) {
    const app = TransactionApp;
    app.isInitializing = true; // Set flag at start

    // Set data from server
    app.productsData = config.products || [];
    app.customersData = config.customers || [];
    app.finishingsData = config.finishings || [];
    app.displaysData = config.displays || [];
    app.materialsData = config.materials || [];
    app.routes = config.routes || {};
    app.csrfToken = config.csrfToken || '';

    // Initialize DOM references
    app.$elements = {
        $quickCustomerModal: $('#quick-customer-modal'),
        $btnQuickCustomer: $('#btn-quick-customer'),
        $btnCloseQuickCustomer: $('#close-quick-customer'),
        $btnCancelQuickCustomer: $('#cancel-quick-customer'),
        $quickCustomerForm: $('#quick-customer-form'),
        $productSelect: $('#product-select'),
        $addProductButton: $('#add-product'),
        $submitButton: $('#transaction-submit'),
        $printInvoiceInput: $('#print-invoice'),
        $printShippingInput: $('#print-shipping-label'),
        $printModal: $('#print-confirm-modal'),
        $printModalConfirm: $('#print-modal-confirm'),
        $printModalShipping: $('#print-modal-shipping'),
        $printModalSkip: $('#print-modal-skip'),
        $printModalClose: $('#print-modal-close'),
        $itemDetailModal: $('#item-detail-modal'),
        $closeItemDetail: $('#close-item-detail'),
        $cancelItemDetail: $('#cancel-item-detail'),
        $itemDetailForm: $('#item-detail-form')
    };

    // Initialize Slim Select
    app.productSlimSelect = new SlimSelect({
        select: '#product-select',
        settings: {
            placeholderText: '-- Pilih Produk --',
            allowDeselect: true,
        },
        events: {
            afterChange: () => {
                FormHandler.toggleAddButton();
            }
        }
    });

    // Initialize Select2 for customer
    $('#customer-select').select2({
        placeholder: '-- Pilih Pelanggan --',
        allowClear: true,
        width: '100%',
    });

    // Focus barcode input
    const $barcodeInput = $('#barcode-input');
    $barcodeInput.trigger('focus');
    setTimeout(() => $barcodeInput.trigger('focus'), 200);

    // Initialize modules
    QuickCustomerModal.init();
    ItemDetailModal.init();
    PrintModal.init();
    FormHandler.init();

    // Load existing items for edit mode
    if (config.existingItems && config.existingItems.length > 0) {
        app.cart = config.existingItems;
        Cart.render();
    }

    // Pre-fill form values for edit mode
    if (config.existingCustomerId) {
        $('#customer-select').val(config.existingCustomerId).trigger('change');
    }
    if (config.existingEksekutorId) {
        $('#eksekutor-select').val(config.existingEksekutorId);
    }
    if (config.existingPaymentMethod) {
        $('#payment-method').val(config.existingPaymentMethod).trigger('change');
    }
    if (config.existingNotes) {
        $('textarea[name="notes"]').val(config.existingNotes);
    }
    if (config.existingDiscountPercent) {
        $('#discount-percent').val(config.existingDiscountPercent);
    }
    if (config.existingDiscountAmount) {
        $('#discount-amount').val(Utils.formatCurrency(config.existingDiscountAmount));
    }
    if (config.existingShippingCost) {
        $('#shipping-cost').val(Utils.formatCurrency(config.existingShippingCost));
    }
    if (config.existingAmountPaid) {
        $('#amount-paid').val(Utils.formatCurrency(config.existingAmountPaid));
    }
    if (config.existingDueDate) {
        $('#due-date').val(config.existingDueDate);
    }

    // Update summary after loading existing data
    Summary.update();

    app.isInitializing = false; // Clear flag at end
}

// Export for global access
window.TransactionApp = TransactionApp;
window.initTransactionApp = initTransactionApp;
