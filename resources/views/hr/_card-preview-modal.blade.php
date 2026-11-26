@push('styles')
<style>
    .employee-card-preview-frame {
        display: block;
        width: 100%;
        height: 560px;
        border: 0;
        background: #edf1f5;
    }
    #employeeCardModal .modal-content {
        overflow: hidden;
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 20px 60px rgba(15, 35, 60, .22);
    }
    #employeeCardModal .modal-header { border-bottom-color: #edf1f5; }
</style>
@endpush

<div class="modal fade" id="employeeCardModal" tabindex="-1" aria-labelledby="employeeCardModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="employeeCardModalTitle"><i class="bi bi-person-vcard text-primary me-2"></i>Employee ID Card</h5>
                    <div class="small text-muted" id="employeeCardModalName">Preview and print the ID card</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe class="employee-card-preview-frame" id="employeeCardPreviewFrame" title="Employee ID card preview" src="about:blank"></iframe>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" id="employeeCardModalPrint"><i class="bi bi-printer me-1"></i>Print</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('employeeCardModal');
    const frame = document.getElementById('employeeCardPreviewFrame');
    const name = document.getElementById('employeeCardModalName');
    const printButton = document.getElementById('employeeCardModalPrint');

    modal?.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        frame.src = trigger.dataset.cardUrl;
        name.textContent = trigger.dataset.employeeName || 'Preview and print the ID card';
    });

    modal?.addEventListener('hidden.bs.modal', () => {
        frame.src = 'about:blank';
        name.textContent = 'Preview and print the ID card';
    });

    printButton?.addEventListener('click', () => {
        frame.contentWindow?.focus();
        frame.contentWindow?.print();
    });
});
</script>
@endpush
