var alcw_update;


function runDiaryView(devUser) {
   alcw_update = new dataProcessor('HDAW.php?load=HDA_UpdateDiaryView&taction=update&enabled='+devUser);
   scheduler.config.xml_date="%Y-%m-%d %H:%i";
			scheduler.config.details_on_dblclick = true;
			scheduler.config.details_on_create = true;
   scheduler.init('planViewDiary',null,"week");
   alcw_update.init(scheduler); 
   scheduler.load('HDAW.php?load=HDA_UpdateDiaryView&taction=load&enabled='+devUser);
   }
   var alc_diary_edit_ev;
   
		scheduler.showLightbox = function(id) {
			alc_diary_edit_ev = scheduler.getEvent(id);
			HDA_ShowDialog('alc_diary_edit');
			$("alc_diary_edit_description").focus();
			$("alc_diary_edit_description").value = alc_diary_edit_ev.text;
			$("alc_diary_edit_details").value = alc_diary_edit_ev.details || "";
			$("alc_diary_edit_tag").value = alc_diary_edit_ev.tagged || "";
			$("alc_diary_edit_start").value = alc_diary_edit_ev.start_date || "";
			$("alc_diary_edit_end").value = alc_diary_edit_ev.end_date || "";
		};
		

		function alc_diary_edit_save_form() {
			alc_diary_edit_ev.text = $("alc_diary_edit_description").value;
			alc_diary_edit_ev.details = $("alc_diary_edit_details").value;
			alc_diary_edit_ev.tagged = $("alc_diary_edit_tag").value;
			alc_diary_edit_ev.start_date = new Date($("alc_diary_edit_start").value);
			alc_diary_edit_ev.end_date = new Date($("alc_diary_edit_end").value);
			HDA_HideDialog('alc_diary_edit');
			scheduler.updateEvent(alc_diary_edit_ev.id);
			alcw_update.setUpdated(alc_diary_edit_ev.id, true, 'updated');
			scheduler.updateView();
		}
		function alc_diary_edit_close_form() {
		HDA_HideDialog('alc_diary_edit');
		}

		function alc_diary_edit_delete_event() {
			scheduler.deleteEvent(alc_diary_edit_ev);
			alcw_update.setUpdated(alc_diary_edit_ev.id, true, 'deleted');
			HDA_HideDialog('alc_diary_edit');
		}

   
