<?PHP
error_reporting(0);
ini_set('display_errors', 'off');

require_once('functions.php');

$rootname = array_pop(explode("/", trim($uploadpath,"/")));
$dirs = getDirTree(STARTINGPATH, false);
			
echo '<ul class="treeview"><li class="selected"><a class="root" href="'.$uploadpath.'">'.$rootname.'</a>'.renderTree($dirs, $uploadpath).'</li></ul>';
?>