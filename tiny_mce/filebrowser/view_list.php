<?PHP
error_reporting(0);
ini_set('display_errors', 'off');

require_once('functions.php');

if(isset($_REQUEST["ajax"]))
{
	$selectedpath = urldecode($_REQUEST["path"]);
	if($selectedpath = checkpath($selectedpath, $uploadpath))
	{
		$dirs = getDirTree($selectedpath, true, false);
	}
	else
	{
		die('0||'.$lang["loadfolder_error_1"]);
	}
}
else
{
	$selectedpath = $uploadpath;	
}

$html = '<ul id="list" class="files">';
						
foreach($dirs as $key => $value)
{
	if($value != "folder")
	{
		if(strtolower($value) == "png" || strtolower($value) == "jpg" || strtolower($value) == "jpeg" || strtolower($value) == "gif" || strtolower($value) == "bmp")
		{						
			$filename = $selectedpath.$key;	
			$htmlFiles .= sprintf(
				'<li><a href="%1$s" title="%2$s" class="image"><span class="begin"></span><span class="filename">%2$s</span><span class="icon %3$s"></span></a></li>', 
				$selectedpath.$key, 
				$key, 
				strtolower($value), 
				urlencode($selectedpath.$key)
			);
		}
		else
		{							
			$filename = $selectedpath.$key;
			$htmlFiles .= sprintf(
				'<li><a href="%1$s" title="%2$s" class="file"><span class="begin"></span><span class="filename">%2$s</span><span class="icon %3$s"></span></a></li>', 
				$selectedpath.$key, 
				$key, 
				$value
			);	
		}
	}
	else
	{							
		$htmlFolders .= sprintf(
			'<li><a href="%1$s" title="%2$s" class="folder"><span class="begin"></span><span class="filename">%2$s</span><span class="icon folder"></span></a></li>', 
			$selectedpath.$key."/", 
			$key
		);
	}
}

$html .= $htmlFolders.$htmlFiles.'</ul>';
echo $html;
?>