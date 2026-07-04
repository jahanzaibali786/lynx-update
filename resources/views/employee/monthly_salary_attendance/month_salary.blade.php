@extends('layouts.admin')

@section('page-title')
    {{ __('Monthly Salary Detail') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Monthly Salary') }}</li>
@endsection
@push('css-page')
    <style>
        .attendance-summary-badges .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 30px;
            font-size: 12.5px;
            line-height: 1.2;
            padding: 7px 12px;
            font-weight: 600;
            border-radius: 999px;
            letter-spacing: 0;
        }

        .attendance-summary-badges .badge-count {
            font-weight: 800;
            font-size: 13px;
        }

        .report-download-loader {
            position: fixed;
            right: 24px;
            top: 77px;
            z-index: 20000;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .report-download-loader.d-none {
            display: none !important;
        }

        .report-download-loader-box {
            min-width: 210px;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 14px 38px rgba(15, 23, 42, 0.18);
            text-align: center;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        table th,
        table td {
            padding: 8px !important;
        }


    </style>
@endpush

@push('script-page')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let monthlySalaryProcessing = false;

        function startMonthlySalaryAction(button, title) {
            if (monthlySalaryProcessing) {
                return false;
            }

            monthlySalaryProcessing = true;
            $('.salary-action-btn').addClass('disabled').attr('aria-disabled', 'true');
            $(button).data('original-html', $(button).html()).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

            Swal.fire({
                title: title || 'Processing...',
                text: 'Please wait.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading(),
            });

            return true;
        }

        function stopMonthlySalaryAction() {
            monthlySalaryProcessing = false;
            $('.salary-action-btn').each(function() {
                $(this).removeClass('disabled').removeAttr('aria-disabled');
                if ($(this).data('original-html')) {
                    $(this).html($(this).data('original-html'));
                    $(this).removeData('original-html');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.generate-btn').forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    if (monthlySalaryProcessing) {
                        return;
                    }
                    var checkedCheckboxes = document.querySelectorAll(
                        '.row-checkbox:checked'
                    );

                    var form = document.getElementById('employee_submit');
                    var formData = new FormData(form);
                    checkedCheckboxes.forEach(function(checkbox) {
                        var row = checkbox.closest('tr');
                        var employeeId = row.getAttribute('data-employee-id');
                        formData.append('employee_ids[]', employeeId);
                    });
                    var selectedDate = document.querySelector('#date').value;
                    formData.append('date', selectedDate);
                    if (checkedCheckboxes.length > 0) {
                        if (!startMonthlySalaryAction(this, 'Generating salary...')) {
                            return;
                        }
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: "{{ route('month_salary_generate') }}",
                            type: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(result) {
                                if (result.success) {
                                    // alert(result.message);
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Salary Generated',
                                        text: result.message,
                                        confirmButtonText: 'OK',
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                    // window.location.reload();
                                } else {
                                    // console.error(result.message);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: result.message,
                                        confirmButtonText: 'OK',
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                    // window.location.reload();
                                }
                            },
                            error: function(xhr, status, error) {
                                // console.error(error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: error,
                                    confirmButtonText: 'OK',
                                }).then(() => {
                                    window.location.reload();
                                });
                            },
                            complete: function() {
                                stopMonthlySalaryAction();
                            }
                        });
                    } else {
                        // alert("Please select at least one row to generate.");
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Rows Selected',
                            text: 'Please select at least one row to generate.',
                            confirmButtonText: 'OK',
                        });
                    }
                });
            });
            document.querySelector('.hold-unhold-btn').addEventListener('click', function(event) {
                event.preventDefault();
                if (monthlySalaryProcessing) {
                    return;
                }
                var checkedCheckboxes = document.querySelectorAll(
                    '.row-checkbox:checked'
                );
                var form = document.getElementById('employee_submit');
                var formData = new FormData(form);
                checkedCheckboxes.forEach(function(checkbox) {
                    var row = checkbox.closest('tr');
                    var employeeId = row.getAttribute('data-employee-id');
                    formData.append('employee_ids[]', employeeId);
                });
                var selectedDate = document.querySelector('#date').value;
                formData.append('date', selectedDate);
                if (checkedCheckboxes.length > 0) {
                    if (!startMonthlySalaryAction(this, 'Processing hold/unhold...')) {
                        return;
                    }
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('salary_hold_unhold') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(result) {
                            if (result.success) {
                                // alert(result.message);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Salary Hold/Unhold',
                                    text: result.message,
                                    confirmButtonText: 'OK',
                                }).then(() => {
                                    window.location.reload();
                                });
                                // window.location.reload();
                            } else {
                                // console.error(result.message);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.message,
                                    confirmButtonText: 'OK',
                                }).then(() => {
                                    window.location.reload();
                                });
                                // window.location.reload();
                            }
                        },
                        error: function(xhr, status, error) {
                            // console.error(error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: error,
                                confirmButtonText: 'OK',
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        complete: function() {
                            stopMonthlySalaryAction();
                        }
                    });
                } else {
                    // alert("Please select at least one row to generate.");
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Rows Selected',
                        text: 'Please select at least one row to hold/unhold.',
                        confirmButtonText: 'OK',
                    });
                }
            });
            document.querySelector('.delete-salary-btn').addEventListener('click', function(event) {
                event.preventDefault();
                if (monthlySalaryProcessing) {
                    return;
                }
                var checkedCheckboxes = document.querySelectorAll(
    '.row-checkbox:checked'
);
                var form = document.getElementById('employee_submit');
                var formData = new FormData(form);
                checkedCheckboxes.forEach(function(checkbox) {
                    var row = checkbox.closest('tr');
                    var employeeId = row.getAttribute('data-employee-id');
                    formData.append('employee_ids[]', employeeId);
                });
                var selectedDate = document.querySelector('#date').value;
                formData.append('date', selectedDate);
                if (checkedCheckboxes.length > 0) {
                    if (!startMonthlySalaryAction(this, 'Rolling back salary...')) {
                        return;
                    }
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('delete_salary') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(result) {
                            if (result.success) {
                                // alert(result.message);
                                // window.location.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Salary Deleted',
                                    text: result.message,
                                    confirmButtonText: 'OK',
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                // console.error(result.message);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.message,
                                    confirmButtonText: 'OK',
                                }).then(() => {
                                    window.location.reload();
                                });
                                // window.location.reload();
                            }
                        },
                        error: function(xhr, status, error) {
                            // console.error(error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: error,
                                confirmButtonText: 'OK',
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        complete: function() {
                            stopMonthlySalaryAction();
                        }
                    });
                } else {
                    // alert("Please select at least one row to generate.");
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Rows Selected',
                        text: 'Please select at least one row to delete.',
                        confirmButtonText: 'OK',
                    });
                }
            });

            document.querySelector('.pay-salary-btn').addEventListener('click', function(event) {
                event.preventDefault();
                if (monthlySalaryProcessing) {
                    return;
                }
                var checkedCheckboxes = document.querySelectorAll(
    '.row-checkbox:checked'
);
                var form = document.getElementById('employee_submit');
                var formData = new FormData(form);
                checkedCheckboxes.forEach(function(checkbox) {
                    var row = checkbox.closest('tr');
                    var employeeId = row.getAttribute('data-employee-id');
                    formData.append('employee_ids[]', employeeId);
                });
                var selectedDate = document.querySelector('#date').value;
                formData.append('date', selectedDate);
                if (checkedCheckboxes.length > 0) {
                    if (!startMonthlySalaryAction(this, 'Processing salary payment...')) {
                        return;
                    }
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('salary.payments') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(result) {
                            if (result.success) {
                                // alert(result.message);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Salary Paid',
                                    html: result.error,
                                    text: result.message,
                                    confirmButtonText: 'OK',
                                }).then(() => {
                                    window.location.reload();
                                });
                                // window.location.reload();
                            } else {
                                // console.error(result.message);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.message,
                                    confirmButtonText: 'OK',
                                }).then(() => {
                                    window.location.reload();
                                });
                                // window.location.reload();
                            }
                        },
                        error: function(xhr, status, error) {
                            // console.error(error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: error,
                                confirmButtonText: 'OK',
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        complete: function() {
                            stopMonthlySalaryAction();
                        }
                    });
                } else {
                    // alert("Please select at least one row to generate.");
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Rows Selected',
                        text: 'Please select at least one row to pay.',
                        confirmButtonText: 'OK',
                    });
                }
            });
        });
        // Check/uncheck all checkboxes
        if (document.getElementById('check-all')) {
            document.getElementById('check-all').addEventListener('change', function(event) {
                var checkboxes = document.querySelectorAll('.row-checkbox');
                checkboxes.forEach(function(checkbox) {
                    checkbox.checked = event.target.checked;
                });
            });
        }
        function collectSelectedSalaryRows() {
            if (monthlySalaryProcessing) {
                return [];
            }
            var checkedRows = [];
            var checkboxes = document.querySelectorAll('.row-checkbox:checked');
            var selectedDate = document.querySelector('#date') ? document.querySelector('#date').value : '';
            checkboxes.forEach(function(checkbox) {
                checkedRows.push({
                    id: checkbox.dataset.id || '',
                    employee_id: checkbox.dataset.employeeId || checkbox.value,
                    date: checkbox.dataset.date || selectedDate
                });
            });
            return checkedRows;
        }

        function submitSalaryFinalAction(button, action) {
            var checkedRows = collectSelectedSalaryRows();
            var selectedDate = document.querySelector('#date') ? document.querySelector('#date').value : '';
            var isUnfinalize = action === 'unfinalize';


            if (checkedRows.length > 0) {
                if (!startMonthlySalaryAction(button, isUnfinalize ? 'Unfinalizing salary...' : 'Finalizing salary...')) {
                    return;
                }
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ route('finalize_salary') }}",
                    type: "POST",
                    data: {
                        rows: checkedRows,
                        date: selectedDate,
                        action: action || 'finalize'
                    },
                    success: function(result) {
                        if (result.success) {
                            // alert(result.message);
                            Swal.fire({
                                icon: 'success',
                                title: isUnfinalize ? 'Salary UnFinalized' : 'Salary Finalized',
                                text: result.message,
                                confirmButtonText: 'OK',
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            // alert(result.message);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message,
                                confirmButtonText: 'OK',
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Request Failed',
                            text: error || 'Check console for details.',
                        });
                    },
                    complete: function() {
                        stopMonthlySalaryAction();
                    }
                });
            } else {
                // alert('No rows selected');
                Swal.fire({
                    icon: 'warning',
                    title: 'No Rows Selected',
                    text: isUnfinalize ? 'Please select at least one row to unfinalize.' : 'Please select at least one row to finalize.',
                    confirmButtonText: 'OK',
                });
            }
        }

        if (document.getElementById('sal-finalize-btn')) {
            document.getElementById('sal-finalize-btn').addEventListener('click', function(event) {
                event.preventDefault();
                submitSalaryFinalAction(this, 'finalize');
            });
        }

        if (document.getElementById('sal-unfinalize-btn')) {
            document.getElementById('sal-unfinalize-btn').addEventListener('click', function(event) {
                event.preventDefault();
                submitSalaryFinalAction(this, 'unfinalize');
            });
        }
    </script>
    <script>
        let reportDownloadLoaderTimer = null;

        function showReportDownloadLoader(message = 'Preparing report...') {
            const loader = document.getElementById('reportDownloadLoader');
            if (!loader) {
                return;
            }

            const text = loader.querySelector('[data-report-loader-text]');
            if (text) {
                text.textContent = message;
            }

            loader.classList.remove('d-none');
            clearTimeout(reportDownloadLoaderTimer);
            reportDownloadLoaderTimer = setTimeout(hideReportDownloadLoader, 15000);
        }

        function hideReportDownloadLoader() {
            const loader = document.getElementById('reportDownloadLoader');
            if (loader) {
                loader.classList.add('d-none');
            }

            clearTimeout(reportDownloadLoaderTimer);
            reportDownloadLoaderTimer = null;
        }

        window.addEventListener('pageshow', hideReportDownloadLoader);
        window.addEventListener('focus', function() {
            setTimeout(hideReportDownloadLoader, 700);
        });

        if (window.jQuery) {
            $(document).ajaxStop(hideReportDownloadLoader);
            $(document).ajaxError(hideReportDownloadLoader);
        }

        function generatedeductionsheet() {
            var form = document.getElementById('employee_submit');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('deduction_sheet') }}?" + queryString,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    const base64Pdf = response.base64Pdf;
                    const byteCharacters = atob(base64Pdf);
                    const byteNumbers = new Array(byteCharacters.length);
                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    const byteArray = new Uint8Array(byteNumbers);
                    const blob = new Blob([byteArray], {
                        type: 'application/pdf'
                    });
                    const blobUrl = URL.createObjectURL(blob);
                    window.open(blobUrl, '_blank');
                },
                error: function(xhr) {
                    // console.log(xhr.responseText);

                }
            });
        }

        function paymode() {
            var form = document.getElementById('employee_submit');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('paymode_sheet') }}?" + queryString,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    const base64Pdf = response.base64Pdf;
                    const byteCharacters = atob(base64Pdf);
                    const byteNumbers = new Array(byteCharacters.length);
                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    const byteArray = new Uint8Array(byteNumbers);
                    const blob = new Blob([byteArray], {
                        type: 'application/pdf'
                    });
                    const blobUrl = URL.createObjectURL(blob);
                    window.open(blobUrl, '_blank');
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

        function salarysheet(pdfAction = 'preview') {
            showReportDownloadLoader('Preparing salary sheet...');
            var form = document.getElementById('employee_submit');
            var formData = new FormData(form);
            formData.append('pdf_action', pdfAction);
            var queryString = new URLSearchParams(formData).toString();

            window.open("{{ route('salary_sheet') }}?" + queryString, '_blank');
        }

        function salary_slip() {
            showReportDownloadLoader('Preparing salary slip...');
            var form = document.getElementById('employee_submit');
            var formData = new FormData(form);

            // ➜ ADD: include only the selected row IDs as employee_ids[]
            if (!formData.has('employee_ids[]')) {
                document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
                    const id = cb.value || cb.closest('tr')?.getAttribute('data-employee-id');
                    if (id) formData.append('employee_ids[]', id);
                });
            }

            formData.append('export_type', 'pdf');
            var queryString = new URLSearchParams(formData).toString();
            window.open("{{ route('salary_slip') }}?" + queryString, '_blank');
        }


        function gross_sheet() {
            var form = document.getElementById('employee_submit');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('gross_sheet') }}?" + queryString,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    const base64Pdf = response.base64Pdf;
                    const byteCharacters = atob(base64Pdf);
                    const byteNumbers = new Array(byteCharacters.length);
                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    const byteArray = new Uint8Array(byteNumbers);
                    const blob = new Blob([byteArray], {
                        type: 'application/pdf'
                    });
                    const blobUrl = URL.createObjectURL(blob);
                    window.open(blobUrl, '_blank');
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

        function advance() {
            var form = document.getElementById('employee_submit');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('advance_sheet') }}?" + queryString,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    const base64Pdf = response.base64Pdf;
                    const byteCharacters = atob(base64Pdf);
                    const byteNumbers = new Array(byteCharacters.length);
                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    const byteArray = new Uint8Array(byteNumbers);
                    const blob = new Blob([byteArray], {
                        type: 'application/pdf'
                    });
                    const blobUrl = URL.createObjectURL(blob);
                    window.open(blobUrl, '_blank');
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

        // function handleSheetOption(select, sheetType) {
        //     const selectedOption = select.value;

        //     var urlprem = new URLSearchParams(window.location.search);
        //     console.log(urlprem);


        //     if (sheetType === 'salary') {
        //         if (selectedOption === "route") {
        //             const routeUrl = "{{ route('export_salary_sheet') }}?" + urlprem.toString();
        //             window.location.href = routeUrl;
        //         } else if (selectedOption === "onclick") {
        //             salarysheet();
        //         }
        //     } else if (sheetType === 'deduction') {
        //         if (selectedOption === "route") {
        //             const routeUrl = "{{ route('export_deduction_sheet') }}?" + urlprem.toString();
        //             window.location.href = routeUrl;
        //         } else if (selectedOption === "onclick") {
        //             generatedeductionsheet();
        //         }
        //     } else if (sheetType === 'paymode') {
        //         if (selectedOption === "route") {
        //             const routeUrl = "{{ route('export_paymode_sheet') }}?" + urlprem.toString();
        //             window.location.href = routeUrl;
        //         } else if (selectedOption === "onclick") {
        //             paymode();
        //         }
        //     } else if (sheetType === 'gross') {
        //         if (selectedOption === "route") {
        //             const routeUrl = "{{ route('export_gross_sheet') }}?" + urlprem.toString();
        //             window.location.href = routeUrl;
        //         } else if (selectedOption === "onclick") {
        //             gross_sheet();
        //         }
        //     } else if (sheetType === 'advance') {
        //         if (selectedOption === "route") {
        //             const routeUrl = "{{ route('export_advance_sheet') }}?" + urlprem.toString();
        //             window.location.href = routeUrl;
        //         } else if (selectedOption === "onclick") {
        //             advance();
        //         }
        //     } else if (sheetType === 'slip') {
        //         if (selectedOption === "route") {
        //             const routeUrl = "{{ route('export_advance_sheet') }}?" + urlprem.toString();
        //             window.location.href = routeUrl;
        //         } else if (selectedOption === "onclick") {
        //             salary_slip();
        //         }
        //     }
        // }
    </script>
    <script>
        document.getElementById('sheetAction').addEventListener('change', function() {
            const action = this.value;
            const sheet = document.getElementById('sheetType').value;
            if (!sheet || !action) return;
            showReportDownloadLoader(action === 'route' ? 'Preparing Excel report...' : 'Preparing report preview...');

            const routes = {
                salary: "{{ route('export_salary_sheet') }}",
                deduction: "{{ route('export_deduction_sheet') }}",
                paymode: "{{ route('export_paymode_sheet') }}",
                gross: "{{ route('export_gross_sheet') }}",
                advance: "{{ route('export_advance_sheet') }}",
            };

            if (action === 'route') {
                if (sheet === 'slip') {
                    // Build query from the form (includes checked employee_ids[])
                    const form = document.getElementById('employee_submit');
                    const fd = new FormData(form);

                    // (Optional fallback) If you didn't change the checkbox name above,
                    // gather manually from .row-checkbox using data-employee-id/value.
                    if (!fd.has('employee_ids[]')) {
                        document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
                            const id = cb.value || cb.closest('tr')?.getAttribute('data-employee-id');
                            if (id) fd.append('employee_ids[]', id);
                        });
                    }

                    fd.append('export_type', 'excel');
                    const qs = new URLSearchParams(fd).toString();
                    window.location.href = "{{ route('salary_slip') }}?" + qs; // triggers download
                } else {
                    const qs = new URLSearchParams(new FormData(document.getElementById('employee_submit')))
                        .toString();
                    window.location.href = routes[sheet] + '?' + qs;
                }
            } else if (action === 'pdf') {
                const fd = new FormData(document.getElementById('employee_submit'));
                fd.append('export_type', 'pdf');
                const qs = new URLSearchParams(fd).toString();

                if (sheet === 'salary') {
                    window.location.href = routes[sheet] + '?' + qs;
                } else if (sheet === 'slip') {
                    salary_slip('pdf');
                } else if (sheet === 'deduction') {
                    generatedeductionsheet();
                } else if (sheet === 'paymode') {
                    paymode();
                } else if (sheet === 'gross') {
                    gross_sheet();
                } else if (sheet === 'advance') {
                    advance();
                }
            } else if (action === 'onclick') {
                // keep your existing PDF/Print functions
                if (sheet === 'slip') {
                    salary_slip('pdf');
                } else if (sheet === 'salary') {
                    salarysheet('preview');
                } else if (sheet === 'deduction') {
                    generatedeductionsheet();
                } else if (sheet === 'paymode') {
                    paymode();
                } else if (sheet === 'gross') {
                    gross_sheet();
                } else if (sheet === 'advance') {
                    advance();
                }
            }

            this.selectedIndex = 0;
        });
    </script>
