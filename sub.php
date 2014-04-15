<?php

 #####
#     #  ####  #    # ###### #  ####  #    # #####    ##   ##### #  ####  #    #
#       #    # ##   # #      # #    # #    # #    #  #  #    #   # #    # ##   #
#       #    # # #  # #####  # #      #    # #    # #    #   #   # #    # # #  #
#       #    # #  # # #      # #  ### #    # #####  ######   #   # #    # #  # #
#     # #    # #   ## #      # #    # #    # #   #  #    #   #   # #    # #   ##
 #####   ####  #    # #      #  ####   ####  #    # #    #   #   #  ####  #    #

$GLOBALS['ABS_PATH'] = '/phpsub'; // NO TRAILING SLASH
$GLOBALS['SQL_FILENAME'] = 'sub.db';
$GLOBALS['MUSIC_DIR'] = '/home/scottbuckley/buck.ly/temp/submusic/';
$GLOBALS['LETTERGROUPS'] = array('A','B','C','D','E','F','G','H','I',
    'J','K','L','M','N','O','P','Q','R','S','T','U','V','W','XYZ', '#');
$GLOBALS['NONALPHALETTERGROUP'] = '#';
$GLOBALS['ARTICLES'] = array('The');
$GLOBALS['IGNOREDFOLDERS'] = array('..', '.');
$GLOBALS['ACCEPTEDFILETYPES'] = array('.mp3');
$GLOBALS['SCAN_COUNTPERREFRESH'] = 75;


#     #
#  #  # ###### #####
#  #  # #      #    #
#  #  # #####  #####
#  #  # #      #    #
#  #  # #      #    #
 ## ##  ###### #####

