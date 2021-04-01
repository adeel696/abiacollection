$('.select2').select2({theme: 'bootstrap4'});
$('#viewForm').DataTable({
	"processing": true,
	"serverSide": true,
	"ajax": $("#ajaxGUrl").val(),
	"columns": columns,
	dom: 'Bfrtip',
	buttons: [
		{
			extend : 'csv',
			className: 'btn btn-primary',
			title : 'Export to CSV',
			exportOptions : {
				modifier : {
 					order : 'index', // 'current', 'applied',
 					page : 'all', // 'all', 'current'
					search : 'none' // 'none', 'applied', 'removed'
				},
			}
		}
	]
});

$('#viewForm').on('click', '#btnDelete[data-remote]', function (e) { 
	if (confirm($("#delTxt").val())) {		
		e.preventDefault();		 
		var url = $('meta[name="base-url"]').attr('content')+$(this).data('remote');
		// confirm then
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		$.ajax({
			url: url,
			type: 'DELETE',
			dataType: 'json',
			data: {method: '_DELETE' , submit: true},
			error: function (result, status, err) {
				//alert(result.responseText);
				//alert(status.responseText);
				//alert(err.Message);
			},
		}).always(function (data) {
			$('#viewForm').DataTable().draw(false);
		});
	}
	return false;
});

$(function () {
	$('.dtlDesc').summernote({height: 250,});
});

$(document).on('change','.imageup',function(){
	var $preview = $('#'+$(this).data("preview"));
	$preview.html('');
	if (this.files) $.each(this.files, readAndPreview);
	function readAndPreview(i, file) 
	{
		if (!/\.(jpe?g|png|gif)$/i.test(file.name))
		{
			return alert(file.name +" is not an image");
		}
		var reader = new FileReader();
		$(reader).on("load", function() 
		{
			$preview.append($("<img/>", {src:this.result, height:125, class:'upload-image-thumb'}));
		});
		reader.readAsDataURL(file);
	}
});

$('.remove-special-characters').on('keypress', function (event) {
    var regex = new RegExp("^[\-]$");
    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
    if (regex.test(key)) {
       event.preventDefault();
       return false;
    }
});
$('.remove-special-characters').on('paste', function (event) {
    if (regex.test(key)) {
       event.preventDefault();
       return false;
    }
});