@extends("cs.layouts.app")

@section('content-header')
<div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Payment</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/home') }}">Home</a></li>
          <li class="breadcrumb-item active">Payment</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@if(Session::has('flash_message'))
    <div class="alert alert-success">
        {{ Session::get('flash_message') }}
    </div>
@endif
@section('content')
 <div class="card">
    <input type="hidden" id="ajaxGUrl" value="{{url('cs/paymentatin/grid')}}" />
    
    <div class="card-body">
    	<div class="table-responsive">
          <table id="viewForm" class="table table-striped table-hover dataTable no-footer">
            <thead>
            <tr>
                <th>Payment ID</th>
                <th>Property ID</th>
                <th>ATIN</th> 
                <th>Mobile</th> 
                <th>Amount</th>
                <th>Store Name</th>
                <th>Market Name</th>
                <th>Zone</th>
                <th>Owner's name</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
            </tfoot>
          </table>
    	</div>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</div>

@endsection

@push('scripts')
<script type="text/javascript">
    var columns = [
			{ data: 'payment_id' },
			{ data: 'property_id' },
			{ data: 'atin' },
			{ data: 'mobile_number' },
			{ data: 'amount' },
			{ data: 'store_name' },
			{ data: 'market_name' },
			{ data: 'zone' },
			{ data: 'owners_name' },
			];
	
</script>

@endpush

