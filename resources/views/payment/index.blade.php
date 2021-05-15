@extends("layouts.app")

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
    <input type="hidden" id="ajaxGUrl" value="{{url('/payment/grid')}}" />
    <!-- /.card-header -->
    <div class="card-body">
    	<div class="table-responsive">
          <table id="viewForm" class="table table-striped table-hover dataTable no-footer">
            <thead>
            <tr>
                <th>S/N</th>
                <th>Date</th>
				<th>Payment Ref</th>
				<th>Payment Retrivial Reference</th>
                <th>Amount paid</th>
                <th>Agent</th>
                <th>Phone Number</th>
                <th>Email</th>
                <th>Address</th>
                <th>Type</th>
                <th>Action</th>
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
			{ data: 'id', name: 'id' },
			{ data: 'paidon', name: 'paidon' },
			{ data: 'paymentref', name: 'paymentref' },
			{ data: 'PaymentRetrivialReference', name: 'PaymentRetrivialReference' },
			{ data: 'totalPayable', name: 'totalPayable' },
			{ data: 'name', name: 'name' },
			{ data: 'msisdn', name: 'msisdn' },
			{ data: 'email', name: 'email' },
			{ data: 'address', name: 'address' },
			{ data: 'type', name: 'type' },
			{ data: 'edit', name: 'edit' },
			];
	
	$('#viewForm').on('click', '#btnResend[data-remote]', function (e) { 
		if (confirm("Are you sure to resend?")) {		
			e.preventDefault();		 
			var url = $(this).data('remote');
			// confirm then
			$.ajax({
				url: url,
				type: 'POST',
				dataType: 'text',
				data: {method: '_POST', "_token": "{{ csrf_token() }}" , submit: true},
				success: function (response) {
					console.log(response);
				},
				error: function (result, status, err) {
					console.log(result);
				},
			}).always(function (data) {
				$('#viewForm').DataTable().draw(false);
			});
		}
		return false;
	});
</script>

@endpush
