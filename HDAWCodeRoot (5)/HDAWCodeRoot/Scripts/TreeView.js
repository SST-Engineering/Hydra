var tree;
var alcw_update;
var tree_update;
var tree_filter;
var tree_user;
function runTreeView(devUser, filter, user) {
   tree_update = devUser;
   tree_filter = filter;
   tree_user = user;
   alcw_update = new dataProcessor('HDAW.php?load=HDA_UpdateTreeView&user='+user+'&taction=update');
   tree = new dhtmlXTreeObject('planViewTree','100%','100%',0);
   alcw_update.init(tree); 
   tree.setImagePath('Images/');
   tree.setStdImages('leaf.gif', 'iconTexts.gif', 'iconTexts.gif');
   if (tree_update) tree.enableDragAndDrop(true, true);
   tree.attachEvent('onClick', 'treeOnClick');
   tree.attachEvent('onXLE', 'resetTreeState');
   tree.loadXML('HDAW.php?load=HDA_UpdateTreeView&user='+user+'&taction=load&cat='+filter);
   }
function reloadTreeView() {
   var idd = $('onOpenState');
   if (idd) idd.value = "ALL,";
   idd = $('onSelectId');
   if (idd) idd.value = "ALL,";
   idd = $('onSelectTitle');
   if (idd) idd.value = "";
   idd = $('onSelectDetails');
   if (idd) idd.innerHTML = "";
   idd = $('onSelectNotes');
   if (idd) idd.value = "";
   refreshTreeView(tree_update, tree_filter);
   }
function refreshTreeView() {
   tree.destructor();
   runTreeView(tree_update, tree_filter, tree_user);
   }
function treeOnClick(id) {
   var idd = $('onSelectId');
   if (idd) {
      iddt = $('onSelectTitle');
      if (iddt) iddt.value = tree.getItemText(id);
      if (idd.value != id) {
	     idd.value = id;
         idd = $('onSelectDetails');
         if (idd) idd.innerHTML = "";
         idd = $('onSelectNotes');
         if (idd) idd.value = "";
	     getTreeDetails(id);
		 }
	  }
   }
function gotoSelectedItem(action, ev) {
   var idd = $('onSelectId');
   if (idd) {
      issuePost(action+'-'+idd.value,ev);
      }
   }
