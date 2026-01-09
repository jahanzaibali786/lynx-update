@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th>{{ __('Sr.') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Product Code') }}</th>
            <th>{{ __('Current Quantity') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($productServices as $productService)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $productService->name }}</td>
                <td>{{ $productService->sku }}</td>
                <td>{{ $productService->quantity }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@include('student.exports.footer')
