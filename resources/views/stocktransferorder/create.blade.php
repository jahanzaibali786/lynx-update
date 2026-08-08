@extends('layouts.admin')

@include('stocktransferorder._form', [
    'mode' => 'create',
    'formAction' => url('stock-transfer-order'),
    'formMethod' => 'POST',
    'selectedBranch' => $branchId,
    'selectedWarehouse' => null,
    'selectedDate' => date('Y-m-d'),
    'initialItems' => [],
])
