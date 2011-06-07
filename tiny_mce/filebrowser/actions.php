<?PHP
error_reporting(0);
ini_set('display_errors', 'off');

require("functions.php");

switch($_POST["action"])
{	
	case "cut_paste":
		$files = $_POST["files"];
		$folder = urldecode($_POST["folder"]);
	
		if($folder = checkpath($folder, $uploadpath))
		{
			if(count($files) && is_dir($folder))
			{	
				$result = true;
				
				foreach($files as $key => $value)
				{	
					$value = urldecode($value);	
					if(($file = checkpath($value, $uploadpath)) && ($file != $folder))
					{	
						$source = $file;
						$dest = $folder;
						
						if (is_dir($source) && $source[strlen($source) - 1] != '/')
						{
            				$source=$source."/"; 
						}
						if ($dest[strlen($dest) - 1] != '/')
						{
            				$dest=$dest."/"; 
						}
						if(is_dir($source))
						{			
							$dest=$dest.basename(rtrim($source, '/'));
						}
						else
						{
							$dest=$dest.basename($source);	
						}
						
						if(!smartCopy($source, $dest))
						{
							$result = false;
						}	
					}
					else
					{
						echo '0||'.$lang["file_tampered_with"];	
						break;	
					}
				}
				
				if($result)
				{
					foreach($files as $key => $value)
					{	
						$value = urldecode($value);
						
						if( $file = checkpath($value, $uploadpath) )
						{	
							if(!recursiveDelete($file))
							{
								$result = false;
							}	
						}
						else
						{
							echo '0||'.$lang["file_tampered_with"];	
							break;	
						}
						
						if($result)
						{
							echo '1||'.$lang["delete_success_1"];
						}
						else
						{
							echo '0||'.$lang["delete_error_1"];	
						}
					}
				}
				else
				{
					echo '0||'.$lang["delete_error_1"];	
				}
			}
			elseif(!count($files))
			{	
				echo '1||'.$lang["delete_success_1"];
			}
			else
			{
				echo '0||'.$lang["delete_error_1"];	
			}
		}
		else
		{
			echo '0||'.$lang["paste_error_2"];
		}
		break;
	
	case "copy_paste":
		$files = $_POST["files"];
		$folder = urldecode($_POST["folder"]);
	
		if($folder = checkpath($folder, $uploadpath))
		{
			$folder = $folder;
			if(count($files) && is_dir($folder))
			{	
				$result = true;	
				foreach($files as $key => $value)
				{	
					$value = urldecode($value);	
					if( ($file = checkpath($value, $uploadpath)) && ($file != $folder) )
					{	
						$source = $file;
						$dest = $folder;
						
						if (is_dir($source) && $source[strlen($source) - 1] != '/')
						{
            				$source = $source."/"; 
						}
						
						if ($dest[strlen($dest) - 1] != '/')
						{
            				$dest = $dest."/"; 
						}
						
						if(is_dir($source)){			
							$dest=$dest.basename(rtrim($source, '/'));
						}
						else
						{
							$dest=$dest.basename($source);	
						}
						
						if(!smartCopy($source, $dest))
						{
							$result = false;
						}
					}
					else
					{
						echo '0||actions.php line 120: '.$lang["file_tampered_with"];	
						break;	
					}
				}
				
				if($result)
				{
					echo 'actions.php line 126: '."\n".$lang["paste_success_1"];
				}
				else
				{
					echo 'actions.php line 128: '."\n". $source . "\n" . $dest . "\n" . $lang["delete_error_1"];	
				}
			}
			elseif(!count($files))
			{
				echo 'actions.php line 134: '."\n".$lang["delete_success"];
			}
			else
			{
				echo 'actions.php line 139: '."\n".$lang["delete_error_1"];	
			}
		}
		else
		{
			echo $lang["paste_error_2"];
		}
		break;
		
	case "rename":
		$new_filename = urldecode($_POST["new_filename"]);
		$old_filename = urldecode($_POST["old_filename"]);
		$folderpath = urldecode($_POST["folder"]);
		$type = $_POST["type"];
		
		$result = false;
	
		if($folderpath = checkpath($folderpath, $uploadpath))
		{
			$folder = $folderpath;
			if($new_filename != "" && $old_filename != "" && is_dir($folder))
			{
				$result = true;
				
				if ($folder[strlen($folder) - 1] != '/')
				{
            		$folder=$folder."/"; 
				}
				
				$source = $folder . $old_filename;
				$dest = $folder . $new_filename;
				
				if($type == 'folder')
				{
					if(is_dir($dest))
					{
						echo "0||" . $lang["directory_already_exists"];
						break;
					}
					else 
					{
						if(!CreateDirectory($folderpath, $new_filename, $uploadpath))
						{
							echo 'error||' . $lang["create_folder_failed"];
							break;
						}
					}			
				}
				else 
				{
					if(is_file($dest))
					{
						echo "0||" . $lang["file_already_exists"];
						break;
					}
				}
					
				if(!smartCopy($source, $dest))
				{
					$result = false;
				}
					
				if($result)
				{
					if(recursiveDelete($source))
					{
						echo "1||Rename success!";	
					}
					else
					{
						echo "0||" . $lang["rename_failed"];		
					}
				}
				else
				{
					echo "0||" . $lang["rename_failed"];	
				}	
			}
		}
		else
		{
			echo '0||' . $lang["file_tampered_with"];	
			break;	
		}

		break;

	case "delete":
		$files = $_POST["files"];
		$result = true;
		
		foreach ($files as $key => $file)
		{
			$file = urldecode($file);
			if (!($file = checkpath($file, $uploadpath)))
			{
				echo $lang["file_tampered_with"];
				break;
			}
			
			if (!recursiveDelete($file))
			{
				$result=false;
			}
		}
		
		if($result)
		{
			echo 'success||'.count($files) . $lang["delete_success"];	
		}
		else
		{
			echo $lang["delete_error_2"];	
		}
		
		break;
		
	case "create_folder":
		$folderpath = urldecode($_POST["folderpath"]);
		$foldername = urldecode($_POST["foldername"]);

		if (CreateDirectory($folderpath, $foldername, $uploadpath))
		{
			echo 'success||' . $lang["create_folder_successful"];
		}
		else
		{
			echo 'error||' . $lang["create_folder_failed"];
		}
		
		break;
}

function CreateDirectory($dirpath, $dirname, $uploadpath)
{
	if(!checkFolderName($dirname))
	{
		return false;
	}

	if (!($dirpath = checkpath($dirpath, $uploadpath)))
	{
		return false;
	}
	
	if(!rmkdir($dirpath.$dirname))
	{
		return false;
	}
	
	return true;
}
?>