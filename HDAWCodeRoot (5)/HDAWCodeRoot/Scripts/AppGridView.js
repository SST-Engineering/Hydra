 
function updatedField(isa, fieldName) {
   var id = $('alc_run_'+isa+'_updatedRows');
   if (id) id.value += ','+fieldName;
   var save_id = $('alc_run_'+isa+'_Save');
   if (save_id) save_id.style.visibility = "visible";
   var undo_id = $('alc_run_'+isa+'_Undo');
   if (undo_id) undo_id.style.visibility = "visible";
   }
   
function keyPressUpdate(isa, fieldName, ev) {
   var keycode; 
   if (window.event) keycode = window.event.keyCode; 
   else if (ev) keycode = ev.which; 
   updatedField(isa, fieldName);

   if (keycode == 13) {
      updatedField(isa, fieldName);
      return false;
	  }
   return true;
   }
