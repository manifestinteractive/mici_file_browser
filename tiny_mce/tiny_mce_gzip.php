<?PHP
@error_reporting(E_ERROR | E_WARNING | E_PARSE);

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

$plugins = explode(',', getParam("plugins", ""));
$languages = explode(',', getParam("languages", ""));
$themes = explode(',', getParam("themes", ""));
$diskCache = getParam("diskcache", "") == "true";
$isJS = getParam("js", "") == "true";
$compress = getParam("compress", "true") == "true";
$core = getParam("core", "true") == "true";
$suffix = getParam("suffix", "_src") == "_src" ? "_src" : "";
$cachePath = $framework_ini['config']['cache_path'];
$httphost = $framework_ini['paths']['domain'];
$expiresOffset = 3600 * 24 * 10;
$content = "";
$encodings = array();
$supportsGzip = false;
$enc = "";
$cacheKey = "";

$custom = array();

header("Content-type: text/javascript");
header("Vary: Accept-Encoding");
header("Expires: " . gmdate("D, d M Y H:i:s", time() + $expiresOffset) . " GMT");

$content .= "

var hostname = window.location.hostname;
var host = hostname.split('.');
var dom = '';

if(host.length == 3)
{
	dom = host[1]+'.'+host[2];
}
else if(host.length == 2)
{
	dom = host[0]+'.'+host[1];
}
else if(host.length == 1)
{
	dom = host[0];
}

if(dom != '')
{
	document.domain = dom.toString();
}

";

if (!$isJS)
{	
	echo getFileContents("tiny_mce_gzip.js");
	echo "tinyMCE_GZ.init({});";
	die();
}

if ($diskCache)
{
	if (!$cachePath)
	{
		die("alert('Real path failed.');");
	}

	$cacheKey = getParam("plugins", "") . getParam("languages", "") . getParam("themes", "") . $suffix;

	foreach ($custom as $file)
	{
		$cacheKey .= $file;
	}

	$cacheKey = md5($cacheKey);

	if ($compress)
	{
		$cacheFile = $cachePath . "/tiny_mce_" . $cacheKey . ".gz";
	}
	else
	{
		$cacheFile = $cachePath . "/tiny_mce_" . $cacheKey . ".js";
	}
}

if (isset($_SERVER['HTTP_ACCEPT_ENCODING']))
{
	$encodings = explode(',', strtolower(preg_replace("/\s+/", "", $_SERVER['HTTP_ACCEPT_ENCODING'])));
}

if ((in_array('gzip', $encodings) || in_array('x-gzip', $encodings) || isset($_SERVER['---------------'])) && function_exists('ob_gzhandler') && !ini_get('zlib.output_compression'))
{
	$enc = in_array('x-gzip', $encodings) ? "x-gzip" : "gzip";
	$supportsGzip = true;
}

if ($diskCache && $supportsGzip && file_exists($cacheFile))
{
	if ($compress)
	{
		header("Content-Encoding: " . $enc);
	}
	
	echo getFileContents($cacheFile);
	die();
}

if ($core == "true")
{
	$content .= getFileContents("tiny_mce" . $suffix . ".js");
	$content .= "tinyMCE_GZ.start();";
}

foreach ($languages as $lang)
{
	$content .= getFileContents("langs/" . $lang . ".js");
}

foreach ($themes as $theme)
{
	$content .= getFileContents( "themes/" . $theme . "/editor_template" . $suffix . ".js");
	foreach ($languages as $lang)
	{
		$content .= getFileContents("themes/" . $theme . "/langs/" . $lang . ".js");
	}
}

foreach ($plugins as $plugin)
{
	$content .= getFileContents("plugins/" . $plugin . "/editor_plugin" . $suffix . ".js");
	foreach ($languages as $lang)
	{
		$content .= getFileContents("plugins/" . $plugin . "/langs/" . $lang . ".js");
	}
}

foreach ($custom as $file)
{
	$content .= getFileContents($file);
}

if ($core == "true")
{
	$content .= "tinyMCE_GZ.end();";
}

if ($supportsGzip)
{
	if ($compress)
	{
		header("Content-Encoding: " . $enc);
		$cacheData = gzencode($content, 9, FORCE_GZIP);
	}
	else
	{
		$cacheData = $content;
	}
	
	if ($diskCache && $cacheKey != "")
	{
		putFileContents($cacheFile, $cacheData);
	}

	echo $cacheData;
}
else
{
	echo $content;
}

function getParam($name, $def = false)
{
	if (!isset($_GET[$name]))
	{
		return $def;
	}
	return preg_replace("/[^0-9a-z\-_,]+/i", "", $_GET[$name]);
}

function getFileContents($path)
{
	$path = realpath($path);

	if (!$path || !@is_file($path))
	{
		return "";
	}
	if (function_exists("file_get_contents"))
	{
		return @file_get_contents($path);
	}
	$content = "";
	$fp = @fopen($path, "r");
	if (!$fp)
	{
		return "";
	}
	while (!feof($fp))
	{
		$content .= fgets($fp);
	}
	
	fclose($fp);
	return $content;
}

function putFileContents($path, $content)
{
	if (function_exists("file_put_contents"))
	{
		return @file_put_contents($path, $content);
	}
	$fp = @fopen($path, "wb");
	if ($fp)
	{
		fwrite($fp, $content);
		fclose($fp);
	}
}
?>