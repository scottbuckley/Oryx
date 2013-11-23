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

		case 'XMLDirectoryFile': return '   <child id="%s" parent="%s" title="%s" album="%s" artist="%s" isDir="false" duration="%s" path="%s" type="music"/>';

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
	echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	echo '<subsonic-response xmlns="http://subsonic.org/restapi" status="ok" version="1.9.0">'."\n";
	echo '</subsonic-response>'."\n";
}

function getLicense() {
	setContentType('XML');
	echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	echo '<subsonic-response xmlns="http://subsonic.org/restapi" status="ok" version="1.9.0">'."\n";
	echo '<license valid="true"/>'."\n";
	echo '</subsonic-response>'."\n";
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
	$f = '/home/scottbuckley/buck.ly/temp/submusic/Gotye/Like Drawing Blood/05-gotye-thanks_for_your_time.mp3';

	$mp3 = new mp3; 

	$d = $mp3->get_mp3($f);

	print_r($d);
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
//'   <child id="%s" parent="%s" title="%s" album="%s" artist="%s" isDir="false" duration="%s" path="%s" type="music"/>';
	foreach ($files as $file) {
		$_id = $file['id'];
		$_parentid = $file['parentid'];
		$_fullpath = safeXMLencode($file['fullpath']);
		$_filename = safeXMLencode($file['filename']);
		$_title = safeXMLencode($file['title']);
		$_artist = safeXMLencode($file['artist']);
		$_album = safeXMLencode($file['album']);
		$_duration = intval($file['duration']);
		$out .= "\n";
		$out .= sprintf($XMLDirectoryFile, $_id, $_parentid, $_filename, $_album, $_artist, $_duration, $_fullpath);
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
		scanFolder($fullPath, $id, $name, true);
		dbEndTrans();
	}
}

