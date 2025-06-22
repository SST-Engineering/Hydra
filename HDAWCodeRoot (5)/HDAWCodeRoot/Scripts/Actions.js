var clicked = false;
function issuePost(aAct) {
	if (clicked) {
		return false;
	}
   var pst = $("pad_post");
   if (pst) {
      pst.name = "ACTION_"+aAct;
	  clicked = true;
      pst.click();
	  }
}

function issueParentPost(aAct) {
   var pst = parent.document.getElementById("pad_post");
   if (pst) {
      pst.name = "ACTION_"+aAct;
      pst.click();
	  }
}
function issueFramePost(aAct, btn) {
   var pst = $(btn);
   pst.name = "ACTION_"+aAct;
   pst.click();
   }
function issueMousePost(aAct, aDiv, ev) {
   var aDivId = $(aDiv);
   if (aDivId) {
	var p = dhtml.getElementPosition(aDivId);
	if (p) {
		var x = Math.round(ev.offsetX);
		var y = Math.round(ev.offsetY);
		aAct = aAct+'-'+x+'-'+y;
		return issuePost(aAct, ev);
		}
	}
   }

function StopEvent(event) {
   if (typeof event != 'undefined') {
      if (typeof event.stopPropagation != 'undefined') {
         event.stopPropagation();
         }
      if (typeof event.cancelBubble != 'undefined') {
         event.cancelBubble = true;
         }
      }
   }

function keyPressPost(aAct, ev, nextdialog) {
   var keycode; 
   if (window.event) keycode = window.event.keyCode; 
   else if (ev) keycode = ev.which; else return true; 
   if (keycode == 13) { 
      issuePost(aAct,ev); return false; 
	  } 
   else return true; 
}

function keyPressRun(runit, ev) {
   var keycode; 
   if (window.event) keycode = window.event.keyCode; 
   else if (ev) keycode = ev.which; else return true; 
   if (keycode == 13) { 
      runit(); return false; 
	  } 
   else return true; 
}

function keyPressFalse(ev) {
   var keycode; 
   if (window.event) keycode = window.event.keyCode; 
   else if (ev) keycode = ev.which; 
   if (keycode == 13 && ev.target.nodeName != "TEXTAREA") return false;
   return true;
   }
   
function keyPressChar() {
   var keycode; 
   var el;
   if (window.event) el = window.event.srcElement;
   else if (ev) el = ev.target;
   if (el) {
      if (window.event) keycode = window.event.keyCode; 
      else if (ev) keycode = ev.which; else return true;
      var rx = new RegExp("^[a-xA-X]{1,1}$");
	  var s = String.fromCharCode(keycode);
	  s = s.toUpperCase();
      if (rx.test(s)) {
	     el.value = s;
		 var idi = el.id.split('-');
		 var idx = parseInt(idi[1]);
		 var xid = document.getElementById(idi[0]+'-0');
		 if (xid) xid.value = xid.value.substr(0,idx-1)+s+xid.value.substr(idx);
		 var nid = document.getElementById(idi[0]+'-'+(idx+1));
		 if (nid) nid.focus();
	     return false;
		 }
	  return false;
	  }
   return true;
   }
function keyChar2Change() {
   var el;
   if (window.event) el = window.event.srcElement;
   else if (ev) el = ev.target;
   if (el) {
      var pair = el.value;
      var idi = el.id.split('-'); // id-idx-len-idx2-XX-check
	  var idx = parseInt(idi[1]);
	  var len = parseInt(idi[2]);
	  var idx2 = parseInt(idi[3]);
	  var def = idi[4];
	  var chk = idi[5];
	  var chkid = document.getElementsByName(chk);
	  var rec = chkid.length==1;
	  var ron = (rec)?chkid.item(0).checked:false;
	  var xid = document.getElementById(idi[0]);
	  if (xid) {
	     xid.value = xid.value.substr(0,idx)+pair.substr(0,len)+xid.value.substr(idx+len); 
		 if (rec) {
			 if (ron) {
				xid.value = xid.value.substr(0,idx2)+def.substr(0,len)+xid.value.substr(idx2+len);
				}
			 else xid.value = xid.value.substr(0,idx2)+pair.substr(0,len)+xid.value.substr(idx2+len); 
		     }
		 }
      }
   return true;
   }
