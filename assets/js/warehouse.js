/**
 * IQA Metal — Warehouse Control Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    initWarehouseDatalists();
});

/**
 * Initializes the brand/model/series datalists using the shared inventory data.
 * Contextually switches between laptops and gaming.
 */
function initWarehouseDatalists() {
    const brandIn = document.getElementById('wh-brand');
    const modelIn = document.getElementById('wh-model');
    const modelDl = document.getElementById('model-options');
    const brandDl = document.getElementById('brand-options');
    const seriesDl = document.getElementById('series-options');

    // Determine target inventory based on active sector
    let targetInventory = IQA_LaptopInventory;
    if (window.activeSector === 'Gaming') targetInventory = IQA_GamingInventory;
    // For general/electronics we might still use a fall-back or merged list if needed
    // but for now, we follow the split.

    // Populate Brands
    if (brandDl) {
        brandDl.innerHTML = Object.keys(targetInventory).map(b => `<option value="${b}">`).join('');
    }

    if (brandIn) {
        brandIn.addEventListener('change', (e) => {
            const selectedBrand = e.target.value;
            const data = targetInventory[selectedBrand];
            
            if (modelIn) modelIn.value = '';
            if (modelDl) modelDl.innerHTML = '';
            if (seriesDl) seriesDl.innerHTML = '';

            if (data) {
                if (modelDl) modelDl.innerHTML = data.models.map(m => `<option value="${m}">`).join('');
                if (seriesDl) seriesDl.innerHTML = (data.series || []).map(s => `<option value="${s}">`).join('');
            }
        });
    }
}

/**
 * Toggles visibility and labels of gaming-specific fields based on category
 */
function toggleGamingFields() {
    const cat = document.getElementById('wh-gaming-cat');
    const pcFields = document.getElementById('wh-gaming-pc-fields');
    const specLabel = document.getElementById('wh-gaming-spec-label');
    const seriesIn = document.getElementById('wh-series');
    const ramIn = document.getElementById('wh-ram');
    const storageIn = document.getElementById('wh-storage');
    
    if (!cat) return;

    const val = cat.value;

    // 1. Handle PC vs Others visibility
    if (pcFields) pcFields.style.display = (val === 'PC' ? 'block' : 'none');

    // 2. Dynamic Labeling for Specs
    if (!specLabel || !seriesIn || !ramIn || !storageIn) return;

    // Reset defaults
    ramIn.style.display = 'block';
    storageIn.style.display = 'block';
    seriesIn.placeholder = 'Series / Edition';
    specLabel.innerText = 'Specs / Series';

    if (val === 'Consoles') {
        specLabel.innerText = 'Series / Edition';
        seriesIn.placeholder = 'e.g. Slim / Pro / Disc / Digital';
        ramIn.placeholder = 'Color / Region';
        storageIn.placeholder = 'Capacity (1TB/512GB)';
    } else if (val === 'Controllers') {
        specLabel.innerText = 'Controller Specs';
        seriesIn.placeholder = 'e.g. DualSense / Elite';
        ramIn.placeholder = 'Color (Midnight Black)';
        storageIn.style.display = 'none'; // Controllers don't have storage
    } else if (val === 'Games') {
        specLabel.innerText = 'Game Edition';
        seriesIn.placeholder = 'e.g. Deluxe / Steelbook';
        ramIn.style.display = 'none'; // Games don't have RAM
        storageIn.style.display = 'none'; // Games don't have Storage
    } else if (val === 'PC') {
        specLabel.innerText = 'Additional Specs';
    }
}

/**
 * Filters the warehouse inventory list based on search input
 */
function filterWarehouse() {
    const searchInput = document.getElementById('wh-search');
    if (!searchInput) return;

    const filter = searchInput.value.toLowerCase();
    const cards = document.getElementsByClassName('inventory-card');
    
    let visibleQtyTotal = 0;

    for (let i = 0; i < cards.length; i++) {
        const text = cards[i].getAttribute('data-search') || "";
        if (text.toLowerCase().includes(filter)) {
            cards[i].style.display = "";
            const qtyPill = cards[i].querySelector('.qty-pill');
            if (qtyPill) {
                visibleQtyTotal += parseInt(qtyPill.innerText, 10) || 0;
            }
        } else {
            cards[i].style.display = "none";
        }
    }
    
    // Update the total qty row if it exists
    const totalQtyElem = document.getElementById('table-total-qty');
    if (totalQtyElem) {
        totalQtyElem.innerText = visibleQtyTotal.toLocaleString();
    }
}

/**
 * Handles editing an existing warehouse item
 * Pre-fills the form and switches to update mode
 */
