var grid;
var alcw_update;
var grid_filter;

function runGridView(devUser, filter) {
   grid_filter = filter;
   alcw_update = new dataProcessor('HDAW.php?load=HDA_UpdateGridView&taction=update&enabled='+devUser);
   grid = new dhtmlXGridObject('planViewGrid');
   grid.setImagePath("Images/");
   grid.enableMultiselect(false);
   if (devUser==0) grid.enableEditEvents(false,false,'disable');
   alcw_update.init(grid); 
   grid.loadXML('HDAW.php?load=HDA_UpdateGridView&taction=load&cat='+filter+'&enabled='+devUser);
   }
   
var gridOpenSequence = 0;
function openGridItem() {
   gridOpenSequence = 0;
   nextGridItem();
   }
function nextGridItem() {
   var idd = $('onGridSearch');
   if (idd) {
      try {
         find = new XMLHttpRequest();
         }
      catch (ee) {
         if (typeof window.ActiveXObject != 'undefined' ) find = new ActiveXObject('Microsoft.XMLHTTP');
         }
      if (find) {
	     var str = encodeURIComponent(idd.value);
         find.open("GET", 'HDAW.php?load=HDA_UpdateGridView&taction=find&id='+str+'&seq='+gridOpenSequence+'&E', false);
		 find.send();
         var txt = find.responseText; 
         if (window.DOMParser) {
            parser=new DOMParser();
            xmlDoc=parser.parseFromString(txt,"text/xml");
            }
         else  {
            xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
            xmlDoc.async=false;
            xmlDoc.loadXML(txt); 
            }
         var n = xmlDoc.getElementsByTagName("pfid");
		 n = n[0];
		 if (n.childNodes.length>0) {
		   n = n.childNodes[0];
		   t = n.nodeValue;
		   grid.selectRowById(t,true);
		   }
         var n = xmlDoc.getElementsByTagName("seq");
		 n = n[0];
		 if (n.childNodes.length>0) {
		   n = n.childNodes[0];
		   t = n.nodeValue;
		   gridOpenSequence = t;
		   }
		gridOpenSequence++;
		}
      }
   }
   
function saveGridState() {
   }
function refreshGridView() {
   }

   
