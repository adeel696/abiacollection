$(".custom-opt-select").on('change', function(){
    var t = "";
	if(["field", "textarea", "date", "date_time", "time"].includes($(this).val()))
	{
		t='<div class="table-responsive"><table class="options table table-bordered"> <thead> <tr> <th>'+Price+'</th> <th>'+PriceType+'</th></tr> </thead><tbody><tr> <td> <input required min="0" type="number" value="0" name="price[]" class="form-control"/> </td> <td> <select name="price_type[]" class="form-control"> <option value="fixed" selected=""> Fixed </option> <option value="percent"> Percent </option> </select> </td> </tr></tbody></table></div>';
	}
	else
	{
		t='<div class="table-responsive"><table id="opt2Tab" class="options table table-bordered"> <thead> <tr> <th></th> <th>'+Label+'</th> <th>'+Price+'</th> <th>'+PriceType+'</th> <th></th></tr> </thead><tbody><tr class="sortable"><td><input type="hidden" id="position" name="position[]" value="1" class="currentposition"><i class="fa fa-sort drag-icon" aria-hidden="true"></i> </td> <td> <input required type="text" name="label[]" class="form-control"/> </td> <td><input required min="0" type="number" value="0" name="price[]" class="form-control"/> </td> <td> <select name="price_type[]" class="form-control"> <option value="fixed" selected="">'+Fixed+'</option> <option value="percent">'+Percent+'</option> </select> </td> <td><button type="button" class="btn bg-gradient-danger delRow" title="Delete"><i class="fa fa-trash"></i></button></td> </tr></tbody></table><div><button type="button" class="btn bg-gradient-info" id="addRow"><i class="fas fa-plus"></i></button></div></div>';
		
	}
	$("#valueRow").html(t);
	$( "#opt2Tab tbody" ).sortable( {
		update: function( event, ui ) {
		$('input.currentposition').each(function(idx) {
			$(this).val(idx+1);
		});
	  }
	});
});
var position=2;
$(document).on('click','#addRow',function(){
	h='<tr> <td><input type="hidden" id="position" name="position[]" value="'+position+'" class="currentposition"> <i class="fa fa-sort drag-icon" aria-hidden="true"></i> </td> <td> <input required type="text" name="label[]" class="form-control"/> </td> <td> <input required min="0" type="number" value="0" name="price[]" class="form-control"/> </td> <td> <select name="price_type[]" class="form-control"> <option value="fixed" selected="">'+Fixed+'</option> <option value="percent">'+Percent+'</option> </select> </td> <td><button type="button" class="btn bg-gradient-danger delRow" title="Delete"><i class="fa fa-trash"></i></button></td> </tr>';
	position = position + 1;
	$('#opt2Tab tbody').append(h);
});
$(document).on('click','.delRow',function(){
	$(this).parent().parent().remove();
});