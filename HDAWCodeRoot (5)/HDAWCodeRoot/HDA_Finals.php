<?php
// Final Statements

function _finals_($scripts=null) {
	global $HDA_DB;
	global $code_root, $home_root;
	if (is_object($HDA_DB)) {
	$HDA_DB->close();
	$HDA_DB = null;
	}
	global $header_content_type;
	if (is_null($header_content_type)) $header_content_type = "text/html;charset=utf-8";

	// PUSH BODY
	global $content;
	if (!is_null($content)) {
		global $__DisplayHeight_,$__DisplayWidth_;
		$d_stack = PRO_ReadParam('alc_dialog_stack');
		$content .= "<input type=\"submit\" name=\"ACTION_NONE\" value=\"post\" id=\"pad_post\" style=\"visibility:hidden;\">";
		$content .= "<input type=\"hidden\" name=\"DisplayHeight\" value=\"{$__DisplayHeight_}\" >";
		$content .= "<input type=\"hidden\" name=\"DisplayWidth\" value=\"{$__DisplayWidth_}\" >";
		$content .= "</form>";

		global $content_iframe_embedded;
		if (!isset($content_iframe_embedded)) echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\" >";

		if (!isset($content_iframe_embedded)) echo "<HTML xmlns=\"http://www.w3.org/1999/xhtml\" >"; else echo "<html>";
		echo "<head>";
		if (!isset($content_iframe_embedded)) echo "<META HTTP-EQUIV=\"content-type\" CONTENT=\"{$header_content_type}\">";
		if (!isset($content_iframe_embedded)) echo "<meta name=\"viewport\" content=\"user-scalable=no, width=device-width\" />";
		if (!isset($content_iframe_embedded)) echo "<meta name=\"apple-mobile-web-app-capable\" content=\"yes\" />";
		if (!isset($content_iframe_embedded)) echo "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=Edge\" >";

		global $HDA_WEB_TITLE, $HDA_WEB_SUBTITLE;
		echo "<title>{$HDA_WEB_TITLE}-{$HDA_WEB_SUBTITLE}</title>";
		if (!isset($content_iframe_embedded)) echo "<meta name=\"keywords\" content=\"project management,change management,business intelligence,BI,PM,collaboration,planning,control,analysis services\">";
		global $HDA_Default_Scripts, $HDA_Add_Scripts;
		$scripts = (is_null($scripts))?$HDA_Default_Scripts:$scripts;
		foreach ($scripts as $script) {
			echo _emit_script($script);
		}
		foreach ($HDA_Add_Scripts as $script) {
			echo _emit_script($script);
		}
		global $HDA_Add_Styles;
		foreach ($HDA_Add_Styles as $style) {
			echo _emit_css($style);
		}
		global $HDA_InLine_Script;
		echo "<script>{$HDA_InLine_Script}</script>";
		echo "<script>document.ondblclick = function(ev) { ev.preventDefault();return false; }</script>";
		echo "</head>";
		global $on_load, $post_load, $bodyclass, $bodystyle;
		echo "<body ";
		echo "onload=\"javascript:{$on_load}FinishedOnLoad();{$post_load}\" ";
		echo "class=\"{$bodyclass}\" style=\"{$bodystyle}\" >";
		$content .= "<div id=\"alc_transit_msg\" class=\"alc-transit-msg\" ></div>";
		echo $content;

		echo "</body>";
		echo "</html>";

	}
}
?>