function getTreeDetails(id) {
   var idd = $('onSelectError');
   var details = null;
   try {
      try {
         details = new XMLHttpRequest();
         }
      catch (ee) {
         if (typeof window.ActiveXObject != 'undefined' ) details = new ActiveXObject('Microsoft.XMLHTTP');
         }
      if (details) {
         details.open("GET", 'HDAW.php?load=HDA_UpdateTreeView&taction=details&id='+id, true);
         details.onreadystatechange = function() {
            if (details.readyState === 4) { 
               if (details.status === 200) { 
                  var txt = details.responseText; 
                  if (window.DOMParser) {
                     parser=new DOMParser();
                     xmlDoc=parser.parseFromString(txt,"text/xml");
                     }
                  else  {
                     xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
                     xmlDoc.async=false;
                     xmlDoc.loadXML(txt); 
                     }
                  var did = $('onSelectDetails');
                  if (did) did.innerHTML = "";
                  var n = xmlDoc.getElementsByTagName("note");
				  n = n[0];
				  if (n.childNodes.length>0) {
				     n = n.childNodes[0];
				     t = n.nodeValue;
                     t = decodeURIComponent(t.replace(/\+/g, ' '));
                     var did = $('onSelectNotes');
                     if (did) did.innerHTML = t;
					 }
				  //
				  n = xmlDoc.getElementsByTagName("Image");
				  n = n[0];
				  if (n.childNodes.length>0) {
				     n = n.childNodes[0];
				     t = n.nodeValue;
                     t = decodeURIComponent(t.replace(/\+/g, ' '));
                     var did = $('onSelectImage');
                     if (did) did.src = t;
					 }
                  //
				  var detail_s = "";
                  n = xmlDoc.getElementsByTagName("Status");
				  n = n[0];
				  if (n.childNodes.length>0) {
				     n = n.childNodes[0];
				     t = n.nodeValue;
                     t = decodeURIComponent(t.replace(/\+/g, ' '));
					 detail_s += t+"<br>";
					 }
                  //
                 n = xmlDoc.getElementsByTagName("autolog");
				  n = n[0];
				  if (n.childNodes.length>0) {
				     n = n.childNodes[0];
				     t = n.nodeValue;
                     t = decodeURIComponent(t.replace(/\+/g, ' '));
					 detail_s += "<span style=\"color:gray;\">"+t+"</span><br>";
					 }
				  //
                  n = xmlDoc.getElementsByTagName("event");
				  n = n[0];
				  if (n.childNodes.length>0) {
				     n = n.childNodes[0];
				     t = n.nodeValue;
                     n = xmlDoc.getElementsByTagName("pass");
				     n = n[0];
					 var t_pass = "red";
				     if (n.childNodes.length>0) {
				        n = n.childNodes[0];
				        t_pass = (n.nodeValue==1)?"green":"red";
						}
					 detail_s += "<span style=\"color:"+t_pass+";\">Success Event Issued:"+t+"</span>";
					 }
				  //
                  var did = $('onSelectDetails');
                  if (did) did.innerHTML = detail_s;
				  //
                  n = xmlDoc.getElementsByTagName("collect");
				  n = n[0];
                  var did = $('onSelectCollect');
				  if (did) {
				     if (n.childNodes.length>0) {
				        n = n.childNodes[0];
				        t = n.nodeValue;
					    if (t.length>0) {
                           did.innerHTML = "Will Auto Collect";
						   did.title = t;
						   }
						else did.innerHTML = "";
						}
					 else did.innerHTML = "";
					 }
                  //
				  n = xmlDoc.getElementsByTagName("rule");
				  n = n[0];
				  if (n.childNodes.length>0) {
				     n = n.childNodes[0];
				     t = n.nodeValue;
					 var did;
					 if (!isNaN(t)&&parseInt(t)==t) {
                        var did = $('onSelectPassedD');
					    if (did) {
						   did.checked=true;
						   var did = $('onSelectPassedAgo');
						   if (did) did.value = t;
						   }
						}
					 else {
                        var did = $('onSelectPassed'+t);
					    did.checked = true;
					    }
					 }
                  //
				  n = xmlDoc.getElementsByTagName("fail_rule");
				  n = n[0];
				  if (n.childNodes.length>0) {
				     n = n.childNodes[0];
				     t = n.nodeValue;
					 var did;
					 if (!isNaN(t)&&parseInt(t)==t) {
                        var did = $('onSelectFailH');
					    if (did) {
						   did.checked=true;
						   var did = $('onSelectFailAgo');
						   if (did) did.value = (t & 0xff);
						   }
						did = $('onSelectFailRetry');
						if (did) did.value = t>>8;
						}
					 else {
                        var did = $('onSelectFail'+t);
					    if (did) did.checked = true;
						did = $('onSelectFailAgo');
						if (did) did.value=1;
						did = $('onSelectFailRetry');
						if (did) did.value=3;
					    }
					 }
                  //
				  n = xmlDoc.getElementsByTagName("def_rule");
				  n = n[0];
				  if (n.childNodes.length>0) {
				     n = n.childNodes[0];
				     t = n.nodeValue;
					 if (t=='N') {
					    var did = $('onSelectDefaultN');
						if (did) did.checked = true;
						did = $('onSelectDefaultDays');
						if (did) did.value=0;
						did = $('onSelectDefaultTod');
						if (did) did.value = '12:00';
						}
					 else {
					    var DTod = t.split(':');
					    var did = $('onSelectDefaultH');
						if (did) did.checked = true;
						did = $('onSelectDefaultDays');
						if (did) did.value = DTod[0];
						var hr = DTod[1]; var min = '00';
						if (DTod.length=3) min = DTod[2];
						did = $('onSelectDefaultTod');
						if (did) did.value = hr+':'+min;
					    }
					 }
                  //
				  n = xmlDoc.getElementsByTagName("enabled");
				  n = n[0];
				  if (n.childNodes.length>0) {
				     n = n.childNodes[0];
				     t = n.nodeValue;
					 }
				  else t = 0;
                  var did = $('onSelectEnabled');
                  if (did) did.checked = (t==1);
                  //
				  n = xmlDoc.getElementsByTagName("proxy");
				  n = n[0];
				  if (n.childNodes.length>0) {
				     n = n.childNodes[0];
				     t = n.nodeValue;
					 }
				  else t = 0;
                  var did = $('onSelectProxy');
                  if (did) did.checked = (t==1);
                  //
				  n = xmlDoc.getElementsByTagName("datadays");
				  n = n[0];
				  if (n.childNodes.length>0) {
				     n = n.childNodes[0];
				     t = n.nodeValue;
					 }
				  else t = 0xff;
				  for (var day=1; day<8; day++) {
                     var did = $('onSelectDataDay'+day);
                     if (did) did.checked = (((t>>day)&1)==1);
				     }
                  }
               else idd.innerHTML = " Failed to find details ";
               }
            };
         details.send(null);
         }
      else idd.innerHTML = " Failed to create am HTML Request reader ";
      }
   catch (e) {
      idd.innerHTML = " Failed with exception "+e.message;
      }
   }
function saveTreeState() {
   var s = tree.getAllItemsWithKids();
   var p = s.split(',');
   var ss = '';
   for (var i = 0; i<p.length; i++) {
      if (tree.getOpenState(p[i])==1) ss += p[i]+',';
      }
   var idd = $('onOpenState');
   if (idd) idd.value = ss;
   idd = $('onSelectId');
   if (idd) idd.value = tree.getSelectedItemId();
   }
function resetTreeState(aTree, anId) {
   var idd = $('onOpenState');
   if (idd) {
      var s = idd.value;
	  var p = s.split(',');
	  for (var i = 0; i<p.length; i++) {
	     tree.openItem(p[i]);
	     }
      }
   idd = $('onSelectId');
   if (idd) {
      if (idd.value.length>0 && idd.value!=tree.getSelectedItemId()) {
	     var sid = idd.value; idd.value = null; // force get details
	     tree.selectItem(sid, true, false);
		 }
	  }
   }
function expandAllTree() {
   tree.openAllItems('ALL');
   }
function collapseAllTree() {
   tree.closeAllItems('ALL');
   }
var treeOpenSequence = 0;
function openTreeItem() {
   treeOpenSequence = 0;
   nextTreeItem();
   }
function nextTreeItem() {
   var idd = $('onTreeSearch');
   if (idd) {
      try {
         find = new XMLHttpRequest();
         }
      catch (ee) {
         if (typeof window.ActiveXObject != 'undefined' ) find = new ActiveXObject('Microsoft.XMLHTTP');
         }
      if (find) {
	     var str = encodeURIComponent(idd.value);
         find.open("GET", 'HDAW.php?load=HDA_UpdateTreeView&taction=find&id='+str+'&seq='+treeOpenSequence+'&E', false);
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
		   tree.selectItem(t, true, false);
		   tree.focusItem(t);
		   }
         var n = xmlDoc.getElementsByTagName("seq");
		 n = n[0];
		 if (n.childNodes.length>0) {
		   n = n.childNodes[0];
		   t = n.nodeValue;
		   treeOpenSequence = t;
		   }
		treeOpenSequence++;
		}
	  }
   }
