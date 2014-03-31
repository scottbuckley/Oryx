<?php
 #####
#     #  ####  #    # ###### #  ####  #    # #####    ##   ##### #  ####  #    #
#       #    # ##   # #      # #    # #    # #    #  #  #    #   # #    # ##   #
#       #    # # #  # #####  # #      #    # #    # #    #   #   # #    # # #  #
#       #    # #  # # #      # #  ### #    # #####  ######   #   # #    # #  # #
#     # #    # #   ## #      # #    # #    # #   #  #    #   #   # #    # #   ##
 #####   ####  #    # #      #  ####   ####  #    # #    #   #   #  ####  #    #

$GLOBALS['SQL_FILENAME'] = 'sub.db';
$GLOBALS['MUSIC_DIR'] = '/home/scottbuckley/buck.ly/temp/submusic/';
$GLOBALS['LETTERGROUPS'] = array('A','B','C','D','E','F','G','H','I',
	'J','K','L','M','N','O','P','Q','R','S','T','U','V','W','XYZ', '#');
$GLOBALS['NONALPHALETTERGROUP'] = '#';
$GLOBALS['ARTICLES'] = array('The');
$GLOBALS['IGNOREDFOLDERS'] = array('..', '.');

######
#     #   ##   #####   ##
#     #  #  #    #    #  #
#     # #    #   #   #    #
#     # ######   #   ######
#     # #    #   #   #    #
######  #    #   #   #    #

function getPageMap() {
	return array(
		'rest/getMusicFolders.view' => 'getMusicFolders',
		'rest/getIndexes.view' => 'getIndexes',
		'rest/getMusicDirectory.view' => 'getMusicDirectory',
		'rest/getCoverArt.view' => 'getCoverArt',
		'rest/stream.view' => 'stream',
		'web/scan' => 'scanDirectories',
		'web/createdb' => 'createDB',
		'web/test' => 'test',
		'' => '',
	);
}