@endpush
@section('action-btn')
    {{-- <div class="float-end" style="display: flex;">
       
        <select id="salarySheetOptions" class="form-select" onchange="handleSheetOption(this, 'salary')">
            <option value="">Salary Sheet</option>
            <option value="onclick">PDF / Print</option>
            <option value="route">Excel Sheet</option>
        </select>

        <!-- Dropdown for Deduction Sheet -->
        <select id="deductionSheetOptions" class="form-select" onchange="handleSheetOption(this, 'deduction')">
            <option value="">Deduction Sheet</option>
            <option value="onclick">PDF / Print</option>
            <option value="route">Excel Sheet</option>
        </select>

        <!-- Dropdown for Paymode Sheet -->
        <select id="paymodeSheetOptions" class="form-select" onchange="handleSheetOption(this, 'paymode')">
            <option value="">Paymode Sheet</option>
            <option value="onclick">PDF / Print</option>
            <option value="route">Excel Sheet</option>
        </select>

        <!-- Dropdown for Gross Sheet -->
        <select id="grossSheetOptions" class="form-select" onchange="handleSheetOption(this, 'gross')">
            <option value="">Gross Sheet</option>
            <option value="onclick">PDF / Print</option>
            <option value="route">Excel Sheet</option>
        </select>

        <!-- Dropdown for Advance Sheet -->
        <select id="advanceSheetOptions" class="form-select" onchange="handleSheetOption(this, 'advance')">
            <option value="">Advance Sheet</option>
            <option value="onclick">PDF / Print</option>
            <option value="route">Excel Sheet</option>
        </select>

        <!-- Dropdown for Salary slip Sheet -->
        <select id="SalarySlipOptions" class="form-select" onchange="handleSheetOption(this, 'slip')">
            <option value="">Salary Slip</option>
            <option value="onclick">PDF / Print</option>
        </select>



        {{-- <a href="#" onclick="salary_slip(); return false;" class="btn mx-1 btn-sm btn-outline-primary"
        data-bs-toggle="tooltip" title="" title="Salary Slip">
        <span class="btn-inner--icon">Salary Slip</span>
    </a> 
    </div> --}}
    <div class="float-end d-flex align-items-center gap-2">
        <!-- Sheet Selector -->
        <select id="sheetType" class="form-select" style="width: 170px;">
            <option value="" selected disabled>Print Sheet</option>
            <option value="salary">Salary Sheet</option>
            <option value="deduction">Deduction Sheet</option>
            <option value="paymode">Paymode Sheet</option>
            <option value="gross">Gross Sheet</option>
            <option value="advance">Advance Sheet</option>
            <option value="slip">Salary Slip</option>
        </select>

        <!-- Action Selector -->
        <select id="sheetAction" class="form-select" style="width: 170px;">
            <option value="" selected disabled>Select Mode</option>
            <option value="onclick">Print / Preview PDF</option>
            {{-- <option value="pdf">PDF Download</option> --}}
            <option value="route">Excel Sheet</option>
        </select>
    </div>
@endsection
@section('content')
    <div id="reportDownloadLoader" class="report-download-loader d-none" aria-live="polite" aria-busy="true">
        <div class="report-download-loader-box">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2 fw-semibold" data-report-loader-text>{{ __('Preparing report...') }}</div>
        </div>
    </div>
    @php
        $salaryRowsTotal = $datas->count();
        $salaryGeneratedCount = $datas->filter(fn($row) => !empty($row->employeemonthlysalary))->count();
        $salaryPendingCount = $datas->filter(fn($row) => empty($row->employeemonthlysalary))->count();
        $salaryUnpaidCount = $datas->filter(fn($row) => !empty($row->employeemonthlysalary) && trim(strtolower($row->employeemonthlysalary->status ?? '')) == 'unpaid')->count();
        $salaryPaidCount = $datas->filter(fn($row) => !empty($row->employeemonthlysalary) && trim(strtolower($row->employeemonthlysalary->status ?? '')) == 'paid')->count();
        $salaryFinalCount = $datas->filter(fn($row) => !empty($row->employeemonthlysalary) && $row->employeemonthlysalary->sal_final)->count();
        $salaryHoldCount = $datas->filter(fn($row) => !empty($row->employeemonthlysalary) && $row->employeemonthlysalary->on_hold)->count();
    @endphp
    {{-- @if (\Auth::user()->type == 'company') --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['month_salary'], 'method' => 'GET', 'id' => 'employee_submit']) }}
                        <div class="row d-flex justify-content-start">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branchesList, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                                    {{ Form::select('department_id', $departments, isset($_GET['department_id']) ? $_GET['department_id'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                                    {{ Form::select('designation_id', $designations, isset($_GET['designation_id']) ? $_GET['designation_id'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {!! Form::label('paymode', __('Paymode'), ['class' => 'form-label']) !!}
                                    {{ Form::select(
                                        'paymode',
                                        [
                                            '' => 'Select All',
                                            'Bank Deposit HBL' => 'Bank Deposit HBL',
                                            'Bank Deposit AF' => 'Bank Deposit AF',
                                            'Demand Draft' => 'Demand Draft',
                                            'Cheque' => 'Cheque',
                                            'Bank' => 'Bank Deposite',
                                            'Cash' => 'Cash',
                                        ],
                                        isset($_GET['paymode']) ? $_GET['paymode'] : '',
                                        ['class' => 'form-control select custom-select'],
                                    ) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('date', __('Month'), ['class' => 'form-label']) }}
                                    {{ Form::input('month', 'date', isset($_GET['date']) ? date('Y-m', strtotime($_GET['date'])) : (!empty($date) ? date('Y-m', strtotime($date)) : now()->format('Y-m')), ['class' => 'form-control', 'id' => 'date']) }}
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                                    <div class="d-flex flex-wrap align-items-center justify-content-end gap-1">
                                        <a href="#" class="btn btn-sm btn-outline-primary"
                                            onclick="document.getElementById('employee_submit').submit(); return false;"
                                            data-bs-toggle="tooltip" data-bs-toggle="{{ __('Apply') }}">
                                            <span class="btn-inner--icon">Search</span>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-success generate-btn salary-action-btn"
                                            data-bs-toggle="tooltip" data-bs-title="Generate">
                                            <span class="btn-inner--icon">Generate</span>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-warning hold-unhold-btn salary-action-btn"
                                            data-bs-toggle="tooltip" data-bs-title="Hold / UnHold">
                                            <span class="btn-inner--icon">Hold / UnHold</span>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-danger pay-salary-btn salary-action-btn"
                                            data-bs-toggle="tooltip" data-bs-title="Pay Salary">
                                            <span class="btn-inner--icon">Pay Salary</span>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-danger delete-salary-btn salary-action-btn"
                                            data-bs-toggle="tooltip" data-bs-title="RollBack Salary">
                                            <span class="btn-inner--icon">RollBack Salary</span>
                                        </a>
                                        <a id="sal-finalize-btn" href="#" class="btn btn-sm btn-outline-warning salary-action-btn"
                                            data-bs-toggle="tooltip" data-bs-title="Finalize Salary">
                                            <span class="btn-inner--icon">Finalize</span>
                                        </a>
                                        <a id="sal-unfinalize-btn" href="#" class="btn btn-sm btn-outline-secondary salary-action-btn"
                                            data-bs-toggle="tooltip" data-bs-title="UnFinalize Salary">
                                            <span class="btn-inner--icon">UnFinalize</span>
                                        </a>
                                        {{-- <a href="{{ route('emp-month-sal-attendance.index') }}"
                                            class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip"
                                            data-bs-title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off"></i></span>
                                        </a> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- @endif --}}

    @if ($datas->isNotEmpty())
        <div class="card mt-3">
            <div class="card-header">
                <div class="d-flex flex-wrap align-items-center gap-2 attendance-summary-badges">
                    <span class="badge bg-secondary">{{ __('Total Rows') }} <span class="badge-count">{{ $salaryRowsTotal }}</span></span>
                    <span class="badge bg-light text-dark">{{ __('Pending Generate') }} <span class="badge-count">{{ $salaryPendingCount }}</span></span>
                    <span class="badge bg-info">{{ __('Generated') }} <span class="badge-count">{{ $salaryGeneratedCount }}</span></span>
                    <span class="badge bg-warning text-dark">{{ __('Unpaid') }} <span class="badge-count">{{ $salaryUnpaidCount }}</span></span>
                    <span class="badge bg-success">{{ __('Paid') }} <span class="badge-count">{{ $salaryPaidCount }}</span></span>
                    <span class="badge bg-primary">{{ __('Final') }} <span class="badge-count">{{ $salaryFinalCount }}</span></span>
                    <span class="badge bg-danger">{{ __('On Hold') }} <span class="badge-count">{{ $salaryHoldCount }}</span></span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive monthly-salary-table-wrap">
                    <table class="">
                <thead>
                        @php
                            $shortenedHeads = [
                                'Initial Basics' => 'Ini.Basic',
                                'House Rent' => 'House Rent',
                                'Medical' => 'Med',
                            ];
                            $allowanceColspan = $salaryheads->count() + 6;
                        @endphp
                    <tr class="table_heads salary-group-header">
                        <th rowspan="2"><input type="checkbox" id="check-all"></th>
                        <th rowspan="2">#</th>
                        <th rowspan="2">{{ __('Emp No') }}</th>
                        <th rowspan="2">{{ __('Name') }}</th>
                        <th rowspan="2">{{ __('Sal. Month') }}</th>
                        <th rowspan="2">{{ __('Sal. Days') }}</th>
                        <th colspan="{{ $allowanceColspan }}">{{ __('Allowances') }}</th>
                        <th colspan="10">{{ __('Deduction') }}</th>
                        <th colspan="6">{{ __('Status') }}</th>
                    </tr>
                    <tr class="table_heads salary-column-header">
                        @foreach ($salaryheads as $head)
                            <th>{{ $shortenedHeads[$head->head] ?? $head->head }}</th>
                        @endforeach
                        <th>{{ __('Basic') }}</th>
                        <th>{{ __('Other') }}</th>
                        <th>{{ __('Other Allowance') }}</th>
                        <th>{{ __('Drns & Misc') }}</th>
                        <th>{{ __('Gross') }}</th>
                        <th>{{ __('Arears') }}</th>
                        <th>{{ __('E.s') }}</th>
                        <th>{{ __('PESSI') }}</th>
                        <th>{{ __('IT') }}</th>
                        <th>{{ __('EOBI') }}</th>
                        <th>{{ __('Ded') }}</th>
                        <th>{{ __('Loan') }}</th>
                        <th>{{ __('Loan Sec') }}</th>
                        <th>{{ __('Stop sal') }}</th>
                        <th>{{ __('Tra. Course') }}</th>
                        <th>{{ __('Salary Adv.') }}</th>
                        <th>{{ __('Net') }}</th>
                        <th>{{ __('prCFinal') }}</th>
                        <th>{{ __('SalFinal') }}</th>
                        <th>{{ __('HR Final') }}</th>
                        <th>{{ __('On Hold') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($datas as $data)
                        @php
                            $date = request()->query('date') ? request()->query('date') : date('Y-m-d');
                            $arrears = \App\Models\EmployeeMonthlySalary::where('employee_id', $data->employee->id)
                                ->whereMonth('salary_date', '<', date('m', strtotime($date)))
                                ->whereYear('salary_date', '<', date('Y', strtotime($date)))
                                ->where('status', 'unpaid')
                                ->sum('net_pay');
                        @endphp
                        <tr data-employee-id="{{ optional($data->employee)->id }}"
                            style="
                                color:
                                @if (isset($data->employeemonthlysalary) && $data->employeemonthlysalary->sal_final) green;
                                @elseif(isset($data->employeemonthlysalary) && trim(strtolower($data->employeemonthlysalary->status)) == 'unpaid')
                                    blue;
                                @elseif(isset($data->employeemonthlysalary) && trim(strtolower($data->employeemonthlysalary->status)) == 'paid')
                                    red;
                                @else
                                    black; @endif
                            ">
                            <td>
                                <input type="checkbox" name="employee_ids[]" class="row-checkbox"
                                    value="{{ optional($data->employee)->id }}"
                                    data-id="{{ $data->id }}"
                                    data-employee-id="{{ optional($data->employee)->id }}"
                                    data-date="{{ $data->for_month_of }}">
                            </td>
                            <td>{{ $loop->iteration }}</td>
                            <td class="font-style">
                                @if (isset($data->employeemonthlysalary))
                                    <a href="#" data-size="xl"
                                        data-url="{{ route('emp-month-sal-attendance.show', $data->employeemonthlysalary->id) }}"
                                        data-size="lg" data-ajax-popup="true"
                                        data-bs-toggle="{{ __('Monthly Salary Detail') }}"
                                        class="mx-1 btn mx-1 btn-sm btn-outline-primary" data-bs-toggle="tooltip"
                                        data-bs-title="{{ __('Monthly Salary Detail') }}"
                                        data-bs-title="{{ __('Monthly Salary Detail') }}">
                                        <span
                                            class="btn-inner--icon">{{ \Auth::user()->employeeIdFormat($data->employee->employee_id) }}</span>

                                    </a>
                                @else
                                    {{ \Auth::user()->employeeIdFormat($data->employee->employee_id) }}
                                @endif
                            </td>
                            <td class="font-style">{{ !empty($data) ? $data->employee->name : '' }}</td>
                            <td>{{ !empty($data) ? date('M-Y', strtotime($data->for_month_of)) : '' }}</td>
                            <td>{{ !empty($data) ? $data->working_days : '' }}</td>

                            @php
                                $gross = 0;
                                $lastPayscaleDetail = $data->employee->employee_payscale_details->last();
                                $payscalesauto = \App\Models\EmployeeScale::with(
                                    'employeeScaleHeads',
                                    'employeeScaleHeads.SalaryHeads',
                                    'employeepayScaledetailHeads',
                                )
                                    ->where('id', @$lastPayscaleDetail->pay_scale_id)
                                    ->first();
                                $heads = $data->employeemonthlysalary->salaryheads ?? [];

                                // Create a lookup array for employee's salary heads
$employeeHeadValues = [];
foreach ($heads as $scale_head) {
    $headName = $scale_head->SalaryHead->head;
    $headValue = @$scale_head->head_value ?? '';
    $employeeHeadValues[$headName] = $headValue;

    if ($headName == 'Initial Basic') {
                                        $initialBasicHeadValue = $headValue;
                                        $basicfinal =
                                            ($initialBasicHeadValue / @$data->month_days) * @$data->working_days;
                                    } else {
                                        $gross += $headValue;
                                    }
                                }
                            @endphp

                            {{-- Display columns for ALL salary heads, showing value if exists or empty if not --}}
                            @foreach ($salaryheads as $head)
                                <td>{{ $employeeHeadValues[$head->head] ?? '0' }}</td>
                            @endforeach

                            <td>{{ !empty($data->employeemonthlysalary) ? $data->employeemonthlysalary->basics : '0' }}
                            </td>
                            <td>{{ !empty(@$data->employeemonthlysalary) ? $data->employeemonthlysalary->conv : '0' }}</td>
                            <td>{{ !empty(@$data->employeemonthlysalary) ? $data->employeemonthlysalary->other : '0' }}
                            </td>
                            <td>
                                {{ !empty(@$data->employeemonthlysalary) ? (($data->employeemonthlysalary->drns ?? 0) + ($data->employeemonthlysalary->misc ?? 0)) : '0' }}
                            </td>
                            <td>{{ !empty(@$data->employeemonthlysalary) ? (($data->employeemonthlysalary->gross ?? 0) + ($data->employeemonthlysalary->stop_sal ?? 0)) : '0' }}
                            </td>
                            <td>{{ !empty(@$arrears) ? $arrears : '0' }}</td>
                            <td>{{ !empty(@$data->employeemonthlysalary) ? $data->employeemonthlysalary->emp_sec : '0' }}
                            </td>
                            <td>{{ !empty(@$data->employeemonthlysalary) ? $data->employeemonthlysalary->pessi : '0' }}
                            </td>
                            <td>{{ !empty(@$data->employeemonthlysalary) ? $data->employeemonthlysalary->it : '0' }}</td>
                            <td>{{ !empty(@$data->employeemonthlysalary) ? $data->employeemonthlysalary->eobi : '0' }}</td>
                            <td>{{ !empty(@$data->employeemonthlysalary) ? $data->employeemonthlysalary->dedu : '0' }}</td>
                            <td>{{ !empty(@$data->employeemonthlysalary) ? $data->employeemonthlysalary->loan : '0' }}</td>
                            <td>{{ !empty(@$data->employeemonthlysalary) ? $data->employeemonthlysalary->emp_sec_loan : '0' }}</td>
                            <td>{{ !empty(@$data->employeemonthlysalary) ? $data->employeemonthlysalary->stop_sal : '0' }}
                            </td>
                            <td>{{ !empty(@$data->employeemonthlysalary) ? $data->employeemonthlysalary->tra_course : '0' }}
                            </td>
                            <td>{{ !empty(@$data->employeemonthlysalary) ? $data->employeemonthlysalary->sal_advance : '0' }}
                            </td>
                            <td>{{ !empty(@$data->employeemonthlysalary) ? $data->employeemonthlysalary->net_pay : '0' }}
                            </td>
                            <td><input type="checkbox" name="gmfinal"
                                    {{ !empty($data) && $data->gm_final == 1 ? 'checked' : '' }} disabled></td>
                            <td><input type="checkbox" name="salfinal"
                                    {{ optional($data->employeemonthlysalary)->sal_final == 1 ? 'checked' : '' }} disabled>
                            </td>
                            <td><input type="checkbox" name="onhold"
                                    {{ !empty($data) && @$data->employeemonthlysalary->on_hold == 1 ? 'checked' : '' }}
                                    disabled></td>

                            <td><input type="checkbox" name="onhold"
                                    {{ !empty($data) && @$data->employeemonthlysalary->on_hold == 1 ? 'checked' : '' }}
                                    disabled></td>
                            <td>
                                @if (!empty($data->employeemonthlysalary) && $data->employeemonthlysalary->on_hold == 1)
                                    <span class="badge bg-danger">{{ __('On Hold') }}</span>
                                @elseif (!empty($data->employeemonthlysalary) && $data->employeemonthlysalary->sal_final == 1)
                                    <span class="badge bg-primary">{{ __('Final') }}</span>
                                @elseif (!empty($data->employeemonthlysalary) && trim(strtolower($data->employeemonthlysalary->status ?? '')) == 'paid')
                                    <span class="badge bg-success">{{ __('Paid') }}</span>
                                @elseif (!empty($data->employeemonthlysalary) && trim(strtolower($data->employeemonthlysalary->status ?? '')) == 'unpaid')
                                    <span class="badge bg-warning text-dark">{{ __('Unpaid') }}</span>
                                @elseif (!empty($data->employeemonthlysalary))
                                    <span class="badge bg-info">{{ __('Generated') }}</span>
                                @else
                                    <span class="badge bg-light text-dark">{{ __('Pending Generate') }}</span>
                                @endif
                            </td>
                            {{-- <td> <a href="#" data-url="{{route('salary.payments',$data->id)}}" data-ajax-popup="true" data-bs-toggle="{{__('Monthly Salary Pay')}}" class="mx-1 btn mx-1 btn-sm btn-outline-primary"  data-bs-title="{{__('Monthly Salary Pay')}}" data-bs-title="{{__('Monthly Salary Pay')}}">
                                <span class="btn-inner--icon">Pay</span>
                                </a></td> --}}
                            {{-- <td> <a href="{{route('salary.payments',$data->id)}}" data-bs-toggle="{{__('Monthly Salary Pay')}}" class="mx-1 btn mx-1 btn-sm btn-outline-primary"  data-bs-title="{{__('Monthly Salary Pay')}}" data-bs-title="{{__('Monthly Salary Pay')}}">
                                <span class="btn-inner--icon">Pay</span>
                                </a></td> --}}
                        </tr>
                    @endforeach
                </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- @if ($datas->hasPages())
            <div class="pagination">
                <ul>
                    @if ($datas->onFirstPage())
                        <li class="disabled">&laquo; Previous</li>
                    @else
                        <li><a href="{{ $datas->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;
                                Previous</a></li>
                    @endif
                    @if ($datas->currentPage() > 1)
                        <li><a href="{{ $datas->appends(request()->query())->url(1) }}">First</a></li>
                    @endif
                    @php
                        $currentPage = $datas->currentPage();
                        $lastPage = $datas->lastPage();
                        $startPage = max(1, $currentPage - 4);
                        $endPage = min($lastPage, $currentPage + 5);
                        if ($endPage - $startPage < 9) {
                            if ($currentPage < $lastPage - 9) {
                                $endPage = $startPage + 9;
                            } else {
                                $startPage = max(1, $lastPage - 9);
                            }
                        }
                    @endphp
                    @for ($page = $startPage; $page <= $endPage; $page++)
                        <li class="{{ $page == $datas->currentPage() ? 'active' : '' }}">
                            <a href="{{ $datas->appends(request()->query())->url($page) }}">{{ $page }}</a>
                        </li>
                    @endfor
                    @if ($datas->hasMorePages())
                        <li><a href="{{ $datas->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                                &raquo;</a></li>
                    @else
                        <li class="disabled">Next &raquo;</li>
                    @endif
                    @if ($datas->currentPage() < $datas->lastPage())
                        <li><a href="{{ $datas->appends(request()->query())->url($datas->lastPage()) }}">Last</a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif --}}
    @endif
@endsection
