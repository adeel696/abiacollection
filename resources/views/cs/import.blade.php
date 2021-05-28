@extends("cs.layouts.app")
@section('content-header')
<div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Payment Atin</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/home') }}">Home</a></li>
          <li class="breadcrumb-item active">Payment Atin</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
{!! Form::open(['url' => 'cs/importpaymentatin', 'files' => true, 'id' => 'main-form']) !!}
<div class="row">
<div class="col-md-12">
  <div class="card card-info">
  	<div class="card-body">
	  	@if(Session::has('error_message'))
            <div class="alert alert-danger">
                {{ Session::get('error_message') }}
            </div>
        @endif
        @if(Session::has('success_message'))
            <div class="alert alert-success">
                {{ Session::get('success_message') }}
            </div>
        @endif
      <fieldset>
       <legend>
          Import 
       </legend>
       <p class="note">Please upload only (.XLSX) file with the following table columns: <br>{<strong>Payment ID,ATIN,Amount 	Store Name,Market Name,Zone,Owner's name
</strong>}</p>
       <br>
       <div class="control-group">
          <div class="col-lg-8">
             <div class="control-group ">
                <input name="payment_file" id="payment_file" type="file">                            
                <div class="text-danger"></div>
             </div>
             <br>
          </div>
       </div>
	  </fieldset>
      
      <div class="col-md-2">
          <label></label>
          {!! Form::submit('Upload', ['class' => 'form-control btn btn-primary']) !!}
      </div>
      
      <div class="row">
		<div class="col-md-12">
        	@if(Session::has('updatedRows'))
            	<br /><br />
            	<center><h3>Updated Rows</h3></center>
                <p>
                    @foreach(Session::get('updatedRows') as $data)
                    	<?php echo implode(",",$data) ?><br />
                    @endforeach
                </p>
            @endif
        </div>
      </div>
   	</div>
  </div>
  <!-- /.row -->
  {!! Form::close() !!}

@endsection
@push('scripts')
<script type="text/javascript">
   
</script>

@endpush