function keyChar3Check() {
   var el;
   if (window.event) el = window.event.srcElement;
   else if (ev) el = ev.target;
   if (el) {
      var toset = el.checked;
      var idi = el.id.split('-'); // id-idx-len-idx2-def
	  var idx = parseInt(idi[1]);
	  var len = parseInt(idi[2]);
	  var idx2 = parseInt(idi[3]);
	  var def = idi[4];
	  var xid = document.getElementById(idi[0]);
	  if (xid) {
	     if (toset) xid.value = xid.value.substr(0,idx)+def+'R'+xid.value.substr(idx+len+1);
	     else xid.value = xid.value.substr(0,idx)+xid.value.substr(idx2, len)+'R'+xid.value.substr(idx+len+1);;
         }		 
      }
   return true;
   }
   
function keyPressString() {
   var keycode; 
   var el;
   if (window.event) keycode = window.event.keyCode; 
   else if (ev) keycode = ev.which; else return true; 
   if (window.event) el = window.event.srcElement;
   else if (ev) el = ev.target;
   if (keycode==8) return true;
   if (el) {
      var idi = el.id.split('-');
      el.value = el.value.toUpperCase();
      var s = el.value;
	  for (var i = 0; i<12; i++) {
	     var xid = document.getElementById(idi[0]+'-'+(i+1));
		 if (xid) xid.value = s.charAt(i);
	     }
      }
   return true;
   }

   var escape_ht = 0;
function keyPressEscape() {
   var keycode; 
   var el;
   if (window.event) keycode = window.event.keyCode; 
   else if (ev) keycode = ev.which; else return true; 
   if (window.event) el = window.event.srcElement;
   else if (ev) el = ev.target;
   if (keycode==27) {
	   if (el.style.height=='100%') {
		  var ht = escape_ht;
		  alert(ht);
		  if (ht == 'undefined' || ht == 0) ht = '300px';
		  el.style.height = ht;
		  el.style.width = '100%';
		  el.style.position = 'static';
		  el.style.top = el.style.left = 0;
          var id = parent.document.getElementById(el.dialog_id);
          if (id) id.className = "alc-dialog alc-dialog-vlarge";
		  }
	   else {
          var id = parent.document.getElementById(el.dialog_id);
          if (id) id.className = "alc-dialog alc-dialog-max";
		  escape_ht=el.style.height;
		  el.style.height = '100%';
		  el.style.width = '100%';
		  el.style.position = 'absolute';
		  el.style.top = el.style.left = 0;
          HDA_msgFadeOut("Press ESC to return");
		  }
      }
   return true;
   }   


function alc_transitFadeOpen(k, v) {
   var id = $(k);
   if (id) {
      if (v>=100) {id.style.opacity=1; id.style.filter='none'; return; }
      id.style.opacity=v/100;
      id.style.filter="alpha(opacity="+v+")";
      if (v==0) id.style.display = 'inline-block';
      v += 15;
      window.setTimeout("alc_transitFadeOpen('"+k+"',"+v+")",10);
      }
   }
      
function alc_transitFadeClose(k, v, r) {
   var id = $(k);
   if (id) {
      if (v<=0) {id.style.opacity=0; id.style.filter="alpha(opacity=0)"; id.style.display='none'; return; }
      id.style.opacity=v/100;
      id.style.filter="alpha(opacity="+v+")";
      v -= r;
      window.setTimeout("alc_transitFadeClose('"+k+"',"+v+","+r+")",50);
      }
   }

function HDA_msgFadeOut(msg) {
   var id = $('alc_transit_msg');
   if (id) {
      id.innerHTML = msg;
      var width        = window.innerWidth  ? window.innerWidth  : document.documentElement.clientWidth;
      var height       = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight;
      id.style.left = (document.documentElement.scrollLeft+(width -id.clientWidth )/2+0)+'px';
      id.style.top  = (document.documentElement.scrollTop +(height-id.clientHeight)/2+0)+'px';
      id.style.display = 'inline-block';
      alc_transitFadeClose('alc_transit_msg', 100, 1);
      }
   }
   
function HDA_run(bat) {
   var shell = new ActiveXObject('wscript.shell');
   var run = shell.Run(bat, 3, false);
   }


