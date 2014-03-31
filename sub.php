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
$GLOBALS['SCAN_COUNTPERREFRESH'] = 75;

######
#     #   ##   #####   ##
#     #  #  #    #    #  #
#     # #    #   #   #    #
#     # ######   #   ######
#     # #    #   #   #    #
######  #    #   #   #    #

function getPageMap() {
	return array(
		'rest/getMusicDirectory.view' => 'getMusicDirectory',
		'rest/getMusicFolders.view' => 'getMusicFolders',
		'rest/getRandomSongs.view' => 'getRandomSongs',
		'rest/getStarred.view' => 'getStarredSongs',
		'rest/getCoverArt.view' => 'getCoverArt',
		'rest/getIndexes.view' => 'getIndexes',
		'rest/getLicense.view' => 'getLicense',
		'rest/stream.view' => 'stream',
		'rest/ping.view' => 'ping',
		'web/createdb' => 'createDB',
		'web/test' => 'test',
		'web/scan' => 'scanEverything',
		'web/scan2' => 'scanTracks_First',
		'web/scan3' => 'scanTracks_Second',
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
</subsonic-response>
';



		case 'DefaultMusicFolders': return
' <musicFolders>
  <musicFolder id="0" name="Music"/>
 </musicFolders>';

		case 'RandomSongs': return
' <randomSongs>
 </randomSongs>';

		case 'StarredSongs': return
' <starred>
 </starred>';

		case 'DBACCESSERROR': return
'Unfortunately we were unable to create/access a database file. Make sure there is write access to this folder.';

		case 'XMLIndexLetterGroup': return 
'  <index name="%s">%s
  </index>';

		case 'XMLIndexWrapper': return 
' <indexes lastModified="1379394451000">%s
 </indexes>';

		case 'XMLDirectoryWrapper': return 
'  <directory id="%s">%s
 </directory>';


		case 'XMLIndexArtist': return '   <artist name="%s" id="%s"/>';

		case 'XMLDirectoryFolder': return '   <child id="%s" parent="%s" title="%s" isDir="true"/>';

		case 'XMLDirectoryFile': return '   <child id="%s" parent="%s" title="%s" album="%s" artist="%s" isDir="false" duration="%s" path="%s" type="music" suffix="%s"/>';

		default: return 'TEMPLATE "$templateName" NOT FOUND';
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
	$indexes = dbGetIndexes();
	$indexes = splitIntoSubarrays($indexes);
	echo formatIndexesXML($indexes);
}

function getMusicFolders() {
	setContentType('XML');
	echo wrapServiceXML(getTemplate('DefaultMusicFolders'));
}

function getMusicDirectory() {
	setContentType('XML');
	$id = $_REQUEST['id'];
	$folders = dbGetSubFolders($id);
	$files = dbGetSubFiles($id);
	echo formatMusicDir($id, $folders, $files);
}

function getCoverArt() {
	setContentType('XML');
	echo "now we is getting the cover art :)";
}

function getRandomSongs() {
	setContentType('XML');
	echo wrapServiceXML(getTemplate('RandomSongs'));
}

function getStarredSongs() {
	setContentType('XML');
	echo wrapServiceXML(getTemplate('StarredSongs'));
}

function ping() {
	setContentType('XML');
	echo wrapServiceXML('');
}

function getLicense() {
	setContentType('XML');
	echo wrapServiceXML('<license valid="true"/>');
}

function stream() {
	setContentType('MPEG');
	$id = $_REQUEST['id'];
	streamMP3($id);
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

function scanTracks_First() {
	echo "<p>scanning mp3 data</p>";
	scanMP3Info_First();
	echo '
<script language="javascript" type="text/javascript">
	setTimeout(function() {
		window.location.href=window.location.href;
	}, 2000);
</script>
';
}

function scanTracks_Second() {
	echo "<p>scanning mp3 data, second pass</p>";
	scanMP3Info_Second();
}

function test() {
	$f = '/home/scottbuckley/buck.ly/temp/submusic/Air/Talkie Walkie/04 - Universal Traveler.mp3';
	$f = '/home/scottbuckley/buck.ly/temp/submusic/A Weather/Everyday Balloons/1. Third of Life.mp3';
	//$f = '/home/scottbuckley/buck.ly/temp/submusic/Alopecia/03 These Few Presidents.mp3';

	$getID3 = mp3lib();
	set_time_limit(30);
    $ThisFileInfo = $getID3->analyze($f);
    getid3_lib::CopyTagsToComments($ThisFileInfo); 
    print_r($ThisFileInfo);


	// $m = new mp3file($f);
	// $a = $m->get_metadata();
	// print_r($a);


	//$mp3 = new mp3; 
	//$d = $mp3->get_mp3($f);
	//print_r($d);
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
function streamMP3($id) {
	$fname = dbGetMP3filename($id);
	header("Content-Length: " . filesize($fname));
	$fp = fopen($fname, 'rb');
	fpassthru($fp);
	exit;
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
		case 'MPEG':
			header('Content-Type: audio/mpeg'); break;
	}
}
function wrapServiceXML($inner) {
	return sprintf(getTemplate('XMLServiceWrapper'), $inner);
}
function formatIndexesXML($indexes) {
	$letterTemplate  = getTemplate('XMLIndexLetterGroup');
	$artistTemplate  = getTemplate('XMLIndexArtist');
	$indexesTemplate = getTemplate('XMLIndexWrapper');
	
	$out = "";
	foreach(array_keys($indexes) as $letter) {
		$thisLetter = "";
		foreach($indexes[$letter] as $artist) {
			$thisLetter .= "\n";
			$thisLetter .= sprintf($artistTemplate, safeXMLencode($artist['name']), $artist['id']);
		}
		$out .= "\n";
		$out .= sprintf($letterTemplate, $letter, $thisLetter);
	}

	$out = sprintf($indexesTemplate, $out);
	return wrapServiceXML($out);
}
function formatMusicDir($id, $folders, $files) {
	$XMLDirectoryWrapper = getTemplate('XMLDirectoryWrapper');
	$XMLDirectoryFolder  = getTemplate('XMLDirectoryFolder');
	$XMLDirectoryFile    = getTemplate('XMLDirectoryFile');

	$out = "";
	foreach ($folders as $folder) {
		$_id = $folder['id'];
		$_name = $folder['name'];
		$out .= "\n";
		$out .= sprintf($XMLDirectoryFolder, $_id, $id, $_name);
	}

	foreach ($files as $file) {
		$_id = $file['id'];
		$_parentid = $file['parentid'];
		$_relpath = safeXMLencode($file['relpath']);
		$_filename = safeXMLencode($file['filename']);
		$_title = safeXMLencode($file['title']);
		$_artist = safeXMLencode($file['artist']);
		$_album = safeXMLencode($file['album']);
		$_duration = round(floatval($file['duration']));
		$_suffix = safeXMLencode($file['fileextension']);
		$out .= "\n";
		$out .= sprintf($XMLDirectoryFile, $_id, $_parentid, $_title, $_album, $_artist, $_duration, $_relpath, $_suffix);
	}


	$out = sprintf($XMLDirectoryWrapper, $id, $out);
	return wrapServiceXML($out);
}



 #####
#     #  ####    ##   #    # #    # # #    #  ####
#       #    #  #  #  ##   # ##   # # ##   # #    #
 #####  #      #    # # #  # # #  # # # #  # #
      # #      ###### #  # # #  # # # #  # # #  ###
#     # #    # #    # #   ## #   ## # #   ## #    #
 #####   ####  #    # #    # #    # # #    #  ####

function mp3lib() {
	static $getid3;
	if (isset($getid3)) {
		return $getid3;
	} else {
		require_once('GetID3/getid3.php');
		$getid3 = new getID3;
		$getid3->option_save_attachments = false;
		$getid3->option_tag_lyrics3 = false;
		set_time_limit(30);
		return $getid3;
	}
}

function getID3Data($filename) {
	$getid3 = mp3lib();
	$data = $getid3->analyze($filename);
	getid3_lib::CopyTagsToComments($data); 
	return $data;
}

function scanIndexes() {
	$topLevelFolders = getSubDirectories($GLOBALS['MUSIC_DIR']);
	$topLevelFoldersByLetter = splitByFirstLetter($topLevelFolders);
	dbWriteIndexes($topLevelFoldersByLetter);
	echo "done writing indexes!";
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

function scanAllFolders() {
	$indexes = dbReadIndexes();
	foreach($indexes as $id => $name) {
		$fullPath = $GLOBALS['MUSIC_DIR'].$name;
		dbBeginTrans();
		scanFolder($fullPath, $id, $name, NULL);
		dbEndTrans();
	}	echo "done scanning folders!";

}

function scanFolder($parentPath, $parentId, $parentName, $grandParentName) {
	$containsMusic = false;

	$files = array(); $folders = array();
	getDirContents($parentPath,$files,$folders);

	foreach ($folders as $childName) {

		$childPath = "$parentPath/$childName";
		$dbChildId = dbCreateDirectory($childName, $parentId, $parentName, $childPath);

 		if (scanFolder($childPath, $dbChildId, $childName, $parentName)) {
			$containsMusic = true;
		} else {
			dbDeleteDirectory($dbChildId);
		}
	}

	foreach ($files as $file) {
		if (isRightFileType($file)) {
			$filePath = "$parentPath/$file";
			$fileExtn = substr($file, strrpos($file, '.')+1);
			$relPath = substr($filePath, strlen($GLOBALS['MUSIC_DIR']));
			dbWriteTrackData($filePath, $file, $fileExtn, $parentId, $parentName, $grandParentName, $relPath);
			$containsMusic = true;
		}
	}

	return $containsMusic;
}

function isRightFileType($filename) {
	foreach ($GLOBALS['ACCEPTEDFILETYPES'] as $fileType) {
		if (endsWith($filename, $fileType))
			return true;
	}
	return false;
}

function scanMP3Info_First() {
	dbBeginTrans();
	$mp3s = dbGetUnscannedTracks($GLOBALS['SCAN_COUNTPERREFRESH']);
	foreach($mp3s as $mp3) {
		$meta = getMP3Info($mp3);
		$meta = localize_GetID3_results($meta);
		dbUpdateTrackInfo($mp3, $meta);
	}
	dbEndTrans();
}

function scanMP3Info_Second() {
	//$mp3Scanner = new mp3; 
	$mp3s = dbGetIncompleteTracks(10);
	foreach($mp3s as $mp3) {
		$fname = $mp3['fullpath'];
		$data = getID3Data($fname);
		print_r($data['comments']);
		echo "\n\n\n";
	}
}

function getMP3Info($filename) {
	echo "<p>$filename</p>";
	return getID3Data($filename);
}

function legacy_getMP3Info($filename) {
	echo "$filename<br>";
	$all = array();
	$id3 = new ID3TagsReader();
	$id3 = $id3->getTagsInfoProgressive($filename);
	$meta = new mp3file($filename);
	$meta = $meta->get_metadata();

	apush('Title',  $id3, $all);
	apush('Album',  $id3, $all);
	apush('Author', $id3, $all);
	apush('Track',  $id3, $all);
	apush('Version', $id3, $all);
	apush('LengthID3', $id3, $all);

	apush('Filesize',      $meta, $all);
	apush('Encoding',      $meta, $all);
	apush('Bitrate',       $meta, $all);
	apush('Sampling Rate', $meta, $all);
	apush('Length',        $meta, $all);

	$all['DurationFinal'] = max(intornull($all['Length']), intornull($all['LengthID3']));
	$all['Track'] = intornull($all['Track']);

	return $all;
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
			parentid INTEGER,
			parentname TEXT NOT NULL,
			fullpath TEXT NOT NULL UNIQUE
		);

		DELETE FROM SQLITE_SEQUENCE WHERE name="tblDirectories";
		REPLACE INTO SQLITE_SEQUENCE (seq, name) VALUES (999999, "tblDirectories");

	'); echo "tblDirectories created.<br/>";
	$db->exec('
		CREATE TABLE IF NOT EXISTS tblTrackData (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			parentid INTEGER NOT NULL,
			parentname TEXT,
			grandparentname TEXT,
			title TEXT,
			album TEXT,
			artist TEXT,
			track INTEGER,
			id3version STRING,
			duration INTEGER,
			encoding TEXT,
			bitrate INTEGER,
			samplerate INTEGER,
			filesize INTEGER,
			fullpath TEXT UNIQUE,
			filename TEXT,
			fileextension TEXT,
			relpath TEXT
		);'); echo "tblTrackData created.<br/>";
	$db->exec('
		CREATE VIEW IF NOT EXISTS vwTracks as
			SELECT * FROM tblTrackData WHERE
				(title IS NOT NULL
				AND album IS NOT NULL
				AND artist IS NOT NULL
				AND track IS NOT NULL
				AND id3version IS NOT NULL
				AND duration IS NOT NULL
				AND encoding IS NOT NULL
				AND bitrate IS NOT NULL
				AND samplerate IS NOT NULL
				AND filesize IS NOT NULL)
		;'); echo "vwTracks created.<br/>";
	$db->exec('
		CREATE VIEW IF NOT EXISTS vwUnscannedTracks as
			SELECT * FROM tblTrackData WHERE
				(title IS NULL
				AND album IS NULL
				AND artist IS NULL
				AND track IS NULL
				AND id3version IS NULL
				AND duration IS NULL
				AND encoding IS NULL
				AND bitrate IS NULL
				AND samplerate IS NULL
				AND filesize IS NULL)
		;'); echo "vwTracks created.<br/>";
	$db->exec('
		CREATE VIEW IF NOT EXISTS vwIncompleteTracks as
			SELECT * FROM tblTrackData WHERE
				(title IS NULL
				OR album IS NULL
				OR artist IS NULL
				OR track IS NULL
				OR id3version IS NULL
				OR duration IS NULL
				OR encoding IS NULL
				OR bitrate IS NULL
				OR samplerate IS NULL
				OR filesize IS NULL)
		;'); echo "vwUnscannedTracks created.<br/>";
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

function dbWriteTrackData($filePath, $filename, $fileExtn, $parentId, $parentName, $grandParentName, $relPath) {
	$db = dbConnect();
	$q=$db->prepare('INSERT INTO tblTrackData (fullpath, filename, fileextension, parentid, parentname, grandparentname, relpath) VALUES (?, ?, ?, ?, ?)');
	$q->execute(array($filePath, $filename, $fileExtn, $parentId, $parentName, $grandParentName, $relPath));
}

function dbGetUnscannedTracks($limit) {

	$db = dbConnect();
	$q=$db->prepare('SELECT COUNT(*) FROM vwUnscannedTracks;');
	$q->execute();
	$count = $q->fetch();
	$count = $count[0];
	echo "<p>Remaining: $count</p>";

	$q=$db->prepare('SELECT fullpath FROM vwUnscannedTracks LIMIT ?;');
	$q->execute(array($limit));
	$data=$q->fetchAll(PDO::FETCH_COLUMN, 0);
	return $data;
}

function dbGetIncompleteTracks($limit) {

	$db = dbConnect();
	$q=$db->prepare('SELECT COUNT(*) FROM vwIncompleteTracks;');
	$q->execute();
	$count = $q->fetch();
	$count = $count[0];
	echo "<p>Remaining: $count</p>";

	$q=$db->prepare('SELECT * FROM vwIncompleteTracks WHERE duration=0 LIMIT ?;');
	//$q->execute();
	$q->execute(array($limit));
	$data=$q->fetchAll();
	return $data;
}

function dbUpdateTrackInfo($fullpath, $data) {
	$db = dbConnect();
	$q=$db->prepare('UPDATE tblTrackData SET title=?,
	                                          album=?,
	                                          artist=?,
	                                          track=?,
		                                      duration=?,
		                                      id3version=?,
		                                      encoding=?,
		                                      bitrate=?,
		                                      samplerate=?,
		                                      filesize=?
		                                WHERE fullpath=?');
	$q->execute(array($data['Title'],
	                  $data['Album'],
	                  $data['Author'],
	                  $data['Track'],
	                  $data['DurationFinal'],
	                  $data['Version'],
	                  $data['Encoding'],
	                  $data['Bitrate'],
	                  $data['Sampling Rate'],
	                  $data['Filesize'],
	                  $fullpath));
}

function dbGetIndexes() {
	$db = dbConnect();
	$q=$db->query('SELECT id, name, letterGroup FROM tblIndexes;');
	$data = $q->fetchAll();
	return $data;
}

function dbGetSubFolders($id) {
	$db = dbConnect();
	$q=$db->prepare('SELECT id, name FROM tblDirectories WHERE parentid=?;');
	$q->execute(array($id));
	$data = $q->fetchAll();
	return $data;
}

function dbGetSubFiles($id) {
	$db = dbConnect();
	$q=$db->prepare('SELECT * FROM vwTracks WHERE parentid=?');
	$q->execute(array($id));
	$data = $q->fetchAll();
	return $data;
}

function dbGetMP3filename($id) {
	$db = dbConnect();
	$q=$db->prepare('SELECT fullpath FROM tblTrackData WHERE id=?');
	$q->execute(array($id));
	$data = $q->fetchAll(PDO::FETCH_COLUMN, 0);
	return $data[0];
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
function safeXMLencode($string) {
	return htmlspecialchars($string);
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
function splitIntoSubarrays($indexes) {
	$result = array();
	foreach($indexes as $index) {
		$letterGroup = $index['letterGroup'];
		if (!array_key_exists($letterGroup, $result)) {
			$result[$letterGroup] = array();
		}
		$result[$letterGroup][] = $index;
	}
	return $result;
}
function apush($key, $src, &$dest) {
	if (isset($src[$key]))
		$dest[$key] = $src[$key];
	else
		$dest[$key] = null;
}
function intornull($a) {
	if ($a == null)
		return null;
	return intval($a);
}

function localize_GetID3_results($data) {
	$new = array();
	$new['Title'] = $data['comments']['title'][0];
	$new['Album'] = $data['comments']['album'][0];
	$new['Author'] = $data['comments']['artist'][0];
	$new['Track'] = $data['comments']['track_number'][0];
	$new['DurationFinal'] = $data['playtime_seconds'];
	$new['Version'] = isset($data['id3v2'])?2:(isset($data['id3v1'])?1:0);
	$new['Encoding'] = $data['audio']['bitrate_mode'];
	$new['Bitrate'] = $data['audio']['bitrate'];
	$new['Sampling Rate'] = $data['audio']['sample_rate'];
	$new['Filesize'] = $data['filesize'];
	return $new;
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


// class ID3TagsReader originally from http://www.script-tutorials.com/id3-tags-reader-with-php/
// however this code has been quite heavily modified from the original version, to remove bugs
// and increase performance.
class ID3TagsReader {

    // variables
    var $aTV23 = array( // array of possible sys tags (for last version of ID3)
        'TIT2',
        'TALB',
        'TPE1',
        'TRCK',
        'TLEN',
    );
    var $aTV23t = array( // array of titles for sys tags
        'Title',
        'Album',
        'Author',
        'Track',
        'LengthID3',
    );
    var $aTV22 = array( // array of possible sys tags (for old version of ID3)
        'TT2',
        'TAL',
        'TP1',
        'TRK',
        'TLE',
    );
    var $aTV22t = array( // array of titles for sys tags
        'Title',
        'Album',
        'Author',
        'Track',
        'LengthID3',
    );

    // constructor
    function ID3TagsReader() {}

    // functions

    function getTagsInfoProgressive($sFilepath) {
        $intervalbytes = 100000;
        $curBytes = "";
    	$iFSize = filesize($sFilepath);
        $vFD = fopen($sFilepath,'r');

        $result = array();
        for ($i=0; $i<5; $i++) {
        	$curBytes .= fread($vFD, $intervalbytes);
        	//echo "trying. cur length " . strlen($curBytes) . " bytes<br>";
        	$result = $this->getTagsInfoFromString($curBytes);
        	if (isset($result['Title'])) {
        		//echo "success!<br>";
        		return $result;
        	}
        	$intervalbytes *= 2;
        }
        //echo "nope :( <br>";
        return $result;

    }

    function getTagsInfo($sFilepath) {
        // read source file
        $iFSize = filesize($sFilepath);
        $vFD = fopen($sFilepath,'r');
        $sSrc = fread($vFD,$iFSize);
        $this->getTagsInfoFromString($sSrc);
        fclose($vFD);
    }

    function getTagsInfoFromString($sSrc) {

        // obtain base info
        if (substr($sSrc,0,3) == 'ID3') {
            $aInfo['Version'] = hexdec(bin2hex(substr($sSrc,3,1))).'.'.hexdec(bin2hex(substr($sSrc,4,1)));
        }

        // passing through possible tags of idv2 (v3 and v4)
        if ($aInfo['Version'] == '4.0' || $aInfo['Version'] == '3.0') {
            for ($i = 0; $i < count($this->aTV23); $i++) {
            	$curTag = $this->aTV23[$i];
            	$curTag0 = $curTag . chr(0);
            	$iPos = strpos($sSrc, $curTag0);
                if ($iPos != FALSE) {

                    $s = '';
                    $iLen = hexdec(bin2hex(substr($sSrc,($iPos + 5),3)));

                    $data = substr($sSrc, $iPos + strlen($curTag) + 4, 2 + $iLen);

                    for ($a = 0; $a < strlen($data); $a++) {
                        $char = substr($data, $a, 1);
                        if ($char >= ' ' && $char <= '~')
                            $s .= $char;
                    }
                    //if (substr($s, 0, strlen($curTag)) == $curTag) {
                        $aInfo[$this->aTV23t[$i]] = $s; //substr($s, strlen($curTag));
                    //}
                }
            }
        }

        // passing through possible tags of idv2 (v2)
        if($aInfo['Version'] == '2.0') {
            for ($i = 0; $i < count($this->aTV22); $i++) {
                if (strpos($sSrc, $this->aTV22[$i].chr(0)) != FALSE) {

                    $s = '';
                    $iPos = strpos($sSrc, $this->aTV22[$i].chr(0));
                    $iLen = hexdec(bin2hex(substr($sSrc,($iPos + 3),3)));

                    $data = substr($sSrc, $iPos, 6 + $iLen);
                    for ($a = 0; $a < strlen($data); $a++) {
                        $char = substr($data, $a, 1);
                        if ($char >= ' ' && $char <= '~')
                            $s .= $char;
                    }

                    if (substr($s, 0, 3) == $this->aTV22[$i]) {
                        $iSL = 3;
                        if ($this->aTV22[$i] == 'ULT') {
                            $iSL = 6;
                        }
                        $aInfo[$this->aTV22t[$i]] = substr($s, $iSL);
                    }
                }
            }
        }
        return $aInfo;
    }
}

// mp3 non-id3 metadata. from:
// http://www.zedwood.com/article/php-calculate-duration-of-mp3
class mp3file{protected $a;protected $c;protected $d;protected $e;protected $f;protected $g;protected $h;public function __construct($k){$this->powarr=array(0=>1,1=>2,2=>4,3=>8,4=>16,5=>32,6=>64,7=>128);$this->blockmax=1024;$this->mp3data=array();$this->mp3data['Filesize']=filesize($k);$this->fd=fopen($k,'rb');$this->prefetchblock();$this->readmp3frame();}public function __destruct(){fclose($this->fd);}public function get_metadata(){return $this->mp3data;}protected function readmp3frame(){$l=true;if($this->startswithid3())$this->skipid3tag();else if($this->containsvbrxing()){$this->mp3data['Encoding']='VBR';$l=false;}else if($this->startswithpk()){$this->mp3data['Encoding']=Null;$l=false;}if($l){$m=0;$o=5000;for($m=0;$m<$o;$m++){if($this->getnextbyte()==0xFF)if($this->getnextbit()&&$this->getnextbit()&&$this->getnextbit())break;}if($m==$o)$l=false;}if($l){$this->mp3data['Encoding']='CBR';$this->mp3data['MPEG version']=$this->getnextbits(2);$this->mp3data['Layer Description']=$this->getnextbits(2);$this->mp3data['Protection Bit']=$this->getnextbits(1);$this->mp3data['Bitrate Index']=$this->getnextbits(4);$this->mp3data['Sampling Freq Idx']=$this->getnextbits(2);$this->mp3data['Padding Bit']=$this->getnextbits(1);$this->mp3data['Private Bit']=$this->getnextbits(1);$this->mp3data['Channel Mode']=$this->getnextbits(2);$this->mp3data['Mode Extension']=$this->getnextbits(2);$this->mp3data['Copyright']=$this->getnextbits(1);$this->mp3data['Original Media']=$this->getnextbits(1);$this->mp3data['Emphasis']=$this->getnextbits(1);$this->mp3data['Bitrate']=mp3file::bitratelookup($this->mp3data);$this->mp3data['Sampling Rate']=mp3file::samplelookup($this->mp3data);$this->mp3data['Frame Size']=mp3file::getframesize($this->mp3data);$this->mp3data['Length']=mp3file::getduration($this->mp3data,$this->tell2());$this->mp3data['Length mm:ss']=mp3file::seconds_to_mmss($this->mp3data['Length']);if($this->mp3data['Bitrate']=='bad'||$this->mp3data['Bitrate']=='free'||$this->mp3data['Sampling Rate']==Null||$this->mp3data['Frame Size']==Null||$this->mp3data['Length']==Null)$this->mp3data=array('Filesize'=>$this->mp3data['Filesize'],'Encoding'=>Null);}else{if(!isset($this->mp3data['Encoding']))$this->mp3data['Encoding']=Null;}}protected function tell(){return ftell($this->fd);}protected function tell2(){return ftell($this->fd)-$this->blockmax+$this->blockpos-1;}protected function startswithid3(){return($this->block[1]==73&&$this->block[2]==68&&$this->block[3]==51);}protected function startswithpk(){return($this->block[1]==80&&$this->block[2]==75);}protected function containsvbrxing(){return(($this->block[37]==88&&$this->block[38]==105&&$this->block[39]==110&&$this->block[40]==103));}protected function debugbytes(){for($p=0;$p<10;$p++){for($m=0;$m<8;$m++){if($m==4)echo" ";echo $this->getnextbit();}echo "<BR>";}}protected function prefetchblock(){$a=fread($this->fd,$this->blockmax);$this->blocksize=strlen($a);$this->block=unpack("C*",$a);$this->blockpos=0;}protected function skipid3tag(){$q=$this->getnextbits(24);$q.=$this->getnextbits(24);$r=array();$r['ID3v2 Major version']=bindec(substr($q,24,8));$r['ID3v2 Minor version']=bindec(substr($q,32,8));$r['ID3v2 flags']=bindec(substr($q,40,8));if(substr($q,40,1))$r['Unsynchronisation']=true;if(substr($q,41,1))$r['Extended header']=true;if(substr($q,42,1))$r['Experimental indicator']=true;if(substr($q,43,1))$r['Footer present']=true;$s="";for($m=0;$m<4;$m++){$this->getnextbit();$s.=$this->getnextbits(7);}$r['ID3v2 Tags Size']=bindec($s);if($r['ID3v2 Tags Size']-$this->blockmax>0){fseek($this->fd,$r['ID3v2 Tags Size']+10);$this->prefetchblock();if(isset($r['Footer present'])&&$r['Footer present']){for($m=0;$m<10;$m++)$this->getnextbyte();}}else{for($m=0;$m<$r['ID3v2 Tags Size'];$m++)$this->getnextbyte();}}protected function getnextbit(){if($this->bitpos==8)return false;$t=0;$u=7-$this->bitpos;$v=$this->powarr[$u];$t=$this->block[$this->blockpos+1]&$v;$t=$t>>$u;$this->bitpos++;if($this->bitpos==8){$this->blockpos++;if($this->blockpos==$this->blockmax){$this->prefetchblock();}else if($this->blockpos==$this->blocksize){return;}$this->bitpos=0;}return $t;}protected function getnextbits($w=1){$t="";for($m=0;$m<$w;$m++)$t.=$this->getnextbit();return $t;}protected function getnextbyte(){if($this->blockpos>=$this->blocksize)return;$this->bitpos=0;$t=$this->block[$this->blockpos+1];$this->blockpos++;return $t;}public static function is_layer1(&$x){return($x['Layer Description']=='11');}public static function is_layer2(&$x){return($x['Layer Description']=='10');}public static function is_layer3(&$x){return($x['Layer Description']=='01');}public static function is_mpeg10(&$x){return($x['MPEG version']=='11');}public static function is_mpeg20(&$x){return($x['MPEG version']=='10');}public static function is_mpeg25(&$x){return($x['MPEG version']=='00');}public static function is_mpeg20or25(&$x){return($x['MPEG version']{1}=='0');}public static function bitratelookup(&$x){$y=array();$y['0000']=array('free','free','free','free','free');$y['0001']=array('32','32','32','32','8');$y['0010']=array('64','48','40','48','16');$y['0011']=array('96','56','48','56','24');$y['0100']=array('128','64','56','64','32');$y['0101']=array('160','80','64','80','40');$y['0110']=array('192','96','80','96','48');$y['0111']=array('224','112','96','112','56');$y['1000']=array('256','128','112','128','64');$y['1001']=array('288','160','128','144','80');$y['1010']=array('320','192','160','160','96');$y['1011']=array('352','224','192','176','112');$y['1100']=array('384','256','224','192','128');$y['1101']=array('416','320','256','224','144');$y['1110']=array('448','384','320','256','160');$y['1111']=array('bad','bad','bad','bad','bad');$z=-1;if(mp3file::is_mpeg10($x)&&mp3file::is_layer1($x))$z=0;else if(mp3file::is_mpeg10($x)&&mp3file::is_layer2($x))$z=1;else if(mp3file::is_mpeg10($x)&&mp3file::is_layer3($x))$z=2;else if(mp3file::is_mpeg20or25($x)&&mp3file::is_layer1($x))$z=3;else if(mp3file::is_mpeg20or25($x)&&(mp3file::is_layer2($x)||mp3file::is_layer3($x)))$z=4;if(isset($y[$x['Bitrate Index']][$z]))return $y[$x['Bitrate Index']][$z];else return "bad";}public static function samplelookup(&$x){$y=array();$y['00']=array('44100','22050','11025');$y['01']=array('48000','24000','12000');$y['10']=array('32000','16000','8000');$y['11']=array('res','res','res');$z=-1;if(mp3file::is_mpeg10($x))$z=0;else if(mp3file::is_mpeg20($x))$z=1;else if(mp3file::is_mpeg25($x))$z=2;if(isset($y[$x['Sampling Freq Idx']][$z]))return $y[$x['Sampling Freq Idx']][$z];else return Null;}public static function getframesize(&$x){if($x['Sampling Rate']>0){return ceil((144*$x['Bitrate']*1000)/$x['Sampling Rate'])+$x['Padding Bit'];}return Null;}public static function getduration(&$x,$aa){if($x['Bitrate']>0){$bb=($x['Bitrate']*1000)/8;$cc=($x['Filesize']-($aa/8));$dd=$cc/$bb;return sprintf("%d",$dd);}return "unknown";}public static function seconds_to_mmss($ee){return sprintf("%d:%02d",($ee/60),$ee%60);}}


/*
	$Author: mfboy
	$Version: 0.1.2
	$Date: 2009-04-11
	from http://www.phpclasses.org/browse/file/26606.html
*/

// removed for now

?>



