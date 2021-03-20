$( "#opt2Tab tbody" ).sortable( {
	update: function( event, ui ) {
	$('input.currentposition').each(function(idx) {
		$(this).val(idx+1);
	});
  }
});
var position=2;
$(document).on('click','#addRowAttr',function(){
	h='<tr><td><input type="hidden" id="position" name="position[]" value="'+position+'" class="currentposition"><i class="fa fa-sort drag-icon" aria-hidden="true"></i> </td><td>'+AttrSel+'</td></td> <td><input required type="text" name="label[]" class="form-control"/></td> <td><button type="button" class="btn bg-gradient-danger delRow" title="Delete"><i class="fa fa-trash"></i></button></td> </tr>';
	position = position + 1;
	$('#opt2Tab tbody').append(h);
});
$(document).on('click','.delRow',function(){
	$(this).parent().parent().remove();
});

$(document).on('click','#collapseOne',function(){
	$("#collapseAdvance > div > a").removeClass("active");
});
$(document).on('click','#collapseTwo',function(){
	$("#collapseBasic > div > a").removeClass("active");
});

$('#viewFormRP').DataTable({
	"processing": true,
	"serverSide": true,
	"ajax": $("#ajaxGUrlRP").val(),
	"columns": columns
});
$('#viewFormUS').DataTable({
	"processing": true,
	"serverSide": true,
	"ajax": $("#ajaxGUrlUS").val(),
	"columns": columns
});
$('#viewFormCS').DataTable({
	"processing": true,
	"serverSide": true,
	"ajax": $("#ajaxGUrlCS").val(),
	"columns": columns
});

function previewImages() {
  var $preview = $('#preview').empty();
  if (this.files) $.each(this.files, readAndPreview);
  function readAndPreview(i, file) {
    if (!/\.(jpe?g|png|gif)$/i.test(file.name)){
      return alert(file.name +" is not an image");
    } // else...
    var reader = new FileReader();
    $(reader).on("load", function() {
      $preview.append($("<img/>", {src:this.result, height:125, class:'upload-image-thumb'}));
    });
    reader.readAsDataURL(file);
  }
}
$('#image1').on("change", previewImages);

function previewImagesOthers() {
  var $preview = $('#previewOthers').empty();
  if(this.files.length>4) 
  {
	  $('#imageOthers').val(''); 
	  $("#imageError").html(uploadErrorMsg);
	  return;
  }
  if (this.files) $.each(this.files, readAndPreview);
  function readAndPreview(i, file) {
    if (!/\.(jpe?g|png|gif)$/i.test(file.name)){
      return alert(file.name +" is not an image");
    } // else...
    var reader = new FileReader();
    $(reader).on("load", function() {
      $preview.append($("<img/>", {src:this.result, height:125, class:'upload-image-thumb'}));
    });
    reader.readAsDataURL(file);
  }
}
$('#imageOthers').on("change", previewImagesOthers);