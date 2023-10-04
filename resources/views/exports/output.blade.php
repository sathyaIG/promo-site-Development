<table>
    <thead>
        <tr>
            <th>SKU ID</th>
            <th>Start Date</th>            
            <th>End Date</th>
            <th>EC ID</th>
            <th>Business Type</th>
            <th>SA City ID</th>
            <th>SA</th>
            <th>Discount Type</th>
            <th>Discount Category</th>
            <th>Discount Value</th>
            <th>Funding - Category</th>
            <th>Funding - Marketing</th>
            <th>Funding - Vendor</th>
            <th>Invoiced ?</th>
            <th>Redemption Limit - Qty Per Order</th>
            <th>Redemption Limit - Qty Per Member</th>
            <th>Redemption Limit - Qty Per Campaign</th>
        </tr>
    </thead>
    <tbody>
        @php
            $i = 1;
        @endphp
        @foreach ($details as $index => $listDetails)
                <tr>
                    <td>{{ $listDetails['skuId'] }}</td>
                    <td>{{ $listDetails['startDate'] }}</td>                    
                    <td>{{ $listDetails['endDate'] }}</td>
                    <td>{{ $listDetails['ecId'] }}</td>
                    <td>{{ $listDetails['businessType'] }}</td>
                    <td>{{ $listDetails['saCityId'] }}</td>                   
                    <td>{{ $listDetails['sa'] }}</td>
                    <td>{{ $listDetails['discountType'] }}</td>
                    <td>{{ $listDetails['discountCategory'] }}</td>
                    <td>{{ $listDetails['discountValue'] }}</td>
                    <td>{{ $listDetails['fundingCategory'] }}</td>
                    <td>{{ $listDetails['fundingMarketing'] }}</td>
                    <td>{{ $listDetails['fundingVendor'] }}</td>
                    <td>{{ $listDetails['isInvoiced'] }}</td>
                    <td>{{ $listDetails['redemptionLimitPerOrder'] }}</td>
                    <td>{{ $listDetails['redemptionLimitPerMember'] }}</td>
                    <td>{{ $listDetails['redemptionLimitPerCampaign'] }}</td>
                    
                </tr>
            @php $i++; @endphp
        @endforeach
    </tbody>
</table>
