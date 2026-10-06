@extends('layouts.app')

@section('content')
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="row mb-3">
                <div class="col-lg-6 d-flex align-items-center">
                    <h4 class="mb-0">Requisition List</h4>
                </div>
                <div class="col-lg-6 text-end">
                    <a href="{{ route('create-requisition') }}" class="btn btn-primary">Create New Requisition</a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="requisitionTable">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Requisition No</th>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requisitions as $index => $req)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $req->requisition_no }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($req->requisition_date)->format('d-m-Y') }}</td>
                                <td>{{ $req->employee_name }}<br><small class="text-muted">{{ $req->employee_id }}</small></td>
                                <td>{{ $req->items->count() ?? 0 }} item(s)</td>
                                <td>
                                    @if($req->status == 1)
                                        <span class="badge bg-secondary">Draft</span>
                                    @elseif($req->status == 2)
                                        <span class="badge bg-warning">Submitted</span>
                                    @else
                                        <span class="badge bg-dark">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('requisition-show', $req->id) }}" class="btn btn-sm btn-info">View</a>
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
        $('#requisitionTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25
        });
    });
</script>
@endsection

