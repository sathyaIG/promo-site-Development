<table>
    <thead>
        <tr>
            <th>SKU ID</th>
            <th>Discount Type</th>            
            <th>Discount Value</th>
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
                    <td>{{ $i }}</td>
                    <td>{{ $listDetails['skuId'] }}</td>                    
                    <td>{{ $listDetails['discountType'] }}</td>
                    <td>{{ $listDetails['discountValue'] }}</td>
                    <td>{{ $listDetails['fundingCategory'] }}</td>
                    <td>{{ $listDetails['fundingMarket'] }}</td>                   
                    <td>{{ $listDetails['fundtionVendor'] }}</td>
                    <td>{{ $listDetails['isInvoiced'] }}</td>
                    <td>{{ $listDetails['redemptionLimitPerOrder'] }}</td>
                    <td>{{ $listDetails['redemptionLimitPerMember'] }}</td>
                    <td>{{ $listDetails['redemptionLimitPerCampaign'] }}</td>
                </tr>

            @php $i++; @endphp

        @endforeach
    </tbody>

</table>
