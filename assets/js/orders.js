/**
 * IQA Metal — Orders Registry Logic
 */

/**
 * Filters the global batch list based on search input
 */
function filterOrders() {
    const input = document.getElementById('order-search');
    if (!input) return;

    const filter = input.value.toLowerCase();
    const cards = document.getElementsByClassName('order-card');
    let hasResults = false;

    for (let i = 0; i < cards.length; i++) {
        const searchBlob = cards[i].getAttribute('data-search') || "";
        if (searchBlob.includes(filter)) {
            cards[i].style.display = "";
            hasResults = true;
        } else {
            cards[i].style.display = "none";
        }
    }

    // Handle empty state during search
    let emptyState = document.querySelector('.orders-empty-state');
    const grid = document.getElementById('orders-grid');

    if (!hasResults) {
        if (!emptyState && grid) {
            emptyState = document.createElement('div');
            emptyState.className = 'orders-empty-state';
            emptyState.style.cssText = 'grid-column: 1/-1; padding: 60px; text-align: center; background: white; border-radius: 20px; border: 2px dashed #eee; color: #94a3b8; font-weight: 600;';
            grid.appendChild(emptyState);
        }
        
        if (emptyState) {
            emptyState.style.display = 'block';
            emptyState.innerText = `No batches found matching "${input.value}"`;
        }
    } else if (emptyState) {
        emptyState.style.display = 'none';
    }
}

/**
 * Updates the order status via AJAX
 * @param {HTMLSelectElement} select 
 * @param {string} orderId 
 */
async function updateOrderStatus(select, orderId) {
    const newStatus = select.value;
    const originalValue = select.getAttribute('data-original-value');
    const badge = select.closest('.order-card').querySelector('.order-badge');

    try {
        select.disabled = true;
        if (badge) badge.style.opacity = '0.5';

        const formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('new_status', newStatus);

        const response = await fetch('api/update_order_status.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.status === 'success') {
            // Update UI
            select.setAttribute('data-original-value', newStatus);
            if (badge) {
                // Remove old status classes
                badge.className = 'order-badge status-' + newStatus.toLowerCase();
                badge.innerText = newStatus;
                badge.style.opacity = '1';
            }
        } else {
            throw new Error(data.error || 'Update failed');
        }
    } catch (err) {
        console.error("Status update failed", err);
        alert('Failed to update status: ' + err.message);
        select.value = originalValue; // Revert
        if (badge) badge.style.opacity = '1';
    } finally {
        select.disabled = false;
    }
}

/**
 * Transfers an order to a new customer via AJAX
 */
async function transferOrder(event) {
    event.preventDefault();
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const origText = submitBtn.innerText;

    try {
        submitBtn.disabled = true;
        submitBtn.innerText = 'Transferring...';

        const formData = new FormData(form);
        const response = await fetch('api/transfer_order.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.status === 'success') {
            alert('Order transferred successfully!');
            location.reload(); // Reload to reflect changes in the list
        } else {
            throw new Error(data.error || 'Transfer failed');
        }
    } catch (err) {
        console.error("Transfer failed", err);
        alert('Failed to transfer order: ' + err.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerText = origText;
    }
}
