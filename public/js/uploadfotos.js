function readURL(input,id,filename) {

  if (input.files && input.files[0]) {
    var reader = new FileReader();

    reader.onload = function(e) {
        
      jQuery('#blah-'+id).attr('src', e.target.result);
      jQuery('#blah-'+id).attr('title', filename);
      jQuery('#blah-'+id).attr('alt', filename);
    }
    reader.readAsDataURL(input.files[0]);
  }
}
jQuery(document).ready(function() {
  jQuery(".Images").change(function() {
    var id = jQuery(this).attr('id');
    var filename = jQuery(this).val().split('\\').pop();
    readURL(this,id,filename);
  });
});

function agregarFila() {


    

}

//document.getElementById('preview').onclick = openPhotoSwipe;
// #Id   .Clase
//document.getElementById('btn-preview').onclick = openPhotoSwipe;