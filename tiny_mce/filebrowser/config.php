<?PHP
error_reporting(0);
ini_set('display_errors', 'off');

session_start();

if(!defined('FRAMEWORK_CONFIG_INI') || !getenv('FRAMEWORK_CONFIG_INI'))
{
	$framework_config_ini = getenv('FRAMEWORK_CONFIG_INI') 
		? getenv('FRAMEWORK_CONFIG_INI')
		: $_SERVER['DOCUMENT_ROOT'].'/framework.ini';
	if(!file_exists($framework_config_ini))
	{
		exit('ERROR: Unable to locate configuration file.  Looking for '.$framework_config_ini);
	}
	define('FRAMEWORK_CONFIG_INI', $framework_config_ini);
}

$framework_ini = parse_ini_file(FRAMEWORK_CONFIG_INI, TRUE);

/* 
 * UPLOAD PATH
 * 
 * absolute path from root to upload folder (DON'T FORGET SLASHES)
 */
$uploadpath = $framework_ini['paths']['assets_abs_path'].'uploads/';
$assetsurl = $framework_ini['paths']['assets_url'];

/* 
 * VIEW LAYOUT
 *
 * Set the default view layout when the file browser is first loaded
 *
 * Your options are: 'large_images', 'small_images', 'list', 'content', 'tiles' and 'details'
 *
 */
$viewlayout = 'details';

/* 
 * DEFAULT LANGUAGE
 * 
 * Set default language to load when &language=? is not included in url
 *
 * See lang directory for included languages. For now your options are 'en' and 'nl'
 * But you are free to translate the language files in the /lang/ directory. Copy the
 * en.php file and translate the lines after the =>
 *
 */
$defaultlanguage = 'en';


//--------------------------DON'T EDIT BEYOND THIS POINT ----------------------------------


define('STARTINGPATH', $uploadpath);


// Figure out which language file to load
if(!empty($_REQUEST['language']))
{
	$language = $_REQUEST['language'];
}
elseif (isset($_SESSION['language']))
{
	$language = $_SESSION['language'];
}
else
{
	$language = $defaultlanguage;
}

require_once('lang/'.$language.'.php');
$_SESSION['language'] = $language;

// Get local settings from language file
$datetimeFormat = $lang['datetime format'];				// 24 hours, AM/PM, etc...
$dec_seperator = $lang['decimal seperator']; 			// character in front of the decimals
$thousands_separator = $lang['thousands separator'];	// character between every group of thousands
$ignore = array(										// ignore these file extensions and do not display them
	'php',
	'htm',
	'html',
	'ico',
	'txt'
);
?>