/**
 * IQA CRM — Leads & Interaction Logic
 */

function openLeadModal(lead) {
    document.getElementById('modal-company-name').innerText = lead.company_name;
    document.getElementById('modal-customer-id').innerText = lead.customer_id;
    document.getElementById('lead_customer_id').value = lead.customer_id;
    
    document.getElementById('lead_status').value = lead.account_status || 'Lead';
    document.getElementById('lead_source').value = lead.lead_source || '';
    document.getElementById('lead_interest').value = lead.interest || '';
    document.getElementById('lead_message_date').value = lead.message_date || '';
    document.getElementById('lead_method').value = lead.contact_method || '';
    document.getElementById('lead_callback_date').value = lead.callback_date || '';
    document.getElementById('lead_notes').value = lead.internal_notes || '';
    
    // Clear the new interaction note
    const newNote = document.getElementById('new_interaction_note');
    if (newNote) newNote.value = '';

    // Load Interaction History
    loadInteractionHistory(lead.customer_id);

    document.getElementById('leadModal').style.display = 'flex';
}

function closeLeadModal() {
    document.getElementById('leadModal').style.display = 'none';
}

async function loadInteractionHistory(customerId) {
    const historyContainer = document.getElementById('interaction-history');
    if (!historyContainer) return;

    historyContainer.innerHTML = '<div style="padding:20px; text-align:center; opacity:0.5;">Loading history...</div>';

    try {
        const response = await fetch(`api/get_interaction_logs.php?customer_id=${encodeURIComponent(customerId)}`);
        const logs = await response.json();

        if (logs.length === 0) {
            historyContainer.innerHTML = '<div style="padding:20px; text-align:center; opacity:0.5; font-size:0.8rem;">No previous interaction logs.</div>';
            return;
        }

        historyContainer.innerHTML = logs.map(log => `
            <div style="padding:12px; border-bottom:1px solid #f1f5f9; font-size:0.85rem;">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span style="font-weight:800; color:var(--accent-color);">${log.contact_date}</span>
                    <span style="font-weight:700; color:#94a3b8; font-size:0.7rem; text-transform:uppercase;">via ${log.method || 'Other'}</span>
                </div>
                <div style="color:var(--text-secondary); line-height:1.4;">${log.note}</div>
            </div>
        `).join('');

    } catch (err) {
        console.error("Failed to load history", err);
        historyContainer.innerHTML = '<div style="color:#ef4444; padding:20px; text-align:center;">Error loading history.</div>';
    }
}

async function saveLead(event) {
    event.preventDefault();
    const form = event.target;
    const btn = document.getElementById('btn-save-lead');
    const originalText = btn.innerText;

    try {
        btn.disabled = true;
        btn.innerText = 'Saving Changes...';

        const formData = new FormData(form);
        const response = await fetch('api/save_lead.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.status === 'success') {
            // Success animation or feedback
            btn.style.background = '#22c55e';
            btn.innerText = '✓ Saved Successfully';
            
            setTimeout(() => {
                location.reload(); // Reload to refresh table data
            }, 800);
        } else {
            throw new Error(data.error || 'Update failed');
        }

    } catch (err) {
        console.error("Save failed", err);
        alert("Failed to save lead: " + err.message);
        btn.disabled = false;
        btn.innerText = originalText;
    }
}

function filterLeads() {
    const input = document.getElementById('lead-search');
    if (!input) return;

    const filter = input.value.toLowerCase();
    const rows = document.getElementsByClassName('lead-row');

    for (let i = 0; i < rows.length; i++) {
        const searchBlob = rows[i].getAttribute('data-search') || "";
        rows[i].style.display = searchBlob.includes(filter) ? "" : "none";
    }
}
