<style>
@keyframes modalPopIn {
    0% { opacity: 0; transform: scale(0.95) translateY(-8px); }
    100% { opacity: 1; transform: scale(1) translateY(0); }
}
.custom-modal-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.7);
    z-index: 10050;
    backdrop-filter: blur(4px);
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.custom-modal-backdrop.is-active {
    display: flex !important;
}
</style>
<!-- GENEL GÜVENLİ SİLME ONAY MODALI (TARAYICI ENGELLEMELERİNDEN ETKİLENMEZ) -->
<div id="appConfirmDeleteModal" class="custom-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); z-index: 10050; backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
    <div class="custom-modal-dialog" style="max-width: 480px; width: 100%; background: #ffffff; border-radius: 14px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); overflow: hidden; animation: modalPopIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        <div style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); color: #ffffff; padding: 18px 22px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 22px; line-height: 1;">⚠️</span>
                <h3 id="appConfirmDeleteTitle" style="margin: 0; font-size: 15px; font-weight: 700; color: #ffffff;">
                    Silme İşlemini Onayla
                </h3>
            </div>
            <button type="button" onclick="closeAppConfirmDeleteModal()" style="background: none; border: none; color: #fecaca; font-size: 20px; cursor: pointer; padding: 0 4px; line-height: 1;">✕</button>
        </div>

        <div style="padding: 22px 24px;">
            <div id="appConfirmDeleteMessage" style="font-size: 13.5px; font-weight: 600; color: #0f172a; line-height: 1.5; margin-bottom: 10px;">
                Bu öğeyi silmek istediğinize emin misiniz?
            </div>
            <div id="appConfirmDeleteDetails" style="font-size: 12px; color: #64748b; line-height: 1.5; background: #f8fafc; border-left: 3px solid #cbd5e1; padding: 10px 12px; border-radius: 4px; margin-bottom: 18px;">
                <!-- Ek detaylar -->
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeAppConfirmDeleteModal()" style="padding: 7px 16px; font-weight: 600; font-size: 12.5px; border-radius: 6px; cursor: pointer;">
                    İptal / Vazgeç
                </button>
                <button type="button" id="appConfirmDeleteBtn" class="btn btn-danger btn-sm" style="padding: 7px 18px; font-weight: 700; font-size: 12.5px; background: #dc2626; border-color: #dc2626; color: #ffffff; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <span>🗑️</span> <span id="appConfirmDeleteBtnText">Evet, Sil</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.showAppConfirmModal = function(options) {
    options = options || {};
    const modal = document.getElementById('appConfirmDeleteModal');
    if (!modal) {
        if (confirm(options.message || 'Silmek istediğinize emin misiniz?')) {
            if (typeof options.onConfirm === 'function') options.onConfirm();
        }
        return;
    }

    document.getElementById('appConfirmDeleteTitle').textContent = options.title || 'Silme İşlemini Onayla';
    document.getElementById('appConfirmDeleteMessage').textContent = options.message || 'Bu öğeyi silmek istediğinize emin misiniz?';
    
    const detailsEl = document.getElementById('appConfirmDeleteDetails');
    if (options.details) {
        detailsEl.textContent = options.details;
        detailsEl.style.display = 'block';
    } else {
        detailsEl.style.display = 'none';
    }

    const btn = document.getElementById('appConfirmDeleteBtn');
    const btnText = document.getElementById('appConfirmDeleteBtnText');
    btnText.textContent = options.confirmButtonText || 'Evet, Sil';
    btn.disabled = false;

    // Tek seferlik onclick ataması
    btn.onclick = function() {
        btn.disabled = true;
        btnText.textContent = 'İşleniyor...';
        if (typeof options.onConfirm === 'function') {
            options.onConfirm();
        }
    };

    modal.style.display = 'flex';
    modal.classList.add('is-active');
};

window.closeAppConfirmDeleteModal = function() {
    const modal = document.getElementById('appConfirmDeleteModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('is-active');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('appConfirmDeleteModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeAppConfirmDeleteModal();
            }
        });
    }
});
</script>