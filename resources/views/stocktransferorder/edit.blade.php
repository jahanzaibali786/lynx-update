@extends('layouts.admin')

@php
    $existingItemsPayload = $StockTransferOrder->items->map(function ($item) {
        return [
            'id' => $item->id,
            'item_id' => $item->product_id,
            'quantity' => $item->quantity,
            'base_quantity' => $item->quantity,
            'price' => $item->price,
            'discount' => $item->discount,
            'description' => $item->description,
            'study_pack_id' => $item->study_pack_id,
            'study_pack_title' => $item->study_pack_title,
            'study_pack_class' => $item->study_pack_class,
        ];
    })->values()->toArray();
@endphp

@include('stocktransferorder._form', [
    'mode' => 'edit',
    'formAction' => route('stock-transfer-order.update', $StockTransferOrder->id),
    'formMethod' => 'PUT',
    'selectedBranch' => $StockTransferOrder->branch_id,
    'selectedWarehouse' => $StockTransferOrder->warehouse_id,
    'selectedSessionId' => $StockTransferOrder->session_id,
    'selectedDate' => $StockTransferOrder->purchase_date,
    'initialItems' => $existingItemsPayload,
])