function getTemplate($templateName) {
	switch($templateName) {
		case 'NOTFOUND': return 
"<html>
	<title>Not found :(</title>
	<body>
		<p>It looks like your page was nooot found :(</p>
		<p>You requested $_SERVER[REQUEST_URI]</p>
	</body>
</html>";

		case 'XMLServiceWrapper': return
'<?xml version="1.0" encoding="UTF-8"?>
<subsonic-response xmlns="http://subsonic.org/restapi" status="ok" version="1.9.0"> 
	%s
</subsonic-response>';

		case 'DefaultMusicFolders': return
'	<musicFolders>
		<musicFolder id="0" name="Music"/>
	</musicFolders>';

		case 'DBACCESSERROR': return
'Unfortunately we were unable to create/access a database file. Make sure there is write access to this folder.';

		default: return
'TEMPLATE "$templateName" NOT FOUND';
	}
}





######
#     #   ##    ####  ######  ####
#     #  #  #  #    # #      #
######  #    # #      #####   ####
#       ###### #  ### #           #
#       #    # #    # #      #    #
#       #    #  ####  ######  ####

function invalidPage() {
	setContentType('HTML');
	header('HTTP/1.1 404 Not Found');
	echo getTemplate('NOTFOUND');
}

function getIndexes() {
	setContentType('XML');
	echo "now we is getting indexes :)";
}

function getMusicFolders() {
	setContentType('XML');
	echo wrapServiceXML(getTemplate('DefaultMusicFolders'));
}

function getMusicDirectory() {
	setContentType('XML');
	echo "now we is getting the music directory :)";
}

function getCoverArt() {
	setContentType('XML');
	echo "now we is getting the cover art :)";
}

function stream() {
	setContentType('XML');
	echo "now we is streaming :O";
}

function createDB() {
	dbCreateTables();
}

function scanDirectories() {
	$topLevelFolders = scandir($GLOBALS['MUSIC_DIR']);
	$topLevelFoldersByLetter = splitByFirstLetter($topLevelFolders);
	dbWriteIndexes($topLevelFoldersByLetter);
	echo "done writing indexes!";
}

function test() {
	echo "MEOOOW";
	$fname = '/home/scottbuckley/buck.ly/temp/submusic/A Weather/Everyday Balloons/1. Third of Life.mp3';
	id3();
	$getID3 = new getID3;
	$m = $getID3->analyze($fname);
	print_r($m);
}





#     #
##   ## ###### #####   ##
# # # # #        #    #  #
#  #  # #####    #   #    #
#     # #        #   ######
#     # #        #   #    #
#     # ######   #   #    #

function processURI() {
	$requestUriInfo = parse_url($_SERVER['REQUEST_URI']);
	$requestPath = $requestUriInfo['path'];

	if ($func = getByEndsWith($requestPath, getPageMap())) {
		$func();
	} else {
		invalidPage();
	}
	
}


 #####
#     #  ####  #    #  ####  ##### #####  #    #  ####  #####
#       #    # ##   # #        #   #    # #    # #    #   #
#       #    # # #  #  ####    #   #    # #    # #        #
#       #    # #  # #      #   #   #####  #    # #        #
#     # #    # #   ## #    #   #   #   #  #    # #    #   #
 #####   ####  #    #  ####    #   #    #  ####   ####    #

function setContentType($ContentType) {
	switch($ContentType) {
		case 'XML':
			header('Content-Type: text/xml'); break;
	}
}
function wrapServiceXML($inner) {
	return sprintf(getTemplate('XMLServiceWrapper'), $inner);
}


#     #
#     # ###### #      #####  ###### #####   ####
#     # #      #      #    # #      #    # #
####### #####  #      #    # #####  #    #  ####
#     # #      #      #####  #      #####       #
#     # #      #      #      #      #   #  #    #
#     # ###### ###### #      ###### #    #  ####

function endsWith($haystack, $needle) {
    return substr($haystack, -strlen($needle)) === $needle;
}
function startsWith($haystack, $needle) {
    return strpos($haystack, $needle) === 0;

}
function getByEndsWith($keyLong, $array) {
	foreach($array as $key => $value) {
		if (endsWith($keyLong, $key))
			return $value;
	}
	return False;
}
function removeArticles($word) {
	foreach ($GLOBALS['ARTICLES'] as $article) {
		if (startsWith($word, $article)) {
			$newWord = substr($word, strlen($article));
			$newWord = trim($newWord);
			if (strlen($newWord) != 0) {
				return $newWord;
			}
		}
	}
	return $word;
}
function strcontains($haystack, $needle) {
	return (strpos($haystack, $needle) !== false);
}
function getLetterGroup($name) {
	$first = removeArticles($name);
	$first = strtoupper(substr($first, 0, 1));
	foreach($GLOBALS['LETTERGROUPS'] as $lGroup) {
		if (strcontains($lGroup, $first)) {
			return $lGroup;
		}
	}
	return $GLOBALS['NONALPHALETTERGROUP'];
}
function splitByFirstLetter($items) {
	$splitItems = array();
	foreach($GLOBALS['LETTERGROUPS'] as $lGroup) {
		$splitItems[$lGroup] = array();
	}
	foreach($items as $item) {
		if (!in_array($item, $GLOBALS['IGNOREDFOLDERS'])) {
			$lGroup = getLetterGroup($item);
			$splitItems[$lGroup][] = $item;
		}
	}
	return $splitItems;
}


 #####
#     #  ####  #
#       #    # #
 #####  #    # #
      # #  # # #
#     # #   #  #
 #####   ### # ######

function dbConnect() {
	static $db;
	if (isset($db)) {
		return $db;
	} else {
		$fname = $GLOBALS['SQL_FILENAME'];
		if (!file_exists($fname)) {
			dbCreateTables();
		}
		if ($db = new PDO("sqlite:$fname")) {
			return $db;
		} else {
			die(getTemplate('DBACCESSERROR'));
		}
	}
}

function dbConnectSQLite3() {
	$fname = $GLOBALS['SQL_FILENAME'];
	if ($db = new SQLite3($fname)) {
		return $db;
	} else {
		die(getTemplate('DBACCESSERROR'));
	}
}

function dbCreateTables() {
	$db = dbConnectSQLite3();
	$db->exec('
		CREATE TABLE IF NOT EXISTS tblLibraries (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			name TEXT NOT NULL,
			dir TEXT
		);'); echo "tblLibraries created.<br/>";
	$db->exec('
		CREATE TABLE IF NOT EXISTS tblIndexes (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			name TEXT NOT NULL UNIQUE,
			letterGroup TEXT
		);'); echo "tblIndexes created.<br/>";
	$db->exec('
		CREATE TABLE IF NOT EXISTS tblDirectories (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			name TEXT NOT NULL,
			parentid INTEGER NOT NULL,
			parentname TEXT NOT NULL
		);'); echo "tblDirectories created.<br/>";
	$db->exec('
		CREATE TABLE IF NOT EXISTS tblTracks (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			parentid INTEGER NOT NULL,
			title TEXT,
			album TEXT,
			artist TEXT,
			duration INTEGER

		);'); echo "tblTracks created.<br/>";
	$db->exec('
		CREATE TABLE IF NOT EXISTS tblCoverArt (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			name TEXT NOT NULL UNIQUE,
			letterGroup TEXT
		);'); echo "tblCoverArt created.<br/>";
	$db->close();
}

function dbWriteIndexes($indexes) {
	$db = dbConnect();
	$db = dbConnect();
	$db = dbConnect();
	$db = dbConnect();
	$db->beginTransaction();
	foreach($indexes as $letter => $folders) {
		foreach($folders as $folder) {
			$q=$db->prepare(
				'INSERT INTO tblIndexes (letterGroup, name) VALUES (?, ?);');
			$q->execute(array($letter, $folder));
		}
	}
	$db->commit();
}




###
 #  #    # # ##### #   ##   #
 #  ##   # #   #   #  #  #  #
 #  # #  # #   #   # #    # #
 #  #  # # #   #   # ###### #
 #  #   ## #   #   # #    # #
### #    # #   #   # #    # ######

ini_set("error_log", getcwd()."/php.log");
ini_set("error_reporting", -1);
processURI();








#
#       # #####  #####    ##   #####  # ######  ####
#       # #    # #    #  #  #  #    # # #      #
#       # #####  #    # #    # #    # # #####   ####
#       # #    # #####  ###### #####  # #           #
#       # #    # #   #  #    # #   #  # #      #    #
####### # #####  #    # #    # #    # # ######  ####



function id3() {

/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                 //
//            or http://www.getid3.org                         //
/////////////////////////////////////////////////////////////////
//                                                             //
// Please see readme.txt for more information                  //
//                                                            ///
/////////////////////////////////////////////////////////////////

// define a constant rather than looking up every time it is needed
if (!defined('GETID3_OS_ISWINDOWS')) {
	define('GETID3_OS_ISWINDOWS', (stripos(PHP_OS, 'WIN') === 0));
}
// Get base path of getID3() - ONCE
if (!defined('GETID3_INCLUDEPATH')) {
	define('GETID3_INCLUDEPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);
}

// attempt to define temp dir as something flexible but reliable
$temp_dir = ini_get('upload_tmp_dir');
if ($temp_dir && (!is_dir($temp_dir) || !is_readable($temp_dir))) {
	$temp_dir = '';
}
if (!$temp_dir && function_exists('sys_get_temp_dir')) {
	// PHP v5.2.1+
	// sys_get_temp_dir() may give inaccessible temp dir, e.g. with open_basedir on virtual hosts
	$temp_dir = sys_get_temp_dir();
}
$temp_dir = realpath($temp_dir);
$open_basedir = ini_get('open_basedir');
if ($open_basedir) {
	// e.g. "/var/www/vhosts/getid3.org/httpdocs/:/tmp/"
	$temp_dir     = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $temp_dir);
	$open_basedir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $open_basedir);
	if (substr($temp_dir, -1, 1) != DIRECTORY_SEPARATOR) {
		$temp_dir .= DIRECTORY_SEPARATOR;
	}
	$found_valid_tempdir = false;
	$open_basedirs = explode(PATH_SEPARATOR, $open_basedir);
	foreach ($open_basedirs as $basedir) {
		if (substr($basedir, -1, 1) != DIRECTORY_SEPARATOR) {
			$basedir .= DIRECTORY_SEPARATOR;
		}
		if (preg_match('#^'.preg_quote($basedir).'#', $temp_dir)) {
			$found_valid_tempdir = true;
			break;
		}
	}
	if (!$found_valid_tempdir) {
		$temp_dir = '';
	}
	unset($open_basedirs, $found_valid_tempdir, $basedir);
}
if (!$temp_dir) {
	$temp_dir = '*'; // invalid directory name should force tempnam() to use system default temp dir
}
// $temp_dir = '/something/else/';  // feel free to override temp dir here if it works better for your system
define('GETID3_TEMP_DIR', $temp_dir);
unset($open_basedir, $temp_dir);

// End: Defines

////////////// BEGINGETID3 ////////////////////

class getID3
{
	// public: Settings
	public $encoding        = 'UTF-8';        // CASE SENSITIVE! - i.e. (must be supported by iconv()). Examples:  ISO-8859-1  UTF-8  UTF-16  UTF-16BE
	public $encoding_id3v1  = 'ISO-8859-1';   // Should always be 'ISO-8859-1', but some tags may be written in other encodings such as 'EUC-CN' or 'CP1252'

	// public: Optional tag checks - disable for speed.
	public $option_tag_id3v1         = true;  // Read and process ID3v1 tags
	public $option_tag_id3v2         = true;  // Read and process ID3v2 tags
	public $option_tag_lyrics3       = true;  // Read and process Lyrics3 tags
	public $option_tag_apetag        = true;  // Read and process APE tags
	public $option_tags_process      = true;  // Copy tags to root key 'tags' and encode to $this->encoding
	public $option_tags_html         = true;  // Copy tags to root key 'tags_html' properly translated from various encodings to HTML entities

	// public: Optional tag/comment calucations
	public $option_extra_info        = true;  // Calculate additional info such as bitrate, channelmode etc

	// public: Optional handling of embedded attachments (e.g. images)
	public $option_save_attachments  = true; // defaults to true (ATTACHMENTS_INLINE) for backward compatibility

	// public: Optional calculations
	public $option_md5_data          = false; // Get MD5 sum of data part - slow
	public $option_md5_data_source   = false; // Use MD5 of source file if availble - only FLAC and OptimFROG
	public $option_sha1_data         = false; // Get SHA1 sum of data part - slow
	public $option_max_2gb_check     = null;  // Check whether file is larger than 2GB and thus not supported by 32-bit PHP (null: auto-detect based on PHP_INT_MAX)

	// public: Read buffer size in bytes
	public $option_fread_buffer_size = 32768;

	// Public variables
	public $filename;                         // Filename of file being analysed.
	public $fp;                               // Filepointer to file being analysed.
	public $info;                             // Result array.
	public $tempdir = GETID3_TEMP_DIR;

	// Protected variables
	protected $startup_error   = '';
	protected $startup_warning = '';
	protected $memory_limit    = 0;

	const VERSION           = '1.9.7-20130705';
	const FREAD_BUFFER_SIZE = 32768;

	const ATTACHMENTS_NONE   = false;
	const ATTACHMENTS_INLINE = true;

	// public: constructor
	public function __construct() {

		// Check for PHP version
		$required_php_version = '5.0.5';
		if (version_compare(PHP_VERSION, $required_php_version, '<')) {
			$this->startup_error .= 'getID3() requires PHP v'.$required_php_version.' or higher - you are running v'.PHP_VERSION;
			return false;
		}

		// Check memory
		$this->memory_limit = ini_get('memory_limit');
		if (preg_match('#([0-9]+)M#i', $this->memory_limit, $matches)) {
			// could be stored as "16M" rather than 16777216 for example
			$this->memory_limit = $matches[1] * 1048576;
		} elseif (preg_match('#([0-9]+)G#i', $this->memory_limit, $matches)) { // The 'G' modifier is available since PHP 5.1.0
			// could be stored as "2G" rather than 2147483648 for example
			$this->memory_limit = $matches[1] * 1073741824;
		}
		if ($this->memory_limit <= 0) {
			// memory limits probably disabled
		} elseif ($this->memory_limit <= 4194304) {
			$this->startup_error .= 'PHP has less than 4MB available memory and will very likely run out. Increase memory_limit in php.ini';
		} elseif ($this->memory_limit <= 12582912) {
			$this->startup_warning .= 'PHP has less than 12MB available memory and might run out if all modules are loaded. Increase memory_limit in php.ini';
		}

		// Check safe_mode off
		if (preg_match('#(1|ON)#i', ini_get('safe_mode'))) {
			$this->warning('WARNING: Safe mode is on, shorten support disabled, md5data/sha1data for ogg vorbis disabled, ogg vorbos/flac tag writing disabled.');
		}

		if (intval(ini_get('mbstring.func_overload')) > 0) {
			$this->warning('WARNING: php.ini contains "mbstring.func_overload = '.ini_get('mbstring.func_overload').'", this may break things.');
		}

		// Check for magic_quotes_runtime
		if (function_exists('get_magic_quotes_runtime')) {
			if (get_magic_quotes_runtime()) {
				return $this->startup_error('magic_quotes_runtime must be disabled before running getID3(). Surround getid3 block by set_magic_quotes_runtime(0) and set_magic_quotes_runtime(1).');
			}
		}

		// Check for magic_quotes_gpc
		if (function_exists('magic_quotes_gpc')) {
			if (get_magic_quotes_gpc()) {
				return $this->startup_error('magic_quotes_gpc must be disabled before running getID3(). Surround getid3 block by set_magic_quotes_gpc(0) and set_magic_quotes_gpc(1).');
			}
		}

		// Load support library
		///if (!include_once(GETID3_INCLUDEPATH.'getid3.lib.php')) {
		///	$this->startup_error .= 'getid3.lib.php is missing or corrupt';
		///}
		/// INCLUDED MANUALLY -Scott

		if ($this->option_max_2gb_check === null) {
			$this->option_max_2gb_check = (PHP_INT_MAX <= 2147483647);
		}


		// Needed for Windows only:
		// Define locations of helper applications for Shorten, VorbisComment, MetaFLAC
		//   as well as other helper functions such as head, tail, md5sum, etc
		// This path cannot contain spaces, but the below code will attempt to get the
		//   8.3-equivalent path automatically
		// IMPORTANT: This path must include the trailing slash
		if (GETID3_OS_ISWINDOWS && !defined('GETID3_HELPERAPPSDIR')) {

			$helperappsdir = GETID3_INCLUDEPATH.'..'.DIRECTORY_SEPARATOR.'helperapps'; // must not have any space in this path

			if (!is_dir($helperappsdir)) {
				$this->startup_warning .= '"'.$helperappsdir.'" cannot be defined as GETID3_HELPERAPPSDIR because it does not exist';
			} elseif (strpos(realpath($helperappsdir), ' ') !== false) {
				$DirPieces = explode(DIRECTORY_SEPARATOR, realpath($helperappsdir));
				$path_so_far = array();
				foreach ($DirPieces as $key => $value) {
					if (strpos($value, ' ') !== false) {
						if (!empty($path_so_far)) {
							$commandline = 'dir /x '.escapeshellarg(implode(DIRECTORY_SEPARATOR, $path_so_far));
							$dir_listing = `$commandline`;
							$lines = explode("\n", $dir_listing);
							foreach ($lines as $line) {
								$line = trim($line);
								if (preg_match('#^([0-9/]{10}) +([0-9:]{4,5}( [AP]M)?) +(<DIR>|[0-9,]+) +([^ ]{0,11}) +(.+)$#', $line, $matches)) {
									list($dummy, $date, $time, $ampm, $filesize, $shortname, $filename) = $matches;
									if ((strtoupper($filesize) == '<DIR>') && (strtolower($filename) == strtolower($value))) {
										$value = $shortname;
									}
								}
							}
						} else {
							$this->startup_warning .= 'GETID3_HELPERAPPSDIR must not have any spaces in it - use 8dot3 naming convention if neccesary. You can run "dir /x" from the commandline to see the correct 8.3-style names.';
						}
					}
					$path_so_far[] = $value;
				}
				$helperappsdir = implode(DIRECTORY_SEPARATOR, $path_so_far);
			}
			define('GETID3_HELPERAPPSDIR', $helperappsdir.DIRECTORY_SEPARATOR);
		}

		return true;
	}

	public function version() {
		return self::VERSION;
	}

	public function fread_buffer_size() {
		return $this->option_fread_buffer_size;
	}


	// public: setOption
	public function setOption($optArray) {
		if (!is_array($optArray) || empty($optArray)) {
			return false;
		}
		foreach ($optArray as $opt => $val) {
			if (isset($this->$opt) === false) {
				continue;
			}
			$this->$opt = $val;
		}
		return true;
	}


	public function openfile($filename) {
		try {
			if (!empty($this->startup_error)) {
				throw new getid3_exception($this->startup_error);
			}
			if (!empty($this->startup_warning)) {
				$this->warning($this->startup_warning);
			}

			// init result array and set parameters
			$this->filename = $filename;
			$this->info = array();
			$this->info['GETID3_VERSION']   = $this->version();
			$this->info['php_memory_limit'] = $this->memory_limit;

			// remote files not supported
			if (preg_match('/^(ht|f)tp:\/\//', $filename)) {
				throw new getid3_exception('Remote files are not supported - please copy the file locally first');
			}

			$filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
			$filename = preg_replace('#(.+)'.preg_quote(DIRECTORY_SEPARATOR).'{2,}#U', '\1'.DIRECTORY_SEPARATOR, $filename);

			// open local file
			if (is_readable($filename) && is_file($filename) && ($this->fp = fopen($filename, 'rb'))) {
				// great
			} else {
				throw new getid3_exception('Could not open "'.$filename.'" (does not exist, or is not a file)');
			}

			$this->info['filesize'] = filesize($filename);
			// set redundant parameters - might be needed in some include file
			$this->info['filename']     = basename($filename);
			$this->info['filepath']     = str_replace('\\', '/', realpath(dirname($filename)));
			$this->info['filenamepath'] = $this->info['filepath'].'/'.$this->info['filename'];


			// option_max_2gb_check
			if ($this->option_max_2gb_check) {
				// PHP (32-bit all, and 64-bit Windows) doesn't support integers larger than 2^31 (~2GB)
				// filesize() simply returns (filesize % (pow(2, 32)), no matter the actual filesize
				// ftell() returns 0 if seeking to the end is beyond the range of unsigned integer
				$fseek = fseek($this->fp, 0, SEEK_END);
				if (($fseek < 0) || (($this->info['filesize'] != 0) && (ftell($this->fp) == 0)) ||
					($this->info['filesize'] < 0) ||
					(ftell($this->fp) < 0)) {
						$real_filesize = getid3_lib::getFileSizeSyscall($this->info['filenamepath']);

						if ($real_filesize === false) {
							unset($this->info['filesize']);
							fclose($this->fp);
							throw new getid3_exception('Unable to determine actual filesize. File is most likely larger than '.round(PHP_INT_MAX / 1073741824).'GB and is not supported by PHP.');
						} elseif (getid3_lib::intValueSupported($real_filesize)) {
							unset($this->info['filesize']);
							fclose($this->fp);
							throw new getid3_exception('PHP seems to think the file is larger than '.round(PHP_INT_MAX / 1073741824).'GB, but filesystem reports it as '.number_format($real_filesize, 3).'GB, please report to info@getid3.org');
						}
						$this->info['filesize'] = $real_filesize;
						$this->warning('File is larger than '.round(PHP_INT_MAX / 1073741824).'GB (filesystem reports it as '.number_format($real_filesize, 3).'GB) and is not properly supported by PHP.');
				}
			}

			// set more parameters
			$this->info['avdataoffset']        = 0;
			$this->info['avdataend']           = $this->info['filesize'];
			$this->info['fileformat']          = '';                // filled in later
			$this->info['audio']['dataformat'] = '';                // filled in later, unset if not used
			$this->info['video']['dataformat'] = '';                // filled in later, unset if not used
			$this->info['tags']                = array();           // filled in later, unset if not used
			$this->info['error']               = array();           // filled in later, unset if not used
			$this->info['warning']             = array();           // filled in later, unset if not used
			$this->info['comments']            = array();           // filled in later, unset if not used
			$this->info['encoding']            = $this->encoding;   // required by id3v2 and iso modules - can be unset at the end if desired

			return true;

		} catch (Exception $e) {
			$this->error($e->getMessage());
		}
		return false;
	}

	// public: analyze file
	public function analyze($filename) {
		try {
			if (!$this->openfile($filename)) {
				return $this->info;
			}

			// Handle tags
			foreach (array('id3v2'=>'id3v2', 'id3v1'=>'id3v1', 'apetag'=>'ape', 'lyrics3'=>'lyrics3') as $tag_name => $tag_key) {
				$option_tag = 'option_tag_'.$tag_name;
				if ($this->$option_tag) {
					$this->include_module('tag.'.$tag_name);
					try {
						$tag_class = 'getid3_'.$tag_name;
						$tag = new $tag_class($this);
						$tag->Analyze();
					}
					catch (getid3_exception $e) {
						throw $e;
					}
				}
			}
			if (isset($this->info['id3v2']['tag_offset_start'])) {
				$this->info['avdataoffset'] = max($this->info['avdataoffset'], $this->info['id3v2']['tag_offset_end']);
			}
			foreach (array('id3v1'=>'id3v1', 'apetag'=>'ape', 'lyrics3'=>'lyrics3') as $tag_name => $tag_key) {
				if (isset($this->info[$tag_key]['tag_offset_start'])) {
					$this->info['avdataend'] = min($this->info['avdataend'], $this->info[$tag_key]['tag_offset_start']);
				}
			}

			// ID3v2 detection (NOT parsing), even if ($this->option_tag_id3v2 == false) done to make fileformat easier
			if (!$this->option_tag_id3v2) {
				fseek($this->fp, 0, SEEK_SET);
				$header = fread($this->fp, 10);
				if ((substr($header, 0, 3) == 'ID3') && (strlen($header) == 10)) {
					$this->info['id3v2']['header']        = true;
					$this->info['id3v2']['majorversion']  = ord($header{3});
					$this->info['id3v2']['minorversion']  = ord($header{4});
					$this->info['avdataoffset']          += getid3_lib::BigEndian2Int(substr($header, 6, 4), 1) + 10; // length of ID3v2 tag in 10-byte header doesn't include 10-byte header length
				}
			}

			// read 32 kb file data
			fseek($this->fp, $this->info['avdataoffset'], SEEK_SET);
			$formattest = fread($this->fp, 32774);

			// determine format
			$determined_format = $this->GetFileFormat($formattest, $filename);

			// unable to determine file format
			if (!$determined_format) {
				fclose($this->fp);
				return $this->error('unable to determine file format');
			}

			// check for illegal ID3 tags
			if (isset($determined_format['fail_id3']) && (in_array('id3v1', $this->info['tags']) || in_array('id3v2', $this->info['tags']))) {
				if ($determined_format['fail_id3'] === 'ERROR') {
					fclose($this->fp);
					return $this->error('ID3 tags not allowed on this file type.');
				} elseif ($determined_format['fail_id3'] === 'WARNING') {
					$this->warning('ID3 tags not allowed on this file type.');
				}
			}

			// check for illegal APE tags
			if (isset($determined_format['fail_ape']) && in_array('ape', $this->info['tags'])) {
				if ($determined_format['fail_ape'] === 'ERROR') {
					fclose($this->fp);
					return $this->error('APE tags not allowed on this file type.');
				} elseif ($determined_format['fail_ape'] === 'WARNING') {
					$this->warning('APE tags not allowed on this file type.');
				}
			}

			// set mime type
			$this->info['mime_type'] = $determined_format['mime_type'];

			// supported format signature pattern detected, but module deleted
			if (!file_exists(GETID3_INCLUDEPATH.$determined_format['include'])) {
				fclose($this->fp);
				return $this->error('Format not supported, module "'.$determined_format['include'].'" was removed.');
			}

			// module requires iconv support
			// Check encoding/iconv support
			if (!empty($determined_format['iconv_req']) && !function_exists('iconv') && !in_array($this->encoding, array('ISO-8859-1', 'UTF-8', 'UTF-16LE', 'UTF-16BE', 'UTF-16'))) {
				$errormessage = 'iconv() support is required for this module ('.$determined_format['include'].') for encodings other than ISO-8859-1, UTF-8, UTF-16LE, UTF16-BE, UTF-16. ';
				if (GETID3_OS_ISWINDOWS) {
					$errormessage .= 'PHP does not have iconv() support. Please enable php_iconv.dll in php.ini, and copy iconv.dll from c:/php/dlls to c:/windows/system32';
				} else {
					$errormessage .= 'PHP is not compiled with iconv() support. Please recompile with the --with-iconv switch';
				}
				return $this->error($errormessage);
			}

			// include module
			include_once(GETID3_INCLUDEPATH.$determined_format['include']);

			// instantiate module class
			$class_name = 'getid3_'.$determined_format['module'];
			if (!class_exists($class_name)) {
				return $this->error('Format not supported, module "'.$determined_format['include'].'" is corrupt.');
			}
			$class = new $class_name($this);
			$class->Analyze();
			unset($class);

			// close file
			fclose($this->fp);

			// process all tags - copy to 'tags' and convert charsets
			if ($this->option_tags_process) {
				$this->HandleAllTags();
			}

			// perform more calculations
			if ($this->option_extra_info) {
				$this->ChannelsBitratePlaytimeCalculations();
				$this->CalculateCompressionRatioVideo();
				$this->CalculateCompressionRatioAudio();
				$this->CalculateReplayGain();
				$this->ProcessAudioStreams();
			}

			// get the MD5 sum of the audio/video portion of the file - without ID3/APE/Lyrics3/etc header/footer tags
			if ($this->option_md5_data) {
				// do not calc md5_data if md5_data_source is present - set by flac only - future MPC/SV8 too
				if (!$this->option_md5_data_source || empty($this->info['md5_data_source'])) {
					$this->getHashdata('md5');
				}
			}

			// get the SHA1 sum of the audio/video portion of the file - without ID3/APE/Lyrics3/etc header/footer tags
			if ($this->option_sha1_data) {
				$this->getHashdata('sha1');
			}

			// remove undesired keys
			$this->CleanUp();

		} catch (Exception $e) {
			$this->error('Caught exception: '.$e->getMessage());
		}

		// return info array
		return $this->info;
	}


	// private: error handling
	public function error($message) {
		$this->CleanUp();
		if (!isset($this->info['error'])) {
			$this->info['error'] = array();
		}
		$this->info['error'][] = $message;
		return $this->info;
	}


	// private: warning handling
	public function warning($message) {
		$this->info['warning'][] = $message;
		return true;
	}


	// private: CleanUp
	private function CleanUp() {

		// remove possible empty keys
		$AVpossibleEmptyKeys = array('dataformat', 'bits_per_sample', 'encoder_options', 'streams', 'bitrate');
		foreach ($AVpossibleEmptyKeys as $dummy => $key) {
			if (empty($this->info['audio'][$key]) && isset($this->info['audio'][$key])) {
				unset($this->info['audio'][$key]);
			}
			if (empty($this->info['video'][$key]) && isset($this->info['video'][$key])) {
				unset($this->info['video'][$key]);
			}
		}

		// remove empty root keys
		if (!empty($this->info)) {
			foreach ($this->info as $key => $value) {
				if (empty($this->info[$key]) && ($this->info[$key] !== 0) && ($this->info[$key] !== '0')) {
					unset($this->info[$key]);
				}
			}
		}

		// remove meaningless entries from unknown-format files
		if (empty($this->info['fileformat'])) {
			if (isset($this->info['avdataoffset'])) {
				unset($this->info['avdataoffset']);
			}
			if (isset($this->info['avdataend'])) {
				unset($this->info['avdataend']);
			}
		}

		// remove possible duplicated identical entries
		if (!empty($this->info['error'])) {
			$this->info['error'] = array_values(array_unique($this->info['error']));
		}
		if (!empty($this->info['warning'])) {
			$this->info['warning'] = array_values(array_unique($this->info['warning']));
		}

		// remove "global variable" type keys
		unset($this->info['php_memory_limit']);

		return true;
	}


	// return array containing information about all supported formats
	public function GetFileFormatArray() {
		static $format_info = array();
		if (empty($format_info)) {
			$format_info = array(

				// Audio formats

				// AC-3   - audio      - Dolby AC-3 / Dolby Digital
				'ac3'  => array(
							'pattern'   => '^\x0B\x77',
							'group'     => 'audio',
							'module'    => 'ac3',
							'mime_type' => 'audio/ac3',
						),

				// AAC  - audio       - Advanced Audio Coding (AAC) - ADIF format
				'adif' => array(
							'pattern'   => '^ADIF',
							'group'     => 'audio',
							'module'    => 'aac',
							'mime_type' => 'application/octet-stream',
							'fail_ape'  => 'WARNING',
						),

/*
				// AA   - audio       - Audible Audiobook
				'aa'   => array(
							'pattern'   => '^.{4}\x57\x90\x75\x36',
							'group'     => 'audio',
							'module'    => 'aa',
							'mime_type' => 'audio/audible',
						),
*/
				// AAC  - audio       - Advanced Audio Coding (AAC) - ADTS format (very similar to MP3)
				'adts' => array(
							'pattern'   => '^\xFF[\xF0-\xF1\xF8-\xF9]',
							'group'     => 'audio',
							'module'    => 'aac',
							'mime_type' => 'application/octet-stream',
							'fail_ape'  => 'WARNING',
						),


				// AU   - audio       - NeXT/Sun AUdio (AU)
				'au'   => array(
							'pattern'   => '^\.snd',
							'group'     => 'audio',
							'module'    => 'au',
							'mime_type' => 'audio/basic',
						),

				// AVR  - audio       - Audio Visual Research
				'avr'  => array(
							'pattern'   => '^2BIT',
							'group'     => 'audio',
							'module'    => 'avr',
							'mime_type' => 'application/octet-stream',
						),

				// BONK - audio       - Bonk v0.9+
				'bonk' => array(
							'pattern'   => '^\x00(BONK|INFO|META| ID3)',
							'group'     => 'audio',
							'module'    => 'bonk',
							'mime_type' => 'audio/xmms-bonk',
						),

				// DSS  - audio       - Digital Speech Standard
				'dss'  => array(
							'pattern'   => '^[\x02-\x03]ds[s2]',
							'group'     => 'audio',
							'module'    => 'dss',
							'mime_type' => 'application/octet-stream',
						),

				// DTS  - audio       - Dolby Theatre System
				'dts'  => array(
							'pattern'   => '^\x7F\xFE\x80\x01',
							'group'     => 'audio',
							'module'    => 'dts',
							'mime_type' => 'audio/dts',
						),

				// FLAC - audio       - Free Lossless Audio Codec
				'flac' => array(
							'pattern'   => '^fLaC',
							'group'     => 'audio',
							'module'    => 'flac',
							'mime_type' => 'audio/x-flac',
						),

				// LA   - audio       - Lossless Audio (LA)
				'la'   => array(
							'pattern'   => '^LA0[2-4]',
							'group'     => 'audio',
							'module'    => 'la',
							'mime_type' => 'application/octet-stream',
						),

				// LPAC - audio       - Lossless Predictive Audio Compression (LPAC)
				'lpac' => array(
							'pattern'   => '^LPAC',
							'group'     => 'audio',
							'module'    => 'lpac',
							'mime_type' => 'application/octet-stream',
						),

				// MIDI - audio       - MIDI (Musical Instrument Digital Interface)
				'midi' => array(
							'pattern'   => '^MThd',
							'group'     => 'audio',
							'module'    => 'midi',
							'mime_type' => 'audio/midi',
						),

				// MAC  - audio       - Monkey's Audio Compressor
				'mac'  => array(
							'pattern'   => '^MAC ',
							'group'     => 'audio',
							'module'    => 'monkey',
							'mime_type' => 'application/octet-stream',
						),

// has been known to produce false matches in random files (e.g. JPEGs), leave out until more precise matching available
//				// MOD  - audio       - MODule (assorted sub-formats)
//				'mod'  => array(
//							'pattern'   => '^.{1080}(M\\.K\\.|M!K!|FLT4|FLT8|[5-9]CHN|[1-3][0-9]CH)',
//							'group'     => 'audio',
//							'module'    => 'mod',
//							'option'    => 'mod',
//							'mime_type' => 'audio/mod',
//						),

				// MOD  - audio       - MODule (Impulse Tracker)
				'it'   => array(
							'pattern'   => '^IMPM',
							'group'     => 'audio',
							'module'    => 'mod',
							//'option'    => 'it',
							'mime_type' => 'audio/it',
						),

				// MOD  - audio       - MODule (eXtended Module, various sub-formats)
				'xm'   => array(
							'pattern'   => '^Extended Module',
							'group'     => 'audio',
							'module'    => 'mod',
							//'option'    => 'xm',
							'mime_type' => 'audio/xm',
						),

				// MOD  - audio       - MODule (ScreamTracker)
				's3m'  => array(
							'pattern'   => '^.{44}SCRM',
							'group'     => 'audio',
							'module'    => 'mod',
							//'option'    => 's3m',
							'mime_type' => 'audio/s3m',
						),

				// MPC  - audio       - Musepack / MPEGplus
				'mpc'  => array(
							'pattern'   => '^(MPCK|MP\+|[\x00\x01\x10\x11\x40\x41\x50\x51\x80\x81\x90\x91\xC0\xC1\xD0\xD1][\x20-37][\x00\x20\x40\x60\x80\xA0\xC0\xE0])',
							'group'     => 'audio',
							'module'    => 'mpc',
							'mime_type' => 'audio/x-musepack',
						),

				// MP3  - audio       - MPEG-audio Layer 3 (very similar to AAC-ADTS)
				'mp3'  => array(
							'pattern'   => '^\xFF[\xE2-\xE7\xF2-\xF7\xFA-\xFF][\x00-\x0B\x10-\x1B\x20-\x2B\x30-\x3B\x40-\x4B\x50-\x5B\x60-\x6B\x70-\x7B\x80-\x8B\x90-\x9B\xA0-\xAB\xB0-\xBB\xC0-\xCB\xD0-\xDB\xE0-\xEB\xF0-\xFB]',
							'group'     => 'audio',
							'module'    => 'mp3',
							'mime_type' => 'audio/mpeg',
						),

				// OFR  - audio       - OptimFROG
				'ofr'  => array(
							'pattern'   => '^(\*RIFF|OFR)',
							'group'     => 'audio',
							'module'    => 'optimfrog',
							'mime_type' => 'application/octet-stream',
						),

				// RKAU - audio       - RKive AUdio compressor
				'rkau' => array(
							'pattern'   => '^RKA',
							'group'     => 'audio',
							'module'    => 'rkau',
							'mime_type' => 'application/octet-stream',
						),

				// SHN  - audio       - Shorten
				'shn'  => array(
							'pattern'   => '^ajkg',
							'group'     => 'audio',
							'module'    => 'shorten',
							'mime_type' => 'audio/xmms-shn',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// TTA  - audio       - TTA Lossless Audio Compressor (http://tta.corecodec.org)
				'tta'  => array(
							'pattern'   => '^TTA',  // could also be '^TTA(\x01|\x02|\x03|2|1)'
							'group'     => 'audio',
							'module'    => 'tta',
							'mime_type' => 'application/octet-stream',
						),

				// VOC  - audio       - Creative Voice (VOC)
				'voc'  => array(
							'pattern'   => '^Creative Voice File',
							'group'     => 'audio',
							'module'    => 'voc',
							'mime_type' => 'audio/voc',
						),

				// VQF  - audio       - transform-domain weighted interleave Vector Quantization Format (VQF)
				'vqf'  => array(
							'pattern'   => '^TWIN',
							'group'     => 'audio',
							'module'    => 'vqf',
							'mime_type' => 'application/octet-stream',
						),

				// WV  - audio        - WavPack (v4.0+)
				'wv'   => array(
							'pattern'   => '^wvpk',
							'group'     => 'audio',
							'module'    => 'wavpack',
							'mime_type' => 'application/octet-stream',
						),


				// Audio-Video formats

				// ASF  - audio/video - Advanced Streaming Format, Windows Media Video, Windows Media Audio
				'asf'  => array(
							'pattern'   => '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C',
							'group'     => 'audio-video',
							'module'    => 'asf',
							'mime_type' => 'video/x-ms-asf',
							'iconv_req' => false,
						),

				// BINK - audio/video - Bink / Smacker
				'bink' => array(
							'pattern'   => '^(BIK|SMK)',
							'group'     => 'audio-video',
							'module'    => 'bink',
							'mime_type' => 'application/octet-stream',
						),

				// FLV  - audio/video - FLash Video
				'flv' => array(
							'pattern'   => '^FLV\x01',
							'group'     => 'audio-video',
							'module'    => 'flv',
							'mime_type' => 'video/x-flv',
						),

				// MKAV - audio/video - Mastroka
				'matroska' => array(
							'pattern'   => '^\x1A\x45\xDF\xA3',
							'group'     => 'audio-video',
							'module'    => 'matroska',
							'mime_type' => 'video/x-matroska', // may also be audio/x-matroska
						),

				// MPEG - audio/video - MPEG (Moving Pictures Experts Group)
				'mpeg' => array(
							'pattern'   => '^\x00\x00\x01(\xBA|\xB3)',
							'group'     => 'audio-video',
							'module'    => 'mpeg',
							'mime_type' => 'video/mpeg',
						),

				// NSV  - audio/video - Nullsoft Streaming Video (NSV)
				'nsv'  => array(
							'pattern'   => '^NSV[sf]',
							'group'     => 'audio-video',
							'module'    => 'nsv',
							'mime_type' => 'application/octet-stream',
						),

				// Ogg  - audio/video - Ogg (Ogg-Vorbis, Ogg-FLAC, Speex, Ogg-Theora(*), Ogg-Tarkin(*))
				'ogg'  => array(
							'pattern'   => '^OggS',
							'group'     => 'audio',
							'module'    => 'ogg',
							'mime_type' => 'application/ogg',
							'fail_id3'  => 'WARNING',
							'fail_ape'  => 'WARNING',
						),

				// QT   - audio/video - Quicktime
				'quicktime' => array(
							'pattern'   => '^.{4}(cmov|free|ftyp|mdat|moov|pnot|skip|wide)',
							'group'     => 'audio-video',
							'module'    => 'quicktime',
							'mime_type' => 'video/quicktime',
						),

				// RIFF - audio/video - Resource Interchange File Format (RIFF) / WAV / AVI / CD-audio / SDSS = renamed variant used by SmartSound QuickTracks (www.smartsound.com) / FORM = Audio Interchange File Format (AIFF)
				'riff' => array(
							'pattern'   => '^(RIFF|SDSS|FORM)',
							'group'     => 'audio-video',
							'module'    => 'riff',
							'mime_type' => 'audio/x-wave',
							'fail_ape'  => 'WARNING',
						),

				// Real - audio/video - RealAudio, RealVideo
				'real' => array(
							'pattern'   => '^(\\.RMF|\\.ra)',
							'group'     => 'audio-video',
							'module'    => 'real',
							'mime_type' => 'audio/x-realaudio',
						),

				// SWF - audio/video - ShockWave Flash
				'swf' => array(
							'pattern'   => '^(F|C)WS',
							'group'     => 'audio-video',
							'module'    => 'swf',
							'mime_type' => 'application/x-shockwave-flash',
						),

				// TS - audio/video - MPEG-2 Transport Stream
				'ts' => array(
							'pattern'   => '^(\x47.{187}){10,}', // packets are 188 bytes long and start with 0x47 "G".  Check for at least 10 packets matching this pattern
							'group'     => 'audio-video',
							'module'    => 'ts',
							'mime_type' => 'video/MP2T',
						),


				// Still-Image formats

				// BMP  - still image - Bitmap (Windows, OS/2; uncompressed, RLE8, RLE4)
				'bmp'  => array(
							'pattern'   => '^BM',
							'group'     => 'graphic',
							'module'    => 'bmp',
							'mime_type' => 'image/bmp',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// GIF  - still image - Graphics Interchange Format
				'gif'  => array(
							'pattern'   => '^GIF',
							'group'     => 'graphic',
							'module'    => 'gif',
							'mime_type' => 'image/gif',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// JPEG - still image - Joint Photographic Experts Group (JPEG)
				'jpg'  => array(
							'pattern'   => '^\xFF\xD8\xFF',
							'group'     => 'graphic',
							'module'    => 'jpg',
							'mime_type' => 'image/jpeg',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// PCD  - still image - Kodak Photo CD
				'pcd'  => array(
							'pattern'   => '^.{2048}PCD_IPI\x00',
							'group'     => 'graphic',
							'module'    => 'pcd',
							'mime_type' => 'image/x-photo-cd',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),


				// PNG  - still image - Portable Network Graphics (PNG)
				'png'  => array(
							'pattern'   => '^\x89\x50\x4E\x47\x0D\x0A\x1A\x0A',
							'group'     => 'graphic',
							'module'    => 'png',
							'mime_type' => 'image/png',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),


				// SVG  - still image - Scalable Vector Graphics (SVG)
				'svg'  => array(
							'pattern'   => '(<!DOCTYPE svg PUBLIC |xmlns="http:\/\/www\.w3\.org\/2000\/svg")',
							'group'     => 'graphic',
							'module'    => 'svg',
							'mime_type' => 'image/svg+xml',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),


				// TIFF - still image - Tagged Information File Format (TIFF)
				'tiff' => array(
							'pattern'   => '^(II\x2A\x00|MM\x00\x2A)',
							'group'     => 'graphic',
							'module'    => 'tiff',
							'mime_type' => 'image/tiff',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),


				// EFAX - still image - eFax (TIFF derivative)
				'efax'  => array(
							'pattern'   => '^\xDC\xFE',
							'group'     => 'graphic',
							'module'    => 'efax',
							'mime_type' => 'image/efax',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),


				// Data formats

				// ISO  - data        - International Standards Organization (ISO) CD-ROM Image
				'iso'  => array(
							'pattern'   => '^.{32769}CD001',
							'group'     => 'misc',
							'module'    => 'iso',
							'mime_type' => 'application/octet-stream',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
							'iconv_req' => false,
						),

				// RAR  - data        - RAR compressed data
				'rar'  => array(
							'pattern'   => '^Rar\!',
							'group'     => 'archive',
							'module'    => 'rar',
							'mime_type' => 'application/octet-stream',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// SZIP - audio/data  - SZIP compressed data
				'szip' => array(
							'pattern'   => '^SZ\x0A\x04',
							'group'     => 'archive',
							'module'    => 'szip',
							'mime_type' => 'application/octet-stream',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// TAR  - data        - TAR compressed data
				'tar'  => array(
							'pattern'   => '^.{100}[0-9\x20]{7}\x00[0-9\x20]{7}\x00[0-9\x20]{7}\x00[0-9\x20\x00]{12}[0-9\x20\x00]{12}',
							'group'     => 'archive',
							'module'    => 'tar',
							'mime_type' => 'application/x-tar',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// GZIP  - data        - GZIP compressed data
				'gz'  => array(
							'pattern'   => '^\x1F\x8B\x08',
							'group'     => 'archive',
							'module'    => 'gzip',
							'mime_type' => 'application/x-gzip',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// ZIP  - data         - ZIP compressed data
				'zip'  => array(
							'pattern'   => '^PK\x03\x04',
							'group'     => 'archive',
							'module'    => 'zip',
							'mime_type' => 'application/zip',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),


				// Misc other formats

				// PAR2 - data        - Parity Volume Set Specification 2.0
				'par2' => array (
							'pattern'   => '^PAR2\x00PKT',
							'group'     => 'misc',
							'module'    => 'par2',
							'mime_type' => 'application/octet-stream',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// PDF  - data        - Portable Document Format
				'pdf'  => array(
							'pattern'   => '^\x25PDF',
							'group'     => 'misc',
							'module'    => 'pdf',
							'mime_type' => 'application/pdf',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				// MSOFFICE  - data   - ZIP compressed data
				'msoffice' => array(
							'pattern'   => '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1', // D0CF11E == DOCFILE == Microsoft Office Document
							'group'     => 'misc',
							'module'    => 'msoffice',
							'mime_type' => 'application/octet-stream',
							'fail_id3'  => 'ERROR',
							'fail_ape'  => 'ERROR',
						),

				 // CUE  - data       - CUEsheet (index to single-file disc images)
				 'cue' => array(
							'pattern'   => '', // empty pattern means cannot be automatically detected, will fall through all other formats and match based on filename and very basic file contents
							'group'     => 'misc',
							'module'    => 'cue',
							'mime_type' => 'application/octet-stream',
						   ),

			);
		}

		return $format_info;
	}



	public function GetFileFormat(&$filedata, $filename='') {
		// this function will determine the format of a file based on usually
		// the first 2-4 bytes of the file (8 bytes for PNG, 16 bytes for JPG,
		// and in the case of ISO CD image, 6 bytes offset 32kb from the start
		// of the file).

		// Identify file format - loop through $format_info and detect with reg expr
		foreach ($this->GetFileFormatArray() as $format_name => $info) {
			// The /s switch on preg_match() forces preg_match() NOT to treat
			// newline (0x0A) characters as special chars but do a binary match
			if (!empty($info['pattern']) && preg_match('#'.$info['pattern'].'#s', $filedata)) {
				$info['include'] = 'module.'.$info['group'].'.'.$info['module'].'.php';
				return $info;
			}
		}


		if (preg_match('#\.mp[123a]$#i', $filename)) {
			// Too many mp3 encoders on the market put gabage in front of mpeg files
			// use assume format on these if format detection failed
			$GetFileFormatArray = $this->GetFileFormatArray();
			$info = $GetFileFormatArray['mp3'];
			$info['include'] = 'module.'.$info['group'].'.'.$info['module'].'.php';
			return $info;
		} elseif (preg_match('/\.cue$/i', $filename) && preg_match('#FILE "[^"]+" (BINARY|MOTOROLA|AIFF|WAVE|MP3)#', $filedata)) {
			// there's not really a useful consistent "magic" at the beginning of .cue files to identify them
			// so until I think of something better, just go by filename if all other format checks fail
			// and verify there's at least one instance of "TRACK xx AUDIO" in the file
			$GetFileFormatArray = $this->GetFileFormatArray();
			$info = $GetFileFormatArray['cue'];
			$info['include']   = 'module.'.$info['group'].'.'.$info['module'].'.php';
			return $info;
		}

		return false;
	}


	// converts array to $encoding charset from $this->encoding
	public function CharConvert(&$array, $encoding) {

		// identical encoding - end here
		if ($encoding == $this->encoding) {
			return;
		}

		// loop thru array
		foreach ($array as $key => $value) {

			// go recursive
			if (is_array($value)) {
				$this->CharConvert($array[$key], $encoding);
			}

			// convert string
			elseif (is_string($value)) {
				$array[$key] = trim(getid3_lib::iconv_fallback($encoding, $this->encoding, $value));
			}
		}
	}


	public function HandleAllTags() {

		// key name => array (tag name, character encoding)
		static $tags;
		if (empty($tags)) {
			$tags = array(
				'asf'       => array('asf'           , 'UTF-16LE'),
				'midi'      => array('midi'          , 'ISO-8859-1'),
				'nsv'       => array('nsv'           , 'ISO-8859-1'),
				'ogg'       => array('vorbiscomment' , 'UTF-8'),
				'png'       => array('png'           , 'UTF-8'),
				'tiff'      => array('tiff'          , 'ISO-8859-1'),
				'quicktime' => array('quicktime'     , 'UTF-8'),
				'real'      => array('real'          , 'ISO-8859-1'),
				'vqf'       => array('vqf'           , 'ISO-8859-1'),
				'zip'       => array('zip'           , 'ISO-8859-1'),
				'riff'      => array('riff'          , 'ISO-8859-1'),
				'lyrics3'   => array('lyrics3'       , 'ISO-8859-1'),
				'id3v1'     => array('id3v1'         , $this->encoding_id3v1),
				'id3v2'     => array('id3v2'         , 'UTF-8'), // not according to the specs (every frame can have a different encoding), but getID3() force-converts all encodings to UTF-8
				'ape'       => array('ape'           , 'UTF-8'),
				'cue'       => array('cue'           , 'ISO-8859-1'),
				'matroska'  => array('matroska'      , 'UTF-8'),
				'flac'      => array('vorbiscomment' , 'UTF-8'),
				'divxtag'   => array('divx'          , 'ISO-8859-1'),
			);
		}

		// loop through comments array
		foreach ($tags as $comment_name => $tagname_encoding_array) {
			list($tag_name, $encoding) = $tagname_encoding_array;

			// fill in default encoding type if not already present
			if (isset($this->info[$comment_name]) && !isset($this->info[$comment_name]['encoding'])) {
				$this->info[$comment_name]['encoding'] = $encoding;
			}

			// copy comments if key name set
			if (!empty($this->info[$comment_name]['comments'])) {
				foreach ($this->info[$comment_name]['comments'] as $tag_key => $valuearray) {
					foreach ($valuearray as $key => $value) {
						if (is_string($value)) {
							$value = trim($value, " \r\n\t"); // do not trim nulls from $value!! Unicode characters will get mangled if trailing nulls are removed!
						}
						if ($value) {
							$this->info['tags'][trim($tag_name)][trim($tag_key)][] = $value;
						}
					}
					if ($tag_key == 'picture') {
						unset($this->info[$comment_name]['comments'][$tag_key]);
					}
				}

				if (!isset($this->info['tags'][$tag_name])) {
					// comments are set but contain nothing but empty strings, so skip
					continue;
				}

				if ($this->option_tags_html) {
					foreach ($this->info['tags'][$tag_name] as $tag_key => $valuearray) {
						foreach ($valuearray as $key => $value) {
							if (is_string($value)) {
								//$this->info['tags_html'][$tag_name][$tag_key][$key] = getid3_lib::MultiByteCharString2HTML($value, $encoding);
								$this->info['tags_html'][$tag_name][$tag_key][$key] = str_replace('&#0;', '', trim(getid3_lib::MultiByteCharString2HTML($value, $encoding)));
							} else {
								$this->info['tags_html'][$tag_name][$tag_key][$key] = $value;
							}
						}
					}
				}

				$this->CharConvert($this->info['tags'][$tag_name], $encoding);           // only copy gets converted!
			}

		}

		// pictures can take up a lot of space, and we don't need multiple copies of them
		// let there be a single copy in [comments][picture], and not elsewhere
		if (!empty($this->info['tags'])) {
			$unset_keys = array('tags', 'tags_html');
			foreach ($this->info['tags'] as $tagtype => $tagarray) {
				foreach ($tagarray as $tagname => $tagdata) {
					if ($tagname == 'picture') {
						foreach ($tagdata as $key => $tagarray) {
							$this->info['comments']['picture'][] = $tagarray;
							if (isset($tagarray['data']) && isset($tagarray['image_mime'])) {
								if (isset($this->info['tags'][$tagtype][$tagname][$key])) {
									unset($this->info['tags'][$tagtype][$tagname][$key]);
								}
								if (isset($this->info['tags_html'][$tagtype][$tagname][$key])) {
									unset($this->info['tags_html'][$tagtype][$tagname][$key]);
								}
							}
						}
					}
				}
				foreach ($unset_keys as $unset_key) {
					// remove possible empty keys from (e.g. [tags][id3v2][picture])
					if (empty($this->info[$unset_key][$tagtype]['picture'])) {
						unset($this->info[$unset_key][$tagtype]['picture']);
					}
					if (empty($this->info[$unset_key][$tagtype])) {
						unset($this->info[$unset_key][$tagtype]);
					}
					if (empty($this->info[$unset_key])) {
						unset($this->info[$unset_key]);
					}
				}
				// remove duplicate copy of picture data from (e.g. [id3v2][comments][picture])
				if (isset($this->info[$tagtype]['comments']['picture'])) {
					unset($this->info[$tagtype]['comments']['picture']);
				}
				if (empty($this->info[$tagtype]['comments'])) {
					unset($this->info[$tagtype]['comments']);
				}
				if (empty($this->info[$tagtype])) {
					unset($this->info[$tagtype]);
				}
			}
		}
		return true;
	}


	public function getHashdata($algorithm) {
		switch ($algorithm) {
			case 'md5':
			case 'sha1':
				break;

			default:
				return $this->error('bad algorithm "'.$algorithm.'" in getHashdata()');
				break;
		}

		if (!empty($this->info['fileformat']) && !empty($this->info['dataformat']) && ($this->info['fileformat'] == 'ogg') && ($this->info['audio']['dataformat'] == 'vorbis')) {

			// We cannot get an identical md5_data value for Ogg files where the comments
			// span more than 1 Ogg page (compared to the same audio data with smaller
			// comments) using the normal getID3() method of MD5'ing the data between the
			// end of the comments and the end of the file (minus any trailing tags),
			// because the page sequence numbers of the pages that the audio data is on
			// do not match. Under normal circumstances, where comments are smaller than
			// the nominal 4-8kB page size, then this is not a problem, but if there are
			// very large comments, the only way around it is to strip off the comment
			// tags with vorbiscomment and MD5 that file.
			// This procedure must be applied to ALL Ogg files, not just the ones with
			// comments larger than 1 page, because the below method simply MD5's the
			// whole file with the comments stripped, not just the portion after the
			// comments block (which is the standard getID3() method.

			// The above-mentioned problem of comments spanning multiple pages and changing
			// page sequence numbers likely happens for OggSpeex and OggFLAC as well, but
			// currently vorbiscomment only works on OggVorbis files.

			if (preg_match('#(1|ON)#i', ini_get('safe_mode'))) {

				$this->warning('Failed making system call to vorbiscomment.exe - '.$algorithm.'_data is incorrect - error returned: PHP running in Safe Mode (backtick operator not available)');
				$this->info[$algorithm.'_data'] = false;

			} else {

				// Prevent user from aborting script
				$old_abort = ignore_user_abort(true);

				// Create empty file
				$empty = tempnam(GETID3_TEMP_DIR, 'getID3');
				touch($empty);

				// Use vorbiscomment to make temp file without comments
				$temp = tempnam(GETID3_TEMP_DIR, 'getID3');
				$file = $this->info['filenamepath'];

				if (GETID3_OS_ISWINDOWS) {

					if (file_exists(GETID3_HELPERAPPSDIR.'vorbiscomment.exe')) {

						$commandline = '"'.GETID3_HELPERAPPSDIR.'vorbiscomment.exe" -w -c "'.$empty.'" "'.$file.'" "'.$temp.'"';
						$VorbisCommentError = `$commandline`;

					} else {

						$VorbisCommentError = 'vorbiscomment.exe not found in '.GETID3_HELPERAPPSDIR;

					}

				} else {

					$commandline = 'vorbiscomment -w -c "'.$empty.'" "'.$file.'" "'.$temp.'" 2>&1';
					$commandline = 'vorbiscomment -w -c '.escapeshellarg($empty).' '.escapeshellarg($file).' '.escapeshellarg($temp).' 2>&1';
					$VorbisCommentError = `$commandline`;

				}

				if (!empty($VorbisCommentError)) {

					$this->info['warning'][]         = 'Failed making system call to vorbiscomment(.exe) - '.$algorithm.'_data will be incorrect. If vorbiscomment is unavailable, please download from http://www.vorbis.com/download.psp and put in the getID3() directory. Error returned: '.$VorbisCommentError;
					$this->info[$algorithm.'_data']  = false;

				} else {

					// Get hash of newly created file
					switch ($algorithm) {
						case 'md5':
							$this->info[$algorithm.'_data'] = md5_file($temp);
							break;

						case 'sha1':
							$this->info[$algorithm.'_data'] = sha1_file($temp);
							break;
					}
				}

				// Clean up
				unlink($empty);
				unlink($temp);

				// Reset abort setting
				ignore_user_abort($old_abort);

			}

		} else {

			if (!empty($this->info['avdataoffset']) || (isset($this->info['avdataend']) && ($this->info['avdataend'] < $this->info['filesize']))) {

				// get hash from part of file
				$this->info[$algorithm.'_data'] = getid3_lib::hash_data($this->info['filenamepath'], $this->info['avdataoffset'], $this->info['avdataend'], $algorithm);

			} else {

				// get hash from whole file
				switch ($algorithm) {
					case 'md5':
						$this->info[$algorithm.'_data'] = md5_file($this->info['filenamepath']);
						break;

					case 'sha1':
						$this->info[$algorithm.'_data'] = sha1_file($this->info['filenamepath']);
						break;
				}
			}

		}
		return true;
	}


	public function ChannelsBitratePlaytimeCalculations() {

		// set channelmode on audio
		if (!empty($this->info['audio']['channelmode']) || !isset($this->info['audio']['channels'])) {
			// ignore
		} elseif ($this->info['audio']['channels'] == 1) {
			$this->info['audio']['channelmode'] = 'mono';
		} elseif ($this->info['audio']['channels'] == 2) {
			$this->info['audio']['channelmode'] = 'stereo';
		}

		// Calculate combined bitrate - audio + video
		$CombinedBitrate  = 0;
		$CombinedBitrate += (isset($this->info['audio']['bitrate']) ? $this->info['audio']['bitrate'] : 0);
		$CombinedBitrate += (isset($this->info['video']['bitrate']) ? $this->info['video']['bitrate'] : 0);
		if (($CombinedBitrate > 0) && empty($this->info['bitrate'])) {
			$this->info['bitrate'] = $CombinedBitrate;
		}
		//if ((isset($this->info['video']) && !isset($this->info['video']['bitrate'])) || (isset($this->info['audio']) && !isset($this->info['audio']['bitrate']))) {
		//	// for example, VBR MPEG video files cannot determine video bitrate:
		//	// should not set overall bitrate and playtime from audio bitrate only
		//	unset($this->info['bitrate']);
		//}

		// video bitrate undetermined, but calculable
		if (isset($this->info['video']['dataformat']) && $this->info['video']['dataformat'] && (!isset($this->info['video']['bitrate']) || ($this->info['video']['bitrate'] == 0))) {
			// if video bitrate not set
			if (isset($this->info['audio']['bitrate']) && ($this->info['audio']['bitrate'] > 0) && ($this->info['audio']['bitrate'] == $this->info['bitrate'])) {
				// AND if audio bitrate is set to same as overall bitrate
				if (isset($this->info['playtime_seconds']) && ($this->info['playtime_seconds'] > 0)) {
					// AND if playtime is set
					if (isset($this->info['avdataend']) && isset($this->info['avdataoffset'])) {
						// AND if AV data offset start/end is known
						// THEN we can calculate the video bitrate
						$this->info['bitrate'] = round((($this->info['avdataend'] - $this->info['avdataoffset']) * 8) / $this->info['playtime_seconds']);
						$this->info['video']['bitrate'] = $this->info['bitrate'] - $this->info['audio']['bitrate'];
					}
				}
			}
		}

		if ((!isset($this->info['playtime_seconds']) || ($this->info['playtime_seconds'] <= 0)) && !empty($this->info['bitrate'])) {
			$this->info['playtime_seconds'] = (($this->info['avdataend'] - $this->info['avdataoffset']) * 8) / $this->info['bitrate'];
		}

		if (!isset($this->info['bitrate']) && !empty($this->info['playtime_seconds'])) {
			$this->info['bitrate'] = (($this->info['avdataend'] - $this->info['avdataoffset']) * 8) / $this->info['playtime_seconds'];
		}
		if (isset($this->info['bitrate']) && empty($this->info['audio']['bitrate']) && empty($this->info['video']['bitrate'])) {
			if (isset($this->info['audio']['dataformat']) && empty($this->info['video']['resolution_x'])) {
				// audio only
				$this->info['audio']['bitrate'] = $this->info['bitrate'];
			} elseif (isset($this->info['video']['resolution_x']) && empty($this->info['audio']['dataformat'])) {
				// video only
				$this->info['video']['bitrate'] = $this->info['bitrate'];
			}
		}

		// Set playtime string
		if (!empty($this->info['playtime_seconds']) && empty($this->info['playtime_string'])) {
			$this->info['playtime_string'] = getid3_lib::PlaytimeString($this->info['playtime_seconds']);
		}
	}


	public function CalculateCompressionRatioVideo() {
		if (empty($this->info['video'])) {
			return false;
		}
		if (empty($this->info['video']['resolution_x']) || empty($this->info['video']['resolution_y'])) {
			return false;
		}
		if (empty($this->info['video']['bits_per_sample'])) {
			return false;
		}

		switch ($this->info['video']['dataformat']) {
			case 'bmp':
			case 'gif':
			case 'jpeg':
			case 'jpg':
			case 'png':
			case 'tiff':
				$FrameRate = 1;
				$PlaytimeSeconds = 1;
				$BitrateCompressed = $this->info['filesize'] * 8;
				break;

			default:
				if (!empty($this->info['video']['frame_rate'])) {
					$FrameRate = $this->info['video']['frame_rate'];
				} else {
					return false;
				}
				if (!empty($this->info['playtime_seconds'])) {
					$PlaytimeSeconds = $this->info['playtime_seconds'];
				} else {
					return false;
				}
				if (!empty($this->info['video']['bitrate'])) {
					$BitrateCompressed = $this->info['video']['bitrate'];
				} else {
					return false;
				}
				break;
		}
		$BitrateUncompressed = $this->info['video']['resolution_x'] * $this->info['video']['resolution_y'] * $this->info['video']['bits_per_sample'] * $FrameRate;

		$this->info['video']['compression_ratio'] = $BitrateCompressed / $BitrateUncompressed;
		return true;
	}


	public function CalculateCompressionRatioAudio() {
		if (empty($this->info['audio']['bitrate']) || empty($this->info['audio']['channels']) || empty($this->info['audio']['sample_rate']) || !is_numeric($this->info['audio']['sample_rate'])) {
			return false;
		}
		$this->info['audio']['compression_ratio'] = $this->info['audio']['bitrate'] / ($this->info['audio']['channels'] * $this->info['audio']['sample_rate'] * (!empty($this->info['audio']['bits_per_sample']) ? $this->info['audio']['bits_per_sample'] : 16));

		if (!empty($this->info['audio']['streams'])) {
			foreach ($this->info['audio']['streams'] as $streamnumber => $streamdata) {
				if (!empty($streamdata['bitrate']) && !empty($streamdata['channels']) && !empty($streamdata['sample_rate'])) {
					$this->info['audio']['streams'][$streamnumber]['compression_ratio'] = $streamdata['bitrate'] / ($streamdata['channels'] * $streamdata['sample_rate'] * (!empty($streamdata['bits_per_sample']) ? $streamdata['bits_per_sample'] : 16));
				}
			}
		}
		return true;
	}


	public function CalculateReplayGain() {
		if (isset($this->info['replay_gain'])) {
			if (!isset($this->info['replay_gain']['reference_volume'])) {
				$this->info['replay_gain']['reference_volume'] = (double) 89.0;
			}
			if (isset($this->info['replay_gain']['track']['adjustment'])) {
				$this->info['replay_gain']['track']['volume'] = $this->info['replay_gain']['reference_volume'] - $this->info['replay_gain']['track']['adjustment'];
			}
			if (isset($this->info['replay_gain']['album']['adjustment'])) {
				$this->info['replay_gain']['album']['volume'] = $this->info['replay_gain']['reference_volume'] - $this->info['replay_gain']['album']['adjustment'];
			}

			if (isset($this->info['replay_gain']['track']['peak'])) {
				$this->info['replay_gain']['track']['max_noclip_gain'] = 0 - getid3_lib::RGADamplitude2dB($this->info['replay_gain']['track']['peak']);
			}
			if (isset($this->info['replay_gain']['album']['peak'])) {
				$this->info['replay_gain']['album']['max_noclip_gain'] = 0 - getid3_lib::RGADamplitude2dB($this->info['replay_gain']['album']['peak']);
			}
		}
		return true;
	}

	public function ProcessAudioStreams() {
		if (!empty($this->info['audio']['bitrate']) || !empty($this->info['audio']['channels']) || !empty($this->info['audio']['sample_rate'])) {
			if (!isset($this->info['audio']['streams'])) {
				foreach ($this->info['audio'] as $key => $value) {
					if ($key != 'streams') {
						$this->info['audio']['streams'][0][$key] = $value;
					}
				}
			}
		}
		return true;
	}

	public function getid3_tempnam() {
		return tempnam($this->tempdir, 'gI3');
	}

	public function include_module($name) {
		//if (!file_exists($this->include_path.'module.'.$name.'.php')) {
		if (!file_exists(GETID3_INCLUDEPATH.'module.'.$name.'.php')) {
			if ($name == "tag.id3v1") return true;
			if ($name == "tag.id3v2") return true;
			throw new getid3_exception('Required module.'.$name.'.php is missing.');
		}
		include_once(GETID3_INCLUDEPATH.'module.'.$name.'.php');
		return true;
	}

}


abstract class getid3_handler
{
	protected $getid3;                       // pointer

	protected $data_string_flag     = false; // analyzing filepointer or string
	protected $data_string          = '';    // string to analyze
	protected $data_string_position = 0;     // seek position in string
	protected $data_string_length   = 0;     // string length

	private $dependency_to = null;


	public function __construct(getID3 $getid3, $call_module=null) {
		$this->getid3 = $getid3;

		if ($call_module) {
			$this->dependency_to = str_replace('getid3_', '', $call_module);
		}
	}


	// Analyze from file pointer
	abstract public function Analyze();


	// Analyze from string instead
	public function AnalyzeString($string) {
		// Enter string mode
	    $this->setStringMode($string);

		// Save info
		$saved_avdataoffset = $this->getid3->info['avdataoffset'];
		$saved_avdataend    = $this->getid3->info['avdataend'];
		$saved_filesize     = (isset($this->getid3->info['filesize']) ? $this->getid3->info['filesize'] : null); // may be not set if called as dependency without openfile() call

		// Reset some info
		$this->getid3->info['avdataoffset'] = 0;
		$this->getid3->info['avdataend']    = $this->getid3->info['filesize'] = $this->data_string_length;

		// Analyze
		$this->Analyze();

		// Restore some info
		$this->getid3->info['avdataoffset'] = $saved_avdataoffset;
		$this->getid3->info['avdataend']    = $saved_avdataend;
		$this->getid3->info['filesize']     = $saved_filesize;

		// Exit string mode
		$this->data_string_flag = false;
	}

	public function setStringMode($string) {
		$this->data_string_flag   = true;
		$this->data_string        = $string;
		$this->data_string_length = strlen($string);
	}

	protected function ftell() {
		if ($this->data_string_flag) {
			return $this->data_string_position;
		}
		return ftell($this->getid3->fp);
	}

	protected function fread($bytes) {
		if ($this->data_string_flag) {
			$this->data_string_position += $bytes;
			return substr($this->data_string, $this->data_string_position - $bytes, $bytes);
		}
	    $pos = $this->ftell() + $bytes;
	    if (!getid3_lib::intValueSupported($pos)) {
			throw new getid3_exception('cannot fread('.$bytes.' from '.$this->ftell().') because beyond PHP filesystem limit', 10);
	    }
		return fread($this->getid3->fp, $bytes);
	}

	protected function fseek($bytes, $whence=SEEK_SET) {
		if ($this->data_string_flag) {
			switch ($whence) {
				case SEEK_SET:
					$this->data_string_position = $bytes;
					break;

				case SEEK_CUR:
					$this->data_string_position += $bytes;
					break;

				case SEEK_END:
					$this->data_string_position = $this->data_string_length + $bytes;
					break;
			}
			return 0;
	    } else {
	    	$pos = $bytes;
	    	if ($whence == SEEK_CUR) {
				$pos = $this->ftell() + $bytes;
	    	} elseif ($whence == SEEK_END) {
				$pos = $this->info['filesize'] + $bytes;
	    	}
	    	if (!getid3_lib::intValueSupported($pos)) {
				throw new getid3_exception('cannot fseek('.$pos.') because beyond PHP filesystem limit', 10);
			}
		}
		return fseek($this->getid3->fp, $bytes, $whence);
	}

	protected function feof() {
		if ($this->data_string_flag) {
			return $this->data_string_position >= $this->data_string_length;
		}
		return feof($this->getid3->fp);
	}

	final protected function isDependencyFor($module) {
		return $this->dependency_to == $module;
	}

	protected function error($text)
	{
		$this->getid3->info['error'][] = $text;

		return false;
	}

	protected function warning($text)
	{
		return $this->getid3->warning($text);
	}

	protected function notice($text)
	{
		// does nothing for now
	}

	public function saveAttachment($name, $offset, $length, $image_mime=null) {
		try {

			// do not extract at all
			if ($this->getid3->option_save_attachments === getID3::ATTACHMENTS_NONE) {

				$attachment = null; // do not set any

			// extract to return array
			} elseif ($this->getid3->option_save_attachments === getID3::ATTACHMENTS_INLINE) {

				$this->fseek($offset);
				$attachment = $this->fread($length); // get whole data in one pass, till it is anyway stored in memory
				if ($attachment === false || strlen($attachment) != $length) {
					throw new Exception('failed to read attachment data');
				}

			// assume directory path is given
			} else {

				// set up destination path
				$dir = rtrim(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $this->getid3->option_save_attachments), DIRECTORY_SEPARATOR);
				if (!is_dir($dir) || !is_writable($dir)) { // check supplied directory
					throw new Exception('supplied path ('.$dir.') does not exist, or is not writable');
				}
				$dest = $dir.DIRECTORY_SEPARATOR.$name.($image_mime ? '.'.getid3_lib::ImageExtFromMime($image_mime) : '');

				// create dest file
				if (($fp_dest = fopen($dest, 'wb')) == false) {
					throw new Exception('failed to create file '.$dest);
				}

				// copy data
				$this->fseek($offset);
				$buffersize = ($this->data_string_flag ? $length : $this->getid3->fread_buffer_size());
				$bytesleft = $length;
				while ($bytesleft > 0) {
					if (($buffer = $this->fread(min($buffersize, $bytesleft))) === false || ($byteswritten = fwrite($fp_dest, $buffer)) === false || ($byteswritten === 0)) {
						throw new Exception($buffer === false ? 'not enough data to read' : 'failed to write to destination file, may be not enough disk space');
					}
					$bytesleft -= $byteswritten;
				}

				fclose($fp_dest);
				$attachment = $dest;

			}

		} catch (Exception $e) {

			// close and remove dest file if created
			if (isset($fp_dest) && is_resource($fp_dest)) {
				fclose($fp_dest);
				unlink($dest);
			}

			// do not set any is case of error
			$attachment = null;
			$this->warning('Failed to extract attachment '.$name.': '.$e->getMessage());

		}

		// seek to the end of attachment
		$this->fseek($offset + $length);

		return $attachment;
	}

}


class getid3_exception extends Exception
{
	public $message;
}

/////// BEGINGETID3LIB ////////////////


class getid3_lib
{

	public static function PrintHexBytes($string, $hex=true, $spaces=true, $htmlencoding='UTF-8') {
		$returnstring = '';
		for ($i = 0; $i < strlen($string); $i++) {
			if ($hex) {
				$returnstring .= str_pad(dechex(ord($string{$i})), 2, '0', STR_PAD_LEFT);
			} else {
				$returnstring .= ' '.(preg_match("#[\x20-\x7E]#", $string{$i}) ? $string{$i} : '¤');
			}
			if ($spaces) {
				$returnstring .= ' ';
			}
		}
		if (!empty($htmlencoding)) {
			if ($htmlencoding === true) {
				$htmlencoding = 'UTF-8'; // prior to getID3 v1.9.0 the function's 4th parameter was boolean
			}
			$returnstring = htmlentities($returnstring, ENT_QUOTES, $htmlencoding);
		}
		return $returnstring;
	}

	public static function trunc($floatnumber) {
		// truncates a floating-point number at the decimal point
		// returns int (if possible, otherwise float)
		if ($floatnumber >= 1) {
			$truncatednumber = floor($floatnumber);
		} elseif ($floatnumber <= -1) {
			$truncatednumber = ceil($floatnumber);
		} else {
			$truncatednumber = 0;
		}
		if (self::intValueSupported($truncatednumber)) {
			$truncatednumber = (int) $truncatednumber;
		}
		return $truncatednumber;
	}


	public static function safe_inc(&$variable, $increment=1) {
		if (isset($variable)) {
			$variable += $increment;
		} else {
			$variable = $increment;
		}
		return true;
	}

	public static function CastAsInt($floatnum) {
		// convert to float if not already
		$floatnum = (float) $floatnum;

		// convert a float to type int, only if possible
		if (self::trunc($floatnum) == $floatnum) {
			// it's not floating point
			if (self::intValueSupported($floatnum)) {
				// it's within int range
				$floatnum = (int) $floatnum;
			}
		}
		return $floatnum;
	}

	public static function intValueSupported($num) {
		// check if integers are 64-bit
		static $hasINT64 = null;
		if ($hasINT64 === null) { // 10x faster than is_null()
			$hasINT64 = is_int(pow(2, 31)); // 32-bit int are limited to (2^31)-1
			if (!$hasINT64 && !defined('PHP_INT_MIN')) {
				define('PHP_INT_MIN', ~PHP_INT_MAX);
			}
		}
		// if integers are 64-bit - no other check required
		if ($hasINT64 || (($num <= PHP_INT_MAX) && ($num >= PHP_INT_MIN))) {
			return true;
		}
		return false;
	}

	public static function DecimalizeFraction($fraction) {
		list($numerator, $denominator) = explode('/', $fraction);
		return $numerator / ($denominator ? $denominator : 1);
	}


	public static function DecimalBinary2Float($binarynumerator) {
		$numerator   = self::Bin2Dec($binarynumerator);
		$denominator = self::Bin2Dec('1'.str_repeat('0', strlen($binarynumerator)));
		return ($numerator / $denominator);
	}


	public static function NormalizeBinaryPoint($binarypointnumber, $maxbits=52) {
		// http://www.scri.fsu.edu/~jac/MAD3401/Backgrnd/binary.html
		if (strpos($binarypointnumber, '.') === false) {
			$binarypointnumber = '0.'.$binarypointnumber;
		} elseif ($binarypointnumber{0} == '.') {
			$binarypointnumber = '0'.$binarypointnumber;
		}
		$exponent = 0;
		while (($binarypointnumber{0} != '1') || (substr($binarypointnumber, 1, 1) != '.')) {
			if (substr($binarypointnumber, 1, 1) == '.') {
				$exponent--;
				$binarypointnumber = substr($binarypointnumber, 2, 1).'.'.substr($binarypointnumber, 3);
			} else {
				$pointpos = strpos($binarypointnumber, '.');
				$exponent += ($pointpos - 1);
				$binarypointnumber = str_replace('.', '', $binarypointnumber);
				$binarypointnumber = $binarypointnumber{0}.'.'.substr($binarypointnumber, 1);
			}
		}
		$binarypointnumber = str_pad(substr($binarypointnumber, 0, $maxbits + 2), $maxbits + 2, '0', STR_PAD_RIGHT);
		return array('normalized'=>$binarypointnumber, 'exponent'=>(int) $exponent);
	}


	public static function Float2BinaryDecimal($floatvalue) {
		// http://www.scri.fsu.edu/~jac/MAD3401/Backgrnd/binary.html
		$maxbits = 128; // to how many bits of precision should the calculations be taken?
		$intpart   = self::trunc($floatvalue);
		$floatpart = abs($floatvalue - $intpart);
		$pointbitstring = '';
		while (($floatpart != 0) && (strlen($pointbitstring) < $maxbits)) {
			$floatpart *= 2;
			$pointbitstring .= (string) self::trunc($floatpart);
			$floatpart -= self::trunc($floatpart);
		}
		$binarypointnumber = decbin($intpart).'.'.$pointbitstring;
		return $binarypointnumber;
	}


	public static function Float2String($floatvalue, $bits) {
		// http://www.scri.fsu.edu/~jac/MAD3401/Backgrnd/ieee-expl.html
		switch ($bits) {
			case 32:
				$exponentbits = 8;
				$fractionbits = 23;
				break;

			case 64:
				$exponentbits = 11;
				$fractionbits = 52;
				break;

			default:
				return false;
				break;
		}
		if ($floatvalue >= 0) {
			$signbit = '0';
		} else {
			$signbit = '1';
		}
		$normalizedbinary  = self::NormalizeBinaryPoint(self::Float2BinaryDecimal($floatvalue), $fractionbits);
		$biasedexponent    = pow(2, $exponentbits - 1) - 1 + $normalizedbinary['exponent']; // (127 or 1023) +/- exponent
		$exponentbitstring = str_pad(decbin($biasedexponent), $exponentbits, '0', STR_PAD_LEFT);
		$fractionbitstring = str_pad(substr($normalizedbinary['normalized'], 2), $fractionbits, '0', STR_PAD_RIGHT);

		return self::BigEndian2String(self::Bin2Dec($signbit.$exponentbitstring.$fractionbitstring), $bits % 8, false);
	}


	public static function LittleEndian2Float($byteword) {
		return self::BigEndian2Float(strrev($byteword));
	}


	public static function BigEndian2Float($byteword) {
		// ANSI/IEEE Standard 754-1985, Standard for Binary Floating Point Arithmetic
		// http://www.psc.edu/general/software/packages/ieee/ieee.html
		// http://www.scri.fsu.edu/~jac/MAD3401/Backgrnd/ieee.html

		$bitword = self::BigEndian2Bin($byteword);
		if (!$bitword) {
			return 0;
		}
		$signbit = $bitword{0};

		switch (strlen($byteword) * 8) {
			case 32:
				$exponentbits = 8;
				$fractionbits = 23;
				break;

			case 64:
				$exponentbits = 11;
				$fractionbits = 52;
				break;

			case 80:
				// 80-bit Apple SANE format
				// http://www.mactech.com/articles/mactech/Vol.06/06.01/SANENormalized/
				$exponentstring = substr($bitword, 1, 15);
				$isnormalized = intval($bitword{16});
				$fractionstring = substr($bitword, 17, 63);
				$exponent = pow(2, self::Bin2Dec($exponentstring) - 16383);
				$fraction = $isnormalized + self::DecimalBinary2Float($fractionstring);
				$floatvalue = $exponent * $fraction;
				if ($signbit == '1') {
					$floatvalue *= -1;
				}
				return $floatvalue;
				break;

			default:
				return false;
				break;
		}
		$exponentstring = substr($bitword, 1, $exponentbits);
		$fractionstring = substr($bitword, $exponentbits + 1, $fractionbits);
		$exponent = self::Bin2Dec($exponentstring);
		$fraction = self::Bin2Dec($fractionstring);

		if (($exponent == (pow(2, $exponentbits) - 1)) && ($fraction != 0)) {
			// Not a Number
			$floatvalue = false;
		} elseif (($exponent == (pow(2, $exponentbits) - 1)) && ($fraction == 0)) {
			if ($signbit == '1') {
				$floatvalue = '-infinity';
			} else {
				$floatvalue = '+infinity';
			}
		} elseif (($exponent == 0) && ($fraction == 0)) {
			if ($signbit == '1') {
				$floatvalue = -0;
			} else {
				$floatvalue = 0;
			}
			$floatvalue = ($signbit ? 0 : -0);
		} elseif (($exponent == 0) && ($fraction != 0)) {
			// These are 'unnormalized' values
			$floatvalue = pow(2, (-1 * (pow(2, $exponentbits - 1) - 2))) * self::DecimalBinary2Float($fractionstring);
			if ($signbit == '1') {
				$floatvalue *= -1;
			}
		} elseif ($exponent != 0) {
			$floatvalue = pow(2, ($exponent - (pow(2, $exponentbits - 1) - 1))) * (1 + self::DecimalBinary2Float($fractionstring));
			if ($signbit == '1') {
				$floatvalue *= -1;
			}
		}
		return (float) $floatvalue;
	}


	public static function BigEndian2Int($byteword, $synchsafe=false, $signed=false) {
		$intvalue = 0;
		$bytewordlen = strlen($byteword);
		if ($bytewordlen == 0) {
			return false;
		}
		for ($i = 0; $i < $bytewordlen; $i++) {
			if ($synchsafe) { // disregard MSB, effectively 7-bit bytes
				//$intvalue = $intvalue | (ord($byteword{$i}) & 0x7F) << (($bytewordlen - 1 - $i) * 7); // faster, but runs into problems past 2^31 on 32-bit systems
				$intvalue += (ord($byteword{$i}) & 0x7F) * pow(2, ($bytewordlen - 1 - $i) * 7);
			} else {
				$intvalue += ord($byteword{$i}) * pow(256, ($bytewordlen - 1 - $i));
			}
		}
		if ($signed && !$synchsafe) {
			// synchsafe ints are not allowed to be signed
			if ($bytewordlen <= PHP_INT_SIZE) {
				$signMaskBit = 0x80 << (8 * ($bytewordlen - 1));
				if ($intvalue & $signMaskBit) {
					$intvalue = 0 - ($intvalue & ($signMaskBit - 1));
				}
			} else {
				throw new Exception('ERROR: Cannot have signed integers larger than '.(8 * PHP_INT_SIZE).'-bits ('.strlen($byteword).') in self::BigEndian2Int()');
				break;
			}
		}
		return self::CastAsInt($intvalue);
	}


	public static function LittleEndian2Int($byteword, $signed=false) {
		return self::BigEndian2Int(strrev($byteword), false, $signed);
	}


	public static function BigEndian2Bin($byteword) {
		$binvalue = '';
		$bytewordlen = strlen($byteword);
		for ($i = 0; $i < $bytewordlen; $i++) {
			$binvalue .= str_pad(decbin(ord($byteword{$i})), 8, '0', STR_PAD_LEFT);
		}
		return $binvalue;
	}


	public static function BigEndian2String($number, $minbytes=1, $synchsafe=false, $signed=false) {
		if ($number < 0) {
			throw new Exception('ERROR: self::BigEndian2String() does not support negative numbers');
		}
		$maskbyte = (($synchsafe || $signed) ? 0x7F : 0xFF);
		$intstring = '';
		if ($signed) {
			if ($minbytes > PHP_INT_SIZE) {
				throw new Exception('ERROR: Cannot have signed integers larger than '.(8 * PHP_INT_SIZE).'-bits in self::BigEndian2String()');
			}
			$number = $number & (0x80 << (8 * ($minbytes - 1)));
		}
		while ($number != 0) {
			$quotient = ($number / ($maskbyte + 1));
			$intstring = chr(ceil(($quotient - floor($quotient)) * $maskbyte)).$intstring;
			$number = floor($quotient);
		}
		return str_pad($intstring, $minbytes, "\x00", STR_PAD_LEFT);
	}


	public static function Dec2Bin($number) {
		while ($number >= 256) {
			$bytes[] = (($number / 256) - (floor($number / 256))) * 256;
			$number = floor($number / 256);
		}
		$bytes[] = $number;
		$binstring = '';
		for ($i = 0; $i < count($bytes); $i++) {
			$binstring = (($i == count($bytes) - 1) ? decbin($bytes[$i]) : str_pad(decbin($bytes[$i]), 8, '0', STR_PAD_LEFT)).$binstring;
		}
		return $binstring;
	}


	public static function Bin2Dec($binstring, $signed=false) {
		$signmult = 1;
		if ($signed) {
			if ($binstring{0} == '1') {
				$signmult = -1;
			}
			$binstring = substr($binstring, 1);
		}
		$decvalue = 0;
		for ($i = 0; $i < strlen($binstring); $i++) {
			$decvalue += ((int) substr($binstring, strlen($binstring) - $i - 1, 1)) * pow(2, $i);
		}
		return self::CastAsInt($decvalue * $signmult);
	}


	public static function Bin2String($binstring) {
		// return 'hi' for input of '0110100001101001'
		$string = '';
		$binstringreversed = strrev($binstring);
		for ($i = 0; $i < strlen($binstringreversed); $i += 8) {
			$string = chr(self::Bin2Dec(strrev(substr($binstringreversed, $i, 8)))).$string;
		}
		return $string;
	}


	public static function LittleEndian2String($number, $minbytes=1, $synchsafe=false) {
		$intstring = '';
		while ($number > 0) {
			if ($synchsafe) {
				$intstring = $intstring.chr($number & 127);
				$number >>= 7;
			} else {
				$intstring = $intstring.chr($number & 255);
				$number >>= 8;
			}
		}
		return str_pad($intstring, $minbytes, "\x00", STR_PAD_RIGHT);
	}


	public static function array_merge_clobber($array1, $array2) {
		// written by kcØhireability*com
		// taken from http://www.php.net/manual/en/function.array-merge-recursive.php
		if (!is_array($array1) || !is_array($array2)) {
			return false;
		}
		$newarray = $array1;
		foreach ($array2 as $key => $val) {
			if (is_array($val) && isset($newarray[$key]) && is_array($newarray[$key])) {
				$newarray[$key] = self::array_merge_clobber($newarray[$key], $val);
			} else {
				$newarray[$key] = $val;
			}
		}
		return $newarray;
	}


	public static function array_merge_noclobber($array1, $array2) {
		if (!is_array($array1) || !is_array($array2)) {
			return false;
		}
		$newarray = $array1;
		foreach ($array2 as $key => $val) {
			if (is_array($val) && isset($newarray[$key]) && is_array($newarray[$key])) {
				$newarray[$key] = self::array_merge_noclobber($newarray[$key], $val);
			} elseif (!isset($newarray[$key])) {
				$newarray[$key] = $val;
			}
		}
		return $newarray;
	}


	public static function ksort_recursive(&$theArray) {
		ksort($theArray);
		foreach ($theArray as $key => $value) {
			if (is_array($value)) {
				self::ksort_recursive($theArray[$key]);
			}
		}
		return true;
	}

	public static function fileextension($filename, $numextensions=1) {
		if (strstr($filename, '.')) {
			$reversedfilename = strrev($filename);
			$offset = 0;
			for ($i = 0; $i < $numextensions; $i++) {
				$offset = strpos($reversedfilename, '.', $offset + 1);
				if ($offset === false) {
					return '';
				}
			}
			return strrev(substr($reversedfilename, 0, $offset));
		}
		return '';
	}


	public static function PlaytimeString($seconds) {
		$sign = (($seconds < 0) ? '-' : '');
		$seconds = round(abs($seconds));
		$H = (int) floor( $seconds                            / 3600);
		$M = (int) floor(($seconds - (3600 * $H)            ) /   60);
		$S = (int) round( $seconds - (3600 * $H) - (60 * $M)        );
		return $sign.($H ? $H.':' : '').($H ? str_pad($M, 2, '0', STR_PAD_LEFT) : intval($M)).':'.str_pad($S, 2, 0, STR_PAD_LEFT);
	}


	public static function DateMac2Unix($macdate) {
		// Macintosh timestamp: seconds since 00:00h January 1, 1904
		// UNIX timestamp:      seconds since 00:00h January 1, 1970
		return self::CastAsInt($macdate - 2082844800);
	}


	public static function FixedPoint8_8($rawdata) {
		return self::BigEndian2Int(substr($rawdata, 0, 1)) + (float) (self::BigEndian2Int(substr($rawdata, 1, 1)) / pow(2, 8));
	}


	public static function FixedPoint16_16($rawdata) {
		return self::BigEndian2Int(substr($rawdata, 0, 2)) + (float) (self::BigEndian2Int(substr($rawdata, 2, 2)) / pow(2, 16));
	}


	public static function FixedPoint2_30($rawdata) {
		$binarystring = self::BigEndian2Bin($rawdata);
		return self::Bin2Dec(substr($binarystring, 0, 2)) + (float) (self::Bin2Dec(substr($binarystring, 2, 30)) / pow(2, 30));
	}


	public static function CreateDeepArray($ArrayPath, $Separator, $Value) {
		// assigns $Value to a nested array path:
		//   $foo = self::CreateDeepArray('/path/to/my', '/', 'file.txt')
		// is the same as:
		//   $foo = array('path'=>array('to'=>'array('my'=>array('file.txt'))));
		// or
		//   $foo['path']['to']['my'] = 'file.txt';
		$ArrayPath = ltrim($ArrayPath, $Separator);
		if (($pos = strpos($ArrayPath, $Separator)) !== false) {
			$ReturnedArray[substr($ArrayPath, 0, $pos)] = self::CreateDeepArray(substr($ArrayPath, $pos + 1), $Separator, $Value);
		} else {
			$ReturnedArray[$ArrayPath] = $Value;
		}
		return $ReturnedArray;
	}

	public static function array_max($arraydata, $returnkey=false) {
		$maxvalue = false;
		$maxkey = false;
		foreach ($arraydata as $key => $value) {
			if (!is_array($value)) {
				if ($value > $maxvalue) {
					$maxvalue = $value;
					$maxkey = $key;
				}
			}
		}
		return ($returnkey ? $maxkey : $maxvalue);
	}

	public static function array_min($arraydata, $returnkey=false) {
		$minvalue = false;
		$minkey = false;
		foreach ($arraydata as $key => $value) {
			if (!is_array($value)) {
				if ($value > $minvalue) {
					$minvalue = $value;
					$minkey = $key;
				}
			}
		}
		return ($returnkey ? $minkey : $minvalue);
	}

	public static function XML2array($XMLstring) {
		if (function_exists('simplexml_load_string')) {
			if (function_exists('get_object_vars')) {
				$XMLobject = simplexml_load_string($XMLstring);
				return self::SimpleXMLelement2array($XMLobject);
			}
		}
		return false;
	}

	public static function SimpleXMLelement2array($XMLobject) {
		if (!is_object($XMLobject) && !is_array($XMLobject)) {
			return $XMLobject;
		}
		$XMLarray = (is_object($XMLobject) ? get_object_vars($XMLobject) : $XMLobject);
		foreach ($XMLarray as $key => $value) {
			$XMLarray[$key] = self::SimpleXMLelement2array($value);
		}
		return $XMLarray;
	}


	// Allan Hansen <ahØartemis*dk>
	// self::md5_data() - returns md5sum for a file from startuing position to absolute end position
	public static function hash_data($file, $offset, $end, $algorithm) {
		static $tempdir = '';
		if (!self::intValueSupported($end)) {
			return false;
		}
		switch ($algorithm) {
			case 'md5':
				$hash_function = 'md5_file';
				$unix_call     = 'md5sum';
				$windows_call  = 'md5sum.exe';
				$hash_length   = 32;
				break;

			case 'sha1':
				$hash_function = 'sha1_file';
				$unix_call     = 'sha1sum';
				$windows_call  = 'sha1sum.exe';
				$hash_length   = 40;
				break;

			default:
				throw new Exception('Invalid algorithm ('.$algorithm.') in self::hash_data()');
				break;
		}
		$size = $end - $offset;
		while (true) {
			if (GETID3_OS_ISWINDOWS) {

				// It seems that sha1sum.exe for Windows only works on physical files, does not accept piped data
				// Fall back to create-temp-file method:
				if ($algorithm == 'sha1') {
					break;
				}

				$RequiredFiles = array('cygwin1.dll', 'head.exe', 'tail.exe', $windows_call);
				foreach ($RequiredFiles as $required_file) {
					if (!is_readable(GETID3_HELPERAPPSDIR.$required_file)) {
						// helper apps not available - fall back to old method
						break 2;
					}
				}
				$commandline  = GETID3_HELPERAPPSDIR.'head.exe -c '.$end.' '.escapeshellarg(str_replace('/', DIRECTORY_SEPARATOR, $file)).' | ';
				$commandline .= GETID3_HELPERAPPSDIR.'tail.exe -c '.$size.' | ';
				$commandline .= GETID3_HELPERAPPSDIR.$windows_call;

			} else {

				$commandline  = 'head -c'.$end.' '.escapeshellarg($file).' | ';
				$commandline .= 'tail -c'.$size.' | ';
				$commandline .= $unix_call;

			}
			if (preg_match('#(1|ON)#i', ini_get('safe_mode'))) {
				//throw new Exception('PHP running in Safe Mode - backtick operator not available, using slower non-system-call '.$algorithm.' algorithm');
				break;
			}
			return substr(`$commandline`, 0, $hash_length);
		}

		if (empty($tempdir)) {
			// yes this is ugly, feel free to suggest a better way
			require_once(dirname(__FILE__).'/getid3.php');
			$getid3_temp = new getID3();
			$tempdir = $getid3_temp->tempdir;
			unset($getid3_temp);
		}
		// try to create a temporary file in the system temp directory - invalid dirname should force to system temp dir
		if (($data_filename = tempnam($tempdir, 'gI3')) === false) {
			// can't find anywhere to create a temp file, just fail
			return false;
		}

		// Init
		$result = false;

		// copy parts of file
		try {
			self::CopyFileParts($file, $data_filename, $offset, $end - $offset);
			$result = $hash_function($data_filename);
		} catch (Exception $e) {
			throw new Exception('self::CopyFileParts() failed in getid_lib::hash_data(): '.$e->getMessage());
		}
		unlink($data_filename);
		return $result;
	}

	public static function CopyFileParts($filename_source, $filename_dest, $offset, $length) {
		if (!self::intValueSupported($offset + $length)) {
			throw new Exception('cannot copy file portion, it extends beyond the '.round(PHP_INT_MAX / 1073741824).'GB limit');
		}
		if (is_readable($filename_source) && is_file($filename_source) && ($fp_src = fopen($filename_source, 'rb'))) {
			if (($fp_dest = fopen($filename_dest, 'wb'))) {
				if (fseek($fp_src, $offset, SEEK_SET) == 0) {
					$byteslefttowrite = $length;
					while (($byteslefttowrite > 0) && ($buffer = fread($fp_src, min($byteslefttowrite, getID3::FREAD_BUFFER_SIZE)))) {
						$byteswritten = fwrite($fp_dest, $buffer, $byteslefttowrite);
						$byteslefttowrite -= $byteswritten;
					}
					return true;
				} else {
					throw new Exception('failed to seek to offset '.$offset.' in '.$filename_source);
				}
				fclose($fp_dest);
			} else {
				throw new Exception('failed to create file for writing '.$filename_dest);
			}
			fclose($fp_src);
		} else {
			throw new Exception('failed to open file for reading '.$filename_source);
		}
		return false;
	}

	public static function iconv_fallback_int_utf8($charval) {
		if ($charval < 128) {
			// 0bbbbbbb
			$newcharstring = chr($charval);
		} elseif ($charval < 2048) {
			// 110bbbbb 10bbbbbb
			$newcharstring  = chr(($charval >>   6) | 0xC0);
			$newcharstring .= chr(($charval & 0x3F) | 0x80);
		} elseif ($charval < 65536) {
			// 1110bbbb 10bbbbbb 10bbbbbb
			$newcharstring  = chr(($charval >>  12) | 0xE0);
			$newcharstring .= chr(($charval >>   6) | 0xC0);
			$newcharstring .= chr(($charval & 0x3F) | 0x80);
		} else {
			// 11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
			$newcharstring  = chr(($charval >>  18) | 0xF0);
			$newcharstring .= chr(($charval >>  12) | 0xC0);
			$newcharstring .= chr(($charval >>   6) | 0xC0);
			$newcharstring .= chr(($charval & 0x3F) | 0x80);
		}
		return $newcharstring;
	}

	// ISO-8859-1 => UTF-8
	public static function iconv_fallback_iso88591_utf8($string, $bom=false) {
		if (function_exists('utf8_encode')) {
			return utf8_encode($string);
		}
		// utf8_encode() unavailable, use getID3()'s iconv_fallback() conversions (possibly PHP is compiled without XML support)
		$newcharstring = '';
		if ($bom) {
			$newcharstring .= "\xEF\xBB\xBF";
		}
		for ($i = 0; $i < strlen($string); $i++) {
			$charval = ord($string{$i});
			$newcharstring .= self::iconv_fallback_int_utf8($charval);
		}
		return $newcharstring;
	}

	// ISO-8859-1 => UTF-16BE
	public static function iconv_fallback_iso88591_utf16be($string, $bom=false) {
		$newcharstring = '';
		if ($bom) {
			$newcharstring .= "\xFE\xFF";
		}
		for ($i = 0; $i < strlen($string); $i++) {
			$newcharstring .= "\x00".$string{$i};
		}
		return $newcharstring;
	}

	// ISO-8859-1 => UTF-16LE
	public static function iconv_fallback_iso88591_utf16le($string, $bom=false) {
		$newcharstring = '';
		if ($bom) {
			$newcharstring .= "\xFF\xFE";
		}
		for ($i = 0; $i < strlen($string); $i++) {
			$newcharstring .= $string{$i}."\x00";
		}
		return $newcharstring;
	}

	// ISO-8859-1 => UTF-16LE (BOM)
	public static function iconv_fallback_iso88591_utf16($string) {
		return self::iconv_fallback_iso88591_utf16le($string, true);
	}

	// UTF-8 => ISO-8859-1
	public static function iconv_fallback_utf8_iso88591($string) {
		if (function_exists('utf8_decode')) {
			return utf8_decode($string);
		}
		// utf8_decode() unavailable, use getID3()'s iconv_fallback() conversions (possibly PHP is compiled without XML support)
		$newcharstring = '';
		$offset = 0;
		$stringlength = strlen($string);
		while ($offset < $stringlength) {
			if ((ord($string{$offset}) | 0x07) == 0xF7) {
				// 11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
				$charval = ((ord($string{($offset + 0)}) & 0x07) << 18) &
						   ((ord($string{($offset + 1)}) & 0x3F) << 12) &
						   ((ord($string{($offset + 2)}) & 0x3F) <<  6) &
							(ord($string{($offset + 3)}) & 0x3F);
				$offset += 4;
			} elseif ((ord($string{$offset}) | 0x0F) == 0xEF) {
				// 1110bbbb 10bbbbbb 10bbbbbb
				$charval = ((ord($string{($offset + 0)}) & 0x0F) << 12) &
						   ((ord($string{($offset + 1)}) & 0x3F) <<  6) &
							(ord($string{($offset + 2)}) & 0x3F);
				$offset += 3;
			} elseif ((ord($string{$offset}) | 0x1F) == 0xDF) {
				// 110bbbbb 10bbbbbb
				$charval = ((ord($string{($offset + 0)}) & 0x1F) <<  6) &
							(ord($string{($offset + 1)}) & 0x3F);
				$offset += 2;
			} elseif ((ord($string{$offset}) | 0x7F) == 0x7F) {
				// 0bbbbbbb
				$charval = ord($string{$offset});
				$offset += 1;
			} else {
				// error? throw some kind of warning here?
				$charval = false;
				$offset += 1;
			}
			if ($charval !== false) {
				$newcharstring .= (($charval < 256) ? chr($charval) : '?');
			}
		}
		return $newcharstring;
	}

	// UTF-8 => UTF-16BE
	public static function iconv_fallback_utf8_utf16be($string, $bom=false) {
		$newcharstring = '';
		if ($bom) {
			$newcharstring .= "\xFE\xFF";
		}
		$offset = 0;
		$stringlength = strlen($string);
		while ($offset < $stringlength) {
			if ((ord($string{$offset}) | 0x07) == 0xF7) {
				// 11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
				$charval = ((ord($string{($offset + 0)}) & 0x07) << 18) &
						   ((ord($string{($offset + 1)}) & 0x3F) << 12) &
						   ((ord($string{($offset + 2)}) & 0x3F) <<  6) &
							(ord($string{($offset + 3)}) & 0x3F);
				$offset += 4;
			} elseif ((ord($string{$offset}) | 0x0F) == 0xEF) {
				// 1110bbbb 10bbbbbb 10bbbbbb
				$charval = ((ord($string{($offset + 0)}) & 0x0F) << 12) &
						   ((ord($string{($offset + 1)}) & 0x3F) <<  6) &
							(ord($string{($offset + 2)}) & 0x3F);
				$offset += 3;
			} elseif ((ord($string{$offset}) | 0x1F) == 0xDF) {
				// 110bbbbb 10bbbbbb
				$charval = ((ord($string{($offset + 0)}) & 0x1F) <<  6) &
							(ord($string{($offset + 1)}) & 0x3F);
				$offset += 2;
			} elseif ((ord($string{$offset}) | 0x7F) == 0x7F) {
				// 0bbbbbbb
				$charval = ord($string{$offset});
				$offset += 1;
			} else {
				// error? throw some kind of warning here?
				$charval = false;
				$offset += 1;
			}
			if ($charval !== false) {
				$newcharstring .= (($charval < 65536) ? self::BigEndian2String($charval, 2) : "\x00".'?');
			}
		}
		return $newcharstring;
	}

	// UTF-8 => UTF-16LE
	public static function iconv_fallback_utf8_utf16le($string, $bom=false) {
		$newcharstring = '';
		if ($bom) {
			$newcharstring .= "\xFF\xFE";
		}
		$offset = 0;
		$stringlength = strlen($string);
		while ($offset < $stringlength) {
			if ((ord($string{$offset}) | 0x07) == 0xF7) {
				// 11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
				$charval = ((ord($string{($offset + 0)}) & 0x07) << 18) &
						   ((ord($string{($offset + 1)}) & 0x3F) << 12) &
						   ((ord($string{($offset + 2)}) & 0x3F) <<  6) &
							(ord($string{($offset + 3)}) & 0x3F);
				$offset += 4;
			} elseif ((ord($string{$offset}) | 0x0F) == 0xEF) {
				// 1110bbbb 10bbbbbb 10bbbbbb
				$charval = ((ord($string{($offset + 0)}) & 0x0F) << 12) &
						   ((ord($string{($offset + 1)}) & 0x3F) <<  6) &
							(ord($string{($offset + 2)}) & 0x3F);
				$offset += 3;
			} elseif ((ord($string{$offset}) | 0x1F) == 0xDF) {
				// 110bbbbb 10bbbbbb
				$charval = ((ord($string{($offset + 0)}) & 0x1F) <<  6) &
							(ord($string{($offset + 1)}) & 0x3F);
				$offset += 2;
			} elseif ((ord($string{$offset}) | 0x7F) == 0x7F) {
				// 0bbbbbbb
				$charval = ord($string{$offset});
				$offset += 1;
			} else {
				// error? maybe throw some warning here?
				$charval = false;
				$offset += 1;
			}
			if ($charval !== false) {
				$newcharstring .= (($charval < 65536) ? self::LittleEndian2String($charval, 2) : '?'."\x00");
			}
		}
		return $newcharstring;
	}

	// UTF-8 => UTF-16LE (BOM)
	public static function iconv_fallback_utf8_utf16($string) {
		return self::iconv_fallback_utf8_utf16le($string, true);
	}

	// UTF-16BE => UTF-8
	public static function iconv_fallback_utf16be_utf8($string) {
		if (substr($string, 0, 2) == "\xFE\xFF") {
			// strip BOM
			$string = substr($string, 2);
		}
		$newcharstring = '';
		for ($i = 0; $i < strlen($string); $i += 2) {
			$charval = self::BigEndian2Int(substr($string, $i, 2));
			$newcharstring .= self::iconv_fallback_int_utf8($charval);
		}
		return $newcharstring;
	}

	// UTF-16LE => UTF-8
	public static function iconv_fallback_utf16le_utf8($string) {
		if (substr($string, 0, 2) == "\xFF\xFE") {
			// strip BOM
			$string = substr($string, 2);
		}
		$newcharstring = '';
		for ($i = 0; $i < strlen($string); $i += 2) {
			$charval = self::LittleEndian2Int(substr($string, $i, 2));
			$newcharstring .= self::iconv_fallback_int_utf8($charval);
		}
		return $newcharstring;
	}

	// UTF-16BE => ISO-8859-1
	public static function iconv_fallback_utf16be_iso88591($string) {
		if (substr($string, 0, 2) == "\xFE\xFF") {
			// strip BOM
			$string = substr($string, 2);
		}
		$newcharstring = '';
		for ($i = 0; $i < strlen($string); $i += 2) {
			$charval = self::BigEndian2Int(substr($string, $i, 2));
			$newcharstring .= (($charval < 256) ? chr($charval) : '?');
		}
		return $newcharstring;
	}

	// UTF-16LE => ISO-8859-1
	public static function iconv_fallback_utf16le_iso88591($string) {
		if (substr($string, 0, 2) == "\xFF\xFE") {
			// strip BOM
			$string = substr($string, 2);
		}
		$newcharstring = '';
		for ($i = 0; $i < strlen($string); $i += 2) {
			$charval = self::LittleEndian2Int(substr($string, $i, 2));
			$newcharstring .= (($charval < 256) ? chr($charval) : '?');
		}
		return $newcharstring;
	}

	// UTF-16 (BOM) => ISO-8859-1
	public static function iconv_fallback_utf16_iso88591($string) {
		$bom = substr($string, 0, 2);
		if ($bom == "\xFE\xFF") {
			return self::iconv_fallback_utf16be_iso88591(substr($string, 2));
		} elseif ($bom == "\xFF\xFE") {
			return self::iconv_fallback_utf16le_iso88591(substr($string, 2));
		}
		return $string;
	}

	// UTF-16 (BOM) => UTF-8
	public static function iconv_fallback_utf16_utf8($string) {
		$bom = substr($string, 0, 2);
		if ($bom == "\xFE\xFF") {
			return self::iconv_fallback_utf16be_utf8(substr($string, 2));
		} elseif ($bom == "\xFF\xFE") {
			return self::iconv_fallback_utf16le_utf8(substr($string, 2));
		}
		return $string;
	}

	public static function iconv_fallback($in_charset, $out_charset, $string) {

		if ($in_charset == $out_charset) {
			return $string;
		}

		// iconv() availble
		if (function_exists('iconv')) {
			if ($converted_string = @iconv($in_charset, $out_charset.'//TRANSLIT', $string)) {
				switch ($out_charset) {
					case 'ISO-8859-1':
						$converted_string = rtrim($converted_string, "\x00");
						break;
				}
				return $converted_string;
			}

			// iconv() may sometimes fail with "illegal character in input string" error message
			// and return an empty string, but returning the unconverted string is more useful
			return $string;
		}


		// iconv() not available
		static $ConversionFunctionList = array();
		if (empty($ConversionFunctionList)) {
			$ConversionFunctionList['ISO-8859-1']['UTF-8']    = 'iconv_fallback_iso88591_utf8';
			$ConversionFunctionList['ISO-8859-1']['UTF-16']   = 'iconv_fallback_iso88591_utf16';
			$ConversionFunctionList['ISO-8859-1']['UTF-16BE'] = 'iconv_fallback_iso88591_utf16be';
			$ConversionFunctionList['ISO-8859-1']['UTF-16LE'] = 'iconv_fallback_iso88591_utf16le';
			$ConversionFunctionList['UTF-8']['ISO-8859-1']    = 'iconv_fallback_utf8_iso88591';
			$ConversionFunctionList['UTF-8']['UTF-16']        = 'iconv_fallback_utf8_utf16';
			$ConversionFunctionList['UTF-8']['UTF-16BE']      = 'iconv_fallback_utf8_utf16be';
			$ConversionFunctionList['UTF-8']['UTF-16LE']      = 'iconv_fallback_utf8_utf16le';
			$ConversionFunctionList['UTF-16']['ISO-8859-1']   = 'iconv_fallback_utf16_iso88591';
			$ConversionFunctionList['UTF-16']['UTF-8']        = 'iconv_fallback_utf16_utf8';
			$ConversionFunctionList['UTF-16LE']['ISO-8859-1'] = 'iconv_fallback_utf16le_iso88591';
			$ConversionFunctionList['UTF-16LE']['UTF-8']      = 'iconv_fallback_utf16le_utf8';
			$ConversionFunctionList['UTF-16BE']['ISO-8859-1'] = 'iconv_fallback_utf16be_iso88591';
			$ConversionFunctionList['UTF-16BE']['UTF-8']      = 'iconv_fallback_utf16be_utf8';
		}
		if (isset($ConversionFunctionList[strtoupper($in_charset)][strtoupper($out_charset)])) {
			$ConversionFunction = $ConversionFunctionList[strtoupper($in_charset)][strtoupper($out_charset)];
			return self::$ConversionFunction($string);
		}
		throw new Exception('PHP does not have iconv() support - cannot convert from '.$in_charset.' to '.$out_charset);
	}


	public static function MultiByteCharString2HTML($string, $charset='ISO-8859-1') {
		$string = (string) $string; // in case trying to pass a numeric (float, int) string, would otherwise return an empty string
		$HTMLstring = '';

		switch ($charset) {
			case '1251':
			case '1252':
			case '866':
			case '932':
			case '936':
			case '950':
			case 'BIG5':
			case 'BIG5-HKSCS':
			case 'cp1251':
			case 'cp1252':
			case 'cp866':
			case 'EUC-JP':
			case 'EUCJP':
			case 'GB2312':
			case 'ibm866':
			case 'ISO-8859-1':
			case 'ISO-8859-15':
			case 'ISO8859-1':
			case 'ISO8859-15':
			case 'KOI8-R':
			case 'koi8-ru':
			case 'koi8r':
			case 'Shift_JIS':
			case 'SJIS':
			case 'win-1251':
			case 'Windows-1251':
			case 'Windows-1252':
				$HTMLstring = htmlentities($string, ENT_COMPAT, $charset);
				break;

			case 'UTF-8':
				$strlen = strlen($string);
				for ($i = 0; $i < $strlen; $i++) {
					$char_ord_val = ord($string{$i});
					$charval = 0;
					if ($char_ord_val < 0x80) {
						$charval = $char_ord_val;
					} elseif ((($char_ord_val & 0xF0) >> 4) == 0x0F  &&  $i+3 < $strlen) {
						$charval  = (($char_ord_val & 0x07) << 18);
						$charval += ((ord($string{++$i}) & 0x3F) << 12);
						$charval += ((ord($string{++$i}) & 0x3F) << 6);
						$charval +=  (ord($string{++$i}) & 0x3F);
					} elseif ((($char_ord_val & 0xE0) >> 5) == 0x07  &&  $i+2 < $strlen) {
						$charval  = (($char_ord_val & 0x0F) << 12);
						$charval += ((ord($string{++$i}) & 0x3F) << 6);
						$charval +=  (ord($string{++$i}) & 0x3F);
					} elseif ((($char_ord_val & 0xC0) >> 6) == 0x03  &&  $i+1 < $strlen) {
						$charval  = (($char_ord_val & 0x1F) << 6);
						$charval += (ord($string{++$i}) & 0x3F);
					}
					if (($charval >= 32) && ($charval <= 127)) {
						$HTMLstring .= htmlentities(chr($charval));
					} else {
						$HTMLstring .= '&#'.$charval.';';
					}
				}
				break;

			case 'UTF-16LE':
				for ($i = 0; $i < strlen($string); $i += 2) {
					$charval = self::LittleEndian2Int(substr($string, $i, 2));
					if (($charval >= 32) && ($charval <= 127)) {
						$HTMLstring .= chr($charval);
					} else {
						$HTMLstring .= '&#'.$charval.';';
					}
				}
				break;

			case 'UTF-16BE':
				for ($i = 0; $i < strlen($string); $i += 2) {
					$charval = self::BigEndian2Int(substr($string, $i, 2));
					if (($charval >= 32) && ($charval <= 127)) {
						$HTMLstring .= chr($charval);
					} else {
						$HTMLstring .= '&#'.$charval.';';
					}
				}
				break;

			default:
				$HTMLstring = 'ERROR: Character set "'.$charset.'" not supported in MultiByteCharString2HTML()';
				break;
		}
		return $HTMLstring;
	}



	public static function RGADnameLookup($namecode) {
		static $RGADname = array();
		if (empty($RGADname)) {
			$RGADname[0] = 'not set';
			$RGADname[1] = 'Track Gain Adjustment';
			$RGADname[2] = 'Album Gain Adjustment';
		}

		return (isset($RGADname[$namecode]) ? $RGADname[$namecode] : '');
	}


	public static function RGADoriginatorLookup($originatorcode) {
		static $RGADoriginator = array();
		if (empty($RGADoriginator)) {
			$RGADoriginator[0] = 'unspecified';
			$RGADoriginator[1] = 'pre-set by artist/producer/mastering engineer';
			$RGADoriginator[2] = 'set by user';
			$RGADoriginator[3] = 'determined automatically';
		}

		return (isset($RGADoriginator[$originatorcode]) ? $RGADoriginator[$originatorcode] : '');
	}


	public static function RGADadjustmentLookup($rawadjustment, $signbit) {
		$adjustment = $rawadjustment / 10;
		if ($signbit == 1) {
			$adjustment *= -1;
		}
		return (float) $adjustment;
	}


	public static function RGADgainString($namecode, $originatorcode, $replaygain) {
		if ($replaygain < 0) {
			$signbit = '1';
		} else {
			$signbit = '0';
		}
		$storedreplaygain = intval(round($replaygain * 10));
		$gainstring  = str_pad(decbin($namecode), 3, '0', STR_PAD_LEFT);
		$gainstring .= str_pad(decbin($originatorcode), 3, '0', STR_PAD_LEFT);
		$gainstring .= $signbit;
		$gainstring .= str_pad(decbin($storedreplaygain), 9, '0', STR_PAD_LEFT);

		return $gainstring;
	}

	public static function RGADamplitude2dB($amplitude) {
		return 20 * log10($amplitude);
	}


	public static function GetDataImageSize($imgData, &$imageinfo=array()) {
		static $tempdir = '';
		if (empty($tempdir)) {
			// yes this is ugly, feel free to suggest a better way
			//require_once(dirname(__FILE__).'/getid3.php');
			/// INCLUDED MANUALLY -Scott
			$getid3_temp = new getID3();
			$tempdir = $getid3_temp->tempdir;
			unset($getid3_temp);
		}
		$GetDataImageSize = false;
		if ($tempfilename = tempnam($tempdir, 'gI3')) {
			if (is_writable($tempfilename) && is_file($tempfilename) && ($tmp = fopen($tempfilename, 'wb'))) {
				fwrite($tmp, $imgData);
				fclose($tmp);
				$GetDataImageSize = @getimagesize($tempfilename, $imageinfo);
			}
			unlink($tempfilename);
		}
		return $GetDataImageSize;
	}

	public static function ImageExtFromMime($mime_type) {
		// temporary way, works OK for now, but should be reworked in the future
		return str_replace(array('image/', 'x-', 'jpeg'), array('', '', 'jpg'), $mime_type);
	}

	public static function ImageTypesLookup($imagetypeid) {
		static $ImageTypesLookup = array();
		if (empty($ImageTypesLookup)) {
			$ImageTypesLookup[1]  = 'gif';
			$ImageTypesLookup[2]  = 'jpeg';
			$ImageTypesLookup[3]  = 'png';
			$ImageTypesLookup[4]  = 'swf';
			$ImageTypesLookup[5]  = 'psd';
			$ImageTypesLookup[6]  = 'bmp';
			$ImageTypesLookup[7]  = 'tiff (little-endian)';
			$ImageTypesLookup[8]  = 'tiff (big-endian)';
			$ImageTypesLookup[9]  = 'jpc';
			$ImageTypesLookup[10] = 'jp2';
			$ImageTypesLookup[11] = 'jpx';
			$ImageTypesLookup[12] = 'jb2';
			$ImageTypesLookup[13] = 'swc';
			$ImageTypesLookup[14] = 'iff';
		}
		return (isset($ImageTypesLookup[$imagetypeid]) ? $ImageTypesLookup[$imagetypeid] : '');
	}

	public static function CopyTagsToComments(&$ThisFileInfo) {

		// Copy all entries from ['tags'] into common ['comments']
		if (!empty($ThisFileInfo['tags'])) {
			foreach ($ThisFileInfo['tags'] as $tagtype => $tagarray) {
				foreach ($tagarray as $tagname => $tagdata) {
					foreach ($tagdata as $key => $value) {
						if (!empty($value)) {
							if (empty($ThisFileInfo['comments'][$tagname])) {

								// fall through and append value

							} elseif ($tagtype == 'id3v1') {

								$newvaluelength = strlen(trim($value));
								foreach ($ThisFileInfo['comments'][$tagname] as $existingkey => $existingvalue) {
									$oldvaluelength = strlen(trim($existingvalue));
									if (($newvaluelength <= $oldvaluelength) && (substr($existingvalue, 0, $newvaluelength) == trim($value))) {
										// new value is identical but shorter-than (or equal-length to) one already in comments - skip
										break 2;
									}
								}

							} elseif (!is_array($value)) {

								$newvaluelength = strlen(trim($value));
								foreach ($ThisFileInfo['comments'][$tagname] as $existingkey => $existingvalue) {
									$oldvaluelength = strlen(trim($existingvalue));
									if (($newvaluelength > $oldvaluelength) && (substr(trim($value), 0, strlen($existingvalue)) == $existingvalue)) {
										$ThisFileInfo['comments'][$tagname][$existingkey] = trim($value);
										break 2;
									}
								}

							}
							if (is_array($value) || empty($ThisFileInfo['comments'][$tagname]) || !in_array(trim($value), $ThisFileInfo['comments'][$tagname])) {
								$value = (is_string($value) ? trim($value) : $value);
								$ThisFileInfo['comments'][$tagname][] = $value;
							}
						}
					}
				}
			}

			// Copy to ['comments_html']
			foreach ($ThisFileInfo['comments'] as $field => $values) {
				if ($field == 'picture') {
					// pictures can take up a lot of space, and we don't need multiple copies of them
					// let there be a single copy in [comments][picture], and not elsewhere
					continue;
				}
				foreach ($values as $index => $value) {
					if (is_array($value)) {
						$ThisFileInfo['comments_html'][$field][$index] = $value;
					} else {
						$ThisFileInfo['comments_html'][$field][$index] = str_replace('&#0;', '', self::MultiByteCharString2HTML($value, $ThisFileInfo['encoding']));
					}
				}
			}
		}
		return true;
	}


	public static function EmbeddedLookup($key, $begin, $end, $file, $name) {

		// Cached
		static $cache;
		if (isset($cache[$file][$name])) {
			return (isset($cache[$file][$name][$key]) ? $cache[$file][$name][$key] : '');
		}

		// Init
		$keylength  = strlen($key);
		$line_count = $end - $begin - 7;

		// Open php file
		$fp = fopen($file, 'r');

		// Discard $begin lines
		for ($i = 0; $i < ($begin + 3); $i++) {
			fgets($fp, 1024);
		}

		// Loop thru line
		while (0 < $line_count--) {

			// Read line
			$line = ltrim(fgets($fp, 1024), "\t ");

			// METHOD A: only cache the matching key - less memory but slower on next lookup of not-previously-looked-up key
			//$keycheck = substr($line, 0, $keylength);
			//if ($key == $keycheck)  {
			//	$cache[$file][$name][$keycheck] = substr($line, $keylength + 1);
			//	break;
			//}

			// METHOD B: cache all keys in this lookup - more memory but faster on next lookup of not-previously-looked-up key
			//$cache[$file][$name][substr($line, 0, $keylength)] = trim(substr($line, $keylength + 1));
			$explodedLine = explode("\t", $line, 2);
			$ThisKey   = (isset($explodedLine[0]) ? $explodedLine[0] : '');
			$ThisValue = (isset($explodedLine[1]) ? $explodedLine[1] : '');
			$cache[$file][$name][$ThisKey] = trim($ThisValue);
		}

		// Close and return
		fclose($fp);
		return (isset($cache[$file][$name][$key]) ? $cache[$file][$name][$key] : '');
	}

	public static function IncludeDependency($filename, $sourcefile, $DieOnFailure=false) {
		global $GETID3_ERRORARRAY;

		if (file_exists($filename)) {
			if (include_once($filename)) {
				return true;
			} else {
				$diemessage = basename($sourcefile).' depends on '.$filename.', which has errors';
			}
		} else {
			$diemessage = basename($sourcefile).' depends on '.$filename.', which is missing';
		}
		if ($DieOnFailure) {
			throw new Exception($diemessage);
		} else {
			$GETID3_ERRORARRAY[] = $diemessage;
		}
		return false;
	}

	public static function trimNullByte($string) {
		return trim($string, "\x00");
	}

	public static function getFileSizeSyscall($path) {
		$filesize = false;

		if (GETID3_OS_ISWINDOWS) {
			if (class_exists('COM')) { // From PHP 5.3.15 and 5.4.5, COM and DOTNET is no longer built into the php core.you have to add COM support in php.ini:
				$filesystem = new COM('Scripting.FileSystemObject');
				$file = $filesystem->GetFile($path);
				$filesize = $file->Size();
				unset($filesystem, $file);
			} else {
				$commandline = 'for %I in ('.escapeshellarg($path).') do @echo %~zI';
			}
		} else {
			$commandline = 'ls -l '.escapeshellarg($path).' | awk \'{print $5}\'';
		}
		if (isset($commandline)) {
			$output = trim(`$commandline`);
			if (ctype_digit($output)) {
				$filesize = (float) $output;
			}
		}
		return $filesize;
	}

}

///// BEGINTAGID3V1 /////////////


class getid3_id3v1 extends getid3_handler
{

	public function Analyze() {
		$info = &$this->getid3->info;

		if (!getid3_lib::intValueSupported($info['filesize'])) {
			$info['warning'][] = 'Unable to check for ID3v1 because file is larger than '.round(PHP_INT_MAX / 1073741824).'GB';
			return false;
		}

		fseek($this->getid3->fp, -256, SEEK_END);
		$preid3v1 = fread($this->getid3->fp, 128);
		$id3v1tag = fread($this->getid3->fp, 128);

		if (substr($id3v1tag, 0, 3) == 'TAG') {

			$info['avdataend'] = $info['filesize'] - 128;

			$ParsedID3v1['title']   = $this->cutfield(substr($id3v1tag,   3, 30));
			$ParsedID3v1['artist']  = $this->cutfield(substr($id3v1tag,  33, 30));
			$ParsedID3v1['album']   = $this->cutfield(substr($id3v1tag,  63, 30));
			$ParsedID3v1['year']    = $this->cutfield(substr($id3v1tag,  93,  4));
			$ParsedID3v1['comment'] =                 substr($id3v1tag,  97, 30);  // can't remove nulls yet, track detection depends on them
			$ParsedID3v1['genreid'] =             ord(substr($id3v1tag, 127,  1));

			// If second-last byte of comment field is null and last byte of comment field is non-null
			// then this is ID3v1.1 and the comment field is 28 bytes long and the 30th byte is the track number
			if (($id3v1tag{125} === "\x00") && ($id3v1tag{126} !== "\x00")) {
				$ParsedID3v1['track']   = ord(substr($ParsedID3v1['comment'], 29,  1));
				$ParsedID3v1['comment'] =     substr($ParsedID3v1['comment'],  0, 28);
			}
			$ParsedID3v1['comment'] = $this->cutfield($ParsedID3v1['comment']);

			$ParsedID3v1['genre'] = $this->LookupGenreName($ParsedID3v1['genreid']);
			if (!empty($ParsedID3v1['genre'])) {
				unset($ParsedID3v1['genreid']);
			}
			if (isset($ParsedID3v1['genre']) && (empty($ParsedID3v1['genre']) || ($ParsedID3v1['genre'] == 'Unknown'))) {
				unset($ParsedID3v1['genre']);
			}

			foreach ($ParsedID3v1 as $key => $value) {
				$ParsedID3v1['comments'][$key][0] = $value;
			}

			// ID3v1 data is supposed to be padded with NULL characters, but some taggers pad with spaces
			$GoodFormatID3v1tag = $this->GenerateID3v1Tag(
											$ParsedID3v1['title'],
											$ParsedID3v1['artist'],
											$ParsedID3v1['album'],
											$ParsedID3v1['year'],
											(isset($ParsedID3v1['genre']) ? $this->LookupGenreID($ParsedID3v1['genre']) : false),
											$ParsedID3v1['comment'],
											(!empty($ParsedID3v1['track']) ? $ParsedID3v1['track'] : ''));
			$ParsedID3v1['padding_valid'] = true;
			if ($id3v1tag !== $GoodFormatID3v1tag) {
				$ParsedID3v1['padding_valid'] = false;
				$info['warning'][] = 'Some ID3v1 fields do not use NULL characters for padding';
			}

			$ParsedID3v1['tag_offset_end']   = $info['filesize'];
			$ParsedID3v1['tag_offset_start'] = $ParsedID3v1['tag_offset_end'] - 128;

			$info['id3v1'] = $ParsedID3v1;
		}

		if (substr($preid3v1, 0, 3) == 'TAG') {
			// The way iTunes handles tags is, well, brain-damaged.
			// It completely ignores v1 if ID3v2 is present.
			// This goes as far as adding a new v1 tag *even if there already is one*

			// A suspected double-ID3v1 tag has been detected, but it could be that
			// the "TAG" identifier is a legitimate part of an APE or Lyrics3 tag
			if (substr($preid3v1, 96, 8) == 'APETAGEX') {
				// an APE tag footer was found before the last ID3v1, assume false "TAG" synch
			} elseif (substr($preid3v1, 119, 6) == 'LYRICS') {
				// a Lyrics3 tag footer was found before the last ID3v1, assume false "TAG" synch
			} else {
				// APE and Lyrics3 footers not found - assume double ID3v1
				$info['warning'][] = 'Duplicate ID3v1 tag detected - this has been known to happen with iTunes';
				$info['avdataend'] -= 128;
			}
		}

		return true;
	}

	public static function cutfield($str) {
		return trim(substr($str, 0, strcspn($str, "\x00")));
	}

	public static function ArrayOfGenres($allowSCMPXextended=false) {
		static $GenreLookup = array(
			0    => 'Blues',
			1    => 'Classic Rock',
			2    => 'Country',
			3    => 'Dance',
			4    => 'Disco',
			5    => 'Funk',
			6    => 'Grunge',
			7    => 'Hip-Hop',
			8    => 'Jazz',
			9    => 'Metal',
			10   => 'New Age',
			11   => 'Oldies',
			12   => 'Other',
			13   => 'Pop',
			14   => 'R&B',
			15   => 'Rap',
			16   => 'Reggae',
			17   => 'Rock',
			18   => 'Techno',
			19   => 'Industrial',
			20   => 'Alternative',
			21   => 'Ska',
			22   => 'Death Metal',
			23   => 'Pranks',
			24   => 'Soundtrack',
			25   => 'Euro-Techno',
			26   => 'Ambient',
			27   => 'Trip-Hop',
			28   => 'Vocal',
			29   => 'Jazz+Funk',
			30   => 'Fusion',
			31   => 'Trance',
			32   => 'Classical',
			33   => 'Instrumental',
			34   => 'Acid',
			35   => 'House',
			36   => 'Game',
			37   => 'Sound Clip',
			38   => 'Gospel',
			39   => 'Noise',
			40   => 'Alt. Rock',
			41   => 'Bass',
			42   => 'Soul',
			43   => 'Punk',
			44   => 'Space',
			45   => 'Meditative',
			46   => 'Instrumental Pop',
			47   => 'Instrumental Rock',
			48   => 'Ethnic',
			49   => 'Gothic',
			50   => 'Darkwave',
			51   => 'Techno-Industrial',
			52   => 'Electronic',
			53   => 'Pop-Folk',
			54   => 'Eurodance',
			55   => 'Dream',
			56   => 'Southern Rock',
			57   => 'Comedy',
			58   => 'Cult',
			59   => 'Gangsta Rap',
			60   => 'Top 40',
			61   => 'Christian Rap',
			62   => 'Pop/Funk',
			63   => 'Jungle',
			64   => 'Native American',
			65   => 'Cabaret',
			66   => 'New Wave',
			67   => 'Psychedelic',
			68   => 'Rave',
			69   => 'Showtunes',
			70   => 'Trailer',
			71   => 'Lo-Fi',
			72   => 'Tribal',
			73   => 'Acid Punk',
			74   => 'Acid Jazz',
			75   => 'Polka',
			76   => 'Retro',
			77   => 'Musical',
			78   => 'Rock & Roll',
			79   => 'Hard Rock',
			80   => 'Folk',
			81   => 'Folk/Rock',
			82   => 'National Folk',
			83   => 'Swing',
			84   => 'Fast-Fusion',
			85   => 'Bebob',
			86   => 'Latin',
			87   => 'Revival',
			88   => 'Celtic',
			89   => 'Bluegrass',
			90   => 'Avantgarde',
			91   => 'Gothic Rock',
			92   => 'Progressive Rock',
			93   => 'Psychedelic Rock',
			94   => 'Symphonic Rock',
			95   => 'Slow Rock',
			96   => 'Big Band',
			97   => 'Chorus',
			98   => 'Easy Listening',
			99   => 'Acoustic',
			100  => 'Humour',
			101  => 'Speech',
			102  => 'Chanson',
			103  => 'Opera',
			104  => 'Chamber Music',
			105  => 'Sonata',
			106  => 'Symphony',
			107  => 'Booty Bass',
			108  => 'Primus',
			109  => 'Porn Groove',
			110  => 'Satire',
			111  => 'Slow Jam',
			112  => 'Club',
			113  => 'Tango',
			114  => 'Samba',
			115  => 'Folklore',
			116  => 'Ballad',
			117  => 'Power Ballad',
			118  => 'Rhythmic Soul',
			119  => 'Freestyle',
			120  => 'Duet',
			121  => 'Punk Rock',
			122  => 'Drum Solo',
			123  => 'A Cappella',
			124  => 'Euro-House',
			125  => 'Dance Hall',
			126  => 'Goa',
			127  => 'Drum & Bass',
			128  => 'Club-House',
			129  => 'Hardcore',
			130  => 'Terror',
			131  => 'Indie',
			132  => 'BritPop',
			133  => 'Negerpunk',
			134  => 'Polsk Punk',
			135  => 'Beat',
			136  => 'Christian Gangsta Rap',
			137  => 'Heavy Metal',
			138  => 'Black Metal',
			139  => 'Crossover',
			140  => 'Contemporary Christian',
			141  => 'Christian Rock',
			142  => 'Merengue',
			143  => 'Salsa',
			144  => 'Thrash Metal',
			145  => 'Anime',
			146  => 'JPop',
			147  => 'Synthpop',

			255  => 'Unknown',

			'CR' => 'Cover',
			'RX' => 'Remix'
		);

		static $GenreLookupSCMPX = array();
		if ($allowSCMPXextended && empty($GenreLookupSCMPX)) {
			$GenreLookupSCMPX = $GenreLookup;
			// http://www.geocities.co.jp/SiliconValley-Oakland/3664/alittle.html#GenreExtended
			// Extended ID3v1 genres invented by SCMPX
			// Note that 255 "Japanese Anime" conflicts with standard "Unknown"
			$GenreLookupSCMPX[240] = 'Sacred';
			$GenreLookupSCMPX[241] = 'Northern Europe';
			$GenreLookupSCMPX[242] = 'Irish & Scottish';
			$GenreLookupSCMPX[243] = 'Scotland';
			$GenreLookupSCMPX[244] = 'Ethnic Europe';
			$GenreLookupSCMPX[245] = 'Enka';
			$GenreLookupSCMPX[246] = 'Children\'s Song';
			$GenreLookupSCMPX[247] = 'Japanese Sky';
			$GenreLookupSCMPX[248] = 'Japanese Heavy Rock';
			$GenreLookupSCMPX[249] = 'Japanese Doom Rock';
			$GenreLookupSCMPX[250] = 'Japanese J-POP';
			$GenreLookupSCMPX[251] = 'Japanese Seiyu';
			$GenreLookupSCMPX[252] = 'Japanese Ambient Techno';
			$GenreLookupSCMPX[253] = 'Japanese Moemoe';
			$GenreLookupSCMPX[254] = 'Japanese Tokusatsu';
			//$GenreLookupSCMPX[255] = 'Japanese Anime';
		}

		return ($allowSCMPXextended ? $GenreLookupSCMPX : $GenreLookup);
	}

	public static function LookupGenreName($genreid, $allowSCMPXextended=true) {
		switch ($genreid) {
			case 'RX':
			case 'CR':
				break;
			default:
				if (!is_numeric($genreid)) {
					return false;
				}
				$genreid = intval($genreid); // to handle 3 or '3' or '03'
				break;
		}
		$GenreLookup = self::ArrayOfGenres($allowSCMPXextended);
		return (isset($GenreLookup[$genreid]) ? $GenreLookup[$genreid] : false);
	}

	public static function LookupGenreID($genre, $allowSCMPXextended=false) {
		$GenreLookup = self::ArrayOfGenres($allowSCMPXextended);
		$LowerCaseNoSpaceSearchTerm = strtolower(str_replace(' ', '', $genre));
		foreach ($GenreLookup as $key => $value) {
			if (strtolower(str_replace(' ', '', $value)) == $LowerCaseNoSpaceSearchTerm) {
				return $key;
			}
		}
		return false;
	}

	public static function StandardiseID3v1GenreName($OriginalGenre) {
		if (($GenreID = self::LookupGenreID($OriginalGenre)) !== false) {
			return self::LookupGenreName($GenreID);
		}
		return $OriginalGenre;
	}

	public static function GenerateID3v1Tag($title, $artist, $album, $year, $genreid, $comment, $track='') {
		$ID3v1Tag  = 'TAG';
		$ID3v1Tag .= str_pad(trim(substr($title,  0, 30)), 30, "\x00", STR_PAD_RIGHT);
		$ID3v1Tag .= str_pad(trim(substr($artist, 0, 30)), 30, "\x00", STR_PAD_RIGHT);
		$ID3v1Tag .= str_pad(trim(substr($album,  0, 30)), 30, "\x00", STR_PAD_RIGHT);
		$ID3v1Tag .= str_pad(trim(substr($year,   0,  4)),  4, "\x00", STR_PAD_LEFT);
		if (!empty($track) && ($track > 0) && ($track <= 255)) {
			$ID3v1Tag .= str_pad(trim(substr($comment, 0, 28)), 28, "\x00", STR_PAD_RIGHT);
			$ID3v1Tag .= "\x00";
			if (gettype($track) == 'string') {
				$track = (int) $track;
			}
			$ID3v1Tag .= chr($track);
		} else {
			$ID3v1Tag .= str_pad(trim(substr($comment, 0, 30)), 30, "\x00", STR_PAD_RIGHT);
		}
		if (($genreid < 0) || ($genreid > 147)) {
			$genreid = 255; // 'unknown' genre
		}
		switch (gettype($genreid)) {
			case 'string':
			case 'integer':
				$ID3v1Tag .= chr(intval($genreid));
				break;
			default:
				$ID3v1Tag .= chr(255); // 'unknown' genre
				break;
		}

		return $ID3v1Tag;
	}

}

//////// BEGINTAGID3V2 ////////////////////


class getid3_id3v2 extends getid3_handler
{
	public $StartingOffset = 0;

	public function Analyze() {
		$info = &$this->getid3->info;

		//    Overall tag structure:
		//        +-----------------------------+
		//        |      Header (10 bytes)      |
		//        +-----------------------------+
		//        |       Extended Header       |
		//        | (variable length, OPTIONAL) |
		//        +-----------------------------+
		//        |   Frames (variable length)  |
		//        +-----------------------------+
		//        |           Padding           |
		//        | (variable length, OPTIONAL) |
		//        +-----------------------------+
		//        | Footer (10 bytes, OPTIONAL) |
		//        +-----------------------------+

		//    Header
		//        ID3v2/file identifier      "ID3"
		//        ID3v2 version              $04 00
		//        ID3v2 flags                (%ab000000 in v2.2, %abc00000 in v2.3, %abcd0000 in v2.4.x)
		//        ID3v2 size             4 * %0xxxxxxx


		// shortcuts
		$info['id3v2']['header'] = true;
		$thisfile_id3v2                  = &$info['id3v2'];
		$thisfile_id3v2['flags']         =  array();
		$thisfile_id3v2_flags            = &$thisfile_id3v2['flags'];


		fseek($this->getid3->fp, $this->StartingOffset, SEEK_SET);
		$header = fread($this->getid3->fp, 10);
		if (substr($header, 0, 3) == 'ID3'  &&  strlen($header) == 10) {

			$thisfile_id3v2['majorversion'] = ord($header{3});
			$thisfile_id3v2['minorversion'] = ord($header{4});

			// shortcut
			$id3v2_majorversion = &$thisfile_id3v2['majorversion'];

		} else {

			unset($info['id3v2']);
			return false;

		}

		if ($id3v2_majorversion > 4) { // this script probably won't correctly parse ID3v2.5.x and above (if it ever exists)

			$info['error'][] = 'this script only parses up to ID3v2.4.x - this tag is ID3v2.'.$id3v2_majorversion.'.'.$thisfile_id3v2['minorversion'];
			return false;

		}

		$id3_flags = ord($header{5});
		switch ($id3v2_majorversion) {
			case 2:
				// %ab000000 in v2.2
				$thisfile_id3v2_flags['unsynch']     = (bool) ($id3_flags & 0x80); // a - Unsynchronisation
				$thisfile_id3v2_flags['compression'] = (bool) ($id3_flags & 0x40); // b - Compression
				break;

			case 3:
				// %abc00000 in v2.3
				$thisfile_id3v2_flags['unsynch']     = (bool) ($id3_flags & 0x80); // a - Unsynchronisation
				$thisfile_id3v2_flags['exthead']     = (bool) ($id3_flags & 0x40); // b - Extended header
				$thisfile_id3v2_flags['experim']     = (bool) ($id3_flags & 0x20); // c - Experimental indicator
				break;

			case 4:
				// %abcd0000 in v2.4
				$thisfile_id3v2_flags['unsynch']     = (bool) ($id3_flags & 0x80); // a - Unsynchronisation
				$thisfile_id3v2_flags['exthead']     = (bool) ($id3_flags & 0x40); // b - Extended header
				$thisfile_id3v2_flags['experim']     = (bool) ($id3_flags & 0x20); // c - Experimental indicator
				$thisfile_id3v2_flags['isfooter']    = (bool) ($id3_flags & 0x10); // d - Footer present
				break;
		}

		$thisfile_id3v2['headerlength'] = getid3_lib::BigEndian2Int(substr($header, 6, 4), 1) + 10; // length of ID3v2 tag in 10-byte header doesn't include 10-byte header length

		$thisfile_id3v2['tag_offset_start'] = $this->StartingOffset;
		$thisfile_id3v2['tag_offset_end']   = $thisfile_id3v2['tag_offset_start'] + $thisfile_id3v2['headerlength'];



		// create 'encoding' key - used by getid3::HandleAllTags()
		// in ID3v2 every field can have it's own encoding type
		// so force everything to UTF-8 so it can be handled consistantly
		$thisfile_id3v2['encoding'] = 'UTF-8';


	//    Frames

	//        All ID3v2 frames consists of one frame header followed by one or more
	//        fields containing the actual information. The header is always 10
	//        bytes and laid out as follows:
	//
	//        Frame ID      $xx xx xx xx  (four characters)
	//        Size      4 * %0xxxxxxx
	//        Flags         $xx xx

		$sizeofframes = $thisfile_id3v2['headerlength'] - 10; // not including 10-byte initial header
		if (!empty($thisfile_id3v2['exthead']['length'])) {
			$sizeofframes -= ($thisfile_id3v2['exthead']['length'] + 4);
		}
		if (!empty($thisfile_id3v2_flags['isfooter'])) {
			$sizeofframes -= 10; // footer takes last 10 bytes of ID3v2 header, after frame data, before audio
		}
		if ($sizeofframes > 0) {

			$framedata = fread($this->getid3->fp, $sizeofframes); // read all frames from file into $framedata variable

			//    if entire frame data is unsynched, de-unsynch it now (ID3v2.3.x)
			if (!empty($thisfile_id3v2_flags['unsynch']) && ($id3v2_majorversion <= 3)) {
				$framedata = $this->DeUnsynchronise($framedata);
			}
			//        [in ID3v2.4.0] Unsynchronisation [S:6.1] is done on frame level, instead
			//        of on tag level, making it easier to skip frames, increasing the streamability
			//        of the tag. The unsynchronisation flag in the header [S:3.1] indicates that
			//        there exists an unsynchronised frame, while the new unsynchronisation flag in
			//        the frame header [S:4.1.2] indicates unsynchronisation.


			//$framedataoffset = 10 + ($thisfile_id3v2['exthead']['length'] ? $thisfile_id3v2['exthead']['length'] + 4 : 0); // how many bytes into the stream - start from after the 10-byte header (and extended header length+4, if present)
			$framedataoffset = 10; // how many bytes into the stream - start from after the 10-byte header


			//    Extended Header
			if (!empty($thisfile_id3v2_flags['exthead'])) {
				$extended_header_offset = 0;

				if ($id3v2_majorversion == 3) {

					// v2.3 definition:
					//Extended header size  $xx xx xx xx   // 32-bit integer
					//Extended Flags        $xx xx
					//     %x0000000 %00000000 // v2.3
					//     x - CRC data present
					//Size of padding       $xx xx xx xx

					$thisfile_id3v2['exthead']['length'] = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, 4), 0);
					$extended_header_offset += 4;

					$thisfile_id3v2['exthead']['flag_bytes'] = 2;
					$thisfile_id3v2['exthead']['flag_raw'] = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, $thisfile_id3v2['exthead']['flag_bytes']));
					$extended_header_offset += $thisfile_id3v2['exthead']['flag_bytes'];

					$thisfile_id3v2['exthead']['flags']['crc'] = (bool) ($thisfile_id3v2['exthead']['flag_raw'] & 0x8000);

					$thisfile_id3v2['exthead']['padding_size'] = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, 4));
					$extended_header_offset += 4;

					if ($thisfile_id3v2['exthead']['flags']['crc']) {
						$thisfile_id3v2['exthead']['flag_data']['crc'] = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, 4));
						$extended_header_offset += 4;
					}
					$extended_header_offset += $thisfile_id3v2['exthead']['padding_size'];

				} elseif ($id3v2_majorversion == 4) {

					// v2.4 definition:
					//Extended header size   4 * %0xxxxxxx // 28-bit synchsafe integer
					//Number of flag bytes       $01
					//Extended Flags             $xx
					//     %0bcd0000 // v2.4
					//     b - Tag is an update
					//         Flag data length       $00
					//     c - CRC data present
					//         Flag data length       $05
					//         Total frame CRC    5 * %0xxxxxxx
					//     d - Tag restrictions
					//         Flag data length       $01

					$thisfile_id3v2['exthead']['length'] = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, 4), true);
					$extended_header_offset += 4;

					$thisfile_id3v2['exthead']['flag_bytes'] = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, 1)); // should always be 1
					$extended_header_offset += 1;

					$thisfile_id3v2['exthead']['flag_raw'] = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, $thisfile_id3v2['exthead']['flag_bytes']));
					$extended_header_offset += $thisfile_id3v2['exthead']['flag_bytes'];

					$thisfile_id3v2['exthead']['flags']['update']       = (bool) ($thisfile_id3v2['exthead']['flag_raw'] & 0x40);
					$thisfile_id3v2['exthead']['flags']['crc']          = (bool) ($thisfile_id3v2['exthead']['flag_raw'] & 0x20);
					$thisfile_id3v2['exthead']['flags']['restrictions'] = (bool) ($thisfile_id3v2['exthead']['flag_raw'] & 0x10);

					if ($thisfile_id3v2['exthead']['flags']['update']) {
						$ext_header_chunk_length = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, 1)); // should be 0
						$extended_header_offset += 1;
					}

					if ($thisfile_id3v2['exthead']['flags']['crc']) {
						$ext_header_chunk_length = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, 1)); // should be 5
						$extended_header_offset += 1;
						$thisfile_id3v2['exthead']['flag_data']['crc'] = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, $ext_header_chunk_length), true, false);
						$extended_header_offset += $ext_header_chunk_length;
					}

					if ($thisfile_id3v2['exthead']['flags']['restrictions']) {
						$ext_header_chunk_length = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, 1)); // should be 1
						$extended_header_offset += 1;

						// %ppqrrstt
						$restrictions_raw = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, 1));
						$extended_header_offset += 1;
						$thisfile_id3v2['exthead']['flags']['restrictions']['tagsize']  = ($restrictions_raw & 0xC0) >> 6; // p - Tag size restrictions
						$thisfile_id3v2['exthead']['flags']['restrictions']['textenc']  = ($restrictions_raw & 0x20) >> 5; // q - Text encoding restrictions
						$thisfile_id3v2['exthead']['flags']['restrictions']['textsize'] = ($restrictions_raw & 0x18) >> 3; // r - Text fields size restrictions
						$thisfile_id3v2['exthead']['flags']['restrictions']['imgenc']   = ($restrictions_raw & 0x04) >> 2; // s - Image encoding restrictions
						$thisfile_id3v2['exthead']['flags']['restrictions']['imgsize']  = ($restrictions_raw & 0x03) >> 0; // t - Image size restrictions

						$thisfile_id3v2['exthead']['flags']['restrictions_text']['tagsize']  = $this->LookupExtendedHeaderRestrictionsTagSizeLimits($thisfile_id3v2['exthead']['flags']['restrictions']['tagsize']);
						$thisfile_id3v2['exthead']['flags']['restrictions_text']['textenc']  = $this->LookupExtendedHeaderRestrictionsTextEncodings($thisfile_id3v2['exthead']['flags']['restrictions']['textenc']);
						$thisfile_id3v2['exthead']['flags']['restrictions_text']['textsize'] = $this->LookupExtendedHeaderRestrictionsTextFieldSize($thisfile_id3v2['exthead']['flags']['restrictions']['textsize']);
						$thisfile_id3v2['exthead']['flags']['restrictions_text']['imgenc']   = $this->LookupExtendedHeaderRestrictionsImageEncoding($thisfile_id3v2['exthead']['flags']['restrictions']['imgenc']);
						$thisfile_id3v2['exthead']['flags']['restrictions_text']['imgsize']  = $this->LookupExtendedHeaderRestrictionsImageSizeSize($thisfile_id3v2['exthead']['flags']['restrictions']['imgsize']);
					}

					if ($thisfile_id3v2['exthead']['length'] != $extended_header_offset) {
						$info['warning'][] = 'ID3v2.4 extended header length mismatch (expecting '.intval($thisfile_id3v2['exthead']['length']).', found '.intval($extended_header_offset).')';
					}
				}

				$framedataoffset += $extended_header_offset;
				$framedata = substr($framedata, $extended_header_offset);
			} // end extended header


			while (isset($framedata) && (strlen($framedata) > 0)) { // cycle through until no more frame data is left to parse
				if (strlen($framedata) <= $this->ID3v2HeaderLength($id3v2_majorversion)) {
					// insufficient room left in ID3v2 header for actual data - must be padding
					$thisfile_id3v2['padding']['start']  = $framedataoffset;
					$thisfile_id3v2['padding']['length'] = strlen($framedata);
					$thisfile_id3v2['padding']['valid']  = true;
					for ($i = 0; $i < $thisfile_id3v2['padding']['length']; $i++) {
						if ($framedata{$i} != "\x00") {
							$thisfile_id3v2['padding']['valid'] = false;
							$thisfile_id3v2['padding']['errorpos'] = $thisfile_id3v2['padding']['start'] + $i;
							$info['warning'][] = 'Invalid ID3v2 padding found at offset '.$thisfile_id3v2['padding']['errorpos'].' (the remaining '.($thisfile_id3v2['padding']['length'] - $i).' bytes are considered invalid)';
							break;
						}
					}
					break; // skip rest of ID3v2 header
				}
				if ($id3v2_majorversion == 2) {
					// Frame ID  $xx xx xx (three characters)
					// Size      $xx xx xx (24-bit integer)
					// Flags     $xx xx

					$frame_header = substr($framedata, 0, 6); // take next 6 bytes for header
					$framedata    = substr($framedata, 6);    // and leave the rest in $framedata
					$frame_name   = substr($frame_header, 0, 3);
					$frame_size   = getid3_lib::BigEndian2Int(substr($frame_header, 3, 3), 0);
					$frame_flags  = 0; // not used for anything in ID3v2.2, just set to avoid E_NOTICEs

				} elseif ($id3v2_majorversion > 2) {

					// Frame ID  $xx xx xx xx (four characters)
					// Size      $xx xx xx xx (32-bit integer in v2.3, 28-bit synchsafe in v2.4+)
					// Flags     $xx xx

					$frame_header = substr($framedata, 0, 10); // take next 10 bytes for header
					$framedata    = substr($framedata, 10);    // and leave the rest in $framedata

					$frame_name = substr($frame_header, 0, 4);
					if ($id3v2_majorversion == 3) {
						$frame_size = getid3_lib::BigEndian2Int(substr($frame_header, 4, 4), 0); // 32-bit integer
					} else { // ID3v2.4+
						$frame_size = getid3_lib::BigEndian2Int(substr($frame_header, 4, 4), 1); // 32-bit synchsafe integer (28-bit value)
					}

					if ($frame_size < (strlen($framedata) + 4)) {
						$nextFrameID = substr($framedata, $frame_size, 4);
						if ($this->IsValidID3v2FrameName($nextFrameID, $id3v2_majorversion)) {
							// next frame is OK
						} elseif (($frame_name == "\x00".'MP3') || ($frame_name == "\x00\x00".'MP') || ($frame_name == ' MP3') || ($frame_name == 'MP3e')) {
							// MP3ext known broken frames - "ok" for the purposes of this test
						} elseif (($id3v2_majorversion == 4) && ($this->IsValidID3v2FrameName(substr($framedata, getid3_lib::BigEndian2Int(substr($frame_header, 4, 4), 0), 4), 3))) {
							$info['warning'][] = 'ID3v2 tag written as ID3v2.4, but with non-synchsafe integers (ID3v2.3 style). Older versions of (Helium2; iTunes) are known culprits of this. Tag has been parsed as ID3v2.3';
							$id3v2_majorversion = 3;
							$frame_size = getid3_lib::BigEndian2Int(substr($frame_header, 4, 4), 0); // 32-bit integer
						}
					}


					$frame_flags = getid3_lib::BigEndian2Int(substr($frame_header, 8, 2));
				}

				if ((($id3v2_majorversion == 2) && ($frame_name == "\x00\x00\x00")) || ($frame_name == "\x00\x00\x00\x00")) {
					// padding encountered

					$thisfile_id3v2['padding']['start']  = $framedataoffset;
					$thisfile_id3v2['padding']['length'] = strlen($frame_header) + strlen($framedata);
					$thisfile_id3v2['padding']['valid']  = true;

					$len = strlen($framedata);
					for ($i = 0; $i < $len; $i++) {
						if ($framedata{$i} != "\x00") {
							$thisfile_id3v2['padding']['valid'] = false;
							$thisfile_id3v2['padding']['errorpos'] = $thisfile_id3v2['padding']['start'] + $i;
							$info['warning'][] = 'Invalid ID3v2 padding found at offset '.$thisfile_id3v2['padding']['errorpos'].' (the remaining '.($thisfile_id3v2['padding']['length'] - $i).' bytes are considered invalid)';
							break;
						}
					}
					break; // skip rest of ID3v2 header
				}

				if ($frame_name == 'COM ') {
					$info['warning'][] = 'error parsing "'.$frame_name.'" ('.$framedataoffset.' bytes into the ID3v2.'.$id3v2_majorversion.' tag). (ERROR: IsValidID3v2FrameName("'.str_replace("\x00", ' ', $frame_name).'", '.$id3v2_majorversion.'))). [Note: this particular error has been known to happen with tags edited by iTunes (versions "X v2.0.3", "v3.0.1" are known-guilty, probably others too)]';
					$frame_name = 'COMM';
				}
				if (($frame_size <= strlen($framedata)) && ($this->IsValidID3v2FrameName($frame_name, $id3v2_majorversion))) {

					unset($parsedFrame);
					$parsedFrame['frame_name']      = $frame_name;
					$parsedFrame['frame_flags_raw'] = $frame_flags;
					$parsedFrame['data']            = substr($framedata, 0, $frame_size);
					$parsedFrame['datalength']      = getid3_lib::CastAsInt($frame_size);
					$parsedFrame['dataoffset']      = $framedataoffset;

					$this->ParseID3v2Frame($parsedFrame);
					$thisfile_id3v2[$frame_name][] = $parsedFrame;

					$framedata = substr($framedata, $frame_size);

				} else { // invalid frame length or FrameID

					if ($frame_size <= strlen($framedata)) {

						if ($this->IsValidID3v2FrameName(substr($framedata, $frame_size, 4), $id3v2_majorversion)) {

							// next frame is valid, just skip the current frame
							$framedata = substr($framedata, $frame_size);
							$info['warning'][] = 'Next ID3v2 frame is valid, skipping current frame.';

						} else {

							// next frame is invalid too, abort processing
							//unset($framedata);
							$framedata = null;
							$info['error'][] = 'Next ID3v2 frame is also invalid, aborting processing.';

						}

					} elseif ($frame_size == strlen($framedata)) {

						// this is the last frame, just skip
						$info['warning'][] = 'This was the last ID3v2 frame.';

					} else {

						// next frame is invalid too, abort processing
						//unset($framedata);
						$framedata = null;
						$info['warning'][] = 'Invalid ID3v2 frame size, aborting.';

					}
					if (!$this->IsValidID3v2FrameName($frame_name, $id3v2_majorversion)) {

						switch ($frame_name) {
							case "\x00\x00".'MP':
							case "\x00".'MP3':
							case ' MP3':
							case 'MP3e':
							case "\x00".'MP':
							case ' MP':
							case 'MP3':
								$info['warning'][] = 'error parsing "'.$frame_name.'" ('.$framedataoffset.' bytes into the ID3v2.'.$id3v2_majorversion.' tag). (ERROR: !IsValidID3v2FrameName("'.str_replace("\x00", ' ', $frame_name).'", '.$id3v2_majorversion.'))). [Note: this particular error has been known to happen with tags edited by "MP3ext (www.mutschler.de/mp3ext/)"]';
								break;

							default:
								$info['warning'][] = 'error parsing "'.$frame_name.'" ('.$framedataoffset.' bytes into the ID3v2.'.$id3v2_majorversion.' tag). (ERROR: !IsValidID3v2FrameName("'.str_replace("\x00", ' ', $frame_name).'", '.$id3v2_majorversion.'))).';
								break;
						}

					} elseif (!isset($framedata) || ($frame_size > strlen($framedata))) {

						$info['error'][] = 'error parsing "'.$frame_name.'" ('.$framedataoffset.' bytes into the ID3v2.'.$id3v2_majorversion.' tag). (ERROR: $frame_size ('.$frame_size.') > strlen($framedata) ('.(isset($framedata) ? strlen($framedata) : 'null').')).';

					} else {

						$info['error'][] = 'error parsing "'.$frame_name.'" ('.$framedataoffset.' bytes into the ID3v2.'.$id3v2_majorversion.' tag).';

					}

				}
				$framedataoffset += ($frame_size + $this->ID3v2HeaderLength($id3v2_majorversion));

			}

		}


	//    Footer

	//    The footer is a copy of the header, but with a different identifier.
	//        ID3v2 identifier           "3DI"
	//        ID3v2 version              $04 00
	//        ID3v2 flags                %abcd0000
	//        ID3v2 size             4 * %0xxxxxxx

		if (isset($thisfile_id3v2_flags['isfooter']) && $thisfile_id3v2_flags['isfooter']) {
			$footer = fread($this->getid3->fp, 10);
			if (substr($footer, 0, 3) == '3DI') {
				$thisfile_id3v2['footer'] = true;
				$thisfile_id3v2['majorversion_footer'] = ord($footer{3});
				$thisfile_id3v2['minorversion_footer'] = ord($footer{4});
			}
			if ($thisfile_id3v2['majorversion_footer'] <= 4) {
				$id3_flags = ord(substr($footer{5}));
				$thisfile_id3v2_flags['unsynch_footer']  = (bool) ($id3_flags & 0x80);
				$thisfile_id3v2_flags['extfoot_footer']  = (bool) ($id3_flags & 0x40);
				$thisfile_id3v2_flags['experim_footer']  = (bool) ($id3_flags & 0x20);
				$thisfile_id3v2_flags['isfooter_footer'] = (bool) ($id3_flags & 0x10);

				$thisfile_id3v2['footerlength'] = getid3_lib::BigEndian2Int(substr($footer, 6, 4), 1);
			}
		} // end footer

		if (isset($thisfile_id3v2['comments']['genre'])) {
			foreach ($thisfile_id3v2['comments']['genre'] as $key => $value) {
				unset($thisfile_id3v2['comments']['genre'][$key]);
				$thisfile_id3v2['comments'] = getid3_lib::array_merge_noclobber($thisfile_id3v2['comments'], array('genre'=>$this->ParseID3v2GenreString($value)));
			}
		}

		if (isset($thisfile_id3v2['comments']['track'])) {
			foreach ($thisfile_id3v2['comments']['track'] as $key => $value) {
				if (strstr($value, '/')) {
					list($thisfile_id3v2['comments']['tracknum'][$key], $thisfile_id3v2['comments']['totaltracks'][$key]) = explode('/', $thisfile_id3v2['comments']['track'][$key]);
				}
			}
		}

		if (!isset($thisfile_id3v2['comments']['year']) && !empty($thisfile_id3v2['comments']['recording_time'][0]) && preg_match('#^([0-9]{4})#', trim($thisfile_id3v2['comments']['recording_time'][0]), $matches)) {
			$thisfile_id3v2['comments']['year'] = array($matches[1]);
		}


		if (!empty($thisfile_id3v2['TXXX'])) {
			// MediaMonkey does this, maybe others: write a blank RGAD frame, but put replay-gain adjustment values in TXXX frames
			foreach ($thisfile_id3v2['TXXX'] as $txxx_array) {
				switch ($txxx_array['description']) {
					case 'replaygain_track_gain':
						if (empty($info['replay_gain']['track']['adjustment']) && !empty($txxx_array['data'])) {
							$info['replay_gain']['track']['adjustment'] = floatval(trim(str_replace('dB', '', $txxx_array['data'])));
						}
						break;
					case 'replaygain_track_peak':
						if (empty($info['replay_gain']['track']['peak']) && !empty($txxx_array['data'])) {
							$info['replay_gain']['track']['peak'] = floatval($txxx_array['data']);
						}
						break;
					case 'replaygain_album_gain':
						if (empty($info['replay_gain']['album']['adjustment']) && !empty($txxx_array['data'])) {
							$info['replay_gain']['album']['adjustment'] = floatval(trim(str_replace('dB', '', $txxx_array['data'])));
						}
						break;
				}
			}
		}


		// Set avdataoffset
		$info['avdataoffset'] = $thisfile_id3v2['headerlength'];
		if (isset($thisfile_id3v2['footer'])) {
			$info['avdataoffset'] += 10;
		}

		return true;
	}


	public function ParseID3v2GenreString($genrestring) {
		// Parse genres into arrays of genreName and genreID
		// ID3v2.2.x, ID3v2.3.x: '(21)' or '(4)Eurodisco' or '(51)(39)' or '(55)((I think...)'
		// ID3v2.4.x: '21' $00 'Eurodisco' $00
		$clean_genres = array();
		if (strpos($genrestring, "\x00") === false) {
			$genrestring = preg_replace('#\(([0-9]{1,3})\)#', '$1'."\x00", $genrestring);
		}
		$genre_elements = explode("\x00", $genrestring);
		foreach ($genre_elements as $element) {
			$element = trim($element);
			if ($element) {
				if (preg_match('#^[0-9]{1,3}#', $element)) {
					$clean_genres[] = getid3_id3v1::LookupGenreName($element);
				} else {
					$clean_genres[] = str_replace('((', '(', $element);
				}
			}
		}
		return $clean_genres;
	}


	public function ParseID3v2Frame(&$parsedFrame) {

		// shortcuts
		$info = &$this->getid3->info;
		$id3v2_majorversion = $info['id3v2']['majorversion'];

		$parsedFrame['framenamelong']  = $this->FrameNameLongLookup($parsedFrame['frame_name']);
		if (empty($parsedFrame['framenamelong'])) {
			unset($parsedFrame['framenamelong']);
		}
		$parsedFrame['framenameshort'] = $this->FrameNameShortLookup($parsedFrame['frame_name']);
		if (empty($parsedFrame['framenameshort'])) {
			unset($parsedFrame['framenameshort']);
		}

		if ($id3v2_majorversion >= 3) { // frame flags are not part of the ID3v2.2 standard
			if ($id3v2_majorversion == 3) {
				//    Frame Header Flags
				//    %abc00000 %ijk00000
				$parsedFrame['flags']['TagAlterPreservation']  = (bool) ($parsedFrame['frame_flags_raw'] & 0x8000); // a - Tag alter preservation
				$parsedFrame['flags']['FileAlterPreservation'] = (bool) ($parsedFrame['frame_flags_raw'] & 0x4000); // b - File alter preservation
				$parsedFrame['flags']['ReadOnly']              = (bool) ($parsedFrame['frame_flags_raw'] & 0x2000); // c - Read only
				$parsedFrame['flags']['compression']           = (bool) ($parsedFrame['frame_flags_raw'] & 0x0080); // i - Compression
				$parsedFrame['flags']['Encryption']            = (bool) ($parsedFrame['frame_flags_raw'] & 0x0040); // j - Encryption
				$parsedFrame['flags']['GroupingIdentity']      = (bool) ($parsedFrame['frame_flags_raw'] & 0x0020); // k - Grouping identity

			} elseif ($id3v2_majorversion == 4) {
				//    Frame Header Flags
				//    %0abc0000 %0h00kmnp
				$parsedFrame['flags']['TagAlterPreservation']  = (bool) ($parsedFrame['frame_flags_raw'] & 0x4000); // a - Tag alter preservation
				$parsedFrame['flags']['FileAlterPreservation'] = (bool) ($parsedFrame['frame_flags_raw'] & 0x2000); // b - File alter preservation
				$parsedFrame['flags']['ReadOnly']              = (bool) ($parsedFrame['frame_flags_raw'] & 0x1000); // c - Read only
				$parsedFrame['flags']['GroupingIdentity']      = (bool) ($parsedFrame['frame_flags_raw'] & 0x0040); // h - Grouping identity
				$parsedFrame['flags']['compression']           = (bool) ($parsedFrame['frame_flags_raw'] & 0x0008); // k - Compression
				$parsedFrame['flags']['Encryption']            = (bool) ($parsedFrame['frame_flags_raw'] & 0x0004); // m - Encryption
				$parsedFrame['flags']['Unsynchronisation']     = (bool) ($parsedFrame['frame_flags_raw'] & 0x0002); // n - Unsynchronisation
				$parsedFrame['flags']['DataLengthIndicator']   = (bool) ($parsedFrame['frame_flags_raw'] & 0x0001); // p - Data length indicator

				// Frame-level de-unsynchronisation - ID3v2.4
				if ($parsedFrame['flags']['Unsynchronisation']) {
					$parsedFrame['data'] = $this->DeUnsynchronise($parsedFrame['data']);
				}

				if ($parsedFrame['flags']['DataLengthIndicator']) {
					$parsedFrame['data_length_indicator'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], 0, 4), 1);
					$parsedFrame['data']                  =                           substr($parsedFrame['data'], 4);
				}
			}

			//    Frame-level de-compression
			if ($parsedFrame['flags']['compression']) {
				$parsedFrame['decompressed_size'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], 0, 4));
				if (!function_exists('gzuncompress')) {
					$info['warning'][] = 'gzuncompress() support required to decompress ID3v2 frame "'.$parsedFrame['frame_name'].'"';
				} else {
					if ($decompresseddata = @gzuncompress(substr($parsedFrame['data'], 4))) {
					//if ($decompresseddata = @gzuncompress($parsedFrame['data'])) {
						$parsedFrame['data'] = $decompresseddata;
						unset($decompresseddata);
					} else {
						$info['warning'][] = 'gzuncompress() failed on compressed contents of ID3v2 frame "'.$parsedFrame['frame_name'].'"';
					}
				}
			}
		}

		if (!empty($parsedFrame['flags']['DataLengthIndicator'])) {
			if ($parsedFrame['data_length_indicator'] != strlen($parsedFrame['data'])) {
				$info['warning'][] = 'ID3v2 frame "'.$parsedFrame['frame_name'].'" should be '.$parsedFrame['data_length_indicator'].' bytes long according to DataLengthIndicator, but found '.strlen($parsedFrame['data']).' bytes of data';
			}
		}

		if (isset($parsedFrame['datalength']) && ($parsedFrame['datalength'] == 0)) {

			$warning = 'Frame "'.$parsedFrame['frame_name'].'" at offset '.$parsedFrame['dataoffset'].' has no data portion';
			switch ($parsedFrame['frame_name']) {
				case 'WCOM':
					$warning .= ' (this is known to happen with files tagged by RioPort)';
					break;

				default:
					break;
			}
			$info['warning'][] = $warning;

		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'UFID')) || // 4.1   UFID Unique file identifier
			(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'UFI'))) {  // 4.1   UFI  Unique file identifier
			//   There may be more than one 'UFID' frame in a tag,
			//   but only one with the same 'Owner identifier'.
			// <Header for 'Unique file identifier', ID: 'UFID'>
			// Owner identifier        <text string> $00
			// Identifier              <up to 64 bytes binary data>
			$exploded = explode("\x00", $parsedFrame['data'], 2);
			$parsedFrame['ownerid'] = (isset($exploded[0]) ? $exploded[0] : '');
			$parsedFrame['data']    = (isset($exploded[1]) ? $exploded[1] : '');

		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'TXXX')) || // 4.2.2 TXXX User defined text information frame
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'TXX'))) {    // 4.2.2 TXX  User defined text information frame
			//   There may be more than one 'TXXX' frame in each tag,
			//   but only one with the same description.
			// <Header for 'User defined text information frame', ID: 'TXXX'>
			// Text encoding     $xx
			// Description       <text string according to encoding> $00 (00)
			// Value             <text string according to encoding>

			$frame_offset = 0;
			$frame_textencoding = ord(substr($parsedFrame['data'], $frame_offset++, 1));

			if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion == 4) && ($frame_textencoding > 3))) {
				$info['warning'][] = 'Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding';
			}
			$frame_terminatorpos = strpos($parsedFrame['data'], $this->TextEncodingTerminatorLookup($frame_textencoding), $frame_offset);
			if (ord(substr($parsedFrame['data'], $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding)), 1)) === 0) {
				$frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
			}
			$frame_description = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			if (ord($frame_description) === 0) {
				$frame_description = '';
			}
			$parsedFrame['encodingid']  = $frame_textencoding;
			$parsedFrame['encoding']    = $this->TextEncodingNameLookup($frame_textencoding);

			$parsedFrame['description'] = $frame_description;
			$parsedFrame['data'] = substr($parsedFrame['data'], $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding)));
			if (!empty($parsedFrame['framenameshort']) && !empty($parsedFrame['data'])) {
				$info['id3v2']['comments'][$parsedFrame['framenameshort']][] = trim(getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $parsedFrame['data']));
			}
			//unset($parsedFrame['data']); do not unset, may be needed elsewhere, e.g. for replaygain


		} elseif ($parsedFrame['frame_name']{0} == 'T') { // 4.2. T??[?] Text information frame
			//   There may only be one text information frame of its kind in an tag.
			// <Header for 'Text information frame', ID: 'T000' - 'TZZZ',
			// excluding 'TXXX' described in 4.2.6.>
			// Text encoding                $xx
			// Information                  <text string(s) according to encoding>

			$frame_offset = 0;
			$frame_textencoding = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion == 4) && ($frame_textencoding > 3))) {
				$info['warning'][] = 'Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding';
			}

			$parsedFrame['data'] = (string) substr($parsedFrame['data'], $frame_offset);

			$parsedFrame['encodingid'] = $frame_textencoding;
			$parsedFrame['encoding']   = $this->TextEncodingNameLookup($frame_textencoding);

			if (!empty($parsedFrame['framenameshort']) && !empty($parsedFrame['data'])) {
				// ID3v2.3 specs say that TPE1 (and others) can contain multiple artist values separated with /
				// This of course breaks when an artist name contains slash character, e.g. "AC/DC"
				// MP3tag (maybe others) implement alternative system where multiple artists are null-separated, which makes more sense
				// getID3 will split null-separated artists into multiple artists and leave slash-separated ones to the user
				switch ($parsedFrame['encoding']) {
					case 'UTF-16':
					case 'UTF-16BE':
					case 'UTF-16LE':
						$wordsize = 2;
						break;
					case 'ISO-8859-1':
					case 'UTF-8':
					default:
						$wordsize = 1;
						break;
				}
				$Txxx_elements = array();
				$Txxx_elements_start_offset = 0;
				for ($i = 0; $i < strlen($parsedFrame['data']); $i += $wordsize) {
					if (substr($parsedFrame['data'], $i, $wordsize) == str_repeat("\x00", $wordsize)) {
						$Txxx_elements[] = substr($parsedFrame['data'], $Txxx_elements_start_offset, $i - $Txxx_elements_start_offset);
						$Txxx_elements_start_offset = $i + $wordsize;
					}
				}
				$Txxx_elements[] = substr($parsedFrame['data'], $Txxx_elements_start_offset, $i - $Txxx_elements_start_offset);
				foreach ($Txxx_elements as $Txxx_element) {
					$string = getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $Txxx_element);
					if (!empty($string)) {
						$info['id3v2']['comments'][$parsedFrame['framenameshort']][] = $string;
					}
				}
				unset($string, $wordsize, $i, $Txxx_elements, $Txxx_element, $Txxx_elements_start_offset);
			}

		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'WXXX')) || // 4.3.2 WXXX User defined URL link frame
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'WXX'))) {    // 4.3.2 WXX  User defined URL link frame
			//   There may be more than one 'WXXX' frame in each tag,
			//   but only one with the same description
			// <Header for 'User defined URL link frame', ID: 'WXXX'>
			// Text encoding     $xx
			// Description       <text string according to encoding> $00 (00)
			// URL               <text string>

			$frame_offset = 0;
			$frame_textencoding = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion == 4) && ($frame_textencoding > 3))) {
				$info['warning'][] = 'Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding';
			}
			$frame_terminatorpos = strpos($parsedFrame['data'], $this->TextEncodingTerminatorLookup($frame_textencoding), $frame_offset);
			if (ord(substr($parsedFrame['data'], $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding)), 1)) === 0) {
				$frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
			}
			$frame_description = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);

			if (ord($frame_description) === 0) {
				$frame_description = '';
			}
			$parsedFrame['data'] = substr($parsedFrame['data'], $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding)));

			$frame_terminatorpos = strpos($parsedFrame['data'], $this->TextEncodingTerminatorLookup($frame_textencoding));
			if (ord(substr($parsedFrame['data'], $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding)), 1)) === 0) {
				$frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
			}
			if ($frame_terminatorpos) {
				// there are null bytes after the data - this is not according to spec
				// only use data up to first null byte
				$frame_urldata = (string) substr($parsedFrame['data'], 0, $frame_terminatorpos);
			} else {
				// no null bytes following data, just use all data
				$frame_urldata = (string) $parsedFrame['data'];
			}

			$parsedFrame['encodingid']  = $frame_textencoding;
			$parsedFrame['encoding']    = $this->TextEncodingNameLookup($frame_textencoding);

			$parsedFrame['url']         = $frame_urldata;
			$parsedFrame['description'] = $frame_description;
			if (!empty($parsedFrame['framenameshort']) && $parsedFrame['url']) {
				$info['id3v2']['comments'][$parsedFrame['framenameshort']][] = getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $parsedFrame['url']);
			}
			unset($parsedFrame['data']);


		} elseif ($parsedFrame['frame_name']{0} == 'W') { // 4.3. W??? URL link frames
			//   There may only be one URL link frame of its kind in a tag,
			//   except when stated otherwise in the frame description
			// <Header for 'URL link frame', ID: 'W000' - 'WZZZ', excluding 'WXXX'
			// described in 4.3.2.>
			// URL              <text string>

			$parsedFrame['url'] = trim($parsedFrame['data']);
			if (!empty($parsedFrame['framenameshort']) && $parsedFrame['url']) {
				$info['id3v2']['comments'][$parsedFrame['framenameshort']][] = $parsedFrame['url'];
			}
			unset($parsedFrame['data']);


		} elseif ((($id3v2_majorversion == 3) && ($parsedFrame['frame_name'] == 'IPLS')) || // 4.4  IPLS Involved people list (ID3v2.3 only)
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'IPL'))) {     // 4.4  IPL  Involved people list (ID3v2.2 only)
			// http://id3.org/id3v2.3.0#sec4.4
			//   There may only be one 'IPL' frame in each tag
			// <Header for 'User defined URL link frame', ID: 'IPL'>
			// Text encoding     $xx
			// People list strings    <textstrings>

			$frame_offset = 0;
			$frame_textencoding = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion == 4) && ($frame_textencoding > 3))) {
				$info['warning'][] = 'Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding';
			}
			$parsedFrame['encodingid'] = $frame_textencoding;
			$parsedFrame['encoding']   = $this->TextEncodingNameLookup($parsedFrame['encodingid']);
			$parsedFrame['data_raw']   = (string) substr($parsedFrame['data'], $frame_offset);

			// http://www.getid3.org/phpBB3/viewtopic.php?t=1369
			// "this tag typically contains null terminated strings, which are associated in pairs"
			// "there are users that use the tag incorrectly"
			$IPLS_parts = array();
			if (strpos($parsedFrame['data_raw'], "\x00") !== false) {
				$IPLS_parts_unsorted = array();
				if (((strlen($parsedFrame['data_raw']) % 2) == 0) && ((substr($parsedFrame['data_raw'], 0, 2) == "\xFF\xFE") || (substr($parsedFrame['data_raw'], 0, 2) == "\xFE\xFF"))) {
					// UTF-16, be careful looking for null bytes since most 2-byte characters may contain one; you need to find twin null bytes, and on even padding
					$thisILPS  = '';
					for ($i = 0; $i < strlen($parsedFrame['data_raw']); $i += 2) {
						$twobytes = substr($parsedFrame['data_raw'], $i, 2);
						if ($twobytes === "\x00\x00") {
							$IPLS_parts_unsorted[] = getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $thisILPS);
							$thisILPS  = '';
						} else {
							$thisILPS .= $twobytes;
						}
					}
					if (strlen($thisILPS) > 2) { // 2-byte BOM
						$IPLS_parts_unsorted[] = getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $thisILPS);
					}
				} else {
					// ISO-8859-1 or UTF-8 or other single-byte-null character set
					$IPLS_parts_unsorted = explode("\x00", $parsedFrame['data_raw']);
				}
				if (count($IPLS_parts_unsorted) == 1) {
					// just a list of names, e.g. "Dino Baptiste, Jimmy Copley, John Gordon, Bernie Marsden, Sharon Watson"
					foreach ($IPLS_parts_unsorted as $key => $value) {
						$IPLS_parts_sorted = preg_split('#[;,\\r\\n\\t]#', $value);
						$position = '';
						foreach ($IPLS_parts_sorted as $person) {
							$IPLS_parts[] = array('position'=>$position, 'person'=>$person);
						}
					}
				} elseif ((count($IPLS_parts_unsorted) % 2) == 0) {
					$position = '';
					$person   = '';
					foreach ($IPLS_parts_unsorted as $key => $value) {
						if (($key % 2) == 0) {
							$position = $value;
						} else {
							$person   = $value;
							$IPLS_parts[] = array('position'=>$position, 'person'=>$person);
							$position = '';
							$person   = '';
						}
					}
				} else {
					foreach ($IPLS_parts_unsorted as $key => $value) {
						$IPLS_parts[] = array($value);
					}
				}

			} else {
				$IPLS_parts = preg_split('#[;,\\r\\n\\t]#', $parsedFrame['data_raw']);
			}
			$parsedFrame['data'] = $IPLS_parts;

			if (!empty($parsedFrame['framenameshort']) && !empty($parsedFrame['data'])) {
				$info['id3v2']['comments'][$parsedFrame['framenameshort']][] = $parsedFrame['data'];
			}


		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'MCDI')) || // 4.4   MCDI Music CD identifier
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'MCI'))) {     // 4.5   MCI  Music CD identifier
			//   There may only be one 'MCDI' frame in each tag
			// <Header for 'Music CD identifier', ID: 'MCDI'>
			// CD TOC                <binary data>

			if (!empty($parsedFrame['framenameshort']) && !empty($parsedFrame['data'])) {
				$info['id3v2']['comments'][$parsedFrame['framenameshort']][] = $parsedFrame['data'];
			}


		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'ETCO')) || // 4.5   ETCO Event timing codes
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'ETC'))) {     // 4.6   ETC  Event timing codes
			//   There may only be one 'ETCO' frame in each tag
			// <Header for 'Event timing codes', ID: 'ETCO'>
			// Time stamp format    $xx
			//   Where time stamp format is:
			// $01  (32-bit value) MPEG frames from beginning of file
			// $02  (32-bit value) milliseconds from beginning of file
			//   Followed by a list of key events in the following format:
			// Type of event   $xx
			// Time stamp      $xx (xx ...)
			//   The 'Time stamp' is set to zero if directly at the beginning of the sound
			//   or after the previous event. All events MUST be sorted in chronological order.

			$frame_offset = 0;
			$parsedFrame['timestampformat'] = ord(substr($parsedFrame['data'], $frame_offset++, 1));

			while ($frame_offset < strlen($parsedFrame['data'])) {
				$parsedFrame['typeid']    = substr($parsedFrame['data'], $frame_offset++, 1);
				$parsedFrame['type']      = $this->ETCOEventLookup($parsedFrame['typeid']);
				$parsedFrame['timestamp'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));
				$frame_offset += 4;
			}
			unset($parsedFrame['data']);


		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'MLLT')) || // 4.6   MLLT MPEG location lookup table
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'MLL'))) {     // 4.7   MLL MPEG location lookup table
			//   There may only be one 'MLLT' frame in each tag
			// <Header for 'Location lookup table', ID: 'MLLT'>
			// MPEG frames between reference  $xx xx
			// Bytes between reference        $xx xx xx
			// Milliseconds between reference $xx xx xx
			// Bits for bytes deviation       $xx
			// Bits for milliseconds dev.     $xx
			//   Then for every reference the following data is included;
			// Deviation in bytes         %xxx....
			// Deviation in milliseconds  %xxx....

			$frame_offset = 0;
			$parsedFrame['framesbetweenreferences'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], 0, 2));
			$parsedFrame['bytesbetweenreferences']  = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], 2, 3));
			$parsedFrame['msbetweenreferences']     = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], 5, 3));
			$parsedFrame['bitsforbytesdeviation']   = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], 8, 1));
			$parsedFrame['bitsformsdeviation']      = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], 9, 1));
			$parsedFrame['data'] = substr($parsedFrame['data'], 10);
			while ($frame_offset < strlen($parsedFrame['data'])) {
				$deviationbitstream .= getid3_lib::BigEndian2Bin(substr($parsedFrame['data'], $frame_offset++, 1));
			}
			$reference_counter = 0;
			while (strlen($deviationbitstream) > 0) {
				$parsedFrame[$reference_counter]['bytedeviation'] = bindec(substr($deviationbitstream, 0, $parsedFrame['bitsforbytesdeviation']));
				$parsedFrame[$reference_counter]['msdeviation']   = bindec(substr($deviationbitstream, $parsedFrame['bitsforbytesdeviation'], $parsedFrame['bitsformsdeviation']));
				$deviationbitstream = substr($deviationbitstream, $parsedFrame['bitsforbytesdeviation'] + $parsedFrame['bitsformsdeviation']);
				$reference_counter++;
			}
			unset($parsedFrame['data']);


		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'SYTC')) || // 4.7   SYTC Synchronised tempo codes
				  (($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'STC'))) {  // 4.8   STC  Synchronised tempo codes
			//   There may only be one 'SYTC' frame in each tag
			// <Header for 'Synchronised tempo codes', ID: 'SYTC'>
			// Time stamp format   $xx
			// Tempo data          <binary data>
			//   Where time stamp format is:
			// $01  (32-bit value) MPEG frames from beginning of file
			// $02  (32-bit value) milliseconds from beginning of file

			$frame_offset = 0;
			$parsedFrame['timestampformat'] = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$timestamp_counter = 0;
			while ($frame_offset < strlen($parsedFrame['data'])) {
				$parsedFrame[$timestamp_counter]['tempo'] = ord(substr($parsedFrame['data'], $frame_offset++, 1));
				if ($parsedFrame[$timestamp_counter]['tempo'] == 255) {
					$parsedFrame[$timestamp_counter]['tempo'] += ord(substr($parsedFrame['data'], $frame_offset++, 1));
				}
				$parsedFrame[$timestamp_counter]['timestamp'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));
				$frame_offset += 4;
				$timestamp_counter++;
			}
			unset($parsedFrame['data']);


		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'USLT')) || // 4.8   USLT Unsynchronised lyric/text transcription
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'ULT'))) {     // 4.9   ULT  Unsynchronised lyric/text transcription
			//   There may be more than one 'Unsynchronised lyrics/text transcription' frame
			//   in each tag, but only one with the same language and content descriptor.
			// <Header for 'Unsynchronised lyrics/text transcription', ID: 'USLT'>
			// Text encoding        $xx
			// Language             $xx xx xx
			// Content descriptor   <text string according to encoding> $00 (00)
			// Lyrics/text          <full text string according to encoding>

			$frame_offset = 0;
			$frame_textencoding = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion == 4) && ($frame_textencoding > 3))) {
				$info['warning'][] = 'Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding';
			}
			$frame_language = substr($parsedFrame['data'], $frame_offset, 3);
			$frame_offset += 3;
			$frame_terminatorpos = strpos($parsedFrame['data'], $this->TextEncodingTerminatorLookup($frame_textencoding), $frame_offset);
			if (ord(substr($parsedFrame['data'], $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding)), 1)) === 0) {
				$frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
			}
			$frame_description = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			if (ord($frame_description) === 0) {
				$frame_description = '';
			}
			$parsedFrame['data'] = substr($parsedFrame['data'], $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding)));

			$parsedFrame['encodingid']   = $frame_textencoding;
			$parsedFrame['encoding']     = $this->TextEncodingNameLookup($frame_textencoding);

			$parsedFrame['data']         = $parsedFrame['data'];
			$parsedFrame['language']     = $frame_language;
			$parsedFrame['languagename'] = $this->LanguageLookup($frame_language, false);
			$parsedFrame['description']  = $frame_description;
			if (!empty($parsedFrame['framenameshort']) && !empty($parsedFrame['data'])) {
				$info['id3v2']['comments'][$parsedFrame['framenameshort']][] = getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $parsedFrame['data']);
			}
			unset($parsedFrame['data']);


		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'SYLT')) || // 4.9   SYLT Synchronised lyric/text
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'SLT'))) {     // 4.10  SLT  Synchronised lyric/text
			//   There may be more than one 'SYLT' frame in each tag,
			//   but only one with the same language and content descriptor.
			// <Header for 'Synchronised lyrics/text', ID: 'SYLT'>
			// Text encoding        $xx
			// Language             $xx xx xx
			// Time stamp format    $xx
			//   $01  (32-bit value) MPEG frames from beginning of file
			//   $02  (32-bit value) milliseconds from beginning of file
			// Content type         $xx
			// Content descriptor   <text string according to encoding> $00 (00)
			//   Terminated text to be synced (typically a syllable)
			//   Sync identifier (terminator to above string)   $00 (00)
			//   Time stamp                                     $xx (xx ...)

			$frame_offset = 0;
			$frame_textencoding = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion == 4) && ($frame_textencoding > 3))) {
				$info['warning'][] = 'Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding';
			}
			$frame_language = substr($parsedFrame['data'], $frame_offset, 3);
			$frame_offset += 3;
			$parsedFrame['timestampformat'] = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$parsedFrame['contenttypeid']   = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$parsedFrame['contenttype']     = $this->SYTLContentTypeLookup($parsedFrame['contenttypeid']);
			$parsedFrame['encodingid']      = $frame_textencoding;
			$parsedFrame['encoding']        = $this->TextEncodingNameLookup($frame_textencoding);

			$parsedFrame['language']        = $frame_language;
			$parsedFrame['languagename']    = $this->LanguageLookup($frame_language, false);

			$timestampindex = 0;
			$frame_remainingdata = substr($parsedFrame['data'], $frame_offset);
			while (strlen($frame_remainingdata)) {
				$frame_offset = 0;
				$frame_terminatorpos = strpos($frame_remainingdata, $this->TextEncodingTerminatorLookup($frame_textencoding));
				if ($frame_terminatorpos === false) {
					$frame_remainingdata = '';
				} else {
					if (ord(substr($frame_remainingdata, $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding)), 1)) === 0) {
						$frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
					}
					$parsedFrame['lyrics'][$timestampindex]['data'] = substr($frame_remainingdata, $frame_offset, $frame_terminatorpos - $frame_offset);

					$frame_remainingdata = substr($frame_remainingdata, $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding)));
					if (($timestampindex == 0) && (ord($frame_remainingdata{0}) != 0)) {
						// timestamp probably omitted for first data item
					} else {
						$parsedFrame['lyrics'][$timestampindex]['timestamp'] = getid3_lib::BigEndian2Int(substr($frame_remainingdata, 0, 4));
						$frame_remainingdata = substr($frame_remainingdata, 4);
					}
					$timestampindex++;
				}
			}
			unset($parsedFrame['data']);


		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'COMM')) || // 4.10  COMM Comments
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'COM'))) {     // 4.11  COM  Comments
			//   There may be more than one comment frame in each tag,
			//   but only one with the same language and content descriptor.
			// <Header for 'Comment', ID: 'COMM'>
			// Text encoding          $xx
			// Language               $xx xx xx
			// Short content descrip. <text string according to encoding> $00 (00)
			// The actual text        <full text string according to encoding>

			if (strlen($parsedFrame['data']) < 5) {

				$info['warning'][] = 'Invalid data (too short) for "'.$parsedFrame['frame_name'].'" frame at offset '.$parsedFrame['dataoffset'];

			} else {

				$frame_offset = 0;
				$frame_textencoding = ord(substr($parsedFrame['data'], $frame_offset++, 1));
				if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion == 4) && ($frame_textencoding > 3))) {
					$info['warning'][] = 'Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding';
				}
				$frame_language = substr($parsedFrame['data'], $frame_offset, 3);
				$frame_offset += 3;
				$frame_terminatorpos = strpos($parsedFrame['data'], $this->TextEncodingTerminatorLookup($frame_textencoding), $frame_offset);
				if (ord(substr($parsedFrame['data'], $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding)), 1)) === 0) {
					$frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
				}
				$frame_description = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
				if (ord($frame_description) === 0) {
					$frame_description = '';
				}
				$frame_text = (string) substr($parsedFrame['data'], $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding)));

				$parsedFrame['encodingid']   = $frame_textencoding;
				$parsedFrame['encoding']     = $this->TextEncodingNameLookup($frame_textencoding);

				$parsedFrame['language']     = $frame_language;
				$parsedFrame['languagename'] = $this->LanguageLookup($frame_language, false);
				$parsedFrame['description']  = $frame_description;
				$parsedFrame['data']         = $frame_text;
				if (!empty($parsedFrame['framenameshort']) && !empty($parsedFrame['data'])) {
					$info['id3v2']['comments'][$parsedFrame['framenameshort']][] = getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $parsedFrame['data']);
				}

			}

		} elseif (($id3v2_majorversion >= 4) && ($parsedFrame['frame_name'] == 'RVA2')) { // 4.11  RVA2 Relative volume adjustment (2) (ID3v2.4+ only)
			//   There may be more than one 'RVA2' frame in each tag,
			//   but only one with the same identification string
			// <Header for 'Relative volume adjustment (2)', ID: 'RVA2'>
			// Identification          <text string> $00
			//   The 'identification' string is used to identify the situation and/or
			//   device where this adjustment should apply. The following is then
			//   repeated for every channel:
			// Type of channel         $xx
			// Volume adjustment       $xx xx
			// Bits representing peak  $xx
			// Peak volume             $xx (xx ...)

			$frame_terminatorpos = strpos($parsedFrame['data'], "\x00");
			$frame_idstring = substr($parsedFrame['data'], 0, $frame_terminatorpos);
			if (ord($frame_idstring) === 0) {
				$frame_idstring = '';
			}
			$frame_remainingdata = substr($parsedFrame['data'], $frame_terminatorpos + strlen("\x00"));
			$parsedFrame['description'] = $frame_idstring;
			$RVA2channelcounter = 0;
			while (strlen($frame_remainingdata) >= 5) {
				$frame_offset = 0;
				$frame_channeltypeid = ord(substr($frame_remainingdata, $frame_offset++, 1));
				$parsedFrame[$RVA2channelcounter]['channeltypeid']  = $frame_channeltypeid;
				$parsedFrame[$RVA2channelcounter]['channeltype']    = $this->RVA2ChannelTypeLookup($frame_channeltypeid);
				$parsedFrame[$RVA2channelcounter]['volumeadjust']   = getid3_lib::BigEndian2Int(substr($frame_remainingdata, $frame_offset, 2), false, true); // 16-bit signed
				$frame_offset += 2;
				$parsedFrame[$RVA2channelcounter]['bitspeakvolume'] = ord(substr($frame_remainingdata, $frame_offset++, 1));
				if (($parsedFrame[$RVA2channelcounter]['bitspeakvolume'] < 1) || ($parsedFrame[$RVA2channelcounter]['bitspeakvolume'] > 4)) {
					$info['warning'][] = 'ID3v2::RVA2 frame['.$RVA2channelcounter.'] contains invalid '.$parsedFrame[$RVA2channelcounter]['bitspeakvolume'].'-byte bits-representing-peak value';
					break;
				}
				$frame_bytespeakvolume = ceil($parsedFrame[$RVA2channelcounter]['bitspeakvolume'] / 8);
				$parsedFrame[$RVA2channelcounter]['peakvolume']     = getid3_lib::BigEndian2Int(substr($frame_remainingdata, $frame_offset, $frame_bytespeakvolume));
				$frame_remainingdata = substr($frame_remainingdata, $frame_offset + $frame_bytespeakvolume);
				$RVA2channelcounter++;
			}
			unset($parsedFrame['data']);


		} elseif ((($id3v2_majorversion == 3) && ($parsedFrame['frame_name'] == 'RVAD')) || // 4.12  RVAD Relative volume adjustment (ID3v2.3 only)
				  (($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'RVA'))) {  // 4.12  RVA  Relative volume adjustment (ID3v2.2 only)
			//   There may only be one 'RVA' frame in each tag
			// <Header for 'Relative volume adjustment', ID: 'RVA'>
			// ID3v2.2 => Increment/decrement     %000000ba
			// ID3v2.3 => Increment/decrement     %00fedcba
			// Bits used for volume descr.        $xx
			// Relative volume change, right      $xx xx (xx ...) // a
			// Relative volume change, left       $xx xx (xx ...) // b
			// Peak volume right                  $xx xx (xx ...)
			// Peak volume left                   $xx xx (xx ...)
			//   ID3v2.3 only, optional (not present in ID3v2.2):
			// Relative volume change, right back $xx xx (xx ...) // c
			// Relative volume change, left back  $xx xx (xx ...) // d
			// Peak volume right back             $xx xx (xx ...)
			// Peak volume left back              $xx xx (xx ...)
			//   ID3v2.3 only, optional (not present in ID3v2.2):
			// Relative volume change, center     $xx xx (xx ...) // e
			// Peak volume center                 $xx xx (xx ...)
			//   ID3v2.3 only, optional (not present in ID3v2.2):
			// Relative volume change, bass       $xx xx (xx ...) // f
			// Peak volume bass                   $xx xx (xx ...)

			$frame_offset = 0;
			$frame_incrdecrflags = getid3_lib::BigEndian2Bin(substr($parsedFrame['data'], $frame_offset++, 1));
			$parsedFrame['incdec']['right'] = (bool) substr($frame_incrdecrflags, 6, 1);
			$parsedFrame['incdec']['left']  = (bool) substr($frame_incrdecrflags, 7, 1);
			$parsedFrame['bitsvolume'] = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$frame_bytesvolume = ceil($parsedFrame['bitsvolume'] / 8);
			$parsedFrame['volumechange']['right'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
			if ($parsedFrame['incdec']['right'] === false) {
				$parsedFrame['volumechange']['right'] *= -1;
			}
			$frame_offset += $frame_bytesvolume;
			$parsedFrame['volumechange']['left'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
			if ($parsedFrame['incdec']['left'] === false) {
				$parsedFrame['volumechange']['left'] *= -1;
			}
			$frame_offset += $frame_bytesvolume;
			$parsedFrame['peakvolume']['right'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
			$frame_offset += $frame_bytesvolume;
			$parsedFrame['peakvolume']['left']  = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
			$frame_offset += $frame_bytesvolume;
			if ($id3v2_majorversion == 3) {
				$parsedFrame['data'] = substr($parsedFrame['data'], $frame_offset);
				if (strlen($parsedFrame['data']) > 0) {
					$parsedFrame['incdec']['rightrear'] = (bool) substr($frame_incrdecrflags, 4, 1);
					$parsedFrame['incdec']['leftrear']  = (bool) substr($frame_incrdecrflags, 5, 1);
					$parsedFrame['volumechange']['rightrear'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
					if ($parsedFrame['incdec']['rightrear'] === false) {
						$parsedFrame['volumechange']['rightrear'] *= -1;
					}
					$frame_offset += $frame_bytesvolume;
					$parsedFrame['volumechange']['leftrear'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
					if ($parsedFrame['incdec']['leftrear'] === false) {
						$parsedFrame['volumechange']['leftrear'] *= -1;
					}
					$frame_offset += $frame_bytesvolume;
					$parsedFrame['peakvolume']['rightrear'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
					$frame_offset += $frame_bytesvolume;
					$parsedFrame['peakvolume']['leftrear']  = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
					$frame_offset += $frame_bytesvolume;
				}
				$parsedFrame['data'] = substr($parsedFrame['data'], $frame_offset);
				if (strlen($parsedFrame['data']) > 0) {
					$parsedFrame['incdec']['center'] = (bool) substr($frame_incrdecrflags, 3, 1);
					$parsedFrame['volumechange']['center'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
					if ($parsedFrame['incdec']['center'] === false) {
						$parsedFrame['volumechange']['center'] *= -1;
					}
					$frame_offset += $frame_bytesvolume;
					$parsedFrame['peakvolume']['center'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
					$frame_offset += $frame_bytesvolume;
				}
				$parsedFrame['data'] = substr($parsedFrame['data'], $frame_offset);
				if (strlen($parsedFrame['data']) > 0) {
					$parsedFrame['incdec']['bass'] = (bool) substr($frame_incrdecrflags, 2, 1);
					$parsedFrame['volumechange']['bass'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
					if ($parsedFrame['incdec']['bass'] === false) {
						$parsedFrame['volumechange']['bass'] *= -1;
					}
					$frame_offset += $frame_bytesvolume;
					$parsedFrame['peakvolume']['bass'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
					$frame_offset += $frame_bytesvolume;
				}
			}
			unset($parsedFrame['data']);


		} elseif (($id3v2_majorversion >= 4) && ($parsedFrame['frame_name'] == 'EQU2')) { // 4.12  EQU2 Equalisation (2) (ID3v2.4+ only)
			//   There may be more than one 'EQU2' frame in each tag,
			//   but only one with the same identification string
			// <Header of 'Equalisation (2)', ID: 'EQU2'>
			// Interpolation method  $xx
			//   $00  Band
			//   $01  Linear
			// Identification        <text string> $00
			//   The following is then repeated for every adjustment point
			// Frequency          $xx xx
			// Volume adjustment  $xx xx

			$frame_offset = 0;
			$frame_interpolationmethod = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
			$frame_idstring = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			if (ord($frame_idstring) === 0) {
				$frame_idstring = '';
			}
			$parsedFrame['description'] = $frame_idstring;
			$frame_remainingdata = substr($parsedFrame['data'], $frame_terminatorpos + strlen("\x00"));
			while (strlen($frame_remainingdata)) {
				$frame_frequency = getid3_lib::BigEndian2Int(substr($frame_remainingdata, 0, 2)) / 2;
				$parsedFrame['data'][$frame_frequency] = getid3_lib::BigEndian2Int(substr($frame_remainingdata, 2, 2), false, true);
				$frame_remainingdata = substr($frame_remainingdata, 4);
			}
			$parsedFrame['interpolationmethod'] = $frame_interpolationmethod;
			unset($parsedFrame['data']);


		} elseif ((($id3v2_majorversion == 3) && ($parsedFrame['frame_name'] == 'EQUA')) || // 4.12  EQUA Equalisation (ID3v2.3 only)
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'EQU'))) {     // 4.13  EQU  Equalisation (ID3v2.2 only)
			//   There may only be one 'EQUA' frame in each tag
			// <Header for 'Relative volume adjustment', ID: 'EQU'>
			// Adjustment bits    $xx
			//   This is followed by 2 bytes + ('adjustment bits' rounded up to the
			//   nearest byte) for every equalisation band in the following format,
			//   giving a frequency range of 0 - 32767Hz:
			// Increment/decrement   %x (MSB of the Frequency)
			// Frequency             (lower 15 bits)
			// Adjustment            $xx (xx ...)

			$frame_offset = 0;
			$parsedFrame['adjustmentbits'] = substr($parsedFrame['data'], $frame_offset++, 1);
			$frame_adjustmentbytes = ceil($parsedFrame['adjustmentbits'] / 8);

			$frame_remainingdata = (string) substr($parsedFrame['data'], $frame_offset);
			while (strlen($frame_remainingdata) > 0) {
				$frame_frequencystr = getid3_lib::BigEndian2Bin(substr($frame_remainingdata, 0, 2));
				$frame_incdec    = (bool) substr($frame_frequencystr, 0, 1);
				$frame_frequency = bindec(substr($frame_frequencystr, 1, 15));
				$parsedFrame[$frame_frequency]['incdec'] = $frame_incdec;
				$parsedFrame[$frame_frequency]['adjustment'] = getid3_lib::BigEndian2Int(substr($frame_remainingdata, 2, $frame_adjustmentbytes));
				if ($parsedFrame[$frame_frequency]['incdec'] === false) {
					$parsedFrame[$frame_frequency]['adjustment'] *= -1;
				}
				$frame_remainingdata = substr($frame_remainingdata, 2 + $frame_adjustmentbytes);
			}
			unset($parsedFrame['data']);


		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'RVRB')) || // 4.13  RVRB Reverb
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'REV'))) {     // 4.14  REV  Reverb
			//   There may only be one 'RVRB' frame in each tag.
			// <Header for 'Reverb', ID: 'RVRB'>
			// Reverb left (ms)                 $xx xx
			// Reverb right (ms)                $xx xx
			// Reverb bounces, left             $xx
			// Reverb bounces, right            $xx
			// Reverb feedback, left to left    $xx
			// Reverb feedback, left to right   $xx
			// Reverb feedback, right to right  $xx
			// Reverb feedback, right to left   $xx
			// Premix left to right             $xx
			// Premix right to left             $xx

			$frame_offset = 0;
			$parsedFrame['left']  = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 2));
			$frame_offset += 2;
			$parsedFrame['right'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 2));
			$frame_offset += 2;
			$parsedFrame['bouncesL']      = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$parsedFrame['bouncesR']      = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$parsedFrame['feedbackLL']    = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$parsedFrame['feedbackLR']    = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$parsedFrame['feedbackRR']    = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$parsedFrame['feedbackRL']    = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$parsedFrame['premixLR']      = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$parsedFrame['premixRL']      = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			unset($parsedFrame['data']);


		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'APIC')) || // 4.14  APIC Attached picture
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'PIC'))) {     // 4.15  PIC  Attached picture
			//   There may be several pictures attached to one file,
			//   each in their individual 'APIC' frame, but only one
			//   with the same content descriptor
			// <Header for 'Attached picture', ID: 'APIC'>
			// Text encoding      $xx
			// ID3v2.3+ => MIME type          <text string> $00
			// ID3v2.2  => Image format       $xx xx xx
			// Picture type       $xx
			// Description        <text string according to encoding> $00 (00)
			// Picture data       <binary data>

			$frame_offset = 0;
			$frame_textencoding = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion == 4) && ($frame_textencoding > 3))) {
				$info['warning'][] = 'Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding';
			}

			if ($id3v2_majorversion == 2 && strlen($parsedFrame['data']) > $frame_offset) {
				$frame_imagetype = substr($parsedFrame['data'], $frame_offset, 3);
				if (strtolower($frame_imagetype) == 'ima') {
					// complete hack for mp3Rage (www.chaoticsoftware.com) that puts ID3v2.3-formatted
					// MIME type instead of 3-char ID3v2.2-format image type  (thanks xbhoffØpacbell*net)
					$frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
					$frame_mimetype = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
					if (ord($frame_mimetype) === 0) {
						$frame_mimetype = '';
					}
					$frame_imagetype = strtoupper(str_replace('image/', '', strtolower($frame_mimetype)));
					if ($frame_imagetype == 'JPEG') {
						$frame_imagetype = 'JPG';
					}
					$frame_offset = $frame_terminatorpos + strlen("\x00");
				} else {
					$frame_offset += 3;
				}
			}
			if ($id3v2_majorversion > 2 && strlen($parsedFrame['data']) > $frame_offset) {
				$frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
				$frame_mimetype = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
				if (ord($frame_mimetype) === 0) {
					$frame_mimetype = '';
				}
				$frame_offset = $frame_terminatorpos + strlen("\x00");
			}

			$frame_picturetype = ord(substr($parsedFrame['data'], $frame_offset++, 1));

			if ($frame_offset >= $parsedFrame['datalength']) {
				$info['warning'][] = 'data portion of APIC frame is missing at offset '.($parsedFrame['dataoffset'] + 8 + $frame_offset);
			} else {
				$frame_terminatorpos = strpos($parsedFrame['data'], $this->TextEncodingTerminatorLookup($frame_textencoding), $frame_offset);
				if (ord(substr($parsedFrame['data'], $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding)), 1)) === 0) {
					$frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
				}
				$frame_description = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
				if (ord($frame_description) === 0) {
					$frame_description = '';
				}
				$parsedFrame['encodingid']       = $frame_textencoding;
				$parsedFrame['encoding']         = $this->TextEncodingNameLookup($frame_textencoding);

				if ($id3v2_majorversion == 2) {
					$parsedFrame['imagetype']    = $frame_imagetype;
				} else {
					$parsedFrame['mime']         = $frame_mimetype;
				}
				$parsedFrame['picturetypeid']    = $frame_picturetype;
				$parsedFrame['picturetype']      = $this->APICPictureTypeLookup($frame_picturetype);
				$parsedFrame['description']      = $frame_description;
				$parsedFrame['data']             = substr($parsedFrame['data'], $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding)));
				$parsedFrame['datalength']       = strlen($parsedFrame['data']);

				$parsedFrame['image_mime'] = '';
				$imageinfo = array();
				$imagechunkcheck = getid3_lib::GetDataImageSize($parsedFrame['data'], $imageinfo);
				if (($imagechunkcheck[2] >= 1) && ($imagechunkcheck[2] <= 3)) {
					$parsedFrame['image_mime']       = 'image/'.getid3_lib::ImageTypesLookup($imagechunkcheck[2]);
					if ($imagechunkcheck[0]) {
						$parsedFrame['image_width']  = $imagechunkcheck[0];
					}
					if ($imagechunkcheck[1]) {
						$parsedFrame['image_height'] = $imagechunkcheck[1];
					}
				}

				do {
					if ($this->getid3->option_save_attachments === false) {
						// skip entirely
						unset($parsedFrame['data']);
						break;
					}
					if ($this->getid3->option_save_attachments === true) {
						// great
/*
					} elseif (is_int($this->getid3->option_save_attachments)) {
						if ($this->getid3->option_save_attachments < $parsedFrame['data_length']) {
							// too big, skip
							$info['warning'][] = 'attachment at '.$frame_offset.' is too large to process inline ('.number_format($parsedFrame['data_length']).' bytes)';
							unset($parsedFrame['data']);
							break;
						}
*/
					} elseif (is_string($this->getid3->option_save_attachments)) {
						$dir = rtrim(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $this->getid3->option_save_attachments), DIRECTORY_SEPARATOR);
						if (!is_dir($dir) || !is_writable($dir)) {
							// cannot write, skip
							$info['warning'][] = 'attachment at '.$frame_offset.' cannot be saved to "'.$dir.'" (not writable)';
							unset($parsedFrame['data']);
							break;
						}
					}
					// if we get this far, must be OK
					if (is_string($this->getid3->option_save_attachments)) {
						$destination_filename = $dir.DIRECTORY_SEPARATOR.md5($info['filenamepath']).'_'.$frame_offset;
						if (!file_exists($destination_filename) || is_writable($destination_filename)) {
							file_put_contents($destination_filename, $parsedFrame['data']);
						} else {
							$info['warning'][] = 'attachment at '.$frame_offset.' cannot be saved to "'.$destination_filename.'" (not writable)';
						}
						$parsedFrame['data_filename'] = $destination_filename;
						unset($parsedFrame['data']);
					} else {
						if (!empty($parsedFrame['framenameshort']) && !empty($parsedFrame['data'])) {
							if (!isset($info['id3v2']['comments']['picture'])) {
								$info['id3v2']['comments']['picture'] = array();
							}
							$info['id3v2']['comments']['picture'][] = array('data'=>$parsedFrame['data'], 'image_mime'=>$parsedFrame['image_mime']);
						}
					}
				} while (false);
			}

		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'GEOB')) || // 4.15  GEOB General encapsulated object
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'GEO'))) {     // 4.16  GEO  General encapsulated object
			//   There may be more than one 'GEOB' frame in each tag,
			//   but only one with the same content descriptor
			// <Header for 'General encapsulated object', ID: 'GEOB'>
			// Text encoding          $xx
			// MIME type              <text string> $00
			// Filename               <text string according to encoding> $00 (00)
			// Content description    <text string according to encoding> $00 (00)
			// Encapsulated object    <binary data>

			$frame_offset = 0;
			$frame_textencoding = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion == 4) && ($frame_textencoding > 3))) {
				$info['warning'][] = 'Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding';
			}
			$frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
			$frame_mimetype = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			if (ord($frame_mimetype) === 0) {
				$frame_mimetype = '';
			}
			$frame_offset = $frame_terminatorpos + strlen("\x00");

			$frame_terminatorpos = strpos($parsedFrame['data'], $this->TextEncodingTerminatorLookup($frame_textencoding), $frame_offset);
			if (ord(substr($parsedFrame['data'], $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding)), 1)) === 0) {
				$frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
			}
			$frame_filename = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			if (ord($frame_filename) === 0) {
				$frame_filename = '';
			}
			$frame_offset = $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding));

			$frame_terminatorpos = strpos($parsedFrame['data'], $this->TextEncodingTerminatorLookup($frame_textencoding), $frame_offset);
			if (ord(substr($parsedFrame['data'], $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding)), 1)) === 0) {
				$frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
			}
			$frame_description = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			if (ord($frame_description) === 0) {
				$frame_description = '';
			}
			$frame_offset = $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding));

			$parsedFrame['objectdata']  = (string) substr($parsedFrame['data'], $frame_offset);
			$parsedFrame['encodingid']  = $frame_textencoding;
			$parsedFrame['encoding']    = $this->TextEncodingNameLookup($frame_textencoding);

			$parsedFrame['mime']        = $frame_mimetype;
			$parsedFrame['filename']    = $frame_filename;
			$parsedFrame['description'] = $frame_description;
			unset($parsedFrame['data']);


		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'PCNT')) || // 4.16  PCNT Play counter
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'CNT'))) {     // 4.17  CNT  Play counter
			//   There may only be one 'PCNT' frame in each tag.
			//   When the counter reaches all one's, one byte is inserted in
			//   front of the counter thus making the counter eight bits bigger
			// <Header for 'Play counter', ID: 'PCNT'>
			// Counter        $xx xx xx xx (xx ...)

			$parsedFrame['data']          = getid3_lib::BigEndian2Int($parsedFrame['data']);


		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'POPM')) || // 4.17  POPM Popularimeter
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'POP'))) {    // 4.18  POP  Popularimeter
			//   There may be more than one 'POPM' frame in each tag,
			//   but only one with the same email address
			// <Header for 'Popularimeter', ID: 'POPM'>
			// Email to user   <text string> $00
			// Rating          $xx
			// Counter         $xx xx xx xx (xx ...)

			$frame_offset = 0;
			$frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
			$frame_emailaddress = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			if (ord($frame_emailaddress) === 0) {
				$frame_emailaddress = '';
			}
			$frame_offset = $frame_terminatorpos + strlen("\x00");
			$frame_rating = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$parsedFrame['counter'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset));
			$parsedFrame['email']   = $frame_emailaddress;
			$parsedFrame['rating']  = $frame_rating;
			unset($parsedFrame['data']);


		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'RBUF')) || // 4.18  RBUF Recommended buffer size
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'BUF'))) {     // 4.19  BUF  Recommended buffer size
			//   There may only be one 'RBUF' frame in each tag
			// <Header for 'Recommended buffer size', ID: 'RBUF'>
			// Buffer size               $xx xx xx
			// Embedded info flag        %0000000x
			// Offset to next tag        $xx xx xx xx

			$frame_offset = 0;
			$parsedFrame['buffersize'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 3));
			$frame_offset += 3;

			$frame_embeddedinfoflags = getid3_lib::BigEndian2Bin(substr($parsedFrame['data'], $frame_offset++, 1));
			$parsedFrame['flags']['embededinfo'] = (bool) substr($frame_embeddedinfoflags, 7, 1);
			$parsedFrame['nexttagoffset'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));
			unset($parsedFrame['data']);


		} elseif (($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'CRM')) { // 4.20  Encrypted meta frame (ID3v2.2 only)
			//   There may be more than one 'CRM' frame in a tag,
			//   but only one with the same 'owner identifier'
			// <Header for 'Encrypted meta frame', ID: 'CRM'>
			// Owner identifier      <textstring> $00 (00)
			// Content/explanation   <textstring> $00 (00)
			// Encrypted datablock   <binary data>

			$frame_offset = 0;
			$frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
			$frame_ownerid = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			$frame_offset = $frame_terminatorpos + strlen("\x00");

			$frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
			$frame_description = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			if (ord($frame_description) === 0) {
				$frame_description = '';
			}
			$frame_offset = $frame_terminatorpos + strlen("\x00");

			$parsedFrame['ownerid']     = $frame_ownerid;
			$parsedFrame['data']        = (string) substr($parsedFrame['data'], $frame_offset);
			$parsedFrame['description'] = $frame_description;
			unset($parsedFrame['data']);


		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'AENC')) || // 4.19  AENC Audio encryption
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'CRA'))) {     // 4.21  CRA  Audio encryption
			//   There may be more than one 'AENC' frames in a tag,
			//   but only one with the same 'Owner identifier'
			// <Header for 'Audio encryption', ID: 'AENC'>
			// Owner identifier   <text string> $00
			// Preview start      $xx xx
			// Preview length     $xx xx
			// Encryption info    <binary data>

			$frame_offset = 0;
			$frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
			$frame_ownerid = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			if (ord($frame_ownerid) === 0) {
				$frame_ownerid == '';
			}
			$frame_offset = $frame_terminatorpos + strlen("\x00");
			$parsedFrame['ownerid'] = $frame_ownerid;
			$parsedFrame['previewstart'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 2));
			$frame_offset += 2;
			$parsedFrame['previewlength'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 2));
			$frame_offset += 2;
			$parsedFrame['encryptioninfo'] = (string) substr($parsedFrame['data'], $frame_offset);
			unset($parsedFrame['data']);


		} elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'LINK')) || // 4.20  LINK Linked information
				(($id3v2_majorversion == 2) && ($parsedFrame['frame_name'] == 'LNK'))) {     // 4.22  LNK  Linked information
			//   There may be more than one 'LINK' frame in a tag,
			//   but only one with the same contents
			// <Header for 'Linked information', ID: 'LINK'>
			// ID3v2.3+ => Frame identifier   $xx xx xx xx
			// ID3v2.2  => Frame identifier   $xx xx xx
			// URL                            <text string> $00
			// ID and additional data         <text string(s)>

			$frame_offset = 0;
			if ($id3v2_majorversion == 2) {
				$parsedFrame['frameid'] = substr($parsedFrame['data'], $frame_offset, 3);
				$frame_offset += 3;
			} else {
				$parsedFrame['frameid'] = substr($parsedFrame['data'], $frame_offset, 4);
				$frame_offset += 4;
			}

			$frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
			$frame_url = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			if (ord($frame_url) === 0) {
				$frame_url = '';
			}
			$frame_offset = $frame_terminatorpos + strlen("\x00");
			$parsedFrame['url'] = $frame_url;

			$parsedFrame['additionaldata'] = (string) substr($parsedFrame['data'], $frame_offset);
			if (!empty($parsedFrame['framenameshort']) && $parsedFrame['url']) {
				$info['id3v2']['comments'][$parsedFrame['framenameshort']][] = utf8_encode($parsedFrame['url']);
			}
			unset($parsedFrame['data']);


		} elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'POSS')) { // 4.21  POSS Position synchronisation frame (ID3v2.3+ only)
			//   There may only be one 'POSS' frame in each tag
			// <Head for 'Position synchronisation', ID: 'POSS'>
			// Time stamp format         $xx
			// Position                  $xx (xx ...)

			$frame_offset = 0;
			$parsedFrame['timestampformat'] = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$parsedFrame['position']        = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset));
			unset($parsedFrame['data']);


		} elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'USER')) { // 4.22  USER Terms of use (ID3v2.3+ only)
			//   There may be more than one 'Terms of use' frame in a tag,
			//   but only one with the same 'Language'
			// <Header for 'Terms of use frame', ID: 'USER'>
			// Text encoding        $xx
			// Language             $xx xx xx
			// The actual text      <text string according to encoding>

			$frame_offset = 0;
			$frame_textencoding = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion == 4) && ($frame_textencoding > 3))) {
				$info['warning'][] = 'Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding';
			}
			$frame_language = substr($parsedFrame['data'], $frame_offset, 3);
			$frame_offset += 3;
			$parsedFrame['language']     = $frame_language;
			$parsedFrame['languagename'] = $this->LanguageLookup($frame_language, false);
			$parsedFrame['encodingid']   = $frame_textencoding;
			$parsedFrame['encoding']     = $this->TextEncodingNameLookup($frame_textencoding);

			$parsedFrame['data']         = (string) substr($parsedFrame['data'], $frame_offset);
			if (!empty($parsedFrame['framenameshort']) && !empty($parsedFrame['data'])) {
				$info['id3v2']['comments'][$parsedFrame['framenameshort']][] = getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $parsedFrame['data']);
			}
			unset($parsedFrame['data']);


		} elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'OWNE')) { // 4.23  OWNE Ownership frame (ID3v2.3+ only)
			//   There may only be one 'OWNE' frame in a tag
			// <Header for 'Ownership frame', ID: 'OWNE'>
			// Text encoding     $xx
			// Price paid        <text string> $00
			// Date of purch.    <text string>
			// Seller            <text string according to encoding>

			$frame_offset = 0;
			$frame_textencoding = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion == 4) && ($frame_textencoding > 3))) {
				$info['warning'][] = 'Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding';
			}
			$parsedFrame['encodingid'] = $frame_textencoding;
			$parsedFrame['encoding']   = $this->TextEncodingNameLookup($frame_textencoding);

			$frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
			$frame_pricepaid = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			$frame_offset = $frame_terminatorpos + strlen("\x00");

			$parsedFrame['pricepaid']['currencyid'] = substr($frame_pricepaid, 0, 3);
			$parsedFrame['pricepaid']['currency']   = $this->LookupCurrencyUnits($parsedFrame['pricepaid']['currencyid']);
			$parsedFrame['pricepaid']['value']      = substr($frame_pricepaid, 3);

			$parsedFrame['purchasedate'] = substr($parsedFrame['data'], $frame_offset, 8);
			if (!$this->IsValidDateStampString($parsedFrame['purchasedate'])) {
				$parsedFrame['purchasedateunix'] = mktime (0, 0, 0, substr($parsedFrame['purchasedate'], 4, 2), substr($parsedFrame['purchasedate'], 6, 2), substr($parsedFrame['purchasedate'], 0, 4));
			}
			$frame_offset += 8;

			$parsedFrame['seller'] = (string) substr($parsedFrame['data'], $frame_offset);
			unset($parsedFrame['data']);


		} elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'COMR')) { // 4.24  COMR Commercial frame (ID3v2.3+ only)
			//   There may be more than one 'commercial frame' in a tag,
			//   but no two may be identical
			// <Header for 'Commercial frame', ID: 'COMR'>
			// Text encoding      $xx
			// Price string       <text string> $00
			// Valid until        <text string>
			// Contact URL        <text string> $00
			// Received as        $xx
			// Name of seller     <text string according to encoding> $00 (00)
			// Description        <text string according to encoding> $00 (00)
			// Picture MIME type  <string> $00
			// Seller logo        <binary data>

			$frame_offset = 0;
			$frame_textencoding = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion == 4) && ($frame_textencoding > 3))) {
				$info['warning'][] = 'Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding';
			}

			$frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
			$frame_pricestring = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			$frame_offset = $frame_terminatorpos + strlen("\x00");
			$frame_rawpricearray = explode('/', $frame_pricestring);
			foreach ($frame_rawpricearray as $key => $val) {
				$frame_currencyid = substr($val, 0, 3);
				$parsedFrame['price'][$frame_currencyid]['currency'] = $this->LookupCurrencyUnits($frame_currencyid);
				$parsedFrame['price'][$frame_currencyid]['value']    = substr($val, 3);
			}

			$frame_datestring = substr($parsedFrame['data'], $frame_offset, 8);
			$frame_offset += 8;

			$frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
			$frame_contacturl = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			$frame_offset = $frame_terminatorpos + strlen("\x00");

			$frame_receivedasid = ord(substr($parsedFrame['data'], $frame_offset++, 1));

			$frame_terminatorpos = strpos($parsedFrame['data'], $this->TextEncodingTerminatorLookup($frame_textencoding), $frame_offset);
			if (ord(substr($parsedFrame['data'], $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding)), 1)) === 0) {
				$frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
			}
			$frame_sellername = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			if (ord($frame_sellername) === 0) {
				$frame_sellername = '';
			}
			$frame_offset = $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding));

			$frame_terminatorpos = strpos($parsedFrame['data'], $this->TextEncodingTerminatorLookup($frame_textencoding), $frame_offset);
			if (ord(substr($parsedFrame['data'], $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding)), 1)) === 0) {
				$frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
			}
			$frame_description = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			if (ord($frame_description) === 0) {
				$frame_description = '';
			}
			$frame_offset = $frame_terminatorpos + strlen($this->TextEncodingTerminatorLookup($frame_textencoding));

			$frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
			$frame_mimetype = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			$frame_offset = $frame_terminatorpos + strlen("\x00");

			$frame_sellerlogo = substr($parsedFrame['data'], $frame_offset);

			$parsedFrame['encodingid']        = $frame_textencoding;
			$parsedFrame['encoding']          = $this->TextEncodingNameLookup($frame_textencoding);

			$parsedFrame['pricevaliduntil']   = $frame_datestring;
			$parsedFrame['contacturl']        = $frame_contacturl;
			$parsedFrame['receivedasid']      = $frame_receivedasid;
			$parsedFrame['receivedas']        = $this->COMRReceivedAsLookup($frame_receivedasid);
			$parsedFrame['sellername']        = $frame_sellername;
			$parsedFrame['description']       = $frame_description;
			$parsedFrame['mime']              = $frame_mimetype;
			$parsedFrame['logo']              = $frame_sellerlogo;
			unset($parsedFrame['data']);


		} elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'ENCR')) { // 4.25  ENCR Encryption method registration (ID3v2.3+ only)
			//   There may be several 'ENCR' frames in a tag,
			//   but only one containing the same symbol
			//   and only one containing the same owner identifier
			// <Header for 'Encryption method registration', ID: 'ENCR'>
			// Owner identifier    <text string> $00
			// Method symbol       $xx
			// Encryption data     <binary data>

			$frame_offset = 0;
			$frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
			$frame_ownerid = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			if (ord($frame_ownerid) === 0) {
				$frame_ownerid = '';
			}
			$frame_offset = $frame_terminatorpos + strlen("\x00");

			$parsedFrame['ownerid']      = $frame_ownerid;
			$parsedFrame['methodsymbol'] = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$parsedFrame['data']         = (string) substr($parsedFrame['data'], $frame_offset);


		} elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'GRID')) { // 4.26  GRID Group identification registration (ID3v2.3+ only)

			//   There may be several 'GRID' frames in a tag,
			//   but only one containing the same symbol
			//   and only one containing the same owner identifier
			// <Header for 'Group ID registration', ID: 'GRID'>
			// Owner identifier      <text string> $00
			// Group symbol          $xx
			// Group dependent data  <binary data>

			$frame_offset = 0;
			$frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
			$frame_ownerid = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			if (ord($frame_ownerid) === 0) {
				$frame_ownerid = '';
			}
			$frame_offset = $frame_terminatorpos + strlen("\x00");

			$parsedFrame['ownerid']       = $frame_ownerid;
			$parsedFrame['groupsymbol']   = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$parsedFrame['data']          = (string) substr($parsedFrame['data'], $frame_offset);


		} elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'PRIV')) { // 4.27  PRIV Private frame (ID3v2.3+ only)
			//   The tag may contain more than one 'PRIV' frame
			//   but only with different contents
			// <Header for 'Private frame', ID: 'PRIV'>
			// Owner identifier      <text string> $00
			// The private data      <binary data>

			$frame_offset = 0;
			$frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
			$frame_ownerid = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
			if (ord($frame_ownerid) === 0) {
				$frame_ownerid = '';
			}
			$frame_offset = $frame_terminatorpos + strlen("\x00");

			$parsedFrame['ownerid'] = $frame_ownerid;
			$parsedFrame['data']    = (string) substr($parsedFrame['data'], $frame_offset);


		} elseif (($id3v2_majorversion >= 4) && ($parsedFrame['frame_name'] == 'SIGN')) { // 4.28  SIGN Signature frame (ID3v2.4+ only)
			//   There may be more than one 'signature frame' in a tag,
			//   but no two may be identical
			// <Header for 'Signature frame', ID: 'SIGN'>
			// Group symbol      $xx
			// Signature         <binary data>

			$frame_offset = 0;
			$parsedFrame['groupsymbol'] = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$parsedFrame['data']        = (string) substr($parsedFrame['data'], $frame_offset);


		} elseif (($id3v2_majorversion >= 4) && ($parsedFrame['frame_name'] == 'SEEK')) { // 4.29  SEEK Seek frame (ID3v2.4+ only)
			//   There may only be one 'seek frame' in a tag
			// <Header for 'Seek frame', ID: 'SEEK'>
			// Minimum offset to next tag       $xx xx xx xx

			$frame_offset = 0;
			$parsedFrame['data']          = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));


		} elseif (($id3v2_majorversion >= 4) && ($parsedFrame['frame_name'] == 'ASPI')) { // 4.30  ASPI Audio seek point index (ID3v2.4+ only)
			//   There may only be one 'audio seek point index' frame in a tag
			// <Header for 'Seek Point Index', ID: 'ASPI'>
			// Indexed data start (S)         $xx xx xx xx
			// Indexed data length (L)        $xx xx xx xx
			// Number of index points (N)     $xx xx
			// Bits per index point (b)       $xx
			//   Then for every index point the following data is included:
			// Fraction at index (Fi)          $xx (xx)

			$frame_offset = 0;
			$parsedFrame['datastart'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));
			$frame_offset += 4;
			$parsedFrame['indexeddatalength'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));
			$frame_offset += 4;
			$parsedFrame['indexpoints'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 2));
			$frame_offset += 2;
			$parsedFrame['bitsperpoint'] = ord(substr($parsedFrame['data'], $frame_offset++, 1));
			$frame_bytesperpoint = ceil($parsedFrame['bitsperpoint'] / 8);
			for ($i = 0; $i < $frame_indexpoints; $i++) {
				$parsedFrame['indexes'][$i] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesperpoint));
				$frame_offset += $frame_bytesperpoint;
			}
			unset($parsedFrame['data']);

		} elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] == 'RGAD')) { // Replay Gain Adjustment
			// http://privatewww.essex.ac.uk/~djmrob/replaygain/file_format_id3v2.html
			//   There may only be one 'RGAD' frame in a tag
			// <Header for 'Replay Gain Adjustment', ID: 'RGAD'>
			// Peak Amplitude                      $xx $xx $xx $xx
			// Radio Replay Gain Adjustment        %aaabbbcd %dddddddd
			// Audiophile Replay Gain Adjustment   %aaabbbcd %dddddddd
			//   a - name code
			//   b - originator code
			//   c - sign bit
			//   d - replay gain adjustment

			$frame_offset = 0;
			$parsedFrame['peakamplitude'] = getid3_lib::BigEndian2Float(substr($parsedFrame['data'], $frame_offset, 4));
			$frame_offset += 4;
			$rg_track_adjustment = getid3_lib::Dec2Bin(substr($parsedFrame['data'], $frame_offset, 2));
			$frame_offset += 2;
			$rg_album_adjustment = getid3_lib::Dec2Bin(substr($parsedFrame['data'], $frame_offset, 2));
			$frame_offset += 2;
			$parsedFrame['raw']['track']['name']       = getid3_lib::Bin2Dec(substr($rg_track_adjustment, 0, 3));
			$parsedFrame['raw']['track']['originator'] = getid3_lib::Bin2Dec(substr($rg_track_adjustment, 3, 3));
			$parsedFrame['raw']['track']['signbit']    = getid3_lib::Bin2Dec(substr($rg_track_adjustment, 6, 1));
			$parsedFrame['raw']['track']['adjustment'] = getid3_lib::Bin2Dec(substr($rg_track_adjustment, 7, 9));
			$parsedFrame['raw']['album']['name']       = getid3_lib::Bin2Dec(substr($rg_album_adjustment, 0, 3));
			$parsedFrame['raw']['album']['originator'] = getid3_lib::Bin2Dec(substr($rg_album_adjustment, 3, 3));
			$parsedFrame['raw']['album']['signbit']    = getid3_lib::Bin2Dec(substr($rg_album_adjustment, 6, 1));
			$parsedFrame['raw']['album']['adjustment'] = getid3_lib::Bin2Dec(substr($rg_album_adjustment, 7, 9));
			$parsedFrame['track']['name']       = getid3_lib::RGADnameLookup($parsedFrame['raw']['track']['name']);
			$parsedFrame['track']['originator'] = getid3_lib::RGADoriginatorLookup($parsedFrame['raw']['track']['originator']);
			$parsedFrame['track']['adjustment'] = getid3_lib::RGADadjustmentLookup($parsedFrame['raw']['track']['adjustment'], $parsedFrame['raw']['track']['signbit']);
			$parsedFrame['album']['name']       = getid3_lib::RGADnameLookup($parsedFrame['raw']['album']['name']);
			$parsedFrame['album']['originator'] = getid3_lib::RGADoriginatorLookup($parsedFrame['raw']['album']['originator']);
			$parsedFrame['album']['adjustment'] = getid3_lib::RGADadjustmentLookup($parsedFrame['raw']['album']['adjustment'], $parsedFrame['raw']['album']['signbit']);

			$info['replay_gain']['track']['peak']       = $parsedFrame['peakamplitude'];
			$info['replay_gain']['track']['originator'] = $parsedFrame['track']['originator'];
			$info['replay_gain']['track']['adjustment'] = $parsedFrame['track']['adjustment'];
			$info['replay_gain']['album']['originator'] = $parsedFrame['album']['originator'];
			$info['replay_gain']['album']['adjustment'] = $parsedFrame['album']['adjustment'];

			unset($parsedFrame['data']);

		}

		return true;
	}


	public function DeUnsynchronise($data) {
		return str_replace("\xFF\x00", "\xFF", $data);
	}

	public function LookupExtendedHeaderRestrictionsTagSizeLimits($index) {
		static $LookupExtendedHeaderRestrictionsTagSizeLimits = array(
			0x00 => 'No more than 128 frames and 1 MB total tag size',
			0x01 => 'No more than 64 frames and 128 KB total tag size',
			0x02 => 'No more than 32 frames and 40 KB total tag size',
			0x03 => 'No more than 32 frames and 4 KB total tag size',
		);
		return (isset($LookupExtendedHeaderRestrictionsTagSizeLimits[$index]) ? $LookupExtendedHeaderRestrictionsTagSizeLimits[$index] : '');
	}

	public function LookupExtendedHeaderRestrictionsTextEncodings($index) {
		static $LookupExtendedHeaderRestrictionsTextEncodings = array(
			0x00 => 'No restrictions',
			0x01 => 'Strings are only encoded with ISO-8859-1 or UTF-8',
		);
		return (isset($LookupExtendedHeaderRestrictionsTextEncodings[$index]) ? $LookupExtendedHeaderRestrictionsTextEncodings[$index] : '');
	}

	public function LookupExtendedHeaderRestrictionsTextFieldSize($index) {
		static $LookupExtendedHeaderRestrictionsTextFieldSize = array(
			0x00 => 'No restrictions',
			0x01 => 'No string is longer than 1024 characters',
			0x02 => 'No string is longer than 128 characters',
			0x03 => 'No string is longer than 30 characters',
		);
		return (isset($LookupExtendedHeaderRestrictionsTextFieldSize[$index]) ? $LookupExtendedHeaderRestrictionsTextFieldSize[$index] : '');
	}

	public function LookupExtendedHeaderRestrictionsImageEncoding($index) {
		static $LookupExtendedHeaderRestrictionsImageEncoding = array(
			0x00 => 'No restrictions',
			0x01 => 'Images are encoded only with PNG or JPEG',
		);
		return (isset($LookupExtendedHeaderRestrictionsImageEncoding[$index]) ? $LookupExtendedHeaderRestrictionsImageEncoding[$index] : '');
	}

	public function LookupExtendedHeaderRestrictionsImageSizeSize($index) {
		static $LookupExtendedHeaderRestrictionsImageSizeSize = array(
			0x00 => 'No restrictions',
			0x01 => 'All images are 256x256 pixels or smaller',
			0x02 => 'All images are 64x64 pixels or smaller',
			0x03 => 'All images are exactly 64x64 pixels, unless required otherwise',
		);
		return (isset($LookupExtendedHeaderRestrictionsImageSizeSize[$index]) ? $LookupExtendedHeaderRestrictionsImageSizeSize[$index] : '');
	}

	public function LookupCurrencyUnits($currencyid) {

		$begin = __LINE__;

		/** This is not a comment!


			AED	Dirhams
			AFA	Afghanis
			ALL	Leke
			AMD	Drams
			ANG	Guilders
			AOA	Kwanza
			ARS	Pesos
			ATS	Schillings
			AUD	Dollars
			AWG	Guilders
			AZM	Manats
			BAM	Convertible Marka
			BBD	Dollars
			BDT	Taka
			BEF	Francs
			BGL	Leva
			BHD	Dinars
			BIF	Francs
			BMD	Dollars
			BND	Dollars
			BOB	Bolivianos
			BRL	Brazil Real
			BSD	Dollars
			BTN	Ngultrum
			BWP	Pulas
			BYR	Rubles
			BZD	Dollars
			CAD	Dollars
			CDF	Congolese Francs
			CHF	Francs
			CLP	Pesos
			CNY	Yuan Renminbi
			COP	Pesos
			CRC	Colones
			CUP	Pesos
			CVE	Escudos
			CYP	Pounds
			CZK	Koruny
			DEM	Deutsche Marks
			DJF	Francs
			DKK	Kroner
			DOP	Pesos
			DZD	Algeria Dinars
			EEK	Krooni
			EGP	Pounds
			ERN	Nakfa
			ESP	Pesetas
			ETB	Birr
			EUR	Euro
			FIM	Markkaa
			FJD	Dollars
			FKP	Pounds
			FRF	Francs
			GBP	Pounds
			GEL	Lari
			GGP	Pounds
			GHC	Cedis
			GIP	Pounds
			GMD	Dalasi
			GNF	Francs
			GRD	Drachmae
			GTQ	Quetzales
			GYD	Dollars
			HKD	Dollars
			HNL	Lempiras
			HRK	Kuna
			HTG	Gourdes
			HUF	Forints
			IDR	Rupiahs
			IEP	Pounds
			ILS	New Shekels
			IMP	Pounds
			INR	Rupees
			IQD	Dinars
			IRR	Rials
			ISK	Kronur
			ITL	Lire
			JEP	Pounds
			JMD	Dollars
			JOD	Dinars
			JPY	Yen
			KES	Shillings
			KGS	Soms
			KHR	Riels
			KMF	Francs
			KPW	Won
			KWD	Dinars
			KYD	Dollars
			KZT	Tenge
			LAK	Kips
			LBP	Pounds
			LKR	Rupees
			LRD	Dollars
			LSL	Maloti
			LTL	Litai
			LUF	Francs
			LVL	Lati
			LYD	Dinars
			MAD	Dirhams
			MDL	Lei
			MGF	Malagasy Francs
			MKD	Denars
			MMK	Kyats
			MNT	Tugriks
			MOP	Patacas
			MRO	Ouguiyas
			MTL	Liri
			MUR	Rupees
			MVR	Rufiyaa
			MWK	Kwachas
			MXN	Pesos
			MYR	Ringgits
			MZM	Meticais
			NAD	Dollars
			NGN	Nairas
			NIO	Gold Cordobas
			NLG	Guilders
			NOK	Krone
			NPR	Nepal Rupees
			NZD	Dollars
			OMR	Rials
			PAB	Balboa
			PEN	Nuevos Soles
			PGK	Kina
			PHP	Pesos
			PKR	Rupees
			PLN	Zlotych
			PTE	Escudos
			PYG	Guarani
			QAR	Rials
			ROL	Lei
			RUR	Rubles
			RWF	Rwanda Francs
			SAR	Riyals
			SBD	Dollars
			SCR	Rupees
			SDD	Dinars
			SEK	Kronor
			SGD	Dollars
			SHP	Pounds
			SIT	Tolars
			SKK	Koruny
			SLL	Leones
			SOS	Shillings
			SPL	Luigini
			SRG	Guilders
			STD	Dobras
			SVC	Colones
			SYP	Pounds
			SZL	Emalangeni
			THB	Baht
			TJR	Rubles
			TMM	Manats
			TND	Dinars
			TOP	Pa'anga
			TRL	Liras
			TTD	Dollars
			TVD	Tuvalu Dollars
			TWD	New Dollars
			TZS	Shillings
			UAH	Hryvnia
			UGX	Shillings
			USD	Dollars
			UYU	Pesos
			UZS	Sums
			VAL	Lire
			VEB	Bolivares
			VND	Dong
			VUV	Vatu
			WST	Tala
			XAF	Francs
			XAG	Ounces
			XAU	Ounces
			XCD	Dollars
			XDR	Special Drawing Rights
			XPD	Ounces
			XPF	Francs
			XPT	Ounces
			YER	Rials
			YUM	New Dinars
			ZAR	Rand
			ZMK	Kwacha
			ZWD	Zimbabwe Dollars

		*/

		return getid3_lib::EmbeddedLookup($currencyid, $begin, __LINE__, __FILE__, 'id3v2-currency-units');
	}


	public function LookupCurrencyCountry($currencyid) {

		$begin = __LINE__;

		/** This is not a comment!

			AED	United Arab Emirates
			AFA	Afghanistan
			ALL	Albania
			AMD	Armenia
			ANG	Netherlands Antilles
			AOA	Angola
			ARS	Argentina
			ATS	Austria
			AUD	Australia
			AWG	Aruba
			AZM	Azerbaijan
			BAM	Bosnia and Herzegovina
			BBD	Barbados
			BDT	Bangladesh
			BEF	Belgium
			BGL	Bulgaria
			BHD	Bahrain
			BIF	Burundi
			BMD	Bermuda
			BND	Brunei Darussalam
			BOB	Bolivia
			BRL	Brazil
			BSD	Bahamas
			BTN	Bhutan
			BWP	Botswana
			BYR	Belarus
			BZD	Belize
			CAD	Canada
			CDF	Congo/Kinshasa
			CHF	Switzerland
			CLP	Chile
			CNY	China
			COP	Colombia
			CRC	Costa Rica
			CUP	Cuba
			CVE	Cape Verde
			CYP	Cyprus
			CZK	Czech Republic
			DEM	Germany
			DJF	Djibouti
			DKK	Denmark
			DOP	Dominican Republic
			DZD	Algeria
			EEK	Estonia
			EGP	Egypt
			ERN	Eritrea
			ESP	Spain
			ETB	Ethiopia
			EUR	Euro Member Countries
			FIM	Finland
			FJD	Fiji
			FKP	Falkland Islands (Malvinas)
			FRF	France
			GBP	United Kingdom
			GEL	Georgia
			GGP	Guernsey
			GHC	Ghana
			GIP	Gibraltar
			GMD	Gambia
			GNF	Guinea
			GRD	Greece
			GTQ	Guatemala
			GYD	Guyana
			HKD	Hong Kong
			HNL	Honduras
			HRK	Croatia
			HTG	Haiti
			HUF	Hungary
			IDR	Indonesia
			IEP	Ireland (Eire)
			ILS	Israel
			IMP	Isle of Man
			INR	India
			IQD	Iraq
			IRR	Iran
			ISK	Iceland
			ITL	Italy
			JEP	Jersey
			JMD	Jamaica
			JOD	Jordan
			JPY	Japan
			KES	Kenya
			KGS	Kyrgyzstan
			KHR	Cambodia
			KMF	Comoros
			KPW	Korea
			KWD	Kuwait
			KYD	Cayman Islands
			KZT	Kazakstan
			LAK	Laos
			LBP	Lebanon
			LKR	Sri Lanka
			LRD	Liberia
			LSL	Lesotho
			LTL	Lithuania
			LUF	Luxembourg
			LVL	Latvia
			LYD	Libya
			MAD	Morocco
			MDL	Moldova
			MGF	Madagascar
			MKD	Macedonia
			MMK	Myanmar (Burma)
			MNT	Mongolia
			MOP	Macau
			MRO	Mauritania
			MTL	Malta
			MUR	Mauritius
			MVR	Maldives (Maldive Islands)
			MWK	Malawi
			MXN	Mexico
			MYR	Malaysia
			MZM	Mozambique
			NAD	Namibia
			NGN	Nigeria
			NIO	Nicaragua
			NLG	Netherlands (Holland)
			NOK	Norway
			NPR	Nepal
			NZD	New Zealand
			OMR	Oman
			PAB	Panama
			PEN	Peru
			PGK	Papua New Guinea
			PHP	Philippines
			PKR	Pakistan
			PLN	Poland
			PTE	Portugal
			PYG	Paraguay
			QAR	Qatar
			ROL	Romania
			RUR	Russia
			RWF	Rwanda
			SAR	Saudi Arabia
			SBD	Solomon Islands
			SCR	Seychelles
			SDD	Sudan
			SEK	Sweden
			SGD	Singapore
			SHP	Saint Helena
			SIT	Slovenia
			SKK	Slovakia
			SLL	Sierra Leone
			SOS	Somalia
			SPL	Seborga
			SRG	Suriname
			STD	São Tome and Principe
			SVC	El Salvador
			SYP	Syria
			SZL	Swaziland
			THB	Thailand
			TJR	Tajikistan
			TMM	Turkmenistan
			TND	Tunisia
			TOP	Tonga
			TRL	Turkey
			TTD	Trinidad and Tobago
			TVD	Tuvalu
			TWD	Taiwan
			TZS	Tanzania
			UAH	Ukraine
			UGX	Uganda
			USD	United States of America
			UYU	Uruguay
			UZS	Uzbekistan
			VAL	Vatican City
			VEB	Venezuela
			VND	Viet Nam
			VUV	Vanuatu
			WST	Samoa
			XAF	Communauté Financière Africaine
			XAG	Silver
			XAU	Gold
			XCD	East Caribbean
			XDR	International Monetary Fund
			XPD	Palladium
			XPF	Comptoirs Français du Pacifique
			XPT	Platinum
			YER	Yemen
			YUM	Yugoslavia
			ZAR	South Africa
			ZMK	Zambia
			ZWD	Zimbabwe

		*/

		return getid3_lib::EmbeddedLookup($currencyid, $begin, __LINE__, __FILE__, 'id3v2-currency-country');
	}



	public static function LanguageLookup($languagecode, $casesensitive=false) {

		if (!$casesensitive) {
			$languagecode = strtolower($languagecode);
		}

		// http://www.id3.org/id3v2.4.0-structure.txt
		// [4.   ID3v2 frame overview]
		// The three byte language field, present in several frames, is used to
		// describe the language of the frame's content, according to ISO-639-2
		// [ISO-639-2]. The language should be represented in lower case. If the
		// language is not known the string "XXX" should be used.


		// ISO 639-2 - http://www.id3.org/iso639-2.html

		$begin = __LINE__;

		/** This is not a comment!

			XXX	unknown
			xxx	unknown
			aar	Afar
			abk	Abkhazian
			ace	Achinese
			ach	Acoli
			ada	Adangme
			afa	Afro-Asiatic (Other)
			afh	Afrihili
			afr	Afrikaans
			aka	Akan
			akk	Akkadian
			alb	Albanian
			ale	Aleut
			alg	Algonquian Languages
			amh	Amharic
			ang	English, Old (ca. 450-1100)
			apa	Apache Languages
			ara	Arabic
			arc	Aramaic
			arm	Armenian
			arn	Araucanian
			arp	Arapaho
			art	Artificial (Other)
			arw	Arawak
			asm	Assamese
			ath	Athapascan Languages
			ava	Avaric
			ave	Avestan
			awa	Awadhi
			aym	Aymara
			aze	Azerbaijani
			bad	Banda
			bai	Bamileke Languages
			bak	Bashkir
			bal	Baluchi
			bam	Bambara
			ban	Balinese
			baq	Basque
			bas	Basa
			bat	Baltic (Other)
			bej	Beja
			bel	Byelorussian
			bem	Bemba
			ben	Bengali
			ber	Berber (Other)
			bho	Bhojpuri
			bih	Bihari
			bik	Bikol
			bin	Bini
			bis	Bislama
			bla	Siksika
			bnt	Bantu (Other)
			bod	Tibetan
			bra	Braj
			bre	Breton
			bua	Buriat
			bug	Buginese
			bul	Bulgarian
			bur	Burmese
			cad	Caddo
			cai	Central American Indian (Other)
			car	Carib
			cat	Catalan
			cau	Caucasian (Other)
			ceb	Cebuano
			cel	Celtic (Other)
			ces	Czech
			cha	Chamorro
			chb	Chibcha
			che	Chechen
			chg	Chagatai
			chi	Chinese
			chm	Mari
			chn	Chinook jargon
			cho	Choctaw
			chr	Cherokee
			chu	Church Slavic
			chv	Chuvash
			chy	Cheyenne
			cop	Coptic
			cor	Cornish
			cos	Corsican
			cpe	Creoles and Pidgins, English-based (Other)
			cpf	Creoles and Pidgins, French-based (Other)
			cpp	Creoles and Pidgins, Portuguese-based (Other)
			cre	Cree
			crp	Creoles and Pidgins (Other)
			cus	Cushitic (Other)
			cym	Welsh
			cze	Czech
			dak	Dakota
			dan	Danish
			del	Delaware
			deu	German
			din	Dinka
			div	Divehi
			doi	Dogri
			dra	Dravidian (Other)
			dua	Duala
			dum	Dutch, Middle (ca. 1050-1350)
			dut	Dutch
			dyu	Dyula
			dzo	Dzongkha
			efi	Efik
			egy	Egyptian (Ancient)
			eka	Ekajuk
			ell	Greek, Modern (1453-)
			elx	Elamite
			eng	English
			enm	English, Middle (ca. 1100-1500)
			epo	Esperanto
			esk	Eskimo (Other)
			esl	Spanish
			est	Estonian
			eus	Basque
			ewe	Ewe
			ewo	Ewondo
			fan	Fang
			fao	Faroese
			fas	Persian
			fat	Fanti
			fij	Fijian
			fin	Finnish
			fiu	Finno-Ugrian (Other)
			fon	Fon
			fra	French
			fre	French
			frm	French, Middle (ca. 1400-1600)
			fro	French, Old (842- ca. 1400)
			fry	Frisian
			ful	Fulah
			gaa	Ga
			gae	Gaelic (Scots)
			gai	Irish
			gay	Gayo
			gdh	Gaelic (Scots)
			gem	Germanic (Other)
			geo	Georgian
			ger	German
			gez	Geez
			gil	Gilbertese
			glg	Gallegan
			gmh	German, Middle High (ca. 1050-1500)
			goh	German, Old High (ca. 750-1050)
			gon	Gondi
			got	Gothic
			grb	Grebo
			grc	Greek, Ancient (to 1453)
			gre	Greek, Modern (1453-)
			grn	Guarani
			guj	Gujarati
			hai	Haida
			hau	Hausa
			haw	Hawaiian
			heb	Hebrew
			her	Herero
			hil	Hiligaynon
			him	Himachali
			hin	Hindi
			hmo	Hiri Motu
			hun	Hungarian
			hup	Hupa
			hye	Armenian
			iba	Iban
			ibo	Igbo
			ice	Icelandic
			ijo	Ijo
			iku	Inuktitut
			ilo	Iloko
			ina	Interlingua (International Auxiliary language Association)
			inc	Indic (Other)
			ind	Indonesian
			ine	Indo-European (Other)
			ine	Interlingue
			ipk	Inupiak
			ira	Iranian (Other)
			iri	Irish
			iro	Iroquoian uages
			isl	Icelandic
			ita	Italian
			jav	Javanese
			jaw	Javanese
			jpn	Japanese
			jpr	Judeo-Persian
			jrb	Judeo-Arabic
			kaa	Kara-Kalpak
			kab	Kabyle
			kac	Kachin
			kal	Greenlandic
			kam	Kamba
			kan	Kannada
			kar	Karen
			kas	Kashmiri
			kat	Georgian
			kau	Kanuri
			kaw	Kawi
			kaz	Kazakh
			kha	Khasi
			khi	Khoisan (Other)
			khm	Khmer
			kho	Khotanese
			kik	Kikuyu
			kin	Kinyarwanda
			kir	Kirghiz
			kok	Konkani
			kom	Komi
			kon	Kongo
			kor	Korean
			kpe	Kpelle
			kro	Kru
			kru	Kurukh
			kua	Kuanyama
			kum	Kumyk
			kur	Kurdish
			kus	Kusaie
			kut	Kutenai
			lad	Ladino
			lah	Lahnda
			lam	Lamba
			lao	Lao
			lat	Latin
			lav	Latvian
			lez	Lezghian
			lin	Lingala
			lit	Lithuanian
			lol	Mongo
			loz	Lozi
			ltz	Letzeburgesch
			lub	Luba-Katanga
			lug	Ganda
			lui	Luiseno
			lun	Lunda
			luo	Luo (Kenya and Tanzania)
			mac	Macedonian
			mad	Madurese
			mag	Magahi
			mah	Marshall
			mai	Maithili
			mak	Macedonian
			mak	Makasar
			mal	Malayalam
			man	Mandingo
			mao	Maori
			map	Austronesian (Other)
			mar	Marathi
			mas	Masai
			max	Manx
			may	Malay
			men	Mende
			mga	Irish, Middle (900 - 1200)
			mic	Micmac
			min	Minangkabau
			mis	Miscellaneous (Other)
			mkh	Mon-Kmer (Other)
			mlg	Malagasy
			mlt	Maltese
			mni	Manipuri
			mno	Manobo Languages
			moh	Mohawk
			mol	Moldavian
			mon	Mongolian
			mos	Mossi
			mri	Maori
			msa	Malay
			mul	Multiple Languages
			mun	Munda Languages
			mus	Creek
			mwr	Marwari
			mya	Burmese
			myn	Mayan Languages
			nah	Aztec
			nai	North American Indian (Other)
			nau	Nauru
			nav	Navajo
			nbl	Ndebele, South
			nde	Ndebele, North
			ndo	Ndongo
			nep	Nepali
			new	Newari
			nic	Niger-Kordofanian (Other)
			niu	Niuean
			nla	Dutch
			nno	Norwegian (Nynorsk)
			non	Norse, Old
			nor	Norwegian
			nso	Sotho, Northern
			nub	Nubian Languages
			nya	Nyanja
			nym	Nyamwezi
			nyn	Nyankole
			nyo	Nyoro
			nzi	Nzima
			oci	Langue d'Oc (post 1500)
			oji	Ojibwa
			ori	Oriya
			orm	Oromo
			osa	Osage
			oss	Ossetic
			ota	Turkish, Ottoman (1500 - 1928)
			oto	Otomian Languages
			paa	Papuan-Australian (Other)
			pag	Pangasinan
			pal	Pahlavi
			pam	Pampanga
			pan	Panjabi
			pap	Papiamento
			pau	Palauan
			peo	Persian, Old (ca 600 - 400 B.C.)
			per	Persian
			phn	Phoenician
			pli	Pali
			pol	Polish
			pon	Ponape
			por	Portuguese
			pra	Prakrit uages
			pro	Provencal, Old (to 1500)
			pus	Pushto
			que	Quechua
			raj	Rajasthani
			rar	Rarotongan
			roa	Romance (Other)
			roh	Rhaeto-Romance
			rom	Romany
			ron	Romanian
			rum	Romanian
			run	Rundi
			rus	Russian
			sad	Sandawe
			sag	Sango
			sah	Yakut
			sai	South American Indian (Other)
			sal	Salishan Languages
			sam	Samaritan Aramaic
			san	Sanskrit
			sco	Scots
			scr	Serbo-Croatian
			sel	Selkup
			sem	Semitic (Other)
			sga	Irish, Old (to 900)
			shn	Shan
			sid	Sidamo
			sin	Singhalese
			sio	Siouan Languages
			sit	Sino-Tibetan (Other)
			sla	Slavic (Other)
			slk	Slovak
			slo	Slovak
			slv	Slovenian
			smi	Sami Languages
			smo	Samoan
			sna	Shona
			snd	Sindhi
			sog	Sogdian
			som	Somali
			son	Songhai
			sot	Sotho, Southern
			spa	Spanish
			sqi	Albanian
			srd	Sardinian
			srr	Serer
			ssa	Nilo-Saharan (Other)
			ssw	Siswant
			ssw	Swazi
			suk	Sukuma
			sun	Sudanese
			sus	Susu
			sux	Sumerian
			sve	Swedish
			swa	Swahili
			swe	Swedish
			syr	Syriac
			tah	Tahitian
			tam	Tamil
			tat	Tatar
			tel	Telugu
			tem	Timne
			ter	Tereno
			tgk	Tajik
			tgl	Tagalog
			tha	Thai
			tib	Tibetan
			tig	Tigre
			tir	Tigrinya
			tiv	Tivi
			tli	Tlingit
			tmh	Tamashek
			tog	Tonga (Nyasa)
			ton	Tonga (Tonga Islands)
			tru	Truk
			tsi	Tsimshian
			tsn	Tswana
			tso	Tsonga
			tuk	Turkmen
			tum	Tumbuka
			tur	Turkish
			tut	Altaic (Other)
			twi	Twi
			tyv	Tuvinian
			uga	Ugaritic
			uig	Uighur
			ukr	Ukrainian
			umb	Umbundu
			und	Undetermined
			urd	Urdu
			uzb	Uzbek
			vai	Vai
			ven	Venda
			vie	Vietnamese
			vol	Volapük
			vot	Votic
			wak	Wakashan Languages
			wal	Walamo
			war	Waray
			was	Washo
			wel	Welsh
			wen	Sorbian Languages
			wol	Wolof
			xho	Xhosa
			yao	Yao
			yap	Yap
			yid	Yiddish
			yor	Yoruba
			zap	Zapotec
			zen	Zenaga
			zha	Zhuang
			zho	Chinese
			zul	Zulu
			zun	Zuni

		*/

		return getid3_lib::EmbeddedLookup($languagecode, $begin, __LINE__, __FILE__, 'id3v2-languagecode');
	}


	public static function ETCOEventLookup($index) {
		if (($index >= 0x17) && ($index <= 0xDF)) {
			return 'reserved for future use';
		}
		if (($index >= 0xE0) && ($index <= 0xEF)) {
			return 'not predefined synch 0-F';
		}
		if (($index >= 0xF0) && ($index <= 0xFC)) {
			return 'reserved for future use';
		}

		static $EventLookup = array(
			0x00 => 'padding (has no meaning)',
			0x01 => 'end of initial silence',
			0x02 => 'intro start',
			0x03 => 'main part start',
			0x04 => 'outro start',
			0x05 => 'outro end',
			0x06 => 'verse start',
			0x07 => 'refrain start',
			0x08 => 'interlude start',
			0x09 => 'theme start',
			0x0A => 'variation start',
			0x0B => 'key change',
			0x0C => 'time change',
			0x0D => 'momentary unwanted noise (Snap, Crackle & Pop)',
			0x0E => 'sustained noise',
			0x0F => 'sustained noise end',
			0x10 => 'intro end',
			0x11 => 'main part end',
			0x12 => 'verse end',
			0x13 => 'refrain end',
			0x14 => 'theme end',
			0x15 => 'profanity',
			0x16 => 'profanity end',
			0xFD => 'audio end (start of silence)',
			0xFE => 'audio file ends',
			0xFF => 'one more byte of events follows'
		);

		return (isset($EventLookup[$index]) ? $EventLookup[$index] : '');
	}

	public static function SYTLContentTypeLookup($index) {
		static $SYTLContentTypeLookup = array(
			0x00 => 'other',
			0x01 => 'lyrics',
			0x02 => 'text transcription',
			0x03 => 'movement/part name', // (e.g. 'Adagio')
			0x04 => 'events',             // (e.g. 'Don Quijote enters the stage')
			0x05 => 'chord',              // (e.g. 'Bb F Fsus')
			0x06 => 'trivia/\'pop up\' information',
			0x07 => 'URLs to webpages',
			0x08 => 'URLs to images'
		);

		return (isset($SYTLContentTypeLookup[$index]) ? $SYTLContentTypeLookup[$index] : '');
	}

	public static function APICPictureTypeLookup($index, $returnarray=false) {
		static $APICPictureTypeLookup = array(
			0x00 => 'Other',
			0x01 => '32x32 pixels \'file icon\' (PNG only)',
			0x02 => 'Other file icon',
			0x03 => 'Cover (front)',
			0x04 => 'Cover (back)',
			0x05 => 'Leaflet page',
			0x06 => 'Media (e.g. label side of CD)',
			0x07 => 'Lead artist/lead performer/soloist',
			0x08 => 'Artist/performer',
			0x09 => 'Conductor',
			0x0A => 'Band/Orchestra',
			0x0B => 'Composer',
			0x0C => 'Lyricist/text writer',
			0x0D => 'Recording Location',
			0x0E => 'During recording',
			0x0F => 'During performance',
			0x10 => 'Movie/video screen capture',
			0x11 => 'A bright coloured fish',
			0x12 => 'Illustration',
			0x13 => 'Band/artist logotype',
			0x14 => 'Publisher/Studio logotype'
		);
		if ($returnarray) {
			return $APICPictureTypeLookup;
		}
		return (isset($APICPictureTypeLookup[$index]) ? $APICPictureTypeLookup[$index] : '');
	}

	public static function COMRReceivedAsLookup($index) {
		static $COMRReceivedAsLookup = array(
			0x00 => 'Other',
			0x01 => 'Standard CD album with other songs',
			0x02 => 'Compressed audio on CD',
			0x03 => 'File over the Internet',
			0x04 => 'Stream over the Internet',
			0x05 => 'As note sheets',
			0x06 => 'As note sheets in a book with other sheets',
			0x07 => 'Music on other media',
			0x08 => 'Non-musical merchandise'
		);

		return (isset($COMRReceivedAsLookup[$index]) ? $COMRReceivedAsLookup[$index] : '');
	}

	public static function RVA2ChannelTypeLookup($index) {
		static $RVA2ChannelTypeLookup = array(
			0x00 => 'Other',
			0x01 => 'Master volume',
			0x02 => 'Front right',
			0x03 => 'Front left',
			0x04 => 'Back right',
			0x05 => 'Back left',
			0x06 => 'Front centre',
			0x07 => 'Back centre',
			0x08 => 'Subwoofer'
		);

		return (isset($RVA2ChannelTypeLookup[$index]) ? $RVA2ChannelTypeLookup[$index] : '');
	}

	public static function FrameNameLongLookup($framename) {

		$begin = __LINE__;

		/** This is not a comment!

			AENC	Audio encryption
			APIC	Attached picture
			ASPI	Audio seek point index
			BUF	Recommended buffer size
			CNT	Play counter
			COM	Comments
			COMM	Comments
			COMR	Commercial frame
			CRA	Audio encryption
			CRM	Encrypted meta frame
			ENCR	Encryption method registration
			EQU	Equalisation
			EQU2	Equalisation (2)
			EQUA	Equalisation
			ETC	Event timing codes
			ETCO	Event timing codes
			GEO	General encapsulated object
			GEOB	General encapsulated object
			GRID	Group identification registration
			IPL	Involved people list
			IPLS	Involved people list
			LINK	Linked information
			LNK	Linked information
			MCDI	Music CD identifier
			MCI	Music CD Identifier
			MLL	MPEG location lookup table
			MLLT	MPEG location lookup table
			OWNE	Ownership frame
			PCNT	Play counter
			PIC	Attached picture
			POP	Popularimeter
			POPM	Popularimeter
			POSS	Position synchronisation frame
			PRIV	Private frame
			RBUF	Recommended buffer size
			REV	Reverb
			RVA	Relative volume adjustment
			RVA2	Relative volume adjustment (2)
			RVAD	Relative volume adjustment
			RVRB	Reverb
			SEEK	Seek frame
			SIGN	Signature frame
			SLT	Synchronised lyric/text
			STC	Synced tempo codes
			SYLT	Synchronised lyric/text
			SYTC	Synchronised tempo codes
			TAL	Album/Movie/Show title
			TALB	Album/Movie/Show title
			TBP	BPM (Beats Per Minute)
			TBPM	BPM (beats per minute)
			TCM	Composer
			TCMP	Part of a compilation
			TCO	Content type
			TCOM	Composer
			TCON	Content type
			TCOP	Copyright message
			TCP	Part of a compilation
			TCR	Copyright message
			TDA	Date
			TDAT	Date
			TDEN	Encoding time
			TDLY	Playlist delay
			TDOR	Original release time
			TDRC	Recording time
			TDRL	Release time
			TDTG	Tagging time
			TDY	Playlist delay
			TEN	Encoded by
			TENC	Encoded by
			TEXT	Lyricist/Text writer
			TFLT	File type
			TFT	File type
			TIM	Time
			TIME	Time
			TIPL	Involved people list
			TIT1	Content group description
			TIT2	Title/songname/content description
			TIT3	Subtitle/Description refinement
			TKE	Initial key
			TKEY	Initial key
			TLA	Language(s)
			TLAN	Language(s)
			TLE	Length
			TLEN	Length
			TMCL	Musician credits list
			TMED	Media type
			TMOO	Mood
			TMT	Media type
			TOA	Original artist(s)/performer(s)
			TOAL	Original album/movie/show title
			TOF	Original filename
			TOFN	Original filename
			TOL	Original Lyricist(s)/text writer(s)
			TOLY	Original lyricist(s)/text writer(s)
			TOPE	Original artist(s)/performer(s)
			TOR	Original release year
			TORY	Original release year
			TOT	Original album/Movie/Show title
			TOWN	File owner/licensee
			TP1	Lead artist(s)/Lead performer(s)/Soloist(s)/Performing group
			TP2	Band/Orchestra/Accompaniment
			TP3	Conductor/Performer refinement
			TP4	Interpreted, remixed, or otherwise modified by
			TPA	Part of a set
			TPB	Publisher
			TPE1	Lead performer(s)/Soloist(s)
			TPE2	Band/orchestra/accompaniment
			TPE3	Conductor/performer refinement
			TPE4	Interpreted, remixed, or otherwise modified by
			TPOS	Part of a set
			TPRO	Produced notice
			TPUB	Publisher
			TRC	ISRC (International Standard Recording Code)
			TRCK	Track number/Position in set
			TRD	Recording dates
			TRDA	Recording dates
			TRK	Track number/Position in set
			TRSN	Internet radio station name
			TRSO	Internet radio station owner
			TS2	Album-Artist sort order
			TSA	Album sort order
			TSC	Composer sort order
			TSI	Size
			TSIZ	Size
			TSO2	Album-Artist sort order
			TSOA	Album sort order
			TSOC	Composer sort order
			TSOP	Performer sort order
			TSOT	Title sort order
			TSP	Performer sort order
			TSRC	ISRC (international standard recording code)
			TSS	Software/hardware and settings used for encoding
			TSSE	Software/Hardware and settings used for encoding
			TSST	Set subtitle
			TST	Title sort order
			TT1	Content group description
			TT2	Title/Songname/Content description
			TT3	Subtitle/Description refinement
			TXT	Lyricist/text writer
			TXX	User defined text information frame
			TXXX	User defined text information frame
			TYE	Year
			TYER	Year
			UFI	Unique file identifier
			UFID	Unique file identifier
			ULT	Unsychronised lyric/text transcription
			USER	Terms of use
			USLT	Unsynchronised lyric/text transcription
			WAF	Official audio file webpage
			WAR	Official artist/performer webpage
			WAS	Official audio source webpage
			WCM	Commercial information
			WCOM	Commercial information
			WCOP	Copyright/Legal information
			WCP	Copyright/Legal information
			WOAF	Official audio file webpage
			WOAR	Official artist/performer webpage
			WOAS	Official audio source webpage
			WORS	Official Internet radio station homepage
			WPAY	Payment
			WPB	Publishers official webpage
			WPUB	Publishers official webpage
			WXX	User defined URL link frame
			WXXX	User defined URL link frame
			TFEA	Featured Artist
			TSTU	Recording Studio
			rgad	Replay Gain Adjustment

		*/

		return getid3_lib::EmbeddedLookup($framename, $begin, __LINE__, __FILE__, 'id3v2-framename_long');

		// Last three:
		// from Helium2 [www.helium2.com]
		// from http://privatewww.essex.ac.uk/~djmrob/replaygain/file_format_id3v2.html
	}


	public static function FrameNameShortLookup($framename) {

		$begin = __LINE__;

		/** This is not a comment!

			AENC	audio_encryption
			APIC	attached_picture
			ASPI	audio_seek_point_index
			BUF	recommended_buffer_size
			CNT	play_counter
			COM	comment
			COMM	comment
			COMR	commercial_frame
			CRA	audio_encryption
			CRM	encrypted_meta_frame
			ENCR	encryption_method_registration
			EQU	equalisation
			EQU2	equalisation
			EQUA	equalisation
			ETC	event_timing_codes
			ETCO	event_timing_codes
			GEO	general_encapsulated_object
			GEOB	general_encapsulated_object
			GRID	group_identification_registration
			IPL	involved_people_list
			IPLS	involved_people_list
			LINK	linked_information
			LNK	linked_information
			MCDI	music_cd_identifier
			MCI	music_cd_identifier
			MLL	mpeg_location_lookup_table
			MLLT	mpeg_location_lookup_table
			OWNE	ownership_frame
			PCNT	play_counter
			PIC	attached_picture
			POP	popularimeter
			POPM	popularimeter
			POSS	position_synchronisation_frame
			PRIV	private_frame
			RBUF	recommended_buffer_size
			REV	reverb
			RVA	relative_volume_adjustment
			RVA2	relative_volume_adjustment
			RVAD	relative_volume_adjustment
			RVRB	reverb
			SEEK	seek_frame
			SIGN	signature_frame
			SLT	synchronised_lyric
			STC	synced_tempo_codes
			SYLT	synchronised_lyric
			SYTC	synchronised_tempo_codes
			TAL	album
			TALB	album
			TBP	bpm
			TBPM	bpm
			TCM	composer
			TCMP	part_of_a_compilation
			TCO	genre
			TCOM	composer
			TCON	genre
			TCOP	copyright_message
			TCP	part_of_a_compilation
			TCR	copyright_message
			TDA	date
			TDAT	date
			TDEN	encoding_time
			TDLY	playlist_delay
			TDOR	original_release_time
			TDRC	recording_time
			TDRL	release_time
			TDTG	tagging_time
			TDY	playlist_delay
			TEN	encoded_by
			TENC	encoded_by
			TEXT	lyricist
			TFLT	file_type
			TFT	file_type
			TIM	time
			TIME	time
			TIPL	involved_people_list
			TIT1	content_group_description
			TIT2	title
			TIT3	subtitle
			TKE	initial_key
			TKEY	initial_key
			TLA	language
			TLAN	language
			TLE	length
			TLEN	length
			TMCL	musician_credits_list
			TMED	media_type
			TMOO	mood
			TMT	media_type
			TOA	original_artist
			TOAL	original_album
			TOF	original_filename
			TOFN	original_filename
			TOL	original_lyricist
			TOLY	original_lyricist
			TOPE	original_artist
			TOR	original_year
			TORY	original_year
			TOT	original_album
			TOWN	file_owner
			TP1	artist
			TP2	band
			TP3	conductor
			TP4	remixer
			TPA	part_of_a_set
			TPB	publisher
			TPE1	artist
			TPE2	band
			TPE3	conductor
			TPE4	remixer
			TPOS	part_of_a_set
			TPRO	produced_notice
			TPUB	publisher
			TRC	isrc
			TRCK	track_number
			TRD	recording_dates
			TRDA	recording_dates
			TRK	track_number
			TRSN	internet_radio_station_name
			TRSO	internet_radio_station_owner
			TS2	album_artist_sort_order
			TSA	album_sort_order
			TSC	composer_sort_order
			TSI	size
			TSIZ	size
			TSO2	album_artist_sort_order
			TSOA	album_sort_order
			TSOC	composer_sort_order
			TSOP	performer_sort_order
			TSOT	title_sort_order
			TSP	performer_sort_order
			TSRC	isrc
			TSS	encoder_settings
			TSSE	encoder_settings
			TSST	set_subtitle
			TST	title_sort_order
			TT1	content_group_description
			TT2	title
			TT3	subtitle
			TXT	lyricist
			TXX	text
			TXXX	text
			TYE	year
			TYER	year
			UFI	unique_file_identifier
			UFID	unique_file_identifier
			ULT	unsychronised_lyric
			USER	terms_of_use
			USLT	unsynchronised_lyric
			WAF	url_file
			WAR	url_artist
			WAS	url_source
			WCM	commercial_information
			WCOM	commercial_information
			WCOP	copyright
			WCP	copyright
			WOAF	url_file
			WOAR	url_artist
			WOAS	url_source
			WORS	url_station
			WPAY	url_payment
			WPB	url_publisher
			WPUB	url_publisher
			WXX	url_user
			WXXX	url_user
			TFEA	featured_artist
			TSTU	recording_studio
			rgad	replay_gain_adjustment

		*/

		return getid3_lib::EmbeddedLookup($framename, $begin, __LINE__, __FILE__, 'id3v2-framename_short');
	}

	public static function TextEncodingTerminatorLookup($encoding) {
		// http://www.id3.org/id3v2.4.0-structure.txt
		// Frames that allow different types of text encoding contains a text encoding description byte. Possible encodings:
		static $TextEncodingTerminatorLookup = array(
			0   => "\x00",     // $00  ISO-8859-1. Terminated with $00.
			1   => "\x00\x00", // $01  UTF-16 encoded Unicode with BOM. All strings in the same frame SHALL have the same byteorder. Terminated with $00 00.
			2   => "\x00\x00", // $02  UTF-16BE encoded Unicode without BOM. Terminated with $00 00.
			3   => "\x00",     // $03  UTF-8 encoded Unicode. Terminated with $00.
			255 => "\x00\x00"
		);
		return (isset($TextEncodingTerminatorLookup[$encoding]) ? $TextEncodingTerminatorLookup[$encoding] : '');
	}

	public static function TextEncodingNameLookup($encoding) {
		// http://www.id3.org/id3v2.4.0-structure.txt
		// Frames that allow different types of text encoding contains a text encoding description byte. Possible encodings:
		static $TextEncodingNameLookup = array(
			0   => 'ISO-8859-1', // $00  ISO-8859-1. Terminated with $00.
			1   => 'UTF-16',     // $01  UTF-16 encoded Unicode with BOM. All strings in the same frame SHALL have the same byteorder. Terminated with $00 00.
			2   => 'UTF-16BE',   // $02  UTF-16BE encoded Unicode without BOM. Terminated with $00 00.
			3   => 'UTF-8',      // $03  UTF-8 encoded Unicode. Terminated with $00.
			255 => 'UTF-16BE'
		);
		return (isset($TextEncodingNameLookup[$encoding]) ? $TextEncodingNameLookup[$encoding] : 'ISO-8859-1');
	}

	public static function IsValidID3v2FrameName($framename, $id3v2majorversion) {
		switch ($id3v2majorversion) {
			case 2:
				return preg_match('#[A-Z][A-Z0-9]{2}#', $framename);
				break;

			case 3:
			case 4:
				return preg_match('#[A-Z][A-Z0-9]{3}#', $framename);
				break;
		}
		return false;
	}

	public static function IsANumber($numberstring, $allowdecimal=false, $allownegative=false) {
		for ($i = 0; $i < strlen($numberstring); $i++) {
			if ((chr($numberstring{$i}) < chr('0')) || (chr($numberstring{$i}) > chr('9'))) {
				if (($numberstring{$i} == '.') && $allowdecimal) {
					// allowed
				} elseif (($numberstring{$i} == '-') && $allownegative && ($i == 0)) {
					// allowed
				} else {
					return false;
				}
			}
		}
		return true;
	}

	public static function IsValidDateStampString($datestamp) {
		if (strlen($datestamp) != 8) {
			return false;
		}
		if (!self::IsANumber($datestamp, false)) {
			return false;
		}
		$year  = substr($datestamp, 0, 4);
		$month = substr($datestamp, 4, 2);
		$day   = substr($datestamp, 6, 2);
		if (($year == 0) || ($month == 0) || ($day == 0)) {
			return false;
		}
		if ($month > 12) {
			return false;
		}
		if ($day > 31) {
			return false;
		}
		if (($day > 30) && (($month == 4) || ($month == 6) || ($month == 9) || ($month == 11))) {
			return false;
		}
		if (($day > 29) && ($month == 2)) {
			return false;
		}
		return true;
	}

	public static function ID3v2HeaderLength($majorversion) {
		 
	}

}

}

?>