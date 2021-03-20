$( "#opt2Tab tbody" ).sortable( {
	update: function( event, ui ) {
	$('input.currentposition').each(function(idx) {
		$(this).val(idx+1);
	});
  }
});
var position=2;
$(document).on('click','#addRow',function(){
	h='<tr> <td><input type="hidden" id="position" name="position[]" value="'+position+'" class="currentposition"><i class="fa fa-sort drag-icon" aria-hidden="true"></i> </td> </td> <td><input required type="text" name="label[]" class="form-control"/></td> <td><button type="button" class="btn bg-gradient-danger delRow" title="Delete"><i class="fa fa-trash"></i></button></td> </tr>';
	position = position + 1;
	$('#opt2Tab tbody').append(h);
});
$(document).on('click','.delRow',function(){
	$(this).parent().parent().remove();
});