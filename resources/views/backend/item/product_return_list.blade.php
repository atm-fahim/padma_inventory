@extends('layouts.app')

@section('content')
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="row mb-3">
                <div class="col-lg-6 d-flex align-items-center">
                    <h4 class="mb-0">Product Repair List</h4>
                </div>
                <div class="col-lg-6 text-end">
                    <a href="{{ route('product-repair') }}" class="btn btn-primary">Add New Repair</a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="returnsTable">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Return No</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Type</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returns as $index => $return)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $return->return_no }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($return->return_date)->format('d-m-Y') }}</td>
                                <td>{{ $return->supplier->supplier_name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $return->return_type == 'damage' ? 'danger' : ($return->return_type == 'repair' ? 'warning' : 'info') }}">
                                        {{ ucfirst($return->return_type) }}
                                    </span>
                                </td>
                                <td>{{ $return->items->count() }} item(s)</td>
                                <td>
                                    @if($return->status == 1)
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($return->status == 2)
                                        <span class="badge bg-info">Received</span>
                                    @elseif($return->status == 3)
                                        <span class="badge bg-success">Completed</span>
                                    @else
                                        <span class="badge bg-secondary">Unknown</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('product-repair-show', $return->id) }}" class="btn btn-sm btn-info">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#returnsTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25
        });
    });
</script>
@endsection
