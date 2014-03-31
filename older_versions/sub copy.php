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
$GLOBALS['ACCEPTEDFILETYPES'] = array('.mp3');

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
		'web/scan' => 'scanEverything',
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

function scanEverything() {
	echo "Scanning Indexes...";
	scanIndexes();
	echo "Scanning folders...";
	scanAllFolders();
}

function test() {
	echo "MEOOOW";
	$fname = '/home/scottbuckley/buck.ly/temp/submusic/A Weather/Everyday Balloons/1. Third of Life.mp3';

	$rustart = getrusage();

	$oReader = new ID3TagsReader();
	for($i=0; $i<100;$i++)
		$m = $oReader->getTagsInfo($fname);
	print_r($m);

	$ru = getrusage();
	echo "This process used " . rutime($ru, $rustart, "utime") . " ms for its computations\n";
	echo "It spent " . rutime($ru, $rustart, "stime") . " ms in system calls\n";
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



 #####
#     #  ####    ##   #    # #    # # #    #  ####
#       #    #  #  #  ##   # ##   # # ##   # #    #
 #####  #      #    # # #  # # #  # # # #  # #
      # #      ###### #  # # #  # # # #  # # #  ###
#     # #    # #    # #   ## #   ## # #   ## #    #
 #####   ####  #    # #    # #    # # #    #  ####


function scanIndexes() {
	$topLevelFolders = getSubDirectories($GLOBALS['MUSIC_DIR']);
	$topLevelFoldersByLetter = splitByFirstLetter($topLevelFolders);
	dbWriteIndexes($topLevelFoldersByLetter);
	echo "done writing indexes!";
}

function scanAllFolders() {
	$indexes = dbReadIndexes();
	foreach($indexes as $id => $name) {
		$fullPath = $GLOBALS['MUSIC_DIR'].$name;
		dbBeginTrans();
		scanFolder($fullPath, $id, $name);
		dbEndTrans();
	}
}

function getSubDirectories($parent) {
	$files = array();
	$folders = array();
	getDirContents($parent,$files,$folders);
	return $folders;
}
function getDirContents($parent, &$files, &$folders) {
	// change the cwd to $parent
	$oldwd = getcwd();
	chdir($parent);

	$everything = scandir($parent);
	foreach ($everything as $item) {
		if (!in_array($item, $GLOBALS['IGNOREDFOLDERS'])) {
			if (is_dir($item)) {
				$folders[] = $item;
			} elseif (is_file($item)) {
				$files[] = $item;
			}
		}
	}

	// set the cwd back
	chdir($oldwd);
}
function isRightFileType($filename) {
	foreach ($GLOBALS['ACCEPTEDFILETYPES'] as $fileType) {
		if (endsWith($filename, $fileType))
			return true;
	}
	return false;
}

function scanFile($filename) {
	echo "<p><i>$filename</i></p>";
}
function scanFolder($parentPath, $parentId, $parentName) {
	echo "<p><b>$parentPath</b></p>";
	$containsMusic = false;

	$files = array(); $folders = array();
	getDirContents($parentPath,$files,$folders);

	foreach ($folders as $childName) {

		$childPath = "$parentPath/$childName";
		$dbChildId = dbCreateDirectory($childName, $parentId, $parentName, $childPath);
		echo "<p><u>CREATING DIR [$dbChildId] parentId=$parentId parentPath=$parentPath parentName=$parentName childName=$childName childPath=$childPath</u></p>";

 		if (scanFolder($childPath, $dbChildId, $childName)) {
			$containsMusic = true;
		} else {
			echo "<p><u>DERETING ROW $dbChildId</u></p>";
			//dbDeleteDirectory($dbChildId);
		}
	}

	foreach ($files as $file) {
		if (isRightFileType($file)) {
			scanFile("$file");
			$containsMusic = true;
		}
	}

	return $containsMusic;
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

function dbBeginTrans() {
	$db = dbConnect();
	$db->beginTransaction();
}

function dbEndTrans() {
	$db = dbConnect();
	$db->commit();
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
			parentname TEXT NOT NULL,
			fullpath TEXT NOT NULL UNIQUE
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
	$db->beginTransaction();
	foreach($indexes as $letter => $folders) {
		foreach($folders as $folder) {
			$q=$db->prepare('INSERT INTO tblIndexes (letterGroup, name) VALUES (?, ?);');
			$q->execute(array($letter, $folder));
		}
	}
	$db->commit();
}

function dbReadIndexes() {
	$db = dbConnect();
	$data = $db->query($sql = 'SELECT id, name FROM tblIndexes;');
	$result = array();
	foreach ($data as $row) {
		$id = $row['id'];
		$result[$id] = $row['name'];
	}
	return $result;
}

function dbCreateDirectory($childName, $parentId, $parentName, $childPath) {
	$db = dbConnect();
	$q=$db->prepare('INSERT INTO tblDirectories (name, parentid, parentname, fullpath) VALUES (?, ?, ?, ?);');
	$q->execute(array($childName, $parentId, $parentName, $childPath));
	return $db->lastInsertId();
}

function dbDeleteDirectory($directoryId) {
	$db = dbConnect();
	$q=$db->prepare('DELETE FROM tblDirectories WHERE id = ?;');
	$q->execute(array($directoryId));
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
function removeTrailingSlash($path) {
	if (endsWith($path, '/'))
		return substr($path, 0, strlen($path)-1);
	return $path;
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
function rutime($ru, $rus, $index) {
    return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
     -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
}





###
 #  #    # # ##### #   ##   #
 #  ##   # #   #   #  #  #  #
 #  # #  # #   #   # #    # #
 #  #  # # #   #   # ###### #
 #  #   ## #   #   # #    # #
### #    # #   #   # #    # ######

processURI();








#
#       # #####  #####    ##   #####  # ######  ####
#       # #    # #    #  #  #  #    # # #      #
#       # #####  #    # #    # #    # # #####   ####
#       # #    # #####  ###### #####  # #           #
#       # #    # #   #  #    # #   #  # #      #    #
####### # #####  #    # #    # #    # # ######  ####


// class ID3TagsReader from http://www.script-tutorials.com/id3-tags-reader-with-php/
class ID3TagsReader{var $aTV23=array('TIT2','TALB','TPE1','TRCK','TLEN');var $aTV23t=array('Title','Album','Author','Track','Length');var $aTV22=array('TT2','TAL','TP1','TRK','TLE');var $aTV22t=array('Title','Album','Author','Track','Length');function ID3TagsReader(){}function getTagsInfo($sFilepath){$iFSize=filesize($sFilepath);$vFD=fopen($sFilepath,'r');$sSrc=fread($vFD,$iFSize);fclose($vFD);if(substr($sSrc,0,3)=='ID3'){$aInfo['FileName']=$sFilepath;$aInfo['Version']=hexdec(bin2hex(substr($sSrc,3,1))).'.'.hexdec(bin2hex(substr($sSrc,4,1)));}if($aInfo['Version']=='4.0'||$aInfo['Version']=='3.0'){for($i=0;$i<count($this->aTV23);$i++){if(strpos($sSrc,$this->aTV23[$i].chr(0))!=FALSE){$s='';$iPos=strpos($sSrc,$this->aTV23[$i].chr(0));$iLen=hexdec(bin2hex(substr($sSrc,($iPos+5),3)));$data=substr($sSrc,$iPos,9+$iLen);for($a=0;$a<strlen($data);$a++){$char=substr($data,$a,1);if($char>=' '&&$char<='~')$s.=$char;}if(substr($s,0,4)==$this->aTV23[$i]){$iSL=4;if($this->aTV23[$i]=='USLT'){$iSL=7;}elseif($this->aTV23[$i]=='TALB'){$iSL=5;}elseif($this->aTV23[$i]=='TENC'){$iSL=6;}$aInfo[$this->aTV23t[$i]]=substr($s,$iSL);}}}}if($aInfo['Version']=='2.0'){for($i=0;$i<count($this->aTV22);$i++){if(strpos($sSrc,$this->aTV22[$i].chr(0))!=FALSE){$s='';$iPos=strpos($sSrc,$this->aTV22[$i].chr(0));$iLen=hexdec(bin2hex(substr($sSrc,($iPos+3),3)));$data=substr($sSrc,$iPos,6+$iLen);for($a=0;$a<strlen($data);$a++){$char=substr($data,$a,1);if($char>=' '&&$char<='~')$s.=$char;}if(substr($s,0,3)==$this->aTV22[$i]){$iSL=3;if($this->aTV22[$i]=='ULT'){$iSL=6;}$aInfo[$this->aTV22t[$i]]=substr($s,$iSL);}}}}return $aInfo;}}


?>