function editWarehouseItem(item) {
    const form = document.getElementById('wh-main-form');
    const title = document.getElementById('wh-form-title');
    const action = document.getElementById('wh-form-action');
    const editId = document.getElementById('wh-edit-id');
    const submitBtn = document.getElementById('wh-submit-btn');
    const cancelBtn = document.getElementById('wh-cancel-edit');

    if (!form || !title || !action || !editId || !submitBtn || !cancelBtn) return;

    // 1. Switch Form Mode
    title.innerText = '📝 Update Inventory';
    action.value = 'edit_inventory';
    editId.value = item.id;
    submitBtn.innerText = '💾 Save Changes';
    cancelBtn.style.display = 'block';

    // 2. Pre-fill Common Fields
    form.brand.value = item.brand;
    form.model.value = item.model;
    form.quantity.value = item.quantity;

    // 3. Pre-fill Specs (parsing JSON)
    const specs = JSON.parse(item.specs_json || '{}');
    if (form.condition) form.condition.value = specs.condition || 'Used';
    if (form.notes) form.notes.value = specs.notes || '';

    // Sector Specifics
    if (item.sector === 'Laptops') {
        if (form.cpu) form.cpu.value = specs.cpu || '';
        if (form.gpu) form.gpu.value = specs.gpu || '';
        if (form.ram) form.ram.value = specs.ram || '';
        if (form.storage) form.storage.value = specs.storage || '';
        if (form.battery) form.battery.value = specs.battery || '';
        if (form.windows) form.windows.value = specs.windows || '';
        if (form.gen) form.gen.value = specs.gen || '';
        if (form.series) form.series.value = specs.series || '';
    } else if (item.sector === 'Gaming') {
        if (form.gaming_category) {
            form.gaming_category.value = specs.category || 'PC';
            toggleGamingFields(); // Trigger visibility
        }
        if (form.series) form.series.value = specs.series || '';
        if (form.ram) form.ram.value = specs.ram || '';
        if (form.storage) form.storage.value = specs.storage || '';
        if (form.cpu) form.cpu.value = specs.cpu || '';
        if (form.gpu) form.gpu.value = specs.gpu || '';
    }

    // Scroll to form for mobile UX
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/**
 * Resets the warehouse form back to 'Add' mode
 */
function resetWarehouseForm() {
    const form = document.getElementById('wh-main-form');
    const title = document.getElementById('wh-form-title');
    const action = document.getElementById('wh-form-action');
    const editId = document.getElementById('wh-edit-id');
    const submitBtn = document.getElementById('wh-submit-btn');
    const cancelBtn = document.getElementById('wh-cancel-edit');

    if (!form) return;

    form.reset();
    title.innerText = '📥 Register Stock';
    action.value = 'add_inventory';
    editId.value = '';
    submitBtn.innerText = '📥 Add to Stock';
    cancelBtn.style.display = 'none';

    // Trigger UI cleanup
    if (typeof toggleGamingFields === 'function') toggleGamingFields();
}

/**
 * Generates and downloads a CSV of the visible warehouse inventory with separated spec columns
 */
function downloadWarehouseCSV() {
    const cards = document.querySelectorAll('.inventory-card');
    
    // Updated to match the specified B2B structure
    const headers = ["Type", "Brand", "Model", "Series", "CPU / Gen", "Description", "Price", "QTY", "Total"];
    let csv = headers.map(h => `"${h}"`).join(",") + "\n";
    
    const sanitize = (val) => `"${(val || "").toString().trim().replace(/"/g, '""')}"`;
    let count = 0;

    cards.forEach(card => {
        // Only export visible items (respects search filter)
        if (card.style.display !== 'none') {
            const specs = JSON.parse(card.getAttribute('data-specs') || '{}');
            
            const brand = card.getAttribute('data-brand') || '';
            const model = card.getAttribute('data-model') || '';
            const qtyElement = card.querySelector('.qty-pill');
            const qty = qtyElement ? qtyElement.innerText.trim() : '0';
            
            // Map Warehouse specs to the simplified B2B columns
            const cpuGen = (specs.cpu || "") + (specs.gen ? " (" + specs.gen + ")" : "");
            const fullDesc = (specs.condition || "") + (specs.notes ? " - " + specs.notes : "");

            const rowData = [
                sanitize("Laptop"),               // Type
                sanitize(brand),                 // Brand
                sanitize(model),                 // Model
                sanitize(specs.series || ""),    // Series
                sanitize(cpuGen),                // CPU / Gen
                sanitize(fullDesc),              // Description
                "0.00",                          // Price (Not stored in warehouse)
                sanitize(qty),                   // QTY
                "0.00"                           // Total
            ];
            
            csv += rowData.join(",") + "\n";
            count++;
        }
    });

    if (count === 0) {
        alert("No visible items to export.");
        return;
    }

    const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    const dateStamp = new Date().toISOString().slice(0, 10);
    const sector = (window.activeSector || "Warehouse").replace(/\s+/g, '_');
    
    link.href = url;
    link.download = `IQA_Inventory_${sector}_${dateStamp}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