function getHome() { ?>
<html>
    <head>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.js"></script>
        <script src="http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.js"></script>
        <link href='http://fonts.googleapis.com/css?family=Roboto:100,300,400,900' rel='stylesheet' type='text/css'>
        <style type="text/css">
            * {
                font-family: 'Roboto', sans-serif;
                font-weight: 100;
            }
            div.globalWrapper {
                display: table;
                width: 100%;
                height: 100%;
            }
            div.innerWrapper {
                display: table-row;
                height: 100%;
            }
            div.leftPanelWrapper {
                display: table-cell;
                background-color: #EEE;
                width: 200px;
            }
            div.centrePanelWrapper {
                display: table-cell;
            }
            div.rightPanelWrapper {
                display: table-cell;
                background-color: #EEE;
                width: 300px;
            }
            div.leftPanel {
                overflow: scroll;
                height: 100%;
            }
            div.centrePanel {
                overflow:scroll;
                height:100%;
            }
            div.footer {
                display: table-row;
            }
            div.indexGroup {

            }
            div.indexGroupHeader {
                font-weight: 700;
                background-color: #BBB;
                padding-left: 8px;
            }
            div.indexItem {
                font-size: 10pt;
                padding-left: 12px;
            }
            div.indexItem:hover {
                background-color: #CCC;
                margin-left: 5px;
            }
            a.indexItemAnchor {
                color: #000;
                text-decoration: none;
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function()            { hashResponseMaybe(); });
            $(document).ready(function()            { loadIndexes();       });
            $(window).bind('hashchange', function() { hashResponse();      });
            _.mixin({
                forEachOrOnce: function(obj, iterator, context) {
                    if (_.isArray(obj))
                        _.forEach(obj, iterator, context);
                    else
                        iterator.call(context, obj, 0, obj);
                }
                ,
                forceArray: function(obj) {
                    if (_.isArray(obj))
                        return obj;
                    return [obj];
                } 
            });


            function loadDirectoryStupid(dirId) {
                $(".centrePanel").load("rest/getMusicDirectory.view?id="+dirId+"&f=json");
            }

            function loadDirectory(dirId) {
                var centrePanel = $(".centrePanel");
                centrePanel.empty();
                centrePanel.append("requesting...");



                // get and process a json directory listing
                $.getJSON("rest/getMusicDirectory.view?id="+dirId+"&f=json", function(json) {
                    centrePanel.append("done.<br/>");
                    var children = json["subsonic-response"]["directory"]["child"];

                    // split into two arrays: folders and files
                    children = _.partition(_.forceArray(children), _.matches({isDir:true}));
                    var folderChildren = children[0];
                    var fileChildren = children[1];


                    _.forEachOrOnce(folderChildren, function(folder) {
                        var name = _.unescape(folder.title);
                        var id = folder.id;
                        
                        var dFolder = $('<a>', {
                              class : "folderItem"
                            , id    : "folderItem" + id
                            , href  : "#/" + id
                            , text  : name
                        });

                        centrePanel.append(dFolder);
                    });

                    _.forEachOrOnce(fileChildren, function(file) {
                        var name = _.unescape(file.title);
                        var id = file.id;
                        
                        var dFolder = $('<div>', {
                              class : "folderItem"
                            , id    : "folderItem" + id
                            , text  : file.track + " " + name
                        });

                        centrePanel.append(dFolder);
                    });
                });
            }

            function loadIndexes() {
                // get and process a json Indexes list
                $.getJSON("rest/getIndexes.view?f=json", function(json) {

                    var indexes = json["subsonic-response"]["indexes"]["index"];
                    var leftPanel = $(".leftPanel");

                    // iterate through index groups (A, B, etc)
                    //for (var i = 0; i < indexes.length; i++) {
                    _.forEachOrOnce(indexes, function(index) {
                        var indexGroupName    = index.name;
                        var indexArtists = index.artist;

                        // create an Index Group DIV
                        var dIndexGroup = $('<div>', { 
                              class : "indexGroup"
                            , id    : "indexGroup"+indexGroupName
                        });

                        // create an Index Group Header DIV
                        var dIndexGroupHeader = $('<div>', {
                              class : "indexGroupHeader"
                            , text  : indexGroupName
                        });

                        // add the header to the group DIV
                        dIndexGroup.append(dIndexGroupHeader);

                        // loop through the artists in this index group
                        // for (var j = 0; j < indexArtists.length; j++) {
                        _.forEachOrOnce(indexArtists, function(indexArtist) {
                            var artistId   = indexArtist.id;
                            var artistName = _.unescape(indexArtist.name);

                            // create an Artist DIV
                            var dIndexItem = $('<div>', {
                                  class : "indexItem"
                                , id    : "index"+artistId
                                , SUBid : artistId
                            });

                            var dIndexItemAnchor = $('<a>', {
                                  class : "indexItemAnchor"
                                , href  : "#/" + artistId
                                , text  : artistName
                            })

                            // append the Artist div to the index group
                            dIndexItem.append(dIndexItemAnchor);
                            dIndexGroup.append(dIndexItem);
                        });
                        
                        leftPanel.append(dIndexGroup);
                    });
                });
            }

            function hashResponse() {

                // remove leading hash
                var hash = location.hash.replace( /^#/, "" );

                // remove leading slash
                var hash = hash.replace( /^\//, "" );

                // process id, if present
                var id = parseInt(hash);
                if (isNaN(id)) {
                    goToRoot();
                } else {
                    loadDirectory(id);
                }
            }

            function hashResponseMaybe() {
                if (!(location.hash===''))
                    hashResponse();
            }
            function safeUnescape(text) {
                return text.replace(/&amp;/g, '&');
            }
            function goToRoot() {
                window.location  = "<?php echo $GLOBALS['ABS_PATH']; ?>/#";
            }
        </script>
    </head>
    <body>
        <div class="globalWrapper">
            <div class="innerWrapper">
                <div class="leftPanelWrapper">
                    <div class="leftPanel"></div>
                </div>
                <div class="centrePanelWrapper">
                    <div class="centrePanel"></div>
                </div>
                <div class="rightPanelWrapper"></div>
            </div>
            <div class="footer"><a href="#meow">Footer</a></div>
        </div>
    </body>
</html>
<?php }

        // <div class="globalWrapper">
        //     <div class="leftPanelWrapper">
        //         <div class="leftPanel"></div>
        //         <div class="centrePanel">meow</div>
        //     </div>
        //     
        // </div>


 #####
#     # #        ##    ####   ####  ######  ####
#       #       #  #  #      #      #      #
#       #      #    #  ####   ####  #####   ####
#       #      ######      #      # #           #
#     # #      #    # #    # #    # #      #    #
 #####  ###### #    #  ####   ####  ######  ####

class ResponseObject {
    private $name;
    private $properties;
    private $children;

    public function ResponseObject($name, $props=array(), $children=array()) {
        $this->name       = $name;
        $this->properties = is_array($props)                      ? $props           : array();
        $this->children   = ($children instanceof ResponseObject) ? array($children) : $children;
    }

    public function addChild($newChild) {
        if ($newChild instanceof ResponseObject) {
            $this->children[] = $newChild;
        }
    }

    public function setProperties($props) {
        if (is_array($props)) {
            $this->properties = $props;
        }
    }

    public function getName() {
        return $this->name;
    }

    public function render() {
        $f = getAPIFormatType();
        if ($f=='xml')
            return $this->renderXML();
        if ($f=='json')
            return $this->renderJSON();
    }

    public function renderJSON() {
        setContentType('JSON');
        $res = $this->wrapInSubsonicResponse();

        return "{\n".$res->toJSON(1)."\n}";
    }

    public function renderXML() {
        setContentType('XML');
        $res = $this->wrapInSubsonicResponse();

        return '<?xml version="1.0" encoding="UTF-8"?>'.$res->toXML(0);
    }

    private function toJSON($indent=0, $printName=true) {
        $name  = $this->name;
        $props = $this->properties;
        $chren = $this->flatChildren();
        $count = 1; // for commas.

        // indent string and previous indent strings
        $ind =  str_repeat('  ', $indent+1);
        $pind = str_repeat('  ', $indent);

        // possibly print the 'name' of this object
        if ($printName)
            $newJSON = "$pind\"$name\": {";
        else
            $newJSON = "\n$pind{";

        // print properties
        foreach($props as $key => $value) {
            $safeValue = safeJSONencode($value);
            $pref = $this->commaStart($count++, "\n");
            $newJSON .= "$pref$ind\"$key\": $safeValue";
        }

        //children (flatten common children into lists)
        foreach($chren as $name => $chlist) {
            if (count($chlist) == 1) {
                $pref = $this->commaStart($count++, "\n");
                $newJSON .= $pref . $chlist[0]->toJSON($indent+1);
            } else {
                $pref = $this->commaStart($count++, "\n");
                $newJSON .= "$pref$ind\"$name\": [";

                $inCount=1;
                foreach($chlist as $child) {
                    $inPref = $this->commaStart($inCount++, '');
                    $newJSON .= $inPref . $child->toJSON($indent+2, false);
                }
                $newJSON .= "\n$ind]";
            }
        }

        // print children, without flattening children into lists
        // if (is_array($chren)) {
        //     foreach($chren as $child) {
        //         $childJSON = $child->toJSON($indent+1);

        //         $pref = $this->commaStart($count++, "\n");
        //         $newJSON .= "$pref$childJSON";
        //     }
        // }

        $newJSON .="\n$pind}";

        return $newJSON;
    }

    // returns a list of lists of children, where the top-level list has
    // keys matching the 'name' of it's array of children.
    private function flatChildren() {
        $flatList = array();
        foreach($this->children as $child) {
            $flatList[$child->name][] = $child;
        }
        return $flatList;
    }

    private function toXML($indent=0) {
        $name  = $this->name;
        $props = $this->properties;
        $chren = $this->children;
        if ($name=='') return '';

        $ind = str_repeat("  ", $indent);
        $newXML = "\n$ind<$name";

        //properties
        foreach ($props as $key => $value) {
            $safeValue = safeXMLencode($value);
            $newXML .= " $key=\"$safeValue\"";
        }

        //children
        // we don't use the /> notation if there are children OR there are no properties.
        if ((is_array($chren) && !empty($chren)) || empty($props)) {
            $newXML .= ">";
            foreach ($chren as $child) {
                $newXML .= $child->toXML($indent+1);
            }
            $newXML .= "\n$ind</$name>";
        } else {
            $newXML .= "/>";
        }

        return $newXML;
    }

    private function wrapInSubsonicResponse() {
        return new ResponseObject('subsonic-response', array
            ( 'xmlns'   => 'http://subsonic.org/restapi'
            , 'status'  => 'ok'
            , 'version' => '1.9.0'
            ), $this);
    }

    // add a comma to the start of the string, unless count is one.
    // this is used to create lists with commas in the middle (before each
    // item except the first item)
    private function commaStart($count, $always='') {
        if ($count==1) {
            return $always;
        } else {
            return ','.$always;
        }
    }

}


######
#     #   ##   #####   ##
#     #  #  #    #    #  #
#     # #    #   #   #    #
#     # ######   #   ######
#     # #    #   #   #    #
######  #    #   #   #    #

function getPageMap() {
    return array
        ( '/rest/getMusicDirectory.view' => 'getMusicDirectory'
        , '/rest/getMusicFolders.view' => 'getMusicFolders'
        , '/rest/getRandomSongs.view' => 'getRandomSongs'
        , '/rest/getCoverArt.view' => 'getCoverArt'
        , '/rest/getStarred.view' => 'getStarredSongs'
        , '/rest/getIndexes.view' => 'getIndexes'
        , '/rest/getLicense.view' => 'getLicense'
        , '/rest/stream.view' => 'stream'
        , '/rest/ping.view' => 'ping'
        , '/web/createdb' => 'createDB'
        , '/web/scan2' => 'scanTracks_First'
        , '/web/scan' => 'scanEverything'
        , '/web/test' => 'test'
        , '/web/getDir' => 'htmlGetDir'
        , '/' => 'getHome'
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

        case 'DBACCESSERROR': return
'Unfortunately we were unable to create/access a database file. Make sure there is write access to this folder.';

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
    header('HTTP/1.1 404 Not Found');
    setContentType('HTML');
    echo getTemplate('NOTFOUND');
}

function getIndexes() {
    $indexes = dbGetIndexes();
    $indexes = splitIntoSubarrays($indexes);
    
    $response = formatIndexesXML($indexes);
    echo $response->render();
}

function getMusicDirectory() {
    $id = $_REQUEST['id'];
    $folders = dbGetSubFolders($id);
    $files = dbGetSubFiles($id);

    $response = formatMusicDir($id, $folders, $files);
    echo $response->render();
}

function getCoverArt() {
    $id = $_REQUEST['id'];
    $size = nonzero($_REQUEST['size'], 100);
    if (!streamCoverArt($id, $size)) {
        header('HTTP/1.1 404 Not Found');
        echo "Sorry, the art doesn't seem to exist :(";
    }

}

function getMusicFolders() {
    $response = new ResponseObject('musicFolders', null, 
        new ResponseObject('musicFolder', array(
              'id' => 0
            , 'name' => 'Music'
        )));

    echo $response->render();
}

function getRandomSongs() {
    $response = new ResponseObject('randomSongs');
    echo $response->render();
}

function getStarredSongs() {
    $response = new ResponseObject('starred');
    echo $response->render();
}

function ping() {
    $response = new ResponseObject('');
    echo $response->render();
}

function getLicense() {
    $response = new ResponseObject('license', array('valid' => 'true'));
    echo $response->render();
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
    }, 800);
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

    $getID3 = mp3lib();
    set_time_limit(30);
    $ThisFileInfo = $getID3->analyze($f);
    getid3_lib::CopyTagsToComments($ThisFileInfo); 
    print_r($ThisFileInfo);
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

    if ($func = getByMatch($requestPath, getPageMap())) {
        $func();
    } else {
        invalidPage();
    }
    
}
function getAPIFormatType() {
    $format = 'xml';
    if ($_REQUEST['f'] == 'json')
        $format = 'json';
    return $format;

}
function streamMP3($id) {
    $fname = dbGetMP3filename($id);
    header("Content-Length: ".filesize($fname));
    fpassthru(fopen($fname, 'rb'));
    exit;
}
function streamCoverArt($id, $size) {
    $thumbPath = "thumbs/id".$id."_size".$size.".jpg";
    if (thumbFromCache($thumbPath))
        return true;
    if (thumbFromFolderJPG($id, $size, $thumbPath))
        return true;
    return false;
}
function thumbFromCache($thumbPath) {
    if (file_exists($thumbPath)) {
        setContentType('JPEG');
        fpassthru(fopen($thumbPath, 'rb'));
        return true;
    }
    return false;
}
function thumbFromFolderJPG($id, $size, $thumbPath) {
    $fname = dbGetFolderPath($id)."/Folder.jpg";
    if (file_exists($fname)) {
        generateThumb($fname, $id, $size, $thumbPath);
        return true;
    }
    return false;
}
function generateThumb($fname, $id, $size, $thumbPath) {
    // load and resize image
    list($width, $height) = getimagesize($fname);
    $oldImage   = imagecreatefromjpeg($fname);
    $newImage = imagecreatetruecolor($size, $size);
    imagecopyresampled($newImage, $oldImage, 0, 0, 0, 0, $size, $size, $width, $height);

    // save image
    if (!is_dir("thumbs"))
        mkdir("thumbs");
    imagejpeg($newImage, $thumbPath, 95);

    // output image
    setContentType('JPEG');
    imagejpeg($newImage, null, 95);
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
        case 'JPEG':
            header('Content-Type: image/jpeg'); break;
        case 'JSON':
            header('Content-Type: application/json'); break;
    }
}
function dictToXMLNode_legacy($nodeTag, $dict, $contents = null) {
    $dict = $dict===null?array():$dict;

    $newXML = "\n<$nodeTag";
    foreach ($dict as $key => $value) {
        $safeValue = safeXMLencode($value);
        $newXML .= " $key=\"$safeValue\"";
    }
    if ($contents===null) {
        $newXML .= "/>";
    } else {
        $newXML .= ">";
        $newXML .= str_replace("\n", "\n  ", $contents);
        $newXML .= "\n</$nodeTag>";
    }
    return $newXML;
}
function formatIndexesXML($indexes) {
    $response = new ResponseObject('indexes');

    // add letters to indexes
    foreach(array_keys($indexes) as $index) {
        $indexRes = new ResponseObject('index', array('name'=>$index));

        // add artists to index
        foreach($indexes[$index] as $artist) {
            $indexRes->addChild(new ResponseObject('artist', array
                ( 'name' => $artist['name']
                , 'id'   => intval($artist['id'])
                )));
        }
        $response->addChild($indexRes);
    }
    return $response;
}
function formatMusicDir($id, $folders, $files) {
    $response = new ResponseObject('directory', array('id' => $id));

    // add folder children
    foreach ($folders as $folder) {
        $response->addChild(new ResponseObject('child', array
            ( 'id'       => intval($folder['id'])
            , 'parent'   => intval($folder['parentid'])
            , 'title'    => $folder['name']
            , 'isDir'    => true
            , 'album'    => $folder['name']
            , 'artist'   => $folder['parentname']
            , 'coverArt' => intval($folder['id'])
            // , 'created'  => $folder['']
            )));
    }

    //add file children
    foreach ($files as $file) {
        $response->addChild(new ResponseObject('child', array
            ( 'id'          => intval($file['id'])
            , 'parent'      => intval($file['parentid'])
            , 'title'       => $file['title']
            , 'album'       => $file['album']
            , 'artist'      => $file['artist']
            , 'isDir'       => false
            , 'duration'    => floatval($file['duration'])
            , 'bitRate'     => floatval($file['bitrate'])
            , 'track'       => intval($file['track'])
            , 'size'        => intval($file['filesize'])
            , 'suffix'      => $file['fileextension']
            , 'contentType' => 'audio/mpeg'
            , 'isVideo'     => false
            , 'path'        => $file['relpath']
            , 'type'        => 'music'
            , 'coverArt'    => intval($file['parentid'])
            // , 'albumId'     => 
            // , 'artistId'    => 
            // , 'created'     => 
            )));
    }

    return $response;
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
        if (is_dir($fullPath)) {
            dbBeginTrans();
            scanFolder($fullPath, $id, $name, NULL);
            dbEndTrans();
        } else {
            dbDeleteIndex($id);
        }
    }    echo "done scanning folders!";

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
            artist TEXT,
            album TEXT,
            track INTEGER,
            duration INTEGER,
            encoding TEXT,
            bitrate INTEGER,
            samplerate INTEGER,
            filename TEXT,
            relpath TEXT,
            filesize INTEGER,
            fileextension TEXT,
            fullpath TEXT UNIQUE,
            coverart INTEGER,
            albumid INTEGER,
            artistid INTEGER,
            id3version STRING,
            created INTEGER
        );'); echo "tblTrackData created.<br/>";
    $db->exec('
        CREATE VIEW IF NOT EXISTS vwTracks as
            SELECT * FROM tblTrackData WHERE
                (title IS NOT NULL
                AND filesize IS NOT NULL)
        ;'); echo "vwTracks created.<br/>";
    $db->exec('
        CREATE VIEW IF NOT EXISTS vwUnscannedTracks as
            SELECT * FROM tblTrackData WHERE
                (title IS NULL
                AND filesize IS NULL)
        ;'); echo "vwTracks created.<br/>";
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

function dbDeleteIndex($indexId) {
    $db = dbConnect();
    $q=$db->prepare('DELETE FROM tblIndexes WHERE id = ?;');
    $q->execute(array($indexId));
}

function dbDeleteDirectory($directoryId) {
    $db = dbConnect();
    $q=$db->prepare('DELETE FROM tblDirectories WHERE id = ?;');
    $q->execute(array($directoryId));
}

function dbWriteTrackData($filePath, $filename, $fileExtn, $parentId, $parentName, $grandParentName, $relPath) {
    $db = dbConnect();
    $q=$db->prepare('INSERT INTO tblTrackData
        ( fullpath
        , filename
        , fileextension
        , parentid
        , parentname
        , grandparentname
        , relpath
        ) VALUES (?, ?, ?, ?, ?, ?, ?)');
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
    $q->execute(array
        ( $data['Title']
        , $data['Album']
        , $data['Author']
        , $data['Track']
        , $data['DurationFinal']
        , $data['Version']
        , $data['Encoding']
        , $data['Bitrate']
        , $data['Sampling Rate']
        , $data['Filesize']
        , $fullpath
        ));
}

function dbGetIndexes() {
    $db = dbConnect();
    $q=$db->query('SELECT id, name, letterGroup FROM tblIndexes;');
    $data = $q->fetchAll();
    return $data;
}

function dbGetSubFolders($id) {
    $db = dbConnect();
    $q=$db->prepare('SELECT * FROM tblDirectories WHERE parentid=?;');
    $q->execute(array($id));
    $data = $q->fetchAll();
    return $data;
}

function dbGetSubFiles($id) {
    $db = dbConnect();
    $q=$db->prepare('SELECT * FROM vwTracks WHERE parentid=? ORDER BY length(track), track, filename');
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

function dbGetFolderPath($id) {
    $db = dbConnect();
    $q=$db->prepare('SELECT fullpath FROM tblDirectories WHERE id=?');
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
function safeXMLencode($input) {
    if (getType($input) == 'string')
        return htmlspecialchars($input);
    return var_export($input, true);
}
function safeJSONencode($input) {
    if (getType($input) == 'string')
        return '"'.htmlspecialchars($input).'"';
    return var_export($input, true);
}
function nonzero($input, $default) {
    $val = intval($input);
    return $val==0?$default:$val;
}
function getByMatch($keyLong, $array) {
    foreach($array as $key => $value) {
        if ($keyLong === $GLOBALS["ABS_PATH"].$key)
            return $value;
    }
    return False;
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
    $new['Title'] =               $data['comments']['title'][0];
    $new['Album'] =               $data['comments']['album'][0];
    $new['Author'] =              $data['comments']['artist'][0];
    $new['Track'] =               $data['comments']['track_number'][0];
    $new['DurationFinal'] = round($data['playtime_seconds']);
    $new['Version'] =       isset($data['id3v2'])?2:(isset($data['id3v1'])?1:0);
    $new['Encoding'] =            $data['audio']['bitrate_mode'];
    $new['Bitrate'] =       round($data['audio']['bitrate']/1000);
    $new['Sampling Rate'] =       $data['audio']['sample_rate'];
    $new['Filesize'] =            $data['filesize'];
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

?>



