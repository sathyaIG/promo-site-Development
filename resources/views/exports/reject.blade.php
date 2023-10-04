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
            <th>Rejected Comments</th>

        </tr>
    </thead>
    <tbody>
        @php
        $i = 1;
        @endphp
        @foreach ($details as $index => $listDetails)
        <tr>
            <td>{{ $listDetails['sr_no'] }}</td>
            <td>{{ $listDetails['manufacturer_name'] }}</td>
            <td>{{ $listDetails['code'] }}</td>
            <td>{{ $listDetails['description'] }}</td>
            <td>{{ $listDetails['mrp'] }}</td>
            <td>{{ $listDetails['start_date'] }}</td>
            <td>{{ $listDetails['end_date'] }}</td>
            <td>{{ $listDetails['offer_details'] }}</td>
            <td>{{ $listDetails['redemption_campaign'] }}</td>
            <td>{{ $listDetails['selection_cities'] }}</td>
            <td>{{ $listDetails['pan_india'] }}</td>
            <td>{{ in_array('South', explode(', ', $listDetails['zone_yes_list'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('North', explode(', ', $listDetails['zone_yes_list'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('East', explode(', ', $listDetails['zone_yes_list'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('West', explode(', ', $listDetails['zone_yes_list'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('Central', explode(', ', $listDetails['zone_yes_list'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('ANDHRA PRADESH', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('TELANGANA', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('ASSAM', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('BIHAR', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('CHHATTISGARH', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('GUJARAT', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('DELHI-NCR', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('JHARKHAND', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('KARNATAKA', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('KERALA', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('MADHYA PRADESH', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('MAHARASHTRA - Mumbai', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('MAHARASHTRA - Pune', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('ORISSA', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('PUNJAB', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('RAJASTHAN', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('TAMIL NADU', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('UTTAR PRADESH', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ in_array('WEST BENGAL', explode(', ', $listDetails['cities_yes_lists'])) ? 'Yes' : 'No' }}</td>
            <td>{{ $listDetails['department'] }}</td>
            <td>{{ $listDetails['business_model'] }}</td>
            <td>{{ $listDetails['rejected_comments'] }}</td>
            

        </tr>
        @php $i++; @endphp
        @endforeach
    </tbody>
</table>