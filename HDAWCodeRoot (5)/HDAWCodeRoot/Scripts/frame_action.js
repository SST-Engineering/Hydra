//<script type="text/javascript">

//=============================================================================


function issuePost(aAct) {
   var pst = parent.document.getElementById("pad_post");
   pst.name = "ACTION_"+aAct;
   pst.click();
}
function HDA_ShowDialog(dialog, action, ev) {
   var id = parent.document.getElementById(dialog);
   if (id) {
      var mask = parent.document.getElementById("alc_mask");
      if (mask) {
         mask.className="alc-masked";
         }
      var id_act = parent.document.getElementById(dialog+'_action');
      if (id_act) id_act.name='ACTION_'+action;
      id.style.display='inline-block';
      }
   }

function HDA_HideDialog(dialog) {
   var mask = parent.document.getElementById("alc_mask");
   if (mask) {
      mask.className="alc-mask";
      }
   var id = parent.document.getElementById(dialog);
   if (id) id.style.display='none';
   return false;
   }

var resetScrollLog=0;

function HDA_Pulse(sessid, acc, rate, limit) {
   if (rate>0) window.setTimeout("HDA_Tick('"+sessid+"','"+acc+"',"+limit+")", rate);
}


function HDA_Tick(sessid, acc, limit) {
  window.location.href="HDAW.php?load=HDA_BackFillLogger&HDASID="+sessid+"&ACC="+acc+"&SCROLL="+resetScrollLog+"&limit="+limit;
}

var scrollSetting = false;
function alc_scroller() {
  var ts = $("HDA_LOG");
  if (ts) {
     if (scrollSetting) return false;
     resetScrollLog = parseInt(ts.scrollTop);
     }
}

function alc_scroll_set(n) {
  scrollSetting = true;
  var ts = $("HDA_LOG");
  if (ts) {
     resetScrollLog = n;
     ts.scrollTop = n;
     }
  scrollSetting = false;
}

var touch_scrolling = false;
var touch_scrolly = 0;
var touch_lastdy = 0;
function touch_scroll_start(ev) {
   if (!ev.target) ev.target = ev.srcElement;
   window.status = ev.target.id;
   if (ev.target.id != "HDA_LOG") {
      touch_scrolling = true;
      touch_lastdy = 0;
      touch_scrolly = ev.clientY;
      }
   else touch_scrolling = false;
}
function touch_scroll_move(ev) {
   if (touch_scrolling) {
      var ts = $("HDA_LOG");
      if (ts) {
         var ats = parseInt(ts.scrollTop);
         var dy = (touch_scrolly - ev.clientY);
         if (Math.abs(dy)<touch_lastdy) {
            touch_scrolly = ev.clientY;
            touch_lastdy = 0;
            }
         else touch_lastdy = Math.abs(dy);
         ats -= dy;
         if (ats<0) ats = 0;
         ts.scrollTop = ats;
         resetScrolling = ats;
         }
      }
}
function touch_scroll_end() {
   touch_scrolling = false;
}



//</script>
