@extends('layouts.app')

@section('content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Inventory</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ url('items-inventory') }}">Items Inventory</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('items-inventory-csv-upload') }}">CSV Upload</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Receive Uploaded Items</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card radius-10 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Receive Items Uploaded from CSV/XLSX</h5>
            <a href="{{ route('items-inventory-csv-upload') }}" class="btn btn-sm btn-secondary">
                <i class="bx bx-arrow-back"></i> Back to Upload
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('eMessage'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('eMessage') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="GET" action="{{ route('items-inventory-csv-receive') }}" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="set_uniq_no" class="form-control" placeholder="Search by SET_UNIQ_NO" value="{{ $searchSetUniqNo ?? '' }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-search"></i> Search</button>
                </div>
            </form>

            <form method="POST" action="{{ route('items-inventory-csv-receive-save') }}" id="csvReceiveForm">
                @csrf

                @php
                    $setUniqNos = collect($pendingItems)->pluck('set_uniq_no')->filter()->unique()->values();
                    $setSummary = collect($pendingItems)->groupBy('set_uniq_no')->map(function ($rows, $setNo) {
                        return [
                            'set_no' => $setNo,
                            'count' => $rows->count(),
                            'items' => $rows->pluck('item_title')->filter()->unique()->take(3)->implode(', '),
                        ];
                    })->values();
                @endphp

                <div class="card border mb-3">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <strong>Receive Employee ID by SET_UNIQ_NO</strong>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="applyFirstToAllBtn">
                                    Apply first Employee ID to all sets
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-dark" id="showSelectedSetsBtn">
                                    Show selected sets only
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-2">
                            <div class="col-md-4">
                                <input type="text" id="setSearchInput" class="form-control form-control-sm" placeholder="Filter set list (e.g. PBL-5001)">
                            </div>
                            <div class="col-md-8 text-md-end small text-muted d-flex align-items-center justify-content-md-end gap-3">
                                <span>Total Sets: <strong id="totalSetCount">{{ count($setSummary) }}</strong></span>
                                <span>Total Pending Items: <strong>{{ count($pendingItems) }}</strong></span>
                            </div>
                        </div>

                        <div class="table-responsive set-table-wrapper">
                            <table class="table table-sm table-bordered align-middle mb-0" id="setEmployeeTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 22%;">SET_UNIQ_NO</th>
                                        <th style="width: 8%;">Items</th>
                                        <th style="width: 35%;">Sample Item(s)</th>
                                        <th style="width: 35%;">Receive Employee ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                            @forelse($setSummary as $summary)
                                <tr class="set-row" data-set="{{ $summary['set_no'] }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td><span class="badge bg-dark">{{ $summary['set_no'] }}</span></td>
                                    <td><span class="badge bg-info">{{ $summary['count'] }}</span></td>
                                    <td class="small text-muted">{{ $summary['items'] ?: '-' }}</td>
                                    <td>
                                    <input
                                        type="text"
                                        name="receive_employee_id_by_set[{{ $summary['set_no'] }}]"
                                        class="form-control form-control-sm set-employee-input"
                                        data-set="{{ $summary['set_no'] }}"
                                        value="{{ old('receive_employee_id_by_set.' . $summary['set_no']) }}"
                                        placeholder="Employee ID for {{ $summary['set_no'] }}"
                                    >
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No SET_UNIQ_NO available.</td>
                                </tr>
                            @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mb-3 d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">Select All</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllBtn">Clear</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" style="font-size: 12px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 2%;"><input type="checkbox" id="masterCheck"></th>
                                <th style="width: 6%;">ID</th>
                                <th style="width: 12%;">SET_UNIQ_NO</th>
                                <th style="width: 10%;">Stock In No</th>
                                <th style="width: 16%;">Item</th>
                                <th style="width: 10%;">Category</th>
                                <th style="width: 8%;">Brand</th>
                                <th style="width: 6%;">Qty</th>
                                <th style="width: 10%;">Serial</th>
                                <th style="width: 20%;">Receive Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingItems as $row)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="inventory_ids[]" value="{{ $row->id }}" class="item-check" data-set="{{ $row->set_uniq_no }}">
                                    </td>
                                    <td>{{ $row->id }}</td>
                                    <td>{{ $row->set_uniq_no ?? '-' }}</td>
                                    <td>{{ $row->stock_in_no ?? '-' }}</td>
                                    <td>
                                        {{ $row->item_title ?? 'N/A' }}
                                        @if(!empty($row->processor) || !empty($row->storage) || !empty($row->ram))
                                            <div class="text-muted small">
                                                @if(!empty($row->processor))CPU: {{ $row->processor }} @endif
                                                @if(!empty($row->storage)) | Storage: {{ $row->storage }} @endif
                                                @if(!empty($row->ram)) | RAM: {{ $row->ram }} @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $row->category_title ?? '-' }}</td>
                                    <td>{{ $row->brand_name ?? '-' }}</td>
                                    <td>{{ $row->quantity ?? 0 }}</td>
                                    <td>{{ $row->uniq_serial_no ?? '-' }}</td>
                                    <td>
                                        <input type="text" name="receive_remarks[{{ $row->id }}]" class="form-control form-control-sm" placeholder="Optional">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">No pending CSV uploaded items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success px-4" {{ count($pendingItems) ? '' : 'disabled' }}>
                        <i class="bx bx-check-circle"></i> Receive Selected Items
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const masterCheck = document.getElementById('masterCheck');
        const selectAllBtn = document.getElementById('selectAllBtn');
        const clearAllBtn = document.getElementById('clearAllBtn');
        const form = document.getElementById('csvReceiveForm');

        function getChecks() {
            return Array.from(document.querySelectorAll('.item-check'));
        }

        function cssEscape(value) {
            if (window.CSS && window.CSS.escape) return window.CSS.escape(value);
            return String(value).replace(/"/g, '\\"');
        }

        function updateSetRowHighlight() {
            const selectedSets = new Set(getChecks().filter(cb => cb.checked).map(cb => cb.dataset.set || ''));
            document.querySelectorAll('.set-row').forEach(row => {
                const setNo = row.dataset.set || '';
                if (selectedSets.has(setNo)) {
                    row.classList.add('table-warning');
                } else {
                    row.classList.remove('table-warning');
                }
            });
        }

        if (masterCheck) {
            masterCheck.addEventListener('change', function () {
                getChecks().forEach(cb => cb.checked = masterCheck.checked);
                updateSetRowHighlight();
            });
        }

        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function () {
                getChecks().forEach(cb => cb.checked = true);
                if (masterCheck) masterCheck.checked = true;
                updateSetRowHighlight();
            });
        }

        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function () {
                getChecks().forEach(cb => cb.checked = false);
                if (masterCheck) masterCheck.checked = false;
                updateSetRowHighlight();
            });
        }

        getChecks().forEach(cb => {
            cb.addEventListener('change', updateSetRowHighlight);
        });

        if (form) {
            form.addEventListener('submit', function (e) {
                const selectedChecks = getChecks().filter(cb => cb.checked);
                if (selectedChecks.length < 1) {
                    e.preventDefault();
                    alert('Please select at least one item.');
                    return false;
                }

                const selectedSets = Array.from(new Set(selectedChecks.map(cb => cb.dataset.set || '')));
                const missingSets = [];

                selectedSets.forEach(setNo => {
                    if (!setNo) return;
                    const input = document.querySelector('.set-employee-input[data-set="' + cssEscape(setNo) + '"]');
                    if (!input || !input.value.trim()) {
                        missingSets.push(setNo);
                    }
                });

                if (missingSets.length > 0) {
                    e.preventDefault();
                    alert('Receive Employee ID দিন: ' + missingSets.join(', '));
                    return false;
                }

                return confirm('Are you sure you want to receive selected items?');
            });
        }

        const applyFirstToAllBtn = document.getElementById('applyFirstToAllBtn');
        if (applyFirstToAllBtn) {
            applyFirstToAllBtn.addEventListener('click', function () {
                const inputs = Array.from(document.querySelectorAll('.set-employee-input'));
                if (inputs.length === 0) return;
                const firstValue = (inputs[0].value || '').trim();
                if (!firstValue) {
                    alert('First Employee ID field is empty.');
                    return;
                }
                inputs.forEach(input => input.value = firstValue);
            });
        }

        const setSearchInput = document.getElementById('setSearchInput');
        if (setSearchInput) {
            setSearchInput.addEventListener('input', function () {
                const q = (setSearchInput.value || '').trim().toLowerCase();
                let visibleCount = 0;
                document.querySelectorAll('.set-row').forEach(row => {
                    const setNo = (row.dataset.set || '').toLowerCase();
                    const show = !q || setNo.includes(q);
                    row.style.display = show ? '' : 'none';
                    if (show) visibleCount++;
                });
                const totalSetCount = document.getElementById('totalSetCount');
                if (totalSetCount) totalSetCount.textContent = String(visibleCount);
            });
        }

        const showSelectedSetsBtn = document.getElementById('showSelectedSetsBtn');
        let selectedOnlyMode = false;
        if (showSelectedSetsBtn) {
            showSelectedSetsBtn.addEventListener('click', function () {
                selectedOnlyMode = !selectedOnlyMode;
                const selectedSets = new Set(getChecks().filter(cb => cb.checked).map(cb => cb.dataset.set || ''));
                let visibleCount = 0;
                document.querySelectorAll('.set-row').forEach(row => {
                    const setNo = row.dataset.set || '';
                    const show = !selectedOnlyMode || selectedSets.has(setNo);
                    row.style.display = show ? '' : 'none';
                    if (show) visibleCount++;
                });
                showSelectedSetsBtn.textContent = selectedOnlyMode ? 'Show all sets' : 'Show selected sets only';
                const totalSetCount = document.getElementById('totalSetCount');
                if (totalSetCount) totalSetCount.textContent = String(visibleCount);
            });
        }

        updateSetRowHighlight();
    });
</script>
@endpush

<style>
    .set-table-wrapper {
        max-height: 360px;
        overflow: auto;
    }
    #setEmployeeTable thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8f9fa;
    }
</style>

