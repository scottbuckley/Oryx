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
	$oReader = new ID3TagsReader();
	$m = $oReader->getTagsInfo($fname);
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


// hopefully i can keep this empty <3

// class ID3TagsReader
class ID3TagsReader {

    // variables
    var $aTV23 = array( // array of possible sys tags (for last version of ID3)
        'TIT2',
        'TALB',
        'TPE1',
        'TPE2',
        'TRCK',
        'TYER',
        'TLEN',
        'USLT',
        'TPOS',
        'TCON',
        'TENC',
        'TCOP',
        'TPUB',
        'TOPE',
        'WXXX',
        'COMM',
        'TCOM'
    );
    var $aTV23t = array( // array of titles for sys tags
        'Title',
        'Album',
        'Author',
        'AlbumAuthor',
        'Track',
        'Year',
        'Lenght',
        'Lyric',
        'Desc',
        'Genre',
        'Encoded',
        'Copyright',
        'Publisher',
        'OriginalArtist',
        'URL',
        'Comments',
        'Composer'
    );
    var $aTV22 = array( // array of possible sys tags (for old version of ID3)
        'TT2',
        'TAL',
        'TP1',
        'TRK',
        'TYE',
        'TLE',
        'ULT'
    );
    var $aTV22t = array( // array of titles for sys tags
        'Title',
        'Album',
        'Author',
        'Track',
        'Year',
        'Lenght',
        'Lyric'
    );

    // constructor
    function ID3TagsReader() {}

    // functions
    function getTagsInfo($sFilepath) {
        // read source file
        $iFSize = filesize($sFilepath);
        $vFD = fopen($sFilepath,'r');
        $sSrc = fread($vFD,$iFSize);
        fclose($vFD);

        // obtain base info
        if (substr($sSrc,0,3) == 'ID3') {
            $aInfo['FileName'] = $sFilepath;
            $aInfo['Version'] = hexdec(bin2hex(substr($sSrc,3,1))).'.'.hexdec(bin2hex(substr($sSrc,4,1)));
        }

        // passing through possible tags of idv2 (v3 and v4)
        if ($aInfo['Version'] == '4.0' || $aInfo['Version'] == '3.0') {
            for ($i = 0; $i < count($this->aTV23); $i++) {
                if (strpos($sSrc, $this->aTV23[$i].chr(0)) != FALSE) {

                    $s = '';
                    $iPos = strpos($sSrc, $this->aTV23[$i].chr(0));
                    $iLen = hexdec(bin2hex(substr($sSrc,($iPos + 5),3)));

                    $data = substr($sSrc, $iPos, 9 + $iLen);
                    for ($a = 0; $a < strlen($data); $a++) {
                        $char = substr($data, $a, 1);
                        if ($char >= ' ' && $char <= '~')
                            $s .= $char;
                    }
                    if (substr($s, 0, 4) == $this->aTV23[$i]) {
                        $iSL = 4;
                        if ($this->aTV23[$i] == 'USLT') {
                            $iSL = 7;
                        } elseif ($this->aTV23[$i] == 'TALB') {
                            $iSL = 5;
                        } elseif ($this->aTV23[$i] == 'TENC') {
                            $iSL = 6;
                        }
                        $aInfo[$this->aTV23t[$i]] = substr($s, $iSL);
                    }
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


?>