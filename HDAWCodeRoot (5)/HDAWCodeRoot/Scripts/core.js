//=============================================================================
// convenience function to access an HTML element by ID
function $(id)
{
	return document.getElementById(id);
}

//=============================================================================
// Browser detection
var Browser = {
    mac: null,
    ie: null,
    macie: null,
    ns4: null,
    op5: null,
    op6: null,

    init: function() {
        var agt = navigator.userAgent.toLowerCase();

        this.mac = (agt.indexOf("mac") != -1);
        this.ie = (agt.indexOf("msie") != -1);
        this.macie = this.mac && this.ie;
        this.ns4 = document.layers;
        this.op5 = (navigator.userAgent.indexOf("Opera 5") != -1) || (navigator.userAgent.indexOf("Opera/5") != -1);
        this.op6 = (navigator.userAgent.indexOf("Opera 6") != -1) || (navigator.userAgent.indexOf("Opera/6") != -1);
    }
}

Browser.init();

//=============================================================================
// DHTML based functions

var dhtmlIE = {
	getElementLeft: function(elt) {
		return elt.offsetLeft;
	},

	getElementRight: function(elt) {
		return dhtmlIE.getElementLeft(elt) + dhtmlIE.getElementWidth(elt);
	},

	getElementTop: function(elt) {
		return elt.offsetTop;
	},

	getElementBottom: function(elt) {
		return dhtmlIE.getElementTop(elt) + dhtmlIE.getElementHeight(elt);
	},

	getElementHeight: function(elt) {
		return elt.offsetHeight;
	},

	getElementWidth: function(elt) {
		return elt.offsetWidth;
	},

	getScrollPos: function() {
		return {
			left:document.body.scrollLeft, 
			top:document.body.scrollTop
		};
	},

	getElementPosition: function (elt) {
		var offsetLeft = 0;
		var offsetTop = 0;
		while (elt) {
			offsetLeft += elt.offsetLeft + elt.clientLeft;
			offsetTop += elt.offsetTop + elt.clientTop;
			elt = elt.offsetParent;
		}
		if (navigator.userAgent.indexOf("Mac") != -1 &&
			typeof document.body.leftMargin != "undefined") {
			offsetLeft += document.body.leftMargin;
			offsetTop += document.body.topMargin;
		}
		return {left:offsetLeft, top:offsetTop};
	}
};

var dhtmlMoz = {
	getElementLeft: function(elt) {
		return (typeof elt.offsetLeft != 'undefined' ? elt.offsetLeft : elt.style.pixelLeft);
	},

	getElementRight: function(elt) {
		return dhtmlMoz.getElementLeft(elt) + dhtmlMoz.getElementWidth(elt);
	},

	getElementTop: function(elt) {
		return (typeof elt.offsetTop != 'undefined' ? elt.offsetTop : elt.style.pixelTop);
	},

	getElementBottom: function(elt) {
		return dhtmlMoz.getElementTop(elt) + dhtmlMoz.getElementHeight(elt);
	},

	getElementHeight: function(elt) {
		return (typeof elt.offsetHeight != 'undefined' ? elt.offsetHeight : elt.style.pixelHeight);
	},

	getElementWidth: function(elt) {
		return (typeof elt.offsetWidth != 'undefined' ? elt.offsetWidth : elt.style.pixelWidth);
	},
	
	getScrollPos: function() {
		return {
			left: (document.documentElement.scrollLeft ?
				document.documentElement.scrollLeft :
				document.body.scrollLeft),
			top: (document.documentElement.scrollTop ?
				document.documentElement.scrollTop :
				document.body.scrollTop)
		};
	},

	parseBorder: function(borderStyle) {
		if ( borderStyle == '' ) return 0;
		var n = parseInt(borderStyle);
		if ( isNaN(n) ) return 0;
		return n;
	},

	getElementPosition: function(elt) {
		var offsetLeft = 0;
		var offsetTop = 0;
		while (elt) {			
			switch ( elt.style.position ) {
			case 'absolute':
				offsetLeft += elt.offsetLeft;
				offsetTop += elt.offsetTop;
				break;
			default:
				if ( elt.offsetLeft > 0 ) offsetLeft += elt.offsetLeft;
				if ( elt.offsetTop > 0 ) offsetTop += elt.offsetTop;
				break;
			}
			
			offsetLeft += dhtmlMoz.parseBorder(elt.style.borderLeft);
			offsetTop += dhtmlMoz.parseBorder(elt.style.borderTop);
			elt = elt.offsetParent;
		}
		if (navigator.userAgent.indexOf("Mac") != -1 && 
			typeof document.body.leftMargin != "undefined") {
			offsetLeft += document.body.leftMargin;
			offsetTop += document.body.topMargin;
		}
		return {left:offsetLeft, top:offsetTop};
	}
};

var dhtml = Browser.ie ? dhtmlIE : dhtmlMoz;
var isIE = Browser.ie;

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

function getBrowserWindowSize() {
   var myWidth = 0, myHeight = 0;
   if( typeof( window.innerWidth ) == 'number' ) {
      //Non-IE
      myWidth = window.innerWidth;
      myHeight = window.innerHeight;
      }
   else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
      //IE 6+ in 'standards compliant mode'
      myWidth = document.documentElement.clientWidth;
      myHeight = document.documentElement.clientHeight;
      }
   else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
      //IE 4 compatible
      myWidth = document.body.clientWidth;
      myHeight = document.body.clientHeight;
      }
   return {width:myWidth, height:myHeight};
   }


function BrowserResize() {
var id = $('HelpDocView');
if (id) {
   var sz = getBrowserWindowSize();
   id.style.width = (sz.width-4)+"px";
   id.style.height = (sz.height-4)+"px";
   }
id = $('HDA_VIEW');
if (id) issuePost('BrowserResize',event);
}

function AuxWindowResize() {
   window.onresize = BrowserResize;
   }   

function FinishedOnLoad() {
   AuxWindowResize();
}


