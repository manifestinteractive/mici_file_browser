$(function() {
	$('#content').tinymce({
		// Location of TinyMCE script
		script_url : media_url+'browser/js/tiny_mce/tiny_mce_gzip.php?diskcache=true',

		// General options
		theme : "advanced",
		mode : "textareas",
		
		plugins : "advhr, advimage, advlink, advlist, autoresize, autosave, contextmenu, directionality, emotions, example, fullscreen, iespell, inlinepopups, insertdatetime, layer, media, nonbreaking, noneditable, pagebreak, paste, preview, print, save, searchreplace, spellchecker, style, tabfocus, table, template, toggletoolbars, visualchars, wordcount, xhtmlxtras",

		file_browser_callback : "filebrowser",
		tab_focus : ':prev, :next',
		toggletoolbars_status: 'on',
		force_p_newlines : false,

		// Theme options
		theme_advanced_buttons1 : "save, cancel, |, undo, redo, |, cut, copy, paste, pastetext, pasteword, |, search, replace, |,code, cleanup, removeformat, |, attribs, visualaid, |,  print, preview, iespell, |, fullscreen, help",
		theme_advanced_buttons2 : "bold, italic, underline, strikethrough, |, justifyleft, justifycenter, justifyright, justifyfull, styleselect, formatselect, fontselect, fontsizeselect, |, forecolor, backcolor, styleprops, |, sub, sup, |, bullist, numlist, |, outdent, indent, blockquote, |, charmap, visualchars",
		theme_advanced_buttons3 : "tablecontrols, |, link, unlink, anchor, |, advhr, hr, |, emotions, image, media, |, insertdate, inserttime, |, insertlayer, moveforward, movebackward, absolute, |, cite, abbr, acronym, del, ins, |, nonbreaking, template, pagebreak",
		
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : false

	});
});

function filebrowser(field_name, url, type, win)
{
	fileBrowserURL = media_url+'browser/js/tiny_mce/filebrowser/index.php?filter='+type+'&assets_url='+assets_url+'&assets_abs_path='+assets_abs_path;
	
	tinyMCE.activeEditor.windowManager.open(
		{
			title: "File Browser",
			url: fileBrowserURL,
			width: 950,
			height: 650,
			inline: 0,
			maximizable: 1,
			close_previous: 0
		},
		{
			window : win,
			input : field_name
		}
	);		
}