@extends('layouts.app')
@section('content')
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">


        <div class="col">
            <div class="card radius-10 border-start border-0 border-3 border-warning shadow-sm card-clickable" data-type="category" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-1 text-secondary fw-semibold">Total Category</p>
                            <h4 class="my-1 text-warning fw-bold total_category">
                                <div class="spinner-border spinner-border-sm text-warning" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-warning text-white ms-auto d-flex align-items-center justify-content-center">
                            <i class='bx bxs-group fs-4'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 border-start border-0 border-3 border-info shadow-sm card-clickable" data-type="item" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-1 text-secondary fw-semibold">Total Item</p>
                            <h4 class="my-1 text-info fw-bold total_item">
                                <div class="spinner-border spinner-border-sm text-info" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-info text-white ms-auto d-flex align-items-center justify-content-center">
                            <i class='bx bxs-spreadsheet fs-4'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm card-clickable" data-type="supplier" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-1 text-secondary fw-semibold">Total Supplier</p>
                            <h4 class="my-1 text-success fw-bold total_supplier">
                                <div class="spinner-border spinner-border-sm text-success" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </h4>
                        </div>
                        <div class="widgets-icons-2 rounded-circle bg-gradient-success text-white ms-auto d-flex align-items-center justify-content-center">
                            <i class='bx bxs-store fs-4'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!--end row-->

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="detailsModalLabel">Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading details...</p>
                    </div>
                    <div id="modalContent" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead id="modalTableHead" class="table-light">
                                </thead>
                                <tbody id="modalTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="modalError" style="display: none;" class="alert alert-danger">
                        <i class='bx bx-error-circle'></i> Error loading details. Please try again.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

{{--    --}}
    <script src="{{asset('backend/assets/js/jquery.min.js')}}"></script>
    <script>
        $(function (){
            // Load dashboard counts
            $.ajax({
                url: "{{ route('dashboard-total-count-log') }}",
                type: 'GET',
                data: {},
                dataType: 'JSON',
                success: function (response) {
                    // Remove spinner and set text for each element
                    $('.total_category').html(response.totalCategory || '0');
                    $('.total_item').html(response.totalItem || '0');
                    $('.total_supplier').html(response.totalSupplier || '0');
                },
                error: function(xhr, status, error) {
                    console.error('Error loading dashboard data:', error);
                    // Show error or default values
                    $('.total_category').html('0');
                    $('.total_item').html('0');
                    $('.total_supplier').html('0');
                }
            });

            // Card click handlers
            $('.card-clickable').on('click', function() {
                const type = $(this).data('type');
                const modalElement = document.getElementById('detailsModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                const modalTitle = $('#detailsModalLabel');
                
                // Set modal title based on type
                const titles = {
                    'category': 'Category Details',
                    'item': 'Item Details',
                    'supplier': 'Supplier Details'
                };
                modalTitle.text(titles[type] || 'Details');
                
                // Show modal and load data
                modal.show();
                loadDetails(type);
            });

            function loadDetails(type) {
                // Show loading, hide content and error
                $('#modalLoading').show();
                $('#modalContent').hide();
                $('#modalError').hide();

                const routes = {
                    'category': "{{ route('dashboard-category-details') }}",
                    'item': "{{ route('dashboard-item-details') }}",
                    'supplier': "{{ route('dashboard-supplier-details') }}"
                };

                $.ajax({
                    url: routes[type],
                    type: 'GET',
                    dataType: 'JSON',
                    success: function(response) {
                        $('#modalLoading').hide();
                        if (response && response.length > 0) {
                            displayTableData(response, type);
                            $('#modalContent').show();
                        } else {
                            $('#modalError').html('<i class="bx bx-info-circle"></i> No data available.').show();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading details:', error);
                        $('#modalLoading').hide();
                        $('#modalError').show();
                    }
                });
            }

            function displayTableData(data, type) {
                const tableHead = $('#modalTableHead');
                const tableBody = $('#modalTableBody');
                
                // Clear previous data
                tableHead.empty();
                tableBody.empty();

                if (data.length === 0) {
                    tableBody.append('<tr><td colspan="100%" class="text-center text-muted">No data available</td></tr>');
                    return;
                }

                // Get all unique keys from the data
                const allKeys = new Set();
                data.forEach(item => {
                    Object.keys(item).forEach(key => {
                        if (key !== 'created_at' && key !== 'updated_at' && key !== 'status') {
                            allKeys.add(key);
                        }
                    });
                });

                // Create table header
                let headerRow = '<tr>';
                allKeys.forEach(key => {
                    const headerText = key.charAt(0).toUpperCase() + key.replace(/_/g, ' ').slice(1);
                    headerRow += `<th>${headerText}</th>`;
                });
                headerRow += '</tr>';
                tableHead.html(headerRow);

                // Create table rows
                data.forEach((item, index) => {
                    let row = '<tr>';
                    allKeys.forEach(key => {
                        let value = item[key] !== null && item[key] !== undefined ? item[key] : '-';
                        // Format the value if needed
                        if (typeof value === 'object') {
                            value = JSON.stringify(value);
                        }
                        row += `<td>${value}</td>`;
                    });
                    row += '</tr>';
                    tableBody.append(row);
                });
            }
        });
    </script>
@endsection