function scanFolder($parentPath, $parentId, $parentName, $isIndex) {
	$containsMusic = false;

	$files = array(); $folders = array();
	getDirContents($parentPath,$files,$folders);

	foreach ($folders as $childName) {

		$childPath = "$parentPath/$childName";
		$dbChildId = dbCreateDirectory($childName, $parentId, $parentName, $childPath, $isIndex);

 		if (scanFolder($childPath, $dbChildId, $childName, false)) {
			$containsMusic = true;
		} else {
			dbDeleteDirectory($dbChildId);
		}
	}

	foreach ($files as $file) {
		if (isRightFileType($file)) {
			$filePath = "$parentPath/$file";
			dbWriteTrackData($filePath, $file, $parentId);
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
	$mp3s = dbGetUnscannedTracks(300);
	foreach($mp3s as $mp3) {
		$meta = getMP3Info($mp3);
		dbUpdateTrackInfo($mp3, $meta);
	}
	dbEndTrans();
}

function scanMP3Info_Second() {
	$mp3Scanner = new mp3; 
	$mp3s = dbGetIncompleteTracks(10);
	foreach($mp3s as $mp3) {

		$fname = $mp3['fullpath'];
		echo "$fname<br/>";
		$data = $mp3Scanner->get_mp3($fname, true, false);
		print_r($data['data']);
		echo "\n\n\n";
	}
}

function getMP3Info($filename) {
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
			indexparentid INTEGER,
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
			filename TEXT
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

function dbCreateDirectory($childName, $parentId, $parentName, $childPath, $isIndex) {
	$db = dbConnect();
	if ($isIndex)
		$q=$db->prepare('INSERT INTO tblDirectories (name, indexparentid, parentname, fullpath) VALUES (?, ?, ?, ?);');
	else
		$q=$db->prepare('INSERT INTO tblDirectories (name, parentid, parentname, fullpath) VALUES (?, ?, ?, ?);');

	$q->execute(array($childName, $parentId, $parentName, $childPath));
	return $db->lastInsertId();
}

function dbDeleteDirectory($directoryId) {
	$db = dbConnect();
	$q=$db->prepare('DELETE FROM tblDirectories WHERE id = ?;');
	$q->execute(array($directoryId));
}

function dbWriteTrackData($filePath, $filename, $parentId) {
	$db = dbConnect();
	$q=$db->prepare('INSERT INTO tblTrackData (fullpath, filename, parentid) VALUES (?, ?, ?)');
	$q->execute(array($filePath, $filename, $parentId));
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

	$q=$db->prepare('SELECT * FROM vwIncompleteTracks LIMIT 20, ?;');
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
	$q=$db->prepare('SELECT id, name FROM tblDirectories WHERE indexparentid=? OR indexparentid IS NULL AND parentid=?;');
	$q->execute(array($id, $id));
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
class mp3file{protected $a;protected $c;protected $d;protected $e;protected $f;protected $g;protected $h;public function __construct($k){$this->powarr=array(0=>1,1=>2,2=>4,3=>8,4=>16,5=>32,6=>64,7=>128);$this->blockmax=1024;$this->mp3data=array();$this->mp3data['Filesize']=filesize($k);$this->fd=fopen($k,'rb');$this->prefetchblock();$this->readmp3frame();}public function __destruct(){fclose($this->fd);}public function get_metadata(){return $this->mp3data;}protected function readmp3frame(){$l=true;if($this->startswithid3())$this->skipid3tag();else if($this->containsvbrxing()){$this->mp3data['Encoding']='VBR';$l=false;}else if($this->startswithpk()){$this->mp3data['Encoding']='Unknown';$l=false;}if($l){$m=0;$o=5000;for($m=0;$m<$o;$m++){if($this->getnextbyte()==0xFF)if($this->getnextbit()&&$this->getnextbit()&&$this->getnextbit())break;}if($m==$o)$l=false;}if($l){$this->mp3data['Encoding']='CBR';$this->mp3data['MPEG version']=$this->getnextbits(2);$this->mp3data['Layer Description']=$this->getnextbits(2);$this->mp3data['Protection Bit']=$this->getnextbits(1);$this->mp3data['Bitrate Index']=$this->getnextbits(4);$this->mp3data['Sampling Freq Idx']=$this->getnextbits(2);$this->mp3data['Padding Bit']=$this->getnextbits(1);$this->mp3data['Private Bit']=$this->getnextbits(1);$this->mp3data['Channel Mode']=$this->getnextbits(2);$this->mp3data['Mode Extension']=$this->getnextbits(2);$this->mp3data['Copyright']=$this->getnextbits(1);$this->mp3data['Original Media']=$this->getnextbits(1);$this->mp3data['Emphasis']=$this->getnextbits(1);$this->mp3data['Bitrate']=mp3file::bitratelookup($this->mp3data);$this->mp3data['Sampling Rate']=mp3file::samplelookup($this->mp3data);$this->mp3data['Frame Size']=mp3file::getframesize($this->mp3data);$this->mp3data['Length']=mp3file::getduration($this->mp3data,$this->tell2());$this->mp3data['Length mm:ss']=mp3file::seconds_to_mmss($this->mp3data['Length']);if($this->mp3data['Bitrate']=='bad'||$this->mp3data['Bitrate']=='free'||$this->mp3data['Sampling Rate']=='unknown'||$this->mp3data['Frame Size']=='unknown'||$this->mp3data['Length']=='unknown')$this->mp3data=array('Filesize'=>$this->mp3data['Filesize'],'Encoding'=>'Unknown');}else{if(!isset($this->mp3data['Encoding']))$this->mp3data['Encoding']='Unknown';}}protected function tell(){return ftell($this->fd);}protected function tell2(){return ftell($this->fd)-$this->blockmax+$this->blockpos-1;}protected function startswithid3(){return($this->block[1]==73&&$this->block[2]==68&&$this->block[3]==51);}protected function startswithpk(){return($this->block[1]==80&&$this->block[2]==75);}protected function containsvbrxing(){return(($this->block[37]==88&&$this->block[38]==105&&$this->block[39]==110&&$this->block[40]==103));}protected function debugbytes(){for($p=0;$p<10;$p++){for($m=0;$m<8;$m++){if($m==4)echo" ";echo $this->getnextbit();}echo "<BR>";}}protected function prefetchblock(){$a=fread($this->fd,$this->blockmax);$this->blocksize=strlen($a);$this->block=unpack("C*",$a);$this->blockpos=0;}protected function skipid3tag(){$q=$this->getnextbits(24);$q.=$this->getnextbits(24);$r=array();$r['ID3v2 Major version']=bindec(substr($q,24,8));$r['ID3v2 Minor version']=bindec(substr($q,32,8));$r['ID3v2 flags']=bindec(substr($q,40,8));if(substr($q,40,1))$r['Unsynchronisation']=true;if(substr($q,41,1))$r['Extended header']=true;if(substr($q,42,1))$r['Experimental indicator']=true;if(substr($q,43,1))$r['Footer present']=true;$s="";for($m=0;$m<4;$m++){$this->getnextbit();$s.=$this->getnextbits(7);}$r['ID3v2 Tags Size']=bindec($s);if($r['ID3v2 Tags Size']-$this->blockmax>0){fseek($this->fd,$r['ID3v2 Tags Size']+10);$this->prefetchblock();if(isset($r['Footer present'])&&$r['Footer present']){for($m=0;$m<10;$m++)$this->getnextbyte();}}else{for($m=0;$m<$r['ID3v2 Tags Size'];$m++)$this->getnextbyte();}}protected function getnextbit(){if($this->bitpos==8)return false;$t=0;$u=7-$this->bitpos;$v=$this->powarr[$u];$t=$this->block[$this->blockpos+1]&$v;$t=$t>>$u;$this->bitpos++;if($this->bitpos==8){$this->blockpos++;if($this->blockpos==$this->blockmax){$this->prefetchblock();}else if($this->blockpos==$this->blocksize){return;}$this->bitpos=0;}return $t;}protected function getnextbits($w=1){$t="";for($m=0;$m<$w;$m++)$t.=$this->getnextbit();return $t;}protected function getnextbyte(){if($this->blockpos>=$this->blocksize)return;$this->bitpos=0;$t=$this->block[$this->blockpos+1];$this->blockpos++;return $t;}public static function is_layer1(&$x){return($x['Layer Description']=='11');}public static function is_layer2(&$x){return($x['Layer Description']=='10');}public static function is_layer3(&$x){return($x['Layer Description']=='01');}public static function is_mpeg10(&$x){return($x['MPEG version']=='11');}public static function is_mpeg20(&$x){return($x['MPEG version']=='10');}public static function is_mpeg25(&$x){return($x['MPEG version']=='00');}public static function is_mpeg20or25(&$x){return($x['MPEG version']{1}=='0');}public static function bitratelookup(&$x){$y=array();$y['0000']=array('free','free','free','free','free');$y['0001']=array('32','32','32','32','8');$y['0010']=array('64','48','40','48','16');$y['0011']=array('96','56','48','56','24');$y['0100']=array('128','64','56','64','32');$y['0101']=array('160','80','64','80','40');$y['0110']=array('192','96','80','96','48');$y['0111']=array('224','112','96','112','56');$y['1000']=array('256','128','112','128','64');$y['1001']=array('288','160','128','144','80');$y['1010']=array('320','192','160','160','96');$y['1011']=array('352','224','192','176','112');$y['1100']=array('384','256','224','192','128');$y['1101']=array('416','320','256','224','144');$y['1110']=array('448','384','320','256','160');$y['1111']=array('bad','bad','bad','bad','bad');$z=-1;if(mp3file::is_mpeg10($x)&&mp3file::is_layer1($x))$z=0;else if(mp3file::is_mpeg10($x)&&mp3file::is_layer2($x))$z=1;else if(mp3file::is_mpeg10($x)&&mp3file::is_layer3($x))$z=2;else if(mp3file::is_mpeg20or25($x)&&mp3file::is_layer1($x))$z=3;else if(mp3file::is_mpeg20or25($x)&&(mp3file::is_layer2($x)||mp3file::is_layer3($x)))$z=4;if(isset($y[$x['Bitrate Index']][$z]))return $y[$x['Bitrate Index']][$z];else return "bad";}public static function samplelookup(&$x){$y=array();$y['00']=array('44100','22050','11025');$y['01']=array('48000','24000','12000');$y['10']=array('32000','16000','8000');$y['11']=array('res','res','res');$z=-1;if(mp3file::is_mpeg10($x))$z=0;else if(mp3file::is_mpeg20($x))$z=1;else if(mp3file::is_mpeg25($x))$z=2;if(isset($y[$x['Sampling Freq Idx']][$z]))return $y[$x['Sampling Freq Idx']][$z];else return 'unknown';}public static function getframesize(&$x){if($x['Sampling Rate']>0){return ceil((144*$x['Bitrate']*1000)/$x['Sampling Rate'])+$x['Padding Bit'];}return 'unknown';}public static function getduration(&$x,$aa){if($x['Bitrate']>0){$bb=($x['Bitrate']*1000)/8;$cc=($x['Filesize']-($aa/8));$dd=$cc/$bb;return sprintf("%d",$dd);}return "unknown";}public static function seconds_to_mmss($ee){return sprintf("%d:%02d",($ee/60),$ee%60);}}


/*
	$Author: mfboy
	$Version: 0.1.2
	$Date: 2009-04-11
	from http://www.phpclasses.org/browse/file/26606.html
*/

class mp3 {

	private $fp, $filesize, $fileanalysis;
	private $id3v1, $id3v2, $data;

	private $audio_frames, $audio_frames_total;
	private $pos_audio_start, $pos_audio_end;

	private $bitrate_max, $bitrate_min, $bitrate_sum;

	var $id3v1_genres = array
		(
		'Blues', 'Classic Rock', 'Country', 'Dance', 'Disco', 'Funk', 'Grunge', 'Hip-Hop', 'Jazz', 'Metal', 
		'New Age', 'Oldies', 'Other', 'Pop', 'R&B', 'Rap', 'Reggae', 'Rock', 'Techno', 'Industrial', 
		'Alternative', 'Ska', 'Death Metal', 'Pranks', 'Soundtrack', 'Euro-Techno', 'Ambient', 'Trip-Hop', 'Vocal', 'Jazz+Funk', 
		'Fusion', 'Trance', 'Classical', 'Instrumental', 'Acid', 'House', 'Game', 'Sound Clip', 'Gospel', 'Noise', 
		'AlternRock', 'Bass', 'Soul', 'Punk', 'Space', 'Meditative', 'Instrumental Pop', 'Instrumental Rock', 'Ethnic', 'Gothic', 
		'Darkwave', 'Techno-Industrial', 'Electronic', 'Pop-Folk', 'Eurodance', 'Dream', 'Southern Rock', 'Comedy', 'Cult', 'Gangsta', 
		'Top 40', 'Christian Rap', 'Pop/Funk', 'Jungle', 'Native American', 'Cabaret', 'New Wave', 'Psychadelic', 'Rave', 'Showtunes', 
		'Trailer', 'Lo-Fi', 'Tribal', 'Acid Punk', 'Acid Jazz', 'Polka', 'Retro', 'Musical', 'Rock & Roll', 'Hard Rock', 
		'Folk', 'Folk/Rock', 'National Folk', 'Swing', 'Fast-Fusion', 'Bebob', 'Latin', 'Revival', 'Celtic', 'Bluegrass', 
		'Advantgarde', 'Gothic Rock', 'Progressive Rock', 'Psychadelic Rock', 'Symphonic Rock', 'Slow Rock', 'Big Band', 'Chorus', 'Easy Listening', 'Acoustic', 
		'Humour', 'Speech', 'Chanson', 'Opera', 'Chamber Music', 'Sonata', 'Symphony', 'Booty Bass', 'Primus', 'Porn Groove', 
		'Satire', 'Slow Jam', 'Club', 'Tango', 'Samba', 'Folklore'
		);

	var $id3v2_frame_descriptions = array
		(
		'AENC' => 'Audio encryption',
		'APIC' => 'Attached picture',
		'COMM' => 'Comments',
		'COMR' => 'Commercial frame',
		'ENCR' => 'Encryption method registration',
		'EQUA' => 'Equalization',
		'ETCO' => 'Event timing codes',
		'GEOB' => 'General encapsulated object',
		'GRID' => 'Group identification registration',
		'IPLS' => 'Involved people list',
		'LINK' => 'Linked information',
		'MCDI' => 'Music CD identifier',
		'MLLT' => 'MPEG location lookup table',
		'OWNE' => 'Ownership frame',
		'PRIV' => 'Private frame',
		'PCNT' => 'Play counter',
		'POPM' => 'Popularimeter',
		'POSS' => 'Position synchronisation frame',
		'RBUF' => 'Recommended buffer size',
		'RVAD' => 'Relative volume adjustment',
		'RVRB' => 'Reverb',
		'SYLT' => 'Synchronized lyric/text',
		'SYTC' => 'Synchronized tempo codes',
		'TALB' => 'Album/Movie/Show title',
		'TBPM' => 'BPM (beats per minute)',
		'TCOM' => 'Composer',
		'TCON' => 'Content type',
		'TCOP' => 'Copyright message',
		'TDAT' => 'Date',
		'TDLY' => 'Playlist delay',
		'TENC' => 'Encoded by',
		'TEXT' => 'Lyricist/Text writer',
		'TFLT' => 'File type',
		'TIME' => 'Time',
		'TIT1' => 'Content group description',
		'TIT2' => 'Title/songname/content description',
		'TIT3' => 'Subtitle/Description refinement',
		'TKEY' => 'Initial key',
		'TLAN' => 'Language(s)',
		'TLEN' => 'Length',
		'TMED' => 'Media type',
		'TOAL' => 'Original album/movie/show title',
		'TOFN' => 'Original filename',
		'TOLY' => 'Original lyricist(s)/text writer(s)',
		'TOPE' => 'Original artist(s)/performer(s)',
		'TORY' => 'Original release year',
		'TOWN' => 'File owner/licensee',
		'TPE1' => 'Lead performer(s)/Soloist(s)',
		'TPE2' => 'Band/orchestra/accompaniment',
		'TPE3' => 'Conductor/performer refinement',
		'TPE4' => 'Interpreted, remixed, or otherwise modified by',
		'TPOS' => 'Part of a set',
		'TPUB' => 'Publisher',
		'TRCK' => 'Track number/Position in set',
		'TRDA' => 'Recording dates',
		'TRSN' => 'Internet radio station name',
		'TRSO' => 'Internet radio station owner',
		'TSIZ' => 'Size',
		'TSRC' => 'ISRC (international standard recording code)',
		'TSSE' => 'Software/Hardware and settings used for encoding',
		'TYER' => 'Year',
		'UFID' => 'Unique file identifier',
		'USER' => 'Terms of use',
		'USLT' => 'Unsychronized lyric/text transcription',
		'WCOM' => 'Commercial information',
		'WCOP' => 'Copyright/Legal information',
		'WOAF' => 'Official audio file webpage',
		'WOAR' => 'Official artist/performer webpage',
		'WOAS' => 'Official audio source webpage',
		'WORS' => 'Official internet radio station homepage',
		'WPAY' => 'Payment',
		'WPUB' => 'Publishers official webpage'
		);

	var $bitrates = array
		(
		'0000' => array(array('~', '~', '~'), array('~', '~', '~')),
		'0001' => array(array('32', '32', '32'), array('32', '8', '8')),
		'0010' => array(array('64', '48', '40'), array('48', '16', '16')),
		'0011' => array(array('96', '56', '48'), array('56', '24', '24')),
		'0100' => array(array('128', '64', '56'), array('64', '32', '32')),
		'0101' => array(array('160', '80', '64'), array('80', '40', '40')),
		'0110' => array(array('192', '96', '80'), array('96', '48', '48')),
		'0111' => array(array('224', '112', '96'), array('112', '56', '56')),
		'1000' => array(array('256', '128', '112'), array('128', '64', '64')),
		'1001' => array(array('288', '160', '128'), array('144', '80', '80')),
		'1010' => array(array('320', '192', '160'), array('160', '96', '96')),
		'1011' => array(array('352', '224', '192'), array('176', '112', '112')),
		'1100' => array(array('384', '256', '224'), array('192', '128', '128')),
		'1101' => array(array('416', '320', '256'), array('224', '144', '144')),
		'1110' => array(array('448', '384', '320'), array('256', '160', '160'))
		);

	var $sampling_frequencys = array
		(
		'00' => array('44100', '22050', '11025'),
		'01' => array('48000', '24000', '12000'),
		'10' => array('32000', '16000', '8000')
		);

	var $modes = array
		(
		'00' => 'Stereo',
		'01' => 'Joint Stereo',
		'10' => 'Dual Channel',
		'11' => 'Single Channel'
		);

	var $mode_extensions = array
		(
		'00' => array(0, 0),
		'01' => array(1, 0),
		'10' => array(0, 1),
		'11' => array(1, 1),
		);

	function get_mp3($filepath, $analysis = false, $getframesindex = false) {

		$getframesindex = $analysis ? $getframesindex : false;
		$this->fileanalysis = intval(!empty($analysis)) + intval(!empty($getframesindex));

		if(!$this->fp = @fopen($filepath, 'rb')) {
			return false;
		}

		$this->filesize = filesize($filepath);
		$this->id3v1 = $this->id3v2 = $this->data = array();

		$this->audio_frames = array();
		$this->audio_frames_total = 0;

		$this->pos_audio_start = $this->pos_audio_end = 0;
		$this->bitrate_max = $this->bitrate_min = $this->bitrate_sum = 0;

		$this->get_id3v2();
		$this->get_id3v1();
		$this->get_data();

		$return = array
			(
			'data' => $this->data,
			'id3v2' => $this->id3v2,
			'id3v1' => $this->id3v1,
			'frames' => $getframesindex ? $this->audio_frames : false
			);

		foreach($return as $variable => $value) {
			if(!$value) {
				unset($return[$variable]);
			}
		}

		return $return;

	}

	private function get_id3v2() {

		$pos_call = ftell($this->fp);
		$tag = array();

		$tagheaderdata = fread($this->fp, 10);
		$tagheader = @unpack('a3identifier/Cversion/Crevision/Cflag/Csize0/Csize1/Csize2/Csize3', $tagheaderdata);

		if(!$tagheader || $tagheader['identifier'] != 'ID3') {
			fseek($this->fp, $pos_call);
			return false;
		}

		$tag['version'] = $tagheader['version'];
		$tag['revision'] = $tagheader['revision'];

		$tagflag = $this->conv_flag($tagheader['flag']);

		$tag['flag'] = array
			(
			'unsynchronisation' => $tagflag{0},
			'extra' => $tagflag{1},
			'istest' => $tagflag{2}
			);

		$tagsize = ($tagheader['size0'] & 0x7F) << 21
			| ($tagheader['size1'] & 0x7F) << 14
			| ($tagheader['size2'] & 0x7F) << 7
			| ($tagheader['size3']);

		if(($tagsize = intval($tagsize)) < 1) {
			return false;
		}

		$tag['size'] = $tagsize;
		$tag['frames'] = array();

		$pos_start = ftell($this->fp);
		$pos_end = $pos_start + $tagsize - 10;

		while(1) {

			if(ftell($this->fp) >= $pos_end) {
				break;
			}

			$frameheaderdata = fread($this->fp, 10);
			$frameheader = @unpack('a4frameid/Nsize/Cflag0/Cflag1', $frameheaderdata);

			if(!$frameheader || !$frameheader['frameid']) {
				continue;
			}

			$frameid = $frameheader['frameid'];
			$framedescription = 'Unknown';

			if(isset($this->id3v2_frame_descriptions[$frameid])) {
				$framedescription = $this->id3v2_frame_descriptions[$frameid];
			} else {
				switch(strtoupper($frameid{0})) {
					case 'T': $framedescription = 'User defined text information frame'; break;
					case 'W': $framedescription = 'User defined URL link frame'; break;
				}
			}

			if(($framesize = $frameheader['size']) < 1 || (ftell($this->fp) + $framesize) > $pos_end) {
				continue;
			}

			$frameflag = array
				(
				$this->conv_flag($frameheader['flag0']),
				$this->conv_flag($frameheader['flag1'])
				);

			$framecharsetdata = @unpack('c', fread($this->fp, 1));
			$framecharset = '';

			switch($framecharsetdata) {
				case 0: $framecharset = 'ISO-8859-1'; break;
				case 1: $framecharset = 'UTF-16'; break;
				case 2: $framecharset = 'UTF-16BE'; break;
				case 3: $framecharset = 'UTF-8'; break;
			}

			if($framecharset) {
				$framedatasize = $framesize - 1;
			} else {
				$framedatasize = $framesize;
				fseek($this->fp, ftell($this->fp) - 1);
			}

			$framedata = @unpack("a{$framedatasize}data", fread($this->fp, $framedatasize));
			$framedata = $framedata['data'];

			if($frameid == 'COMM') {
				$framelang = substr($framedata, 0, 3);
				$framedata = substr($framedata, 3 + ($framedata{3} == "\x00" ? 1 : 0));
			} else {
				$framelang = '';
			}

			$frame = array
				(
				'frameid' => $frameid,
				'description' => $framedescription,
				'flag' => array
					(
					'tag_protect' => $frameflag[0]{0},
					'file_protect' => $frameflag[0]{1},
					'readonly' => $frameflag[0]{2},
					'compressed' => $frameflag[1]{0},
					'encrypted' => $frameflag[1]{1},
					'group' => $frameflag[1]{2},
					),
				'size' => $framesize,
				'data' => $framedata
				);

			$framecharset && $frame['charset'] = $framecharset;
			$framelang && $frame['language'] = $framelang;

			$tag['frames'][$frameid][] = $frame;

		}

		if($this->id3v2) {
			if(!isset($this->id3v2[0])) {
				$id3v2 = $this->id3v2;
				$this->id3v2 = array($id3v2);
			}
			$this->id3v2[] = $tag;
		} else {
			$this->id3v2 = $tag;
		}

		$this->pos_audio_start = $pos_end;
		return true;

	}

	private function get_id3v1() {

		$tagsize = 128;
		$tagstart = $this->filesize - $tagsize;

		fseek($this->fp, $tagstart);

		$tagdata = fread($this->fp, $tagsize);
		$tag = @unpack('a3header/a30title/a30artist/a30album/a4year/a28comment/Creserve/Ctrack/Cgenre', $tagdata);

		if($tag['header'] == 'TAG') {
			$this->pos_audio_end = $this->filesize - $tagsize;
		} else {
			$this->pos_audio_end = $this->filesize;
			return false;
		}

		$tag['genre'] = $this->id3v1_genres[$tag['genre']];
		$tag['genre'] = $tag['genre'] ? $tag['genre'] : 'Unknown';

		unset($tag['header']);
		$this->id3v1 = $tag;

		return true;

	}

	private function get_data() {

		while(1) {

			fseek($this->fp, $this->pos_audio_start);
			$checkdata = fread($this->fp, 3);

			if($checkdata == "ID3") {
				if(!$this->get_id3v2()) {
					return false;
				}
			} else {
				fseek($this->fp, $this->pos_audio_start);
				break;
			}

		}

		$padding_data = fread($this->fp, 1024);
		$padding_size = @max(0, strpos($padding_data, trim($padding_data)));

		fseek($this->fp, $this->pos_audio_start + $padding_size);

		if($this->fileanalysis > 0) {

			if(!$framedata = $this->get_data_frames()) {
				return false;
			}

		} else {

			$first_frame_header_data = fread($this->fp, 4);
			$first_frame_header = $this->get_frameheader($first_frame_header_data);

			if(!$first_frame_header || !is_array($first_frame_header)) {
				return false;
			}

			$framedata = fread($this->fp, 36);
			$frametype = strpos($framedata, 'Xing') ? 'VBR' : 'CBR';

			if($frametype == 'CBR') {
				$frametotal = $this->get_data_cbr($first_frame_header);
			} else {
				$frametotal = $this->get_data_vbr($first_frame_header);
			}

			$framedata = $first_frame_header;
			unset($framedata['framesize']);

			$framedata['frametotal'] = $frametotal;
			$framedata['type'] = $frametype;

		}

		$framelength = $framedata['frametotal'] * 0.026;
		$frametime = $this->conv_time(round($framelength));

		$framedata['length'] = $framelength;
		$framedata['time'] = $frametime;
		$framedata['filesize'] = $this->filesize;

		$this->data = $framedata;
		return true;

	}

	private function get_data_frames() {

		$first_frame = array();
		$frame_total = 0;

		while(1) {

			$frameheaders = fread($this->fp, 4);
			$pos_frame = ftell($this->fp);

			if($pos_frame >= $this->pos_audio_end) {
				break;
			}

			if(!$frameheader = $this->get_frameheader($frameheaders)) {
				break;
			}

			$first_frame = $first_frame ? $first_frame : $frameheader;
			extract($frameheader);

			$this->bitrate_min = $this->bitrate_min > 0 ? min($this->bitrate_min, $bitrate) : $bitrate;
			$this->bitrate_max = max($this->bitrate_max, $bitrate);
			$this->bitrate_sum += $bitrate;

			if($this->fileanalysis > 1) {
				$this->audio_frames[] = array($pos_frame - 4, $bitrate, $framesize);
			}

			fseek($this->fp, $pos_frame + $framesize - 4);
			$frame_total++;

		}

		$first_frame['bitrate'] = @round($this->bitrate_sum / $frame_total);
		$first_frame['frametotal'] = $frame_total;

		if($this->bitrate_max != $this->bitrate_min) {
			$first_frame['bitrate_max'] = $this->bitrate_max;
			$first_frame['bitrate_min'] = $this->bitrate_min;
			$first_frame['type'] = 'VBR';
		} else {
			$first_frame['type'] = 'CBR';
		}

		unset($first_frame['framesize']);

		return $first_frame;

	}

	private function get_data_cbr($frameheader) {

		extract($frameheader);
		$audio_size = $this->pos_audio_end - $this->pos_audio_start;

		return @ceil($audio_size / $framesize);

	}

	private function get_data_vbr($frameheader) {

		$framevbrdata = @unpack('NVBR', fread($this->fp, 4));;
		$framevbrs = array(1, 3, 5, 7, 9, 11, 13, 15);

		if(!in_array($framevbrdata['VBR'], $framevbrs)) {
			return 0;
		}

		$frametotaldata = @unpack('Nframetotal', fread($this->fp, 4));
		$frametotal = $frametotaldata['frametotal'];

		return $frametotal;

	}

	function get_frameheader($frameheaders) {

		$frameheader = array();
		$frameheaderlength = 4;

		if(strlen($frameheaders) != $frameheaderlength) {
			return false;
		}

		for($i = 0; $i < $frameheaderlength; $i++) {
			$frameheader[] = $this->conv_flag(ord($frameheaders{$i}));
		}

		if($frameheaders{0} != "\xFF" || substr($frameheader[1], 0, 3) != '111') {
			return false;
		}

		switch(substr($frameheader[1], 3, 2)) {
			case '00': $mpegver = '2.5'; break;
			case '10': $mpegver = '2'; break;
			case '11': $mpegver = '1'; break;
			default: return false;
		}

		switch(substr($frameheader[1], 5, 2)) {
			case '01': $layer = '3'; break;
			case '10': $layer = '2'; break;
			case '11': $layer = '1'; break;
			default: return false;
		}

		$bitrate = substr($frameheader[2], 0, 4);
		$bitrate = $this->bitrates[$bitrate][intval($mpegver) - 1][intval($layer) - 1];

		$sampling_frequency = substr($frameheader[2], 4, 2);
		$sampling_frequency = $this->sampling_frequencys[$sampling_frequency][ceil($mpegver) - 1];

		if(!$bitrate || !$sampling_frequency) {
			return false;
		}

		$padding = $frameheader[2]{6};

		$mode = substr($frameheader[3], 0, 2);
		$mode = $this->modes[$mode];

		$mode_extension = substr($frameheader[3], 2, 2);
		$mode_extension = $this->mode_extensions[$mode_extension];

		if(!$mode || !$mode_extension) {
			return false;
		}

		$copyright = substr($frameheader[3], 4, 1) ? 1 : 0;
		$original = substr($frameheader[3], 5, 1) ? 1 : 0;

		switch($mpegver) {
			case '1':
				$definite = $layer == '1' ? 48 : 144;
				break;
			case '2': case '2.5':
				$definite = $layer == '1' ? 24 : 72;
				break;
			default:
				return false;
		}

		$framesize = intval($definite * $bitrate * 1000 / $sampling_frequency + intval($padding));

		return array
			(
			'mpegver' => $mpegver,
			'layer' => $layer,
			'bitrate' => $bitrate,
			'sampling_frequency' => $sampling_frequency,
			'padding' => $padding,
			'mode' => $mode,
			'mode_extension' => array
				(
				'Intensity_Stereo' => $mode_extension[0],
				'MS_Stereo' => $mode_extension[1]
				),
			'copyright' => $copyright,
			'original' => $original,
			'framesize' => $framesize
			);

	}

	function set_mp3($file_input, $file_output, $id3v2 = array(), $id3v1 = array()) {

		if(!$mp3 = $this->get_mp3($file_input)) {
			return false;
		}

		if(!$fp = @fopen($file_output, 'wb')) {
			return false;
		}

		$id3v2 = is_array($id3v2) ? $id3v2 : array();
		$id3v1 = is_array($id3v1) ? $id3v1 : array();

		$id3v2_data = $id3v1_data = '';
		fseek($this->fp, $this->pos_audio_start);

		$audio_length = $this->pos_audio_end - $this->pos_audio_start;
		$audio_data = fread($this->fp, $audio_length);

		foreach($id3v2 as $frameid => $frame) {

			if(strlen($frameid) != 4 || !is_array($frame)) {
				continue;
			}

			$frameid = strtoupper($frameid);
			$framecharset = 0;

			$frameflag = array
				(
				0 => bindec(($frame['tag_protect'] ? '1' : '0').($frame['file_protect'] ? '1' : '0').($frame['readonly'] ? '1' : '0').'00000'),
				1 => bindec(($frame['compressed'] ? '1' : '0').($frame['encrypted'] ? '1' : '0').($frame['group'] ? '1' : '0').'00000'),
				);

			if($frame['charset'] = strtolower($frame['charset'])) {
				switch($frame['charset']) {
					case 'UTF-16': $framecharset = 1; break;
					case 'UTF-16BE': $framecharset = 2; break;
					case 'UTF-8': $framecharset = 3; break;
				}
			}

			$framedata = chr($framecharset).$frame['data'];
			$framesize = strlen($framedata);

			$id3v2_data .= pack('a4NCCa'.$framesize, $frameid, $framesize, $frameflag[0], $frameflag[1], $framedata);

		}

		if($id3v2_data) {

			$id3v2_flag = bindec(($id3v2['unsynchronisation'] ? '1' : '0').($id3v2['extra'] ? '1' : '0').($id3v2['istest'] ? '1' : '0').'00000');
			$id3v2_size = strlen($id3v2_data) + 10;

			$id3v2_sizes = array
				(
				0 => ($id3v2_size >> 21) & 0x7F,
				1 => ($id3v2_size >> 14) & 0x7F,
				2 => ($id3v2_size >> 7) & 0x7F,
				3 => $id3v2_size & 0x7F
				);

			$id3v2_header = pack('a3CCC', 'ID3', 3, 0, $id3v2_flag);
			$id3v2_header .= pack('CCCC', $id3v2_sizes[0], $id3v2_sizes[1], $id3v2_sizes[2], $id3v2_sizes[3]);

			$audio_data = $id3v2_header.$id3v2_data.$audio_data;

		}

		if($id3v1) {
			$id3v1_data = pack('a3a30a30a30a4a28CCC', 'TAG', $id3v1['title'], $id3v1['artist'], $id3v1['album'], $id3v1['year'], $id3v1['comment'], intval($id3v1['reserve']), intval($id3v1['track']), intval($id3v1['genre']));
			$audio_data .= $id3v1_data;
		}

		fwrite($fp, $audio_data);
		fclose($fp);

		return true;

	}

	function cut_mp3($file_input, $file_output, $startindex = 0, $endindex = -1, $indextype = 'frame', $cleantags = false) {

		if(!in_array($indextype, array('frame', 'second', 'percent'))) {
			return false;
		}

		if(!$mp3 = $this->get_mp3($file_input, true, true)) {
			return false;
		}

		if(!$mp3['data'] || !$mp3['frames']) {
			return false;
		}

		if(!$fp = @fopen($file_output, 'wb')) {
			return false;
		}

		$indexs = $mp3['frames'];
		$indextotal = count($mp3['frames']);

		$cutdata = '';
		$maxendindex = $indextotal - 1;

		if($indextype == 'second') {
			$startindex = ceil($startindex * (1 / 0.026));
			$endindex = $endindex > 0 ? ceil($endindex * (1 / 0.026)) : -1;
		} elseif ($indextype == 'percent') {
			$startindex = round($maxendindex * $startindex);
			$endindex = $endindex > 0 ? round($maxendindex * $endindex) : -1;
		}

		if($startindex < 0 || $start > $maxendindex) {
			return false;
		}

		$endindex = $endindex < 0 ? $maxendindex : $endindex;
		$endindex = min($endindex, $maxendindex);

		if($endindex <= $startindex) {
			return false;
		}

		$pos_start = $indexs[$startindex][0];
		$pos_end = $indexs[$endindex][0] + $indexs[$endindex][2];

		fseek($this->fp, $pos_start);
		$cutdata = fread($this->fp, $pos_end - $pos_start);

		if($mp3['data']['type'] == 'VBR') {

			fseek($this->fp, $indexs[0][0]);
			$frame = fread($this->fp, $indexs[0][2]);

			if(strpos($frame, 'Xing')) {

				$cutdata = substr($cutdata, $indexs[0][2]);

				$newvbr = substr($frame, 0, 4);
				$newvbr_sign_padding = 0;

				if($mp3['data']['mpegver'] == 1) {
					$newvbr_sign_padding = $mp3['data']['mode'] == $this->modes['11'] ? 16 : 31;
				} else if($mp3['data']['mpegver'] == 2) {
					$newvbr_sign_padding = $mp3['data']['mode'] == $this->modes['11'] ? 8 : 16;
				}

				if($newvbr_sign_padding) {

					$newvbr .= pack("a{$newvbr_sign_padding}a4", null, 'Xing');
					$newvbr .= pack('a'.(32 - $newvbr_sign_padding), null);
					$newvbr .= pack('NNNa100N', 1, $endindex - $startindex + 1, 0, null, 0);

					$newvbr .= pack('a'.($indexs[0][2] - strlen($newvbr)), null);
					$cutdata = $newvbr.$cutdata;

				}

			}

		}

		if(!$cleantags) {

			rewind($this->fp);

			if($this->pos_audio_start != 0) {
				$cutdata = fread($this->fp, $this->pos_audio_start).$cutdata;
			}

			if($this->pos_audio_end != $this->filesize) {
				fseek($this->fp, $this->pos_audio_end);
				$cutdata .= fread($this->fp, 128);
			}

		}

		fwrite($fp, $cutdata);
		fclose($fp);

		return true;

	}

	function conv_flag($flag, $convtobin = true, $length = 8) {

		$flag = $convtobin ? decbin($flag) : $flag;
		$recruit = $length - strlen($flag);

		if($recruit < 1) {
			return $flag;
		}

		return sprintf('%0'.$length.'d', $flag);

	}

	function conv_time($seconds) {

		$return = '';
		$separator = ':';

		if($seconds > 3600) {
			$return .= intval($seconds / 3600).' ';
			$seconds -= intval($seconds / 3600) * 3600;
		}

		if($seconds > 60) {
			$return .= sprintf('%02d', intval($seconds / 60)).' ';
			$seconds -= intval($seconds / 60) * 60;
		} else {
			$return .= '00 ';
		}

		$return .= sprintf('%02d', $seconds);
		$return = trim($return);

		return str_replace(' ', $separator, $return);

	}

}

?>



