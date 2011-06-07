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
else {
	$selectedpath = $uploadpath;	
}

$html = '<ul id="content" class="files">';
						
foreach($dirs as $key => $value)
{
	if($value != "folder")
	{
		if(strtolower($value) == "png" || strtolower($value) == "jpg" || strtolower($value) == "jpeg" || strtolower($value) == "gif" || strtolower($value) == "bmp")
		{					
			$filename = $selectedpath.$key;
			$image_info = getimagesize($filename);
			$file_modified = date($datetimeFormat, filemtime($filename));
			$file_size = filesize($filename);						
			$file_size = $file_size < 1024  ? $file_size. ' bytes' : $file_size < 1048576 ? number_format($file_size / 1024, 2, $dec_seperator, $thousands_separator) . ' kB' : number_format($file_size / 1048576, 2, $dec_seperator, $thousands_separator) . ' MB';
									
			$htmlFiles .= sprintf(
				'<li><a href="%1$s" title="%2$s" class="image"><span class="begin"></span><span class="filename">%2$s</span><span class="filetype"><span class="label">%8$s:</span> %9$s</span><span class="filedim"><span class="label">%5$s:</span> %6$s x %7$s</span><span class="filemodified"><span class="label">%12$s:</span> %13$s</span><span class="filesize"><span class="label">%10$s:</span> %11$s</span><span class="icon image"><img src="phpthumb/phpThumb.php?h=32&w=32&src=%4$s&far=1&bg=0000FF" /></span></a></li>',
				$selectedpath.$key, 
				$key, 
				$value, 
				urlencode($selectedpath.$key),
				$lang["Dimensions"],
				$image_info[0],
				$image_info[1],
				$lang["Filetype"],
				$image_info['mime'],
				$lang["Size"],
				$file_size,
				$lang["Modified on"],
				$file_modified
			);	
		}
		else
		{							
			$filename = $selectedpath.$key;
			$file_modified = date($datetimeFormat, filemtime($filename));
			$file_size = filesize($filename);
			$file_type = mime_content_type($filename);
			$file_size = $file_size < 1024  ? $file_size. ' bytes' : $file_size < 1048576 ? number_format($file_size / 1024, 2, $dec_seperator, $thousands_separator) . ' kB' : number_format($file_size / 1048576, 2, $dec_seperator, $thousands_separator) . ' MB';
									
			$htmlFiles .= sprintf(
				'<li><a href="%1$s" title="%2$s" class="file"><span class="begin"></span><span class="filename">%2$s</span><span class="filetype"><span class="label">%4$s:</span> %5$s</span><span class="filemodified"><span class="label">%8$s:</span> %9$s</span><span class="filesize"><span class="label">%6$s:</span> %7$s</span><span class="icon %3$s"></span></a></li>', 
				$selectedpath.$key, 
				$key, 
				$value,
				$lang["Filetype"],
				$file_type,
				$lang["Size"],
				$file_size,
				$lang["Modified on"],
				$file_modified
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