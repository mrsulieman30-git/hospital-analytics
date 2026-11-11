{{-- =========================================================================
     TAB 4: LABORATORY INVENTORY & RECEIVED SUPPLIES
     ========================================================================= --}}
@php
    $labSuppliers = $departmentForm?->dropdown_options['suppliers'] ?? [
        'Somali Medical Supplies Co.',
        'Horn of Africa Diagnostic Distributors',
        'Banadir Scientific & Healthcare Ltd.',
        'Global Health Reagents Mogadishu',
    ];
    $commonReagents = array_unique(array_merge(
        $departmentForm?->dropdown_options['nil_reagents'] ?? [],
        [
            'Mindray BA 88A Glucose Reagent 4x50ml',
            'Mindray BA 88A Urea/BUN Kinetic Kit',
            'Mindray BA 88A Creatinine Jaffe Reagent',
            'Mindray BC5000 CBC Diluent 20L',
            'Mindray BC5000 Lyse Reagent 500ml',
            'Getein 1160 TSH Test Cartridges (25T)',
            'Finecare Plus Troponin I Rapid Cassettes',
            'Coagulometer PT/INR Thromboplastin Reagent',
            'Blood Grouping Antisera Anti-A / Anti-B',
            'Widal Rapid Titre Diagnostic Antigen',
            'Vacutainer K2-EDTA Purple Top Tubes (100s)',
            'Vacutainer Clot Activator Plain Red Tubes',
            'Vacutainer Sodium Citrate Blue Tubes (PT)',
            'Immersion Oil 100x Microscope Grade',
        ]
    ));
    $labBrands = ['Mindray', 'Sysmex', 'Getein', 'Finecare', 'BD Vacutainer', 'Biolabo', 'Human Diagnostics', 'Roche', 'Spinreact'];
    $unitOptions = ['Box', 'Kit', 'Bottle', 'Piece', 'Bag', 'Pack', 'Vial', 'Roll', 'Carton'];
@endphp

<!-- Fast Selection Datalists for Inventory -->
<datalist id="lab-reagents-inventory-datalist">
    @foreach($commonReagents as $reagent)
        <option value="{{ $reagent }}"></option>
    @endforeach
</datalist>

<datalist id="lab-brands-datalist">
    @foreach($labBrands as $brand)
        <option value="{{ $brand }}"></option>
    @endforeach
</datalist>

<datalist id="lab-suppliers-datalist">
    @foreach($labSuppliers as $sup)
        <option value="{{ $sup }}"></option>
    @endforeach
</datalist>

