@extends('layouts.app')

@section('content')
<div class="container card border-top border-0 border-4 border-primary">
    <div class="row justify-content-center">
        <div class="col-lg-12 p-4">
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <h5 class="mb-1">DELIVERY CHALLAN</h5>
                    <small>Mutual Trust Bank PLC</small>
                </div>
                <div class="text-end">
                    <p class="mb-1"><strong>Challan No:</strong> {{ $challan->challan_no }}</p>
                    <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($challan->challan_date)->format('d-m-Y') }}</p>
                    @if($challan->reference_no)
                        <p class="mb-1"><strong>Reference No:</strong> {{ $challan->reference_no }}</p>
                    @endif
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Buyer:</strong> {{ $challan->buyer_name }}</p>
                    @if($challan->buyer_address)
                        <p class="mb-1"><strong>Address:</strong> {{ $challan->buyer_address }}</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Delivery Location:</strong> {{ $challan->delivery_location }}</p>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 60px;">SL</th>
                            <th>Description</th>
                            <th style="width: 120px;">Qty</th>
                            <th style="width: 120px;">UoM</th>
                            <th style="width: 150px;">Warranty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($challan->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ number_format($item->quantity, 2) }}</td>
                                <td>{{ $item->uom }}</td>
                                <td>{{ $item->warranty }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row mt-5">
                <div class="col-md-4 text-center">
                    <p class="mb-5">Received by</p>
                    <hr class="mt-5">
                </div>
                <div class="col-md-4 text-center">
                    <p class="mb-5">Checked by</p>
                    <hr class="mt-5">
                </div>
                <div class="col-md-4 text-center">
                    <p class="mb-5">Delivered by</p>
                    <hr class="mt-5">
                </div>
            </div>

            <div class="mt-4 text-end no-print">
                <button onclick="window.print();" class="btn btn-primary">Print</button>
                <a href="{{ route('delivery-challan') }}" class="btn btn-secondary">New Challan</a>
            </div>
        </div>
    </div>
</div>
@endsection

