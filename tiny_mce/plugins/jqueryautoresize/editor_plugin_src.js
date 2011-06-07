/**
 * jQuery Auto Resize
 *
 * This jQuery-powered plugin automatically resizes the TinyMCE content area to fit its content height.
 * Enable by adding "jqueryautoresize" to your plugins setting in your TinyMCE initialization script.
 *
 * Created by Hannes Rydén, 16 June 2010
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

/***
    This script requires jQuery.
    
    For an easy way to load jQuery, put the following code in the head of the html document that loads TinyMCE:
        <script src="http://www.google.com/jsapi"></script>
        <script>google.load("jquery", "1.4.2");</script>
***/

(function() {
	tinymce.create('tinymce.plugins.jQueryAutoResizePlugin', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			var t = this;

            // Do nothing if fullscreen mode is enabled
			if (ed.getParam('fullscreen_is_enabled'))
				return;
			
			// Resize method that gets executed each time the editor needs to resize.
			function resize() {
                t.iframe.css('height', t.iframe_body.outerHeight() + 'px'); // Set iframe height to full height of its content body
			};

			// Things to do when the editor is ready
			ed.onInit.add(function(ed, l) {
				// Hide vertical scrollbars
				ed.getBody().style.overflowY = "hidden";
				
				// Shortcuts to jQuery selectors so we don't need to select the elements each time we resize
				// Placed here because they must be set after editor is ready (when iframe has been rendered)
				t.iframe = $('#' + ed.id + '_ifr'); // Select iframe
		        t.iframe_body = t.iframe.contents().find('#' + ed.id); // Select body tag within iframe
		        
		        // Set height of table container that surrounds iframe to auto so it doesn't interfere
		        $('#' + ed.id + '_tbl').css('height', 'auto');
		        
		        // Add appropriate listeners for resizing content area.
		        // The reason these are added here and not earlier is
		        // because the jQuery selectors must be set before.
       			ed.onChange.add(resize);
       			ed.onSetContent.add(resize);
       			ed.onPaste.add(resize);
       			ed.onKeyUp.add(resize);
       			ed.onPostRender.add(resize);
       			ed.onMouseUp.add(resize);
			});

            // Because the content area resizes when its content CSS loads,
			// and we can't easily add a listener to its onload event,
			// we'll just trigger a resize after a short loading period
			ed.onLoadContent.add(function(ed, l) {
				setTimeout(function() { resize(); }, 1000); // 1 second delay (in ms)
			});

			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('mcejQueryAutoResize', resize);
		},
	});

	// Register plugin
	tinymce.PluginManager.add('jqueryautoresize', tinymce.plugins.jQueryAutoResizePlugin);
})();