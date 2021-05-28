@extends("cs.layouts.app")

@section('content-header')
<div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">CS Log</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/home') }}">Home</a></li>
          <li class="breadcrumb-item active">CS Log</li>
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
    <input type="hidden" id="ajaxGUrl" value="{{url('cs/cslog/grid')}}" />
    
    <div class="card-body">
    	<div class="table-responsive">
          <table id="viewForm" class="table table-striped table-hover dataTable no-footer">
            <thead>
            <tr>
                <th>ATIN</th> 
                <th>Revenue Name</th>
                <th>Amount</th>
                <th>Transaction Reference</th>
                <th>PaymentRetrivial Reference</th>
                <th>IBRIS Request</th>
                <th>IBRIS Response</th>
                <th>created_at</th>
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
			{ data: 'payment_atin_id' },
			{ data: 'shop_fees_id' },
			{ data: 'amount' },
			{ data: 'transactionreference' },
			{ data: 'PaymentRetrivialReference' },
			{ data: 'request_dump' },
			{ data: 'ibris_dump' },
			{ data: 'created_at' },
			];
	
</script>

@endpush
