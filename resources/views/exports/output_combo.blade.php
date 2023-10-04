<table>
    <thead>
        <tr>
            <th>Combo SKU ID</th>
            <th>Child SKU ID</th>                        
            <th>Discount Type</th>
            <th>Discount Value</th>
            <th>Funding - Category</th>
            <th>Funding - Marketing</th>
            <th>Funding - Vendor</th>
            <th>Invoiced ?</th>           
        </tr>
    </thead>
    <tbody>
        @php
            $i = 1;
        @endphp
        @foreach ($details as $index => $listDetails)
                <tr>
                    <td>{{ $listDetails['skuId'] }}</td>
                    <td>{{ $listDetails['combo_code'] }}</td>                                                            
                    <td>{{ $listDetails['discountType'] }}</td>
                    <td>{{ $listDetails['discountValue'] }}</td>
                    <td>{{ $listDetails['fundingCategory'] }}</td>
                    <td>{{ $listDetails['fundingMarketing'] }}</td>
                    <td>{{ $listDetails['fundingVendor'] }}</td>
                    <td>{{ $listDetails['isInvoiced'] }}</td>                    
                    
                </tr>
            @php $i++; @endphp
        @endforeach
    </tbody>
</table>
