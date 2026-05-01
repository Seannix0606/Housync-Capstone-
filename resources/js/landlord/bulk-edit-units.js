import { mountBulkEditPage } from './bulk-edit/app.js';

const el = document.getElementById('bulk-edit-config');
if (el) {
    try {
        const config = JSON.parse(el.textContent);
        mountBulkEditPage(config);
    } catch (e) {
        console.error('bulk-edit-units: invalid #bulk-edit-config JSON', e);
    }
}