<div class="space-y-6">

    {{-- Inventory Receiving Top Bar --}}
    <div class="hc-card">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 mb-4 border-b border-[var(--hc-border)]">
            <div class="flex items-center gap-2">
                <span class="hc-badge hc-badge-brand">SECTION G — LABORATORY INVENTORY</span>
                <div>
                    <h3 class="text-sm font-bold tracking-tight text-[var(--hc-text)]">
                        Received Laboratory Reagents &amp; Supplies Register
                    </h3>
                    <p class="text-xs text-[var(--hc-text-muted)]">
                        Select reagents, brands, and suppliers with zero typing needed
                    </p>
                </div>
            </div>
            <button type="button" @click="addReceivedSupply()" class="btn btn-secondary btn-sm">
                <i data-lucide="package-plus" class="w-3.5 h-3.5"></i>
                <span>Register Received Supply</span>
            </button>
        </div>

        <template x-if="receivedSupplies.length === 0">
            <div class="p-8 text-center border border-dashed border-[var(--hc-border)] rounded-md bg-[var(--hc-surface-alt)]">
                <i data-lucide="package" class="w-8 h-8 mx-auto text-[var(--hc-text-muted)] mb-2"></i>
                <p class="text-xs font-semibold text-[var(--hc-text)]">No supplies or reagents delivered on this date.</p>
                <p class="text-[11px] text-[var(--hc-text-muted)] mt-1">When delivery orders arrive from suppliers, click "Register Received Supply" to log lot, expiry, and supplier representative.</p>
            </div>
        </template>

        <template x-if="receivedSupplies.length > 0">
            <div class="space-y-4">
                <template x-for="(inv, idx) in receivedSupplies" :key="'inv-card-'+idx">
                    <div class="hc-card bg-[var(--hc-surface)] border border-[var(--hc-border)] p-4 space-y-3">
                        {{-- Top Header Row of Supply Card --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-[var(--hc-border)]">
                            <div class="flex items-center gap-2 flex-1">
                                <span class="hc-badge hc-badge-muted" x-text="'Item #' + (idx + 1)"></span>
                                <input type="text" list="lab-reagents-inventory-datalist" x-model="inv.item_name" class="hc-input font-bold text-sm w-full max-w-md"
                                       placeholder="Select or enter reagent/item name...">
                            </div>
                            <div class="flex items-center gap-2">
                                <select x-model="inv.acceptance_status" class="hc-input hc-select text-xs font-bold w-48"
                                        :style="inv.acceptance_status === 'Accepted & Stocked' ? 'color: var(--hc-success);' : (inv.acceptance_status === 'Rejected & Returned' ? 'color: var(--hc-danger);' : 'color: var(--hc-warning);')">
                                    <option value="Accepted & Stocked">Accepted &amp; Stocked</option>
                                    <option value="Quarantined - Pending QC">Quarantined - Pending QC</option>
                                    <option value="Rejected & Returned">Rejected &amp; Returned</option>
                                </select>
                                <button type="button" @click="removeReceivedSupply(idx)" class="btn btn-danger btn-xs" title="Remove delivery entry">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Details Grid --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                            <div>
                                <label class="hc-label">Category</label>
                                <select x-model="inv.category" class="hc-input hc-select text-xs">
                                    <option value="Biochemistry Reagent">Biochemistry Reagent</option>
                                    <option value="Hematology Reagent">Hematology Reagent</option>
                                    <option value="Rapid Test Kit">Rapid Test Kit</option>
                                    <option value="Blood Collection Consumable">Blood Collection Consumable</option>
                                    <option value="Control / Calibrator">Control / Calibrator</option>
                                    <option value="Safety & PPE">Safety &amp; PPE</option>
                                </select>
                            </div>
                            <div>
                                <label class="hc-label">Brand / Maker</label>
                                <input type="text" list="lab-brands-datalist" x-model="inv.brand" class="hc-input text-xs font-medium" placeholder="Mindray / Sysmex / BD">
                            </div>
                            <div>
                                <label class="hc-label">Quantity + Unit</label>
                                <div class="flex items-center gap-1">
                                    <input type="number" min="1" x-model.number="inv.quantity" class="hc-input text-center font-bold text-xs w-16" placeholder="1">
                                    <select x-model="inv.unit" class="hc-input hc-select text-xs flex-1">
                                        @foreach($unitOptions as $u)
                                            <option value="{{ $u }}">{{ $u }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="hc-label">Lot / Batch #</label>
                                <input type="text" x-model="inv.lot_number" class="hc-input font-mono text-xs font-semibold" placeholder="LOT-XXXX">
                            </div>
                            <div>
                                <label class="hc-label">Expiry Date</label>
                                <input type="date" x-model="inv.expiry_date" class="hc-input font-mono text-xs">
                            </div>
                            <div>
                                <label class="hc-label">Cold-Chain Storage</label>
                                <select x-model="inv.storage_condition" class="hc-input hc-select text-xs">
                                    <option value="Cold Chain (2°C - 8°C)">Cold Chain (2°C - 8°C)</option>
                                    <option value="Frozen (-20°C)">Frozen (-20°C)</option>
                                    <option value="Ambient (15°C - 25°C)">Ambient (15°C - 25°C)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Supplier & Chain of Custody --}}
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-2 border-t border-[var(--hc-border)] bg-[var(--hc-surface-alt)] p-3 rounded-md text-xs">
                            <div>
                                <label class="hc-label">Supplier / Vendor Company</label>
                                <input type="text" list="lab-suppliers-datalist" x-model="inv.supplier_name" class="hc-input text-xs" placeholder="Select or type supplier">
                            </div>
                            <div>
                                <label class="hc-label">Supplier Representative</label>
                                <input type="text" x-model="inv.supplier_representative" class="hc-input text-xs" placeholder="Delivery driver / rep">
                            </div>
                            <div>
                                <label class="hc-label">Invoice / PO / Delivery Note #</label>
                                <input type="text" x-model="inv.invoice_po_ref" class="hc-input font-mono text-xs" placeholder="PO-2026-XXXX">
                            </div>
                            <div>
                                <label class="hc-label">Received In-House By</label>
                                <div class="text-xs font-semibold text-[var(--hc-text)] mt-1" x-text="inv.who_received || currentUser"></div>
                                <div class="text-[10px] font-mono text-[var(--hc-text-muted)]" x-text="inv.received_date || reportDate"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>

</div>

