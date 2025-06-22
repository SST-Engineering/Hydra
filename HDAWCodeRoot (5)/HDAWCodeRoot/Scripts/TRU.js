var resetScrollRecon=0;

function TRU_Pulse(sessid, rate, limit, home_root) {
   if (rate>0) window.setTimeout("TRU_Tick('"+sessid+"',"+limit+",'"+home_root+"')", rate);
}


function TRU_Tick(sessid, limit, home_root) {
  window.location.href="HDAW.php?load=../"+home_root+"/TRU_BackFillRecon&HDASID="+sessid+"&SCROLL="+resetScrollRecon+"&limit="+limit;
}




var scrollSettingRecon = false;
function tru_scroller() {
  var ts = $("TRU_REC");
  if (ts) {
     if (scrollSettingRecon) return false;
     resetScrollRecon = parseInt(ts.scrollTop);
     }
}

function tru_scroll_set(n) {
  scrollSettingRecon = true;
  var ts = $("TRU_REC");
  if (ts) {
     resetScrollRecon = n;
     ts.scrollTop = n;
     }
  scrollSettingRecon = false;
}

var tru_touch_scrolling = false;
var tru_touch_scrolly = 0;
var tru_touch_lastdy = 0;
function tru_touch_scroll_start(ev) {
   if (!ev.target) ev.target = ev.srcElement;
   window.status = ev.target.id;
   if (ev.target.id != "TRU_REC") {
      tru_touch_scrolling = true;
      tru_touch_lastdy = 0;
      tru_touch_scrolly = ev.clientY;
      }
   else tru_touch_scrolling = false;
}
function tru_touch_scroll_move(ev) {
   if (tru_touch_scrolling) {
      var ts = $("TRU_REC");
      if (ts) {
         var ats = parseInt(ts.scrollTop);
         var dy = (tru_touch_scrolly - ev.clientY);
         if (Math.abs(dy)<tru_touch_lastdy) {
            tru_touch_scrolly = ev.clientY;
            tru_touch_lastdy = 0;
            }
         else tru_touch_lastdy = Math.abs(dy);
         ats -= dy;
         if (ats<0) ats = 0;
         ts.scrollTop = ats;
         resetScrollingRecon = ats;
         }
      }
}
function tru_touch_scroll_end() {
   tru_touch_scrolling = false;
}

