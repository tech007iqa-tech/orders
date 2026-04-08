// ============================================================
// IQA Metal — Checkout Manifest JS
// Data variables (rawItems, customerName, orderID, orderDate)
// are injected inline by checkout.php before this file loads.
// ============================================================

async function handlePrintManifest() {
    const form = document.getElementById('checkout-form');
    const formData = new FormData(form);
    formData.set('action', 'update_items');
    formData.set('finalize_status', 'true');

    const printBtn = document.querySelector('.btn-print-action');
    const originalText = printBtn.innerHTML;

    try {
        printBtn.innerHTML = 'Saving...';
        printBtn.disabled = true;

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        if (response.ok) {
            printBtn.innerHTML = 'Preparing...';
            setTimeout(() => {
                window.print();
                printBtn.innerHTML = originalText;
                printBtn.disabled = false;
            }, 400);
        } else { throw new Error(); }
    } catch (err) {
        alert('Save failed. Try manually saving first.');
        printBtn.innerHTML = originalText;
        printBtn.disabled = false;
    }
}

function downloadCSV() {
    // Header Template (Quoted to prevent splitting on spaces)
    let csv = "\"IQA Metal B2B Purchase Form\",,,,,,,,\n\n";
    csv += `\"Name\",\"${customerName}\",,,,,,,\n`;
    csv += `\"Date\",\"${orderDate}\",,,,,,,\n`;
    csv += `\"Order #\",\"${orderID}\",,,,,,,\n\n`;

    // Column Headers
    csv += "\"Type\",\"Brand\",\"Model\",\"Series\",\"CPU / Gen\",\"Description\",\"Price\",\"QTY\",\"Total\"\n";

    let totalQty = 0;
    let grandTotal = 0;

    rawItems.forEach(item => {
        const row = document.querySelector(`input[name="quantities[${item.id}]"]`).closest('tr');
        const liveQty = parseInt(row.querySelector('.qty-input').value) || 0;
        const livePrice = parseFloat(row.querySelector('.price-input').value) || 0;
        const rowTotal = liveQty * livePrice;

        // Mandatory Quoting for all text fields
        const type  = `"${(item.type        || '').replace(/"/g, '""')}"`;
        const brand = `"${(item.brand       || '').replace(/"/g, '""')}"`;
        const model = `"${(item.model       || '').replace(/"/g, '""')}"`;
        const series= `"${(item.series      || '').replace(/"/g, '""')}"`;
        const cpu   = `"${(item.cpu         || '').replace(/"/g, '""')}"`;
        const desc  = `"${(item.description || '').replace(/"/g, '""')}"`;

        csv += `${type},${brand},${model},${series},${cpu},${desc},${livePrice},${liveQty},${rowTotal.toFixed(2)}\n`;
        totalQty  += liveQty;
        grandTotal += rowTotal;
    });

    // Footer (Aligned to QTY and Total columns)
    csv += `\n,,,,,,,,\"Total QTY\",\"${totalQty}\"\n`;
    csv += `,,,,,,,,\"Total Amount\",\"$${grandTotal.toFixed(2)}\"\n`;

    const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url  = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", `IQA_B2B_Form_${orderID}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function recalculateTotals() {
    let grandTotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qtyInput   = row.querySelector('.qty-input');
        const qty        = parseFloat(qtyInput.value) || 0;
        const priceInput = row.querySelector('.price-input');
        const price      = parseFloat(priceInput.value) || 0;
        const subtotal   = qty * price;
        row.querySelector('.row-subtotal').innerText = subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        const spans = row.querySelectorAll('.print-only');
        if (spans.length >= 2) {
            spans[0].innerText = qty;
            spans[1].innerText = '$' + price.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        grandTotal += subtotal;
    });
    document.getElementById('grand-total-display').innerText = grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function copyEntry(btn) {
    const container = btn.closest('.col-desc');
    const textNode  = container.querySelector('.copyable-text');
    const text      = textNode.innerText.trim();

    // Robust copy — works on mobile/Safari (no HTTPS required fallback)
    const performCopy = (textToCopy) => {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(textToCopy);
        } else {
            const textArea = document.createElement("textarea");
            textArea.value = textToCopy;
            textArea.style.position = "fixed";
            textArea.style.left = "-9999px";
            textArea.style.top  = "0";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try { document.execCommand('copy'); } catch (err) {}
            document.body.removeChild(textArea);
            return Promise.resolve();
        }
    };

    performCopy(text).then(() => {
        const originalText = btn.innerHTML;
        btn.innerHTML = '✅';
        btn.style.opacity = '1';
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.opacity = '0.4';
        }, 1500);
    });
}

// --- Modal Logic ---
function openEditModal(index) {
    const item = rawItems[index];
    if (!item) return;

    document.getElementById('editModal').classList.add('active');
    document.getElementById('modal-item-id').value = item.id;
    document.getElementById('modal-brand').value   = item.brand;
    document.getElementById('modal-model').value   = item.model;
    document.getElementById('modal-series').value  = item.series;
    document.getElementById('modal-cpu').value     = item.cpu;
    document.getElementById('modal-desc').value    = item.description;
    document.getElementById('modal-qty').value     = item.quantity;
    document.getElementById('modal-price').value   = parseFloat(item.unit_price).toFixed(2);
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

function saveItemChanges() {
    const formData = new FormData();
    formData.append('action',     'save_single_item');
    formData.append('item_id',    document.getElementById('modal-item-id').value);
    formData.append('brand',      document.getElementById('modal-brand').value);
    formData.append('model',      document.getElementById('modal-model').value);
    formData.append('series',     document.getElementById('modal-series').value);
    formData.append('cpu',        document.getElementById('modal-cpu').value);
    formData.append('description',document.getElementById('modal-desc').value);
    formData.append('quantity',   document.getElementById('modal-qty').value);
    formData.append('unit_price', document.getElementById('modal-price').value);

    const saveBtn  = document.querySelector('.modal-box .btn-main');
    const origText = saveBtn.innerText;
    saveBtn.innerText  = 'Syncing...';
    saveBtn.disabled   = true;

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    }).then(res => res.json()).then(data => {
        if (data.status === 'success') location.reload();
        else alert('Sync failed. Try again.');
    }).catch(err => {
        saveBtn.innerText = origText;
        saveBtn.disabled  = false;
        alert('Connection Lost.');
    });
}

function printLabel() {
    const form    = document.createElement('form');
    form.method   = 'POST';
    form.action   = 'generate_odt.php';

    const fields = ['brand', 'model', 'series', 'cpu', 'desc'];
    fields.forEach(f => {
        const hiddenField  = document.createElement('input');
        hiddenField.type   = 'hidden';
        // map "desc" → "description" for the backend
        hiddenField.name   = f === 'desc' ? 'description' : f;
        hiddenField.value  = document.getElementById('modal-' + f).value;
        form.appendChild(hiddenField);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function filterManifest() {
    const filter = document.getElementById('manifest-search').value.toLowerCase();
    const rows   = document.getElementsByClassName('item-row');

    for (let i = 0; i < rows.length; i++) {
        const text = rows[i].innerText.toLowerCase();
        rows[i].style.display = text.includes(filter) ? "" : "none";
    }
}