function HDA_HideDialog(dialog) {
   var id = $(dialog);
   if (id) id.style.display='none';
   var mask = $("alc_mask");
   if (mask) {
         mask.className="alc-mask hda-background";
         }
   issuePost("ClosedDialog");
   return false;
   }



function HDA_maxDialog(dialog_id) {
   var id = $(dialog_id);
   if (id) id.className = id.className + ' alc-dialog-max';
   }

function show_help(from, help_root) {
   var element = document.getElementById(from);
   var totext = document.getElementById('show_code_help');
   var txtFile = null;
   try {
      try {
         txtFile = new XMLHttpRequest();
         }
      catch (ee) {
         if (typeof window.ActiveXObject != 'undefined' ) txtFile = new ActiveXObject('Microsoft.XMLHTTP');
         }
      if (txtFile) {
      //   txtFile.open("GET", help_root+"/"+element.value.toLowerCase()+".txt", true);
         txtFile.open("GET", help_root+element.value.toLowerCase(), true);
         txtFile.onreadystatechange = function() {
            if (txtFile.readyState === 4) { 
               if (txtFile.status === 200) { 
                  t = txtFile.responseText;  totext.innerHTML =t;
                  }
               else totext.innerHTML = " Failed to find help for "+element.value;
               }
            };
         txtFile.send(null);
         }
      else totext.innerHTML = " Failed to create am HTML Request reader ";
      }
   catch (e) {
      totext.innerHTML = " Failed with exception "+e.message;
      }
   }

function show_auto_help(in_text, keys, fns) {
   var dwin = document.getElementById(keys);
   if (dwin) {
      if (dwin.parentNode.parentNode.style.display=="none") return;
      }
   var t = document.getElementById(in_text);
   if (t) {
      var subject = t.value.split(';');
      if (subject && subject.length>0) {
         var kid = document.getElementById(keys);
         var fid = document.getElementById(fns);
         for (i=subject.length-1; i>=0; i--) {
           for (k=0; k<kid.options.length; k++) {
              if (kid.options[k].value==subject[i].toUpperCase()) {
                 kid.selectedIndex = k; 
                 if (kid.fireEvent) kid.fireEvent('onchange'); else kid.onchange();
                 return;
                 }
              }
           for (f = 0; f<fid.options.length; f++) {
              if (fid.options[f].value==subject[i].toUpperCase()) {
                 fid.selectedIndex = f; if (fid.fireEvent) fid.fireEvent('onchange'); else fid.onchange();
                 return;
                 }
              }
           }
         }
      }
   }

   
function HDA_ShowDiv(div_name) {
   var d = $(div_name);
   if (d) {
      d.style.visibility = 'visible';
      }
   }
function HDA_HideDiv(div_name) {
   var d = $(div_name);
   if (d) {
      d.style.visibility = 'hidden';
      }
   }
function showParentDiv(div_name) {
   var d = parent.document.getElementById(div_name);
   if (d) {
      d.style.visibility = 'visible';
	  d.style.display='block';
      }
   }
   
function HDA_toggleDivClass(div_name, class_init, class_alt) {
   var d = parent.document.getElementById(div_name);
   if (d) {
      if (d.className == class_init) d.className = class_alt;
      else d.className = class_init;
      }
   }	  
function HDA_sizeDivMax(div_name) {
   var d = parent.document.getElementById(div_name);
   if (d) {
      var parent_ht = dhtml.getElementHeight(d.parentElement);
	  d.style.height = (parent_ht-24)+"px";
      }
   }
   
function setParentSpanColor(tid, color) {
   var id = parent.document.getElementById(tid);
   if (id) id.style.backgroundColor = color;
   }   

function setValue(tid,v) { 
   var id = $(tid);
   if (id) id.value = v;
   } 
function setParentSpanValue(tid, v) {
   var id = parent.document.getElementById(tid);
   if (id) id.innerHTML = v;
   id = parent.document.getElementById(tid+'_v');
   if (id) id.value = v;
   }
function getParentValue(tid) {
   var id = parent.document.getElementById(tid);
   if (id) return id.value;
   return null;
   }
   
function getValue(tid) {
   var id = $(tid);
   if (id) return id.value;
   return 0;
   }
function postValue(tid, did) {
   var aid = $(tid);
   var bid = $(did);
   bid.value = aid.value;
   }
