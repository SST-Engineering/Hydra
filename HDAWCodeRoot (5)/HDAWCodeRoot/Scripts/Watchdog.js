
function Watchdog_Pulse(sessid) {
var tout = ShowMessages();
window.setTimeout("Watchdog('"+sessid+"')", tout);

}


function Monitor(sessid, process_item, etime, userid) {


  window.location.href="HDAW.php?load=HDA_BackCodeMonitor&HDASID="+sessid+"&CODE="+process_item+"&USERCODE="+userid+"&ETIME="+etime;

}
function Monitor_Pulse(sessid, process_item, etime, userid) {
window.setTimeout("Monitor('"+sessid+"','"+process_item+"',"+etime+",'"+userid+"')", 5000);

}


function Watchdog(sessid) {
  window.location.href="HDAW.php?load=HDA_Watchdog&HDASID="+sessid;
//  window.setTimeout("Watchdog('"+sessid+"')", 10000);
}

var textSpan = null;
var textSpanWd = 0;

function PlayMessage() {
   var m = parseInt(textSpan.style.marginLeft);
   m -= 1;
   if (m<(-textSpanWd)) m = textSpanWd;
   textSpan.style.marginLeft = m+"px";
   setTimeout("PlayMessage()",20);
}

function ShowMessages() {
   var inDiv = parent.document.getElementById("Watchdog_Message");
   if (inDiv) inDiv.style.visibility='visible';
   textSpanWd = dhtml.getElementWidth(inDiv);
   textSpan = parent.document.getElementById("Watchdog_Message_Text");
   var textLen = parent.document.getElementById("Watchdog_Message_TextLen");
   if (typeof msgs !== 'undefined') {
      var msg = ""; for(var i = 0; i<msgs.length; i++) msg += msgs[i];
      textLen.innerHTML = msg;
      textSpan.style.width=textLen.offsetWidth;
      textSpan.innerHTML = msg;
      textSpan.style.marginLeft=textSpanWd+"px";
      setTimeout("PlayMessage()",1000);
      return Math.min((msg.length*100+5000), 30000);
      }
   return 10000;
}


function SayOnlineCount(n) {
   var spanDiv = parent.document.getElementById("OnLineCount");
   if (spanDiv) spanDiv.innerHTML = n;
   var keepN = parent.document.getElementById("OnLineCountP");
   if (keepN) keepN.value = n;
}




