<table>
    <thead>
    <tr>
        <th>S.No</th>
        <th>TLC</th>
        <th>MLC</th>
        <th>LLC</th>
        <th>Department</th>
        <th>Brand</th>
        <th>Product Name</th>
        <th>BB Code</th>
        <th>Weight</th>
        <th>Pack Type</th>
        <th>Multipack Description</th>
        <th>Created By</th>
        <th>Created Date</th>
        
        
    </tr>
    </thead>
    <tbody>
        @php $i=1; @endphp
    @foreach($details as $detail)
        <tr>
            <td>{{ $i }}</td>
            <td>{{ $detail->tlc_name  }}</td>
            <td>{{ $detail->slc_name  }}</td>
            <td>{{ $detail->blc_name  }}</td>
            <td>{{ $detail->department_name  }}</td>
            <td>{{ $detail->brand_name  }}</td>
            <td>{{ $detail->product_name  }}</td>
            <td>{{ $detail->bb_code  }}</td>
            <td>{{ $detail->weight  }}</td>
            <td>{{ $detail->pack_type  }}</td>
            <td>{{ $detail->multipack_description  }}</td>
            <td>{{ getUsername($detail->created_by)  }}</td>
            <td>{{ Displaydateformat($detail->created_at)   }}</td>
            
            
        </tr>
         @php $i++; @endphp
    @endforeach
    </tbody>
</table>