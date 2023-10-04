<table>
    <thead>
        <tr>
            <th>Sr. No.</th>
            <th>Manufacturer / Suppler Name</th>            
            <th>Code</th>
            <th>Product Description</th>
            <th>MRP</th>
            <th>Start Date
                (DD-MMM-YY)</th>
            <th>End Date
                (DD-MMM-YY)</th>
            <th>Offer Details(100% Vendor Funded)</th>
            <th>Redemption Limit - Qty Per Campaign</th>
            <th>Selections of Cities</th>
            <th>Pan India</th>
            <th>South</th>
            <th>North</th>
            <th>East</th>
            <th>West</th>
            <th>Central</th>
            <th>ANDHRA PRADESH</th>
            <th>TELANGANA</th>
            <th>ASSAM</th>
            <th>BIHAR</th>
            <th>CHHATTISGARH</th>
            <th>GUJARAT</th>
            <th>DELHI-NCR</th>
            <th>JHARKHAND</th>
            <th>KARNATAKA</th>
            <th>KERALA</th>
            <th>MADHYA PRADESH</th>
            <th>MAHARASHTRA - Mumbai</th>
            <th>MAHARASHTRA - Pune</th>
            <th>ORISSA</th>
            <th>PUNJAB</th>
            <th>RAJASTHAN</th>
            <th>TAMIL NADU</th>
            <th>UTTAR PRADESH</th>
            <th>WEST BENGAL</th>
            <th>Department</th>
            <th>Business Model</th>
            <!-- <th>Category Value</th> -->

        </tr>
    </thead>
    <tbody>
        @php
            $i = 1;
        @endphp
        @foreach ($details as $index => $listDetails)
            @if ($listDetails['manufacturer_suppler_name'] != null && $listDetails['code'] != null)
                <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $listDetails['manufacturer_suppler_name'] }}</td>                    
                    <td>{{ $listDetails['code'] }}</td>
                    <td>{{ $listDetails['product_description'] }}</td>
                    <td>{{ $listDetails['mrp'] }}</td>
                    <td>{{  $listDetails['start_date_dd_mmm_yy'] }}</td>                   
                    <td>{{   $listDetails['end_date_dd_mmm_yy'] }}</td>
                    <td>{{ $listDetails['offer_details100_vendor_funded'] }}</td>
                    <td>{{ $listDetails['redemption_limit_qty_per_campaign'] }}</td>
                    <td>{{ $listDetails['selections_of_cities'] }}</td>
                    <td>{{ $listDetails['pan_india'] }}</td>
                    <td>{{ $listDetails['south'] }}</td>
                    <td>{{ $listDetails['north'] }}</td>
                    <td>{{ $listDetails['east'] }}</td>
                    <td>{{ $listDetails['west'] }}</td>
                    <td>{{ $listDetails['central'] }}</td>
                    <td>{{ $listDetails['andhra_pradesh'] }}</td>
                    <td>{{ $listDetails['telangana'] }}</td>
                    <td>{{ $listDetails['assam'] }}</td>
                    <td>{{ $listDetails['bihar'] }}</td>
                    <td>{{ $listDetails['chhattisgarh'] }}</td>
                    <td>{{ $listDetails['gujarat'] }}</td>
                    <td>{{ $listDetails['delhi_ncr'] }}</td>
                    <td>{{ $listDetails['jharkhand'] }}</td>
                    <td>{{ $listDetails['karnataka'] }}</td>
                    <td>{{ $listDetails['kerala'] }}</td>
                    <td>{{ $listDetails['madhya_pradesh'] }}</td>
                    <td>{{ $listDetails['maharashtra_mumbai']}}</td>
                    <td>{{ $listDetails['maharashtra_pune']}}</td>
                    <td>{{ $listDetails['orissa']}}</td>
                    <td>{{ $listDetails['punjab'] }}</td>
                    <td>{{ $listDetails['rajasthan'] }}</td>
                    <td>{{ $listDetails['tamil_nadu'] }}</td>
                    <td>{{ $listDetails['uttar_pradesh'] }}</td>
                    <td>{{ $listDetails['west_bengal'] }}</td>
                    <td>{{ $listDetails['department'] }}</td>
                    <td>{{ $listDetails['business_model'] }}</td>
                    <!-- <td>
                        @if(isset($listDetails['category_value']))
                            {{ $listDetails['category_value'] }}
                        
                        
                        @endif
                    </td> -->
                </tr>
            @endif
            @php $i++; @endphp
        @endforeach
    </tbody>
</table>