var positionDialog = null;
var positionValue = 0;
function setPositionValue(startend) {
   if (positionDialog) {
      setValue(positionDialog+"_"+startend, positionValue);
      var len = getValue(positionDialog+"_End")-getValue(positionDialog+"_Start")+1;
      setValue(positionDialog+"_Length",len);
      cancelPositionValue();
      }
   }
function cancelPositionValue() {
   var dialog_id = $(positionDialog+"_StartEnd");
   if (dialog_id) dialog_id.style.display='none';
   }
function setPosition(dialog, v, ev) {
   var dialog_id = $(dialog+"_StartEnd");
   if (dialog_id) {
      dialog_id.style.display='block';
      dialog_id.style.top = ev.layerY+"px";
      dialog_id.style.left = ev.layerX+"px";
      positionDialog = dialog;
      positionValue = v;
      }
   }

function setStyleAttr(id, att, v) {
	var did = $(id);
	if (did) {
		var style = did.style.cssText;
		var rx = new RegExp(att+"[^;]{1,}");
		style = style.replace(rx,att+":"+v);
		did.setAttribute("style",style);
	}
}

var row_selected=null;
function HDA_SelectRow(div_name) {
   if (row_selected) {
      row_selected.className = 'alc-form-row';
      }
   var d = $(div_name);
   if (d) {
      row_selected = d;
      d.className = 'alc-form-srow';
      }
   }
function HDA_UnSelectRow(div_name) {
   var d = $(div_name);
   if (d) {
      d.className = 'alc-form-row';
      }
   }

function HDA_setLoadTags(v, c, postid) {
   var setid = $(postid);
   if (setid) {
      var seticon = $(postid+'_'+setid.value+'_TAG_ICON');
      if (seticon) seticon.className='alc-tag-item';
      setid.value = v;
      seticon = $(postid+'_'+v+'_TAG_ICON');
      if (seticon) seticon.className='alc-tag-item-selected';
      }
   var caption = $(postid+'_CAPTION');
   if (caption) caption.innerHTML=c;
   }


function openWindow(url, title, options) {
   if (options) {
      if (options.length==0) options = 'left=20,top=20,width=500,height=500,toolbar=0,resizable=1,location=1,status=0,menubar=0,scrollbars=1';
      }
   else options = '';
   var winRef = window.open(url, title, options);
   return winRef;
   }

function closeWindow() {
   window.close();
   }

var monitor_session_id;
function doOnClose(e) {
      e = e || window.event;
	  if (e) {
		var go = confirm("Abort Run On Close?");
		if (go) {
			window.location = "HDAW.php?load=HDA_CodeMonitor&CODE="+monitor_session_id+"&ABORT=YES&ACTION_CodeMonitor_Abort-"+monitor_session_id;
			if (e) e.returnValue = "Aborted Run";
			return 'Aborted Run';
			}
		return "Not aborting - Will continue in background";
	  }
   }

function setOnClose(sessid) {
   monitor_session_id = sessid;
   if (window.attachEvent) {
      window.attachEvent('onbeforeunload',doOnClose);
	  }
   else {
	   window.addEventListener('beforeunload',doOnClose,false);
	}
   }
function monitorClearClose() {
   if (window.detachEvent) {
      window.detachEvent('onbeforeunload',doOnClose);
	  }
   else {
	   window.removeEventListener('beforeunload',doOnClose,false);
	}
   }

function HDA_playLoading(n) {
   n++;
   if (n>4) n = 0;
   var loadId = $('pad_loading');
   if (loadId) {
      var t = "Loading ";
      for (var i=0; i<3; i++) if (n>i) t = t + "."; else t = t + "&nbsp;";
      loadId.innerHTML = "<p>"+t+"</p>";
      window.setTimeout("HDA_playLoading("+n+")", 500);
      }
   }

function HDA_sayLoading() {
   var mask = $("alc_mask");
   if (mask) {
      mask.style.opacity="0.5";
      mask.style.filter="alpha(opacity=50)";
      }
   var loadId = $('pad_loading');
   if (loadId) {
      loadId.style.display='block';
      window.setTimeout("HDA_playLoading(0)", 500);
      }
   }
   




function GotoALCSite(p) {
window.location.href="HDA_Entry.php?"+p+"&E";
}




