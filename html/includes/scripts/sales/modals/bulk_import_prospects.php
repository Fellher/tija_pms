<?php
/**
 * Bulk Import Prospects Modal
 * CSV/Excel file upload with column mapping and validation
 */
?>

<!-- Bulk Import Modal -->
<div class="modal fade" id="bulkImportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Bulk Import Prospects</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="importStep1">
                    <h6 class="mb-3">Step 1: Upload File</h6>
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        Upload a CSV or Excel file containing prospect data. The file should include columns for prospect name, email, and other details.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select File</label>
                        <input type="file" class="form-control" id="importFile" accept=".csv,.xlsx,.xls">
                    </div>

                    <div class="mb-3">
                        <a href="<?= "{$base}downloads/prospect_import_template.csv" ?>" class="btn btn-sm btn-outline-primary">
                            <i class="ri-download-line me-1"></i> Download Template
                        </a>
                    </div>
                </div>

                <div id="importStep2" class="d-none">
                    <h6 class="mb-3">Step 2: Map Columns</h6>
                    <div class="alert alert-warning">
                        <i class="ri-alert-line me-2"></i>
                        Map the columns from your file to the prospect fields. Required fields are marked with *.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm" id="columnMappingTable">
                            <thead>
                                <tr>
                                    <th>File Column</th>
                                    <th>Maps To</th>
                                    <th>Sample Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Will be populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="importStep3" class="d-none">
                    <h6 class="mb-3">Step 3: Review & Import</h6>
                    <div id="importSummary"></div>
                    <div class="progress mt-3 d-none" id="importProgress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="importNextBtn">Next</button>
                <button type="button" class="btn btn-success d-none" id="importStartBtn">Start Import</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const importModal = document.getElementById('bulkImportModal');
    const importFile = document.getElementById('importFile');
    const importNextBtn = document.getElementById('importNextBtn');
    const importStartBtn = document.getElementById('importStartBtn');
    let currentImportStep = 1;
    let fileData = null;
    let columnMapping = {};

    const fieldOptions = {
        'salesProspectName': 'Prospect Name *',
        'prospectEmail': 'Email *',
        'prospectCaseName': 'Case Name *',
        'prospectPhone': 'Phone',
        'prospectWebsite': 'Website',
        'address': 'Address',
        'estimatedValue': 'Estimated Value',
        'probability': 'Probability',
        'businessUnitID': 'Business Unit ID *',
        'leadSourceID': 'Lead Source ID *',
        'industryID': 'Industry ID',
        'companySize': 'Company Size',
        'expectedCloseDate': 'Expected Close Date',
        'nextFollowUpDate': 'Next Follow-up Date',
        'sourceDetails': 'Source Details',
        'tags': 'Tags'
    };

    importFile.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            uploadFile(file);
        }
    });

    function uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'upload');

        importNextBtn.disabled = true;
        importNextBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';

        fetch('<?= $base ?>php/scripts/sales/import_prospects.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fileData = {
                    headers: data.data.headers,
                    data: data.data.preview,
                    rowCount: data.data.rowCount,
                    suggestedMapping: data.data.suggestedMapping
                };
                importNextBtn.disabled = false;
                importNextBtn.innerHTML = 'Next';
            } else {
                alert('Error: ' + data.message);
                importNextBtn.innerHTML = 'Next';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while uploading the file');
            importNextBtn.innerHTML = 'Next';
        });
    }

    importNextBtn.addEventListener('click', function() {
        if (currentImportStep === 1) {
            if (!fileData) {
                alert('Please select a file first');
                return;
            }
            showColumnMapping();
            currentImportStep = 2;
            importNextBtn.textContent = 'Review';
        } else if (currentImportStep === 2) {
            showImportSummary();
            currentImportStep = 3;
            importNextBtn.classList.add('d-none');
            importStartBtn.classList.remove('d-none');
        }
    });

    function showColumnMapping() {
        document.getElementById('importStep1').classList.add('d-none');
        document.getElementById('importStep2').classList.remove('d-none');

        const tbody = document.querySelector('#columnMappingTable tbody');
        tbody.innerHTML = '';

        fileData.headers.forEach((header, index) => {
            const row = document.createElement('tr');
            const sampleData = fileData.data.map(row => row[header] || '').join(', ');

            row.innerHTML = `
                <td><strong>${header}</strong></td>
                <td>
                    <select class="form-select form-select-sm column-map" data-header="${header}">
                        <option value="">-- Skip --</option>
                        ${Object.entries(fieldOptions).map(([key, label]) =>
                            `<option value="${key}" ${fileData.suggestedMapping[header] === key ? 'selected' : ''}>${label}</option>`
                        ).join('')}
                    </select>
                </td>
                <td><small class="text-muted">${sampleData.substring(0, 50)}...</small></td>
            `;
            tbody.appendChild(row);
        });

        document.querySelectorAll('.column-map').forEach(select => {
            const header = select.dataset.header;
            if (select.value) {
                columnMapping[header] = select.value;
            }
            select.addEventListener('change', function() {
                if (this.value) {
                    columnMapping[header] = this.value;
                } else {
                    delete columnMapping[header];
                }
            });
        });
    }

    function showImportSummary() {
        document.getElementById('importStep2').classList.add('d-none');
        document.getElementById('importStep3').classList.remove('d-none');

        const requiredFields = ['salesProspectName', 'prospectEmail'];
        const mappedFields = Object.values(columnMapping).filter(v => v);
        const missingRequired = requiredFields.filter(f => !mappedFields.includes(f));

        let summaryHTML = `
            <div class="alert alert-${missingRequired.length > 0 ? 'danger' : 'success'}">
                <h6>Import Summary</h6>
                <p>Total rows to import: <strong>${fileData.rowCount}</strong></p>
                <p>Mapped columns: <strong>${mappedFields.length}</strong></p>
        `;

        if (missingRequired.length > 0) {
            summaryHTML += `
                <div class="alert alert-danger mt-2">
                    <strong>Missing required fields:</strong>
                    <ul class="mb-0">
                        ${missingRequired.map(f => `<li>${fieldOptions[f]}</li>`).join('')}
                    </ul>
                </div>
            `;
            importStartBtn.disabled = true;
        } else {
            summaryHTML += `<p class="text-success mb-0"><i class="ri-checkbox-circle-line me-1"></i> All required fields mapped</p>`;
            importStartBtn.disabled = false;
        }

        summaryHTML += `</div>`;
        document.getElementById('importSummary').innerHTML = summaryHTML;
    }

    importStartBtn.addEventListener('click', function() {
        performImport();
    });

    function performImport() {
        const progressBar = document.querySelector('#importProgress .progress-bar');
        document.getElementById('importProgress').classList.remove('d-none');
        importStartBtn.disabled = true;
        importStartBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Importing...';

        // Prepare import data
        const formData = new FormData();
        formData.append('action', 'import');
        formData.append('columnMapping', JSON.stringify(columnMapping));

        // Get default values (you may want to add UI for these)
        formData.append('defaultBusinessUnitID', '<?= $userDetails->businessUnitID ?? 1 ?>');
        formData.append('defaultLeadSourceID', '1'); // Default lead source

        progressBar.style.width = '30%';

        fetch('<?= $base ?>php/scripts/sales/import_prospects.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            progressBar.style.width = '100%';

            if (data.success) {
                let resultHTML = `
                    <div class="alert alert-success">
                        <h6>Import Completed!</h6>
                        <p><strong>${data.data.successCount}</strong> prospects imported successfully</p>
                `;

                if (data.data.errorCount > 0) {
                    resultHTML += `
                        <p class="text-warning"><strong>${data.data.errorCount}</strong> rows had errors</p>
                        <details>
                            <summary>View Errors</summary>
                            <ul class="mt-2">
                                ${data.data.errors.map(err => `<li>${err}</li>`).join('')}
                                ${data.data.duplicates.map(dup => `<li>${dup}</li>`).join('')}
                            </ul>
                        </details>
                    `;
                }

                resultHTML += `</div>`;
                document.getElementById('importSummary').innerHTML = resultHTML;

                setTimeout(() => {
                    location.reload();
                }, 3000);
            } else {
                alert('Error: ' + data.message);
                importStartBtn.disabled = false;
                importStartBtn.innerHTML = 'Start Import';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during import');
            importStartBtn.disabled = false;
            importStartBtn.innerHTML = 'Start Import';
        });
    }

    // Reset on modal close
    importModal.addEventListener('hidden.bs.modal', function() {
        currentImportStep = 1;
        fileData = null;
        columnMapping = {};
        document.getElementById('importStep1').classList.remove('d-none');
        document.getElementById('importStep2').classList.add('d-none');
        document.getElementById('importStep3').classList.add('d-none');
        importNextBtn.classList.remove('d-none');
        importNextBtn.textContent = 'Next';
        importStartBtn.classList.add('d-none');
        importFile.value = '';
    });
});
</script>
