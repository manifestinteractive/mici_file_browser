<?PHP
error_reporting(0);
ini_set('display_errors', 'off');

ob_start( 'ob_gzhandler' );

require_once('functions.php');
require_once('minify.php');

if(!empty($_COOKIE["view"]))
{
	$viewlayout = $_COOKIE["view"];
}
elseif(isset($_REQUEST['view']))
{
	$viewlayout = $_REQUEST['view'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>File Browser</title>
<link rel="shortcut icon" href="mediabrowser.ico" />

<?PHP
$minifyCSS = new Minify(TYPE_CSS);
$minifyJS = new Minify(TYPE_JS);
$cssFiles = array('css/mediabrowser.css');

// Only load skin if $_GET["skin"] is set.
if (isset($_GET["skin"])):
	$cssFiles[count($cssFiles)] = 'skins/'.$_GET["skin"].'/skin.css';
endif;

$minifyCSS->addFile($cssFiles);
$minifyJS->addFile(array(
	'js/jquery.js',
	'js/jquery.mediabrowser.js',
	'js/jquery.plugins.js',
	'swfupload/swfupload.min.js',
	'swfupload/plugins/swfupload.queue.js',
	'swfupload/fileprogress.js',
	'swfupload/handlers.js'
	//'js/tiny_mce_popup.js'
));

// JAVASCRIPT
echo '<script type="text/javascript">'."\n";
echo 'var field_name = "'.$_GET['field_name'].'";';
echo 'var assets_abs_path = "'.$_GET['assets_abs_path'].'";';
echo 'var assets_url = "'.$_GET['assets_url'].'";';
echo str_replace('</', '<\/', $minifyJS->combine());
echo '</script>'."\n";

// CSS
echo '<style type="text/css">'."\n";
	echo $minifyCSS->combine(); 
echo '</style>'."\n";
?>

<script type="text/javascript">
var swfu;
var foldercmenu;
var filecmenu;
var imagecmenu;
var cmenu;

var select_one_file = "<?PHP echo $lang['select_one_file'];?>";
var insert_cancelled = "<?PHP echo $lang['insert_cancelled'];?>";
var invalid_characters_used = "<?PHP echo 'Invalid characters used!'?>";
var rename_file = "<?PHP echo $lang['rename_file'];?>";
var rename_folder = "<?PHP echo $lang['rename_folder'];?>";
var rename_error = "<?PHP echo $lang['rename_error'];?>";

$(document).ready(function() {
	
	// Prevent text selections
	divFiles = document.getElementById('files');
	divFiles.onselectstart = function() {return false;} // ie
	divFiles.onmousedown = function() {return false;} // mozilla
	
	// *** Context Menu ***//
	$.contextMenu.theme = 'mb';
	$.contextMenu.shadowOpacity = .3;
	
	// activate folder/file selection before show
	$.contextMenu.beforeShow = function(){
		// Hide all other contextmenus
		$('table.contextmenu, div.context-menu-shadow').css({'display': 'none'});
		
		// Enable paste button if clipboard has items
		if($.MediaBrowser.clipboard.length > 0){
			$('table.contextmenu div.context-menu-item').removeClass('context-menu-item-disabled');
		} else {
			// Disable paste button if no items are added to the clipboard
			$('table.contextmenu div.context-menu-item[title=paste]').addClass('context-menu-item-disabled');
		}
		
		// Show selection of file, folder or image
		if($(this.target).hasClass('folder')){ //Folder
			$.MediaBrowser.selectFileOrFolder(this.target, $(this.target).attr('href'), 'folder', 'cmenu');	
		} else if($(this.target).hasClass('file')){ //File
			$.MediaBrowser.selectFileOrFolder(this.target, $(this.target).attr('href'), 'file', 'cmenu');	
		} else if($(this.target).hasClass('image')){ //Image
			$.MediaBrowser.selectFileOrFolder(this.target, $(this.target).attr('href'), 'image', 'cmenu');	
		}
		
		return true;
	}

	//Context menus
	foldercmenu = [
		{'<?PHP echo $lang["cmenu_open"];?>':{
			onclick:function(menuItem,menu) {
		  			$.MediaBrowser.loadFolder($(this).attr('href'));
		  		},
		  	title:'<?PHP echo $lang["cmenu_open"];?>',
			icon:'img/contextmenu/open.png'
			}
	  	},
		$.contextMenu.separator,
	  	{'<?PHP echo $lang["cmenu_copy"];?>':{
			onclick:function(menuItem,menu) {
				$.MediaBrowser.copy();
			},
			icon:'img/contextmenu/copy.gif',
		  	title:'<?PHP echo $lang["cmenu_copy"];?>'
			}
	  	},
	  	{'<?PHP echo $lang["cmenu_cut"];?>':{
			onclick:function(menuItem,menu) {
				$.MediaBrowser.cut();
			},
			icon:'img/contextmenu/cut.gif',
		  	title:'<?PHP echo $lang["cmenu_cut"];?>'
			}
	  	},
		{'<?PHP echo $lang["cmenu_paste"];?>':{
			onclick:function(menuItem,menu) {
				$.MediaBrowser.paste();
			},
		  	title:'<?PHP echo $lang["cmenu_paste"];?>',
		  	icon:'img/contextmenu/paste.gif',
		  	disabled:true
			}
	  	},
		$.contextMenu.separator,
		{'<?PHP echo $lang["cmenu_rename"];?>':{
			onclick:function(menuItem,menu) {
				$.MediaBrowser.rename($(this).attr('href'), 'folder');
			},
		  	title:'<?PHP echo $lang["cmenu_rename"];?>',
		  	icon:'img/contextmenu/rename.png'
			}
	  	},
		$.contextMenu.separator,
	  	{'<?PHP echo $lang["cmenu_delete"];?>':{
			onclick:function(menuItem,menu) {
					if(confirm('<?PHP echo $lang["cmenu_delete_confirm_message_folder"];?>')){
						$.MediaBrowser.delete_all();
					} 
				},
		  	title:'<?PHP echo $lang["cmenu_delete"];?>',
			icon:'img/contextmenu/delete.gif',
		  	disabled:false
			}
	  	}
	];

	filecmenu = [
		{'<?PHP echo $lang["cmenu_insert"];?>':{
			onclick:function(menuItem,menu) { $.MediaBrowser.insertFile(); },
			icon:'img/contextmenu/insert.png',
		  	title:'<?PHP echo $lang["cmenu_insert"];?>'
			}
	  	},
	  	$.contextMenu.separator,
	  	{'<?PHP echo $lang["cmenu_copy"];?>':{
			onclick:function(menuItem,menu) {
				$.MediaBrowser.copy();
			},
			icon:'img/contextmenu/copy.gif',
		  	title:'<?PHP echo $lang["cmenu_copy"];?>'
			}
	  	},
	  	{'<?PHP echo $lang["cmenu_cut"];?>':{
			onclick:function(menuItem,menu) {
				$.MediaBrowser.cut();
			},
			icon:'img/contextmenu/cut.gif',
		  	title:'<?PHP echo $lang["cmenu_cut"];?>'
			}
	  	},
		{'<?PHP echo $lang["cmenu_paste"];?>':{
			onclick:function(menuItem,menu) {
				$.MediaBrowser.paste();
			},
		  	title:'<?PHP echo $lang["cmenu_paste"];?>',
		  	icon:'img/contextmenu/paste.gif',
		  	disabled:true
			}
	  	},
		$.contextMenu.separator,
		{'<?PHP echo $lang["cmenu_rename"];?>':{
			onclick:function(menuItem,menu) {
				$.MediaBrowser.rename($(this).attr('href'), 'file');
			},
		  	title:'<?PHP echo $lang["cmenu_rename"];?>',
		  	icon:'img/contextmenu/rename.png'
			}
	  	},
	  	$.contextMenu.separator,
	  	{'<?PHP echo $lang["cmenu_delete"];?>':{
			onclick:function(menuItem,menu) {
					if(confirm('<?PHP echo $lang["cmenu_delete_confirm_message_file"];?>')){
						$.MediaBrowser.delete_all();
					} 
				},
		  	title:'<?PHP echo $lang["cmenu_delete"];?>',
			icon:'img/contextmenu/delete.gif',
		  	disabled:false
			}
	  	}
	];

	imagecmenu = [
		{'<?PHP echo $lang["cmenu_insert"];?>':{
			onclick:function(menuItem,menu) {
		  			$.MediaBrowser.insertFile();
		  		},
			icon:'img/contextmenu/insert.png',
		  	title:'<?PHP echo $lang["cmenu_insert"];?>'
			}
	  	},
	  	$.contextMenu.separator,
	  	{'<?PHP echo $lang["cmenu_copy"];?>':{
			onclick:function(menuItem,menu) {
				$.MediaBrowser.copy();
			},
			icon:'img/contextmenu/copy.gif',
		  	title:'<?PHP echo $lang["cmenu_copy"];?>'
			}
	  	},
	  	{'<?PHP echo $lang["cmenu_cut"];?>':{
			onclick:function(menuItem,menu) {
				$.MediaBrowser.cut();
			},
			icon:'img/contextmenu/cut.gif',
		  	title:'<?PHP echo $lang["cmenu_cut"];?>'
			}
	  	},
		{'<?PHP echo $lang["cmenu_paste"];?>':{
			onclick:function(menuItem,menu) {
				$.MediaBrowser.paste();
			},
		  	title:'<?PHP echo $lang["cmenu_paste"];?>',
		  	icon:'img/contextmenu/paste.gif',
		  	disabled:true
			}
	  	},
		$.contextMenu.separator,
		{'<?PHP echo $lang["cmenu_rename"];?>':{
			onclick:function(menuItem,menu) {
				$.MediaBrowser.rename($(this).attr('href'), 'file');
			},
		  	title:'<?PHP echo $lang["cmenu_rename"];?>',
		  	icon:'img/contextmenu/rename.png'
			}
	  	},
	  	$.contextMenu.separator,
	  	{'<?PHP echo $lang["cmenu_delete"];?>':{
			onclick:function(menuItem,menu) {
					if(confirm('<?PHP echo $lang["cmenu_delete_confirm_message_image"];?>')){
						$.MediaBrowser.delete_all();
					} 
				},
		  	title:'<?PHP echo $lang["cmenu_delete"];?>',
			icon:'img/contextmenu/delete.gif',
		  	disabled:false
			}
	  	}
	];
	
	cmenu = [
		{'<?PHP echo $lang["cmenu_large_images"];?>':{
			onclick:function(menuItem,menu) { $.MediaBrowser.changeview('large_images'); },
		  	title:'<?PHP echo $lang["cmenu_large_images"];?>',
		  	icon:'img/contextmenu/view_images_large.png'
			}
	  	},
		{'<?PHP echo $lang["cmenu_small_images"];?>':{
			onclick:function(menuItem,menu) { $.MediaBrowser.changeview('small_images'); },
		  	title:'<?PHP echo $lang["cmenu_small_images"];?>',
		  	icon:'img/contextmenu/view_images_small.png'
			}
	  	},
		{'<?PHP echo $lang["cmenu_list"];?>':{
			onclick:function(menuItem,menu) { $.MediaBrowser.changeview('list'); },
		  	title:'<?PHP echo $lang["cmenu_list"];?>',
		  	icon:'img/contextmenu/view_list.png'
			}
	  	},
		{'<?PHP echo $lang["cmenu_details"];?>':{
			onclick:function(menuItem,menu) { $.MediaBrowser.changeview('details'); },
		  	title:'<?PHP echo $lang["cmenu_details"];?>',
		  	icon:'img/contextmenu/view_details.png'
			}
	  	},
		{'<?PHP echo $lang["cmenu_tiles"];?>':{
			onclick:function(menuItem,menu) { $.MediaBrowser.changeview('tiles'); },
		  	title:'<?PHP echo $lang["cmenu_tiles"];?>',
		  	icon:'img/contextmenu/view_tiles.png'
			}
	  	},
		{'<?PHP echo $lang["cmenu_content"];?>':{
			onclick:function(menuItem,menu) { $.MediaBrowser.changeview('content'); },
		  	title:'<?PHP echo $lang["cmenu_content"];?>',
		  	icon:'img/contextmenu/view_content.png'
			}
	  	},
		$.contextMenu.separator,
		{'<?PHP echo $lang["cmenu_new_folder"];?>':{
			onclick:function(menuItem,menu) {
				$.MediaBrowser.showLayer('newfolder');
			},
		  	title:'<?PHP echo $lang["cmenu_new_folder"];?>',
		  	icon:'img/contextmenu/open.png'
			}
	  	},
		$.contextMenu.separator,
		{'<?PHP echo $lang["cmenu_paste"];?>':{
			onclick:function(menuItem,menu) {
				$.MediaBrowser.paste();
			},
		  	title:'<?PHP echo $lang["cmenu_paste"];?>',
		  	icon:'img/contextmenu/paste.gif',
		  	disabled:true
			}
	  	}
	];
		
	// *** Media Browser ***//
	$.MediaBrowser.init();
	
	// Add context menu to the files, folders and images
	$.MediaBrowser.contextmenu();

	// *** SWFUpload ***//
	// Upload configuration
	var settings = {
			flash_url: "swfupload/swfupload.swf",
			upload_url: "swfupload/upload.php",
			post_params: {
				"PHPSESSID": "<?PHP echo session_id(); ?>",
				"uploadpath": "<?PHP echo rawurlencode($uploadpath); ?>"
				},
			file_size_limit: "100 MB",
			file_types: "*.*",
			file_types_description: "All Files",
			file_upload_limit: 100,
			file_queue_limit: 0,
			custom_settings: {
				progressTarget: "fsUploadProgress"
			},
			debug: false,

			// Button settings
			//button_image_url: "img/TestImageNoText_65x29.png",
			button_width: "175",
			button_height: "20",
			button_placeholder_id: "spanButtonPlaceHolder",
			button_text: '<span class="browseButton"><?PHP echo $lang["Browse"]; ?><\/span>',
			button_text_style: ".browseButton {font-family:sans-serif; color:#000000; font-size:14px; font-weight: bold;}",
			button_cursor: SWFUpload.CURSOR.HAND,
			button_text_top_padding: 1,
			
			// The event handler functions are defined in handlers.js
			file_queued_handler: fileQueued,
			file_queue_error_handler: fileQueueError,
			file_dialog_complete_handler: fileDialogComplete,
			upload_start_handler: uploadStart,
			upload_progress_handler: uploadProgress,
			upload_error_handler: uploadError,
			upload_success_handler: uploadSuccess,
			upload_complete_handler: uploadComplete,
			queue_complete_handler: queueComplete	// Queue plugin event
	};

	swfu = new SWFUpload(settings);
});	
</script>
</head>

<body>
<?PHP
$rootname = array_pop(explode("/", trim($uploadpath,"/")));
?>
<input type="hidden" id="currentfolder" value="<?PHP echo $uploadpath;?>" />
<input type="hidden" id="currentview" value="<?PHP echo $viewlayout;?>" />

<div id="addressbar" class="ab">
  <ol>
		<li class="root"><span>&nbsp;</span></li>
        <li><a href="<?PHP echo $uploadpath;?>" title="<?PHP echo $rootname;?>"><span><?PHP echo $rootname;?></span></a></li>
	</ol>
    <div id="searchbar">
    	<div class="cap"></div>
    	<input name="search" id="search" value="<?PHP echo $lang["Search"];?>" />
	    <div class="button"></div>
    </div>
</div>

<div id="navbar" class="nb">
	<ul class="left">
    	<!--
		<li><a href="#" title="<?PHP echo $lang["Close"];?>"><span><?PHP echo $lang["Close"];?></span></a></li>	
        -->
		<li><a href="#" onclick="return $.MediaBrowser.showLayer('newfolder');" title="<?PHP echo $lang["New folder"];?>"><span><?PHP echo $lang["New folder"];?></span></a></li>
		<li><a href="#" onclick="return $.MediaBrowser.showLayer('upload');" title="<?PHP echo $lang["Upload"];?>"><span><?PHP echo $lang["Upload"];?></span></a></li>
		<li class="label"><a href="#" onclick="return $.MediaBrowser.printClipboard();" title="<?PHP echo $lang["Clipboard"];?>"><span><?PHP echo $lang["Clipboard"];?>&nbsp;(&nbsp;<div id="cbItems">0</div>&nbsp;<?PHP echo $lang["Items"];?>&nbsp;)</span></a></li>
  </ul>
	<ul class="right">
	    <li><a href="#" title="<?PHP echo $lang["Change view"];?>"><span><?PHP echo $lang["View"];?></span></a>
        	<ul>
                <li><a href="#" onclick="return $.MediaBrowser.changeview('large_images');" title="<?PHP echo $lang["Large images"];?>"><span class="icon large"></span><?PHP echo $lang["Large images"];?></a></li>
                <li><a href="#" onclick="return $.MediaBrowser.changeview('small_images');" title="<?PHP echo $lang["Small images"];?>"><span class="icon small"></span><?PHP echo $lang["Small images"];?></a></li>
                <li><a href="#" onclick="return $.MediaBrowser.changeview('list');" title="<?PHP echo $lang["List"];?>"><span class="icon list"></span><?PHP echo $lang["List"];?></a></li>
                <li><a href="#" onclick="return $.MediaBrowser.changeview('details');" title="<?PHP echo $lang["Details"];?>"><span class="icon details"></span><?PHP echo $lang["Details"];?></a></li>
                <li><a href="#" onclick="return $.MediaBrowser.changeview('tiles');" title="<?PHP echo $lang["Tiles"];?>"><span class="icon tiles"></span><?PHP echo $lang["Tiles"];?></a></li>
                <li><a href="#" onclick="return $.MediaBrowser.changeview('content');" title="<?PHP echo $lang["Content"];?>"><span class="icon content"></span><?PHP echo $lang["Content"];?></a></li>                
            </ul>
        </li>
      <li><a href="#" onclick="return $.MediaBrowser.showLayer('help');" class="help" title="<?PHP echo $lang["Help"];?>"><span><img src="img/help.png" alt="<?PHP echo $lang["Help"];?>" /></span></a></li>
  </ul>
</div>

<div id="message"></div>

<div id="explorer">

	<div id="tree">
		<?PHP
			require_once("treeview.php");
		?>
	</div>
	
	<div id="vertical-resize-handler" class="resize-grip"></div>
	
	<div id="main">
    	<div id="filelist" class="layer">
        	<h2><?PHP echo $rootname?></h2>
          	<select id="filters">
           		<option value="">All files (*.*)&nbsp;</option>
                <option<?PHP echo ($_GET["filter"] == "flash" ? ' selected="selected"' : '');?> value=".swf|.flv|.fla">Flash&nbsp;</option>
	            <option<?PHP echo ($_GET["filter"] == "image" ? ' selected="selected"' : '');?> value=".bmp|.gif|.jpg|.jpeg|.png">Images&nbsp;</option>
                <option<?PHP echo ($_GET["filter"] == "media" ? ' selected="selected"' : '');?> value=".avi|.flv|.mov|.mp3|.mp4|.mpeg|.mpg|.ogg|.wav|.wma|.wmv">Media&nbsp;</option>
    		</select>
            <hr />
            <div id="files">
                <?PHP
					// Get all folders in root upload folder but don't iterate
                    $dirs = getDirTree(STARTINGPATH, true, false);
                    
                    switch($viewlayout){
                        case 'large_images': 
                            require_once("view_images_large.php");
                            break;
                        case 'small_images': 
                            require_once("view_images_small.php");
                            break;
                        case 'list': 
                            require_once("view_list.php");
                            break;
                        case 'details': 
                            require_once("view_details.php");
                            break;
                        case 'tiles':
                            require_once("view_tiles.php");
                            break;
                        default: //Content
                            require_once("view_content.php");
                            break;
                    }
                ?>
            </div>
        </div>
        <div id="newfolder" class="layer" style="display:none;">
        	<h2><?PHP echo $lang["Add a new folder"]?></h2>
        	<a href="#" class="close" onclick="$.MediaBrowser.hideLayer(); $.MediaBrowser.loadFolder($.MediaBrowser.currentFolder); return false;">X</a>
			<hr />
            <form id="newfolderform" name="newfolderform" onsubmit="$.MediaBrowser.newFolder(); return false;">
			<div style="padding:10px;">	
                <div style=" height:20px; line-height:20px;">
                	<label for="folderpath"><?PHP echo $lang["New folder is created in"];?>: <input class="path" type="text" name="folderpath" id="folderpath" readonly="readonly"/></label>
                </div>
				<div style="margin-bottom:5px; padding-top:10px; height:20px; line-height:20px;">
					<label for="newfoldername"><?PHP echo $lang["Name of the new folder"];?>: <input class="path border" type="text" name="foldername" id="foldername" /></label>
				</div>
				<div style="margin-bottom:5px; padding-top:10px; height:20px; line-height:20px;">
                	<button type="submit"><?PHP echo $lang["Create folder"];?></button>
                    <button type="button" onclick="$.MediaBrowser.hideLayer(); $.MediaBrowser.loadFolder($.MediaBrowser.currentFolder); return false;"><?PHP echo $lang["Close"];?></button>
                </div>
            </div>
            </form>
        </div>
		<div id="upload" class="layer" style="display:none;">
			<h2><?PHP echo $lang["Upload a new file"]?></h2>
			<a href="#" class="close" onclick="$.MediaBrowser.hideLayer(); $.MediaBrowser.loadFolder($.MediaBrowser.currentFolder); return false;">X</a>
			<hr />
           	<form id="form1" action="index.php" method="post" enctype="multipart/form-data">
                <div style="padding:10px 0 0 10px; height:20px; line-height:20px;">
                	<label for="uploadpath"><?PHP echo $lang["Currently uploading in folder"];?>: <input class="path" type="text" name="uploadpath" id="uploadpath" readonly="readonly"/></label>
                </div>
                <div style="padding:10px 0 0 10px; height:20px; line-height:20px;">
					<?PHP echo $lang["Select your file"];?>: &nbsp;&nbsp;<span id="spanButtonPlaceHolder"></span>
				</div>
				<div style="margin-bottom:5px; padding:10px; height:20px; line-height:20px;">Upload limited to 2 MB!</div>
                
				<div class="fieldset flash" id="fsUploadProgress">
					<span class="legend"><?PHP echo $lang["Upload Queue"];?></span>
		    </div>
                <div style="padding-left: 10px;">
                	<button type="button" onclick="$.MediaBrowser.hideLayer(); $.MediaBrowser.loadFolder($.MediaBrowser.currentFolder); return false;"><?PHP echo $lang["Close"];?></button>
				</div>
            </form>
		</div>
        <div id="help" class="layer" style="display:none;">
        	<h2>PDW File Browser v1.0 beta</h2>
            <a href="#" class="close" onclick="$.MediaBrowser.hideLayer(); return false;" title="<?PHP echo $lang["Close"]?>"><?PHP echo $lang["Close"]?></a>
            <hr />
            <div style="padding:10px; width: 500px;">
                <p>Author: Guido Neele<br />
				Date: May 9, 2010<br />
				Url: http://www.neele.name</p>
				<p>Copyright (c) 2010 Guido Neele</p>
				<p>Permission is hereby granted, free of charge, to any person obtaining a copy
				of this software and associated documentation files (the "Software"), to deal
				in the Software without restriction, including without limitation the rights
				to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
				copies of the Software, and to permit persons to whom the Software is
				furnished to do so, subject to the following conditions:</p>
				<p>The above copyright notice and this permission notice shall be included in
				all copies or substantial portions of the Software.</p>
				<p>THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
				IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
				FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
				AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
				LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
				OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
				THE SOFTWARE.</p>
                <p>This plugin makes use of:</p>
                <ul>
                	<li>jQuery (jquery.com)</li>
                    <li>jQuery.contextmenu - Matt Kruse (javascripttoolbox.com)</li>
                    <li>SWFUpload - (swfupload.org)</li>
                    <li>Javascript functions urlencode/urldecode - (phpjs.org)</li>
					<li>createCookie - Peter-Paul Koch (http://www.quirksmode.org/js/cookies.html)</li>
					<li>Javascript function printf - Dav Glass extension for the Yahoo UI Library</li>
                </ul>
                <p><button type="button" onclick="$.MediaBrowser.hideLayer(); return false;"><?PHP echo $lang["Close"];?></button></p>
            </div> 
        </div>
	</div>
</div>

<div id="file-specs">
    <div id="info">
	<?PHP
		require_once("file_specs.php");
	?>
    </div>
    <form id="fileform" name="fileform" onsubmit="$.MediaBrowser.insertFile(); return false;">
    	<label for="file"><?PHP echo $lang["File"]?></label>
        <input type="text" name="file" id="file" readonly="readonly" value="" />
    	<button type="submit"><?PHP echo $lang["Insert"]?></button>
	</form>
</div>
</body>
</html>