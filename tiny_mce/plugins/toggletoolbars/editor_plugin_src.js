/**
 * TinyMCE Toggle Toolbars plugin
 * 
 * @author Claudio Rivetti
 * @version 1.0 03/05/2010
 */

(function() {
	var DOM = tinymce.DOM;
	tinymce.PluginManager.requireLangPack('toggletoolbars');
	
	tinymce.create('tinymce.plugins.ToggleToolbars', {
		init : function(ed, url) {
			var t = this;
			
			if (ed.settings.theme_advanced_toolbar_location == 'external')	{
				return;
			}

			DOM.loadCSS(url + "/css/ttb.css");
			
			var pos = (ed.settings.theme_advanced_toolbar_location == 'top') ? 0 : 1;
			var handle_class = ['mceToggleToolbarTop', 'mceToggleToolbarBottom'];
			var handle_align = ['bottom', 'top'];

			ed.addCommand('mceToggleToolbars', function() {

				var tb = document.getElementById(ed.id + '_tbl').rows[pos];

				if (DOM.isHidden(tb)) {
					DOM.setStyle(tb, 'display', '');
					t._resizeIframe(ed,-1);
					document.getElementById(ed.id + '_toggle_toolbar_btn').style.backgroundPosition='-55px ' + handle_align[pos];
					document.getElementById(ed.id + '_toggle_toolbar_btn').title = ed.getLang('ttb.hide');
				} else {
					DOM.setStyle(tb, 'display', 'none');
					t._resizeIframe(ed,1);
					document.getElementById(ed.id + '_toggle_toolbar_btn').style.backgroundPosition='left '  + handle_align[pos];
					document.getElementById(ed.id + '_toggle_toolbar_btn').title = ed.getLang('ttb.show');
				}
			});
			
	
			ed.onPostRender.add(function(){
				DOM.win.setTimeout(function() {
					var ct = document.getElementById(ed.id + '_parent');
					var tb = document.getElementById(ed.id + '_tbl').rows[pos];
					
					c = DOM.create('div', {id : ed.id + '_toggle_toolbar', 'class' : 'mceToggleToolbar'});
					h = DOM.add(c, 'a', {id : ed.id + '_toggle_toolbar_btn', href : 'javascript:tinyMCE.execCommand("mceToggleToolbars");', 'class' : handle_class[pos], 'title': ed.getLang('ttb.hide')});

					tinymce.dom.Event.add(h, 'mousedown', function() {
						tinyMCE.execInstanceCommand(ed.id, "mceFocus");
					});

					DOM.setStyle(c, 'width', tb.clientWidth + 'px');
					if (ed.getParam('fullscreen_is_enabled') == true) {
						DOM.setStyle(c, 'background-color', '#ffffff');
					}
					DOM.setStyle(h, 'margin-left', (tb.clientWidth-52)/2 + 'px');
					
					if (pos == 0) {
						ct.insertBefore(c, ct.firstChild);
					} else {
						DOM.insertAfter(c, ct.firstChild);				
					}

					// If setting toggletoolbars_status is set to 'off' hide toolbars
					if (ed.settings.toggletoolbars_status == 'off' && !ed.getParam('fullscreen_is_enabled')) {
						DOM.setStyle(tb, 'display', 'none');
						t._resizeIframe(ed,1);
						h.style.backgroundPosition='left ' + handle_align[pos];
						h.title = ed.getLang('ttb.show');
					}
				},10);
			});
		},
		
		// Resizes the iframe based on the number of toolbars
		_resizeIframe : function(ed,s) {

			var ifr = ed.getContentAreaContainer().firstChild;
			if (typeof ed.dy == 'undefined') {
				var i=1;
				while (document.getElementById(ed.id + '_toolbar' + i) != null)	{
					i++;
				}
				ed.dy = (i-1)*26+2;
			}
			DOM.setStyle(ifr, 'height', ifr.clientHeight + ed.dy*s); // Resize iframe
			ed.theme.deltaHeight += ed.dy*s; // For resize cookie
		},

		getInfo : function() {
			return {
				longname : 'Toggle Toolbars',
				author : 'Claudio Rivetti',
				authorurl : '',
				infourl : '',
				version : "1.1"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('toggletoolbars', tinymce.plugins.ToggleToolbars);
})();