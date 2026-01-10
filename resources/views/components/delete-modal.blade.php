<!-- Delete Confirmation Modal (Bootstrap) -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">
                    <span id="delete-modal-title">Confirm Delete</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <!-- Circular danger badge with delete icon -->
                <div class="mb-3">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                        style="width: 80px; height: 80px;">
                        <i class="ri-delete-bin-2-line fs-1 text-danger"></i>
                    </div>
                </div>

                <p id="delete-modal-message" class="mb-0">
                    Are you sure you want to delete this <strong id="delete-record-name"
                        class="fw-semibold">record</strong>?
                    <br><small class="text-muted">This action cannot be undone.</small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" id="delete-confirm-btn" class="btn btn-danger"
                    onclick="window.DeleteModal.confirm()">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for AJAX deletion -->
<form id="delete-modal-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
    // Initialize delete modal after DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Delete modal script initializing...');

        // Check if Bootstrap is loaded and elements exist
        let retryCount = 0;
        const maxRetries = 50; // 5 seconds max

        function checkBootstrapAndInit() {
            // Check if modal elements exist
            const modalElement = document.getElementById('deleteConfirmationModal');
            const titleElement = document.getElementById('delete-modal-title');

            if (!modalElement || !titleElement) {
                retryCount++;
                if (retryCount < maxRetries) {
                    console.log('Modal elements not found yet, retrying... (' + retryCount + '/' + maxRetries +
                        ')');
                    setTimeout(checkBootstrapAndInit, 100);
                } else {
                    console.error('Modal elements not found after ' + maxRetries + ' retries!');
                    alert('Delete modal failed to load. Please refresh the page.');
                }
                return;
            }

            // Check if Bootstrap is loaded
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                console.log('Bootstrap loaded, initializing delete modal');
                initDeleteModal();
            } else {
                retryCount++;
                if (retryCount < maxRetries) {
                    console.log('Bootstrap not ready, retrying... (' + retryCount + '/' + maxRetries + ')');
                    setTimeout(checkBootstrapAndInit, 100);
                } else {
                    console.error('Bootstrap not loaded after ' + maxRetries + ' retries!');
                    alert('Bootstrap failed to load. Please refresh the page.');
                }
            }
        }

        function initDeleteModal() {
            // Verify all required elements exist before initializing
            const requiredElements = {
                title: document.getElementById('delete-modal-title'),
                message: document.getElementById('delete-modal-message'),
                form: document.getElementById('delete-modal-form'),
                recordName: document.getElementById('delete-record-name'),
                modal: document.getElementById('deleteConfirmationModal')
            };

            const missingElements = Object.entries(requiredElements)
                .filter(([name, el]) => !el)
                .map(([name]) => name);

            if (missingElements.length > 0) {
                console.error('Delete modal elements missing:', missingElements);
                console.log('Available elements:', requiredElements);
                alert('Delete modal not properly loaded. Missing: ' + missingElements.join(', '));
                return;
            }

            console.log('All delete modal elements found, initializing...');

            // Global delete modal functionality
            window.DeleteModal = {
                show: function(title, message, formId, recordName = 'record') {
                    console.log('DeleteModal.show called with:', {
                        title,
                        message,
                        formId,
                        recordName
                    });

                    // Check if all required elements exist
                    const titleEl = document.getElementById('delete-modal-title');
                    const messageEl = document.getElementById('delete-modal-message');
                    const formEl = document.getElementById('delete-modal-form');
                    const recordNameEl = document.getElementById('delete-record-name');
                    const modalElement = document.getElementById('deleteConfirmationModal');

                    if (!titleEl || !messageEl || !formEl || !recordNameEl || !modalElement) {
                        console.error('Delete modal elements not found!', {
                            titleEl: !!titleEl,
                            messageEl: !!messageEl,
                            formEl: !!formEl,
                            recordNameEl: !!recordNameEl,
                            modalElement: !!modalElement
                        });
                        alert('Delete modal not properly loaded. Please refresh the page.');
                        return;
                    }

                    // Set content
                    titleEl.textContent = title;
                    messageEl.textContent = message;
                    formEl.action = formId;
                    recordNameEl.textContent = recordName;

                    try {
                        // Check if Bootstrap is available
                        if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                            console.error('Bootstrap Modal not available!');
                            alert('Bootstrap not loaded. Please refresh the page.');
                            return;
                        }

                        const modal = new bootstrap.Modal(modalElement);
                        modal.show();
                        console.log('Modal shown successfully');
                    } catch (error) {
                        console.error('Error showing modal:', error);
                        alert('Error showing delete modal: ' + error.message);
                    }
                },

                hide: function() {
                    try {
                        const modalElement = document.getElementById('deleteConfirmationModal');
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        }
                    } catch (error) {
                        console.error('Error hiding modal:', error);
                    }
                },

                confirm: function() {
                    const form = document.getElementById('delete-modal-form');
                    const formData = new FormData(form);

                    // Show loading state
                    const confirmBtn = document.getElementById('delete-confirm-btn');
                    const originalText = confirmBtn.innerHTML;
                    confirmBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i> Deleting...';
                    confirmBtn.disabled = true;

                    fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Success - reload page or redirect
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                } else {
                                    window.location.reload();
                                }
                            } else {
                                // Error - show message
                                this.showError(data.message || 'Failed to delete record');
                            }
                        })
                        .catch(error => {
                            console.error('Delete error:', error);
                            this.showError('An error occurred while deleting the record');
                        })
                        .finally(() => {
                            // Reset button state
                            confirmBtn.innerHTML = originalText;
                            confirmBtn.disabled = false;
                            this.hide();
                        });
                },

                showError: function(message) {
                    alert('Error: ' + message);
                }
            };

            // Attach click handlers for delete buttons
            document.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('[data-delete-modal]');
                if (deleteBtn) {
                    e.preventDefault();
                    console.log('Delete button clicked - FOUND!', deleteBtn);

                    const title = deleteBtn.getAttribute('data-title') || 'Confirm Delete';
                    const message = deleteBtn.getAttribute('data-message') ||
                        'Are you sure you want to delete this record?';
                    const formId = deleteBtn.getAttribute('data-form-id');
                    const recordName = deleteBtn.getAttribute('data-record-name') || 'record';

                    console.log('Modal data:', {
                        title,
                        message,
                        formId,
                        recordName
                    });
                    window.DeleteModal.show(title, message, formId, recordName);
                }
            });

            console.log('Delete modal initialized successfully');
            console.log('Modal element exists:', !!document.getElementById('deleteConfirmationModal'));
            console.log('Delete buttons found:', document.querySelectorAll('[data-delete-modal]').length);
        }

        checkBootstrapAndInit();
    });
</script>
