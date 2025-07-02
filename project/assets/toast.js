function showToast(message, type = 'primary') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '1100';
        document.body.appendChild(container);
    }
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-bg-${type} border-0`;
    toastEl.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    container.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

function showConfirm(message) {
    return new Promise(resolve => {
        let modal = document.getElementById('confirm-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = 'confirm-modal';
            modal.tabIndex = -1;
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content bg-dark text-light">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body confirm-message"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary btn-ok">OK</button>
                        </div>
                    </div>
                </div>`;
            document.body.appendChild(modal);
        }

        modal.querySelector('.confirm-message').textContent = message;

        const bsModal = bootstrap.Modal.getOrCreateInstance(modal);

        const okBtn = modal.querySelector('.btn-ok');
        const cancelBtn = modal.querySelector('.btn-secondary');

        const cleanup = () => {
            okBtn.removeEventListener('click', okHandler);
            cancelBtn.removeEventListener('click', cancelHandler);
        };

        const okHandler = () => {
            cleanup();
            bsModal.hide();
            resolve(true);
        };
        const cancelHandler = () => {
            cleanup();
            bsModal.hide();
            resolve(false);
        };

        okBtn.addEventListener('click', okHandler);
        cancelBtn.addEventListener('click', cancelHandler);

        bsModal.show();
    });
}
