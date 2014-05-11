<?php
 #####
#     #  ####  #    # ###### #  ####  #    # #####    ##   ##### #  ####  #    #
#       #    # ##   # #      # #    # #    # #    #  #  #    #   # #    # ##   #
#       #    # # #  # #####  # #      #    # #    # #    #   #   # #    # # #  #
#       #    # #  # # #      # #  ### #    # #####  ######   #   # #    # #  # #
#     # #    # #   ## #      # #    # #    # #   #  #    #   #   # #    # #   ##
 #####   ####  #    # #      #  ####   ####  #    # #    #   #   #  ####  #    #

$OPT_SQL_FILENAME = 'sub.db';
$OPT_MUSICDIR = '/home2/buckly/nonpublic/submusic2/'; // with trailing slash !IMPORTANT!
$OPT_LETTERGROUPS = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','XYZ', '#');
$OPT_NONALPHALETTERGROUP = '#';
$OPT_ARTICLES = array('The');

// disable this if you want phpsub to require no authentication. Not recommended if your server is public-facing.
define('REQUIRE_AUTH', false);

// Allowing phpsub to serve a partial file is required for being able to skip through a track (past the buffer).
// Also Safari won't even show playing progress or time without partial file service. Turn this off, however, if
// you want to decrease the load on your server, because partial file service uses more resources than otherwise.
define('STREAM_ALLOW_PARTIAL', true);

// this defines whether the JavaScript playback engine will preload tracks, so the next song will play without delay.
define('PLAYBACK_PRELOAD', true);

// this defines whether the backdrop of a folder view (on the web frontend) will enclude a (very faded) album
// art backdrop
define('ALBUMART_BACKDROP', true);


// this defines which images will be accepted as cover art (currently only jpeg is supported)
define('ALBUMART_REGEXP', '/^(id3)?(folder|cover|album)(art)?\.(jpg|jpeg)$/i');


/**** The following settings should only be modified if you know what you're doing. ****/
define('TIME_2033', 2000000000);
define('OPT_SCAN_COUNTPERREFRESH', 150);
$OPT_ACCEPTED_FILE_TYPES = array('.mp3');
$OPT_IGNOREDFOLDERS = array('..', '.');
define('IMG_ICON', 'image/gif;base64,R0lGODlhDAAMAJECAAAAAJaWlv///wAAACH5BAEAAAIALAAAAAAMAAwAAAIZlI+pGe2NgpKHxssOAGobn4AgInKhuaRpAQA7');

#     # ####### #     # #
#     #    #    ##   ## #
#     #    #    # # # # #
#######    #    #  #  # #
#     #    #    #     # #
#     #    #    #     # #
#     #    #    #     # #######



// the main page for phpsub's web interface. below you see only what is common to all pages.
// the three functions provided $style, $script, and $body will be called at the appropriate times
// to insert these sections. If you specify no arguments, the default (authorised) page is rendered.
function webHome($style='htmlStyle', $script='htmlScript', $body='htmlBody') { ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">

        <!-- EXTERNAL JS  -->
        <!-- // <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"            ></script> -->
        <!-- // <script src="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"          ></script> -->
        
        <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js"                  ></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.1.1/js/bootstrap.min.js" ></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.js"       ></script>
        
        <!-- HTML5SORTABLE (DUMPED) -->
        <script type="text/javascript">!function(a){var b,c=a();a.fn.sortable=function(d){var e=String(d);return d=a.extend({connectWith:!1,placeholder:null,dragImage:null},d),this.each(function(){if("reload"===e&&a(this).children(d.items).off("dragstart.h5s dragend.h5s selectstart.h5s dragover.h5s dragenter.h5s drop.h5s"),/^enable|disable|destroy$/.test(e)){var f=a(this).children(a(this).data("items")).attr("draggable","enable"===e);return void("destroy"===e&&(a(this).off("sortupdate"),f.add(this).removeData("connectWith items").off("dragstart.h5s dragend.h5s selectstart.h5s dragover.h5s dragenter.h5s drop.h5s").off("sortupdate")))}var g=a(this).data("opts");"undefined"==typeof g?a(this).data("opts",d):d=g;var h,i,j,k,l=a(this).children(d.items),m=null===d.placeholder?a("<"+(/^ul|ol$/i.test(this.tagName)?"li":"div")+' class="sortable-placeholder">'):a(d.placeholder).addClass("sortable-placeholder");l.find(d.handle).mousedown(function(){h=!0}).mouseup(function(){h=!1}),a(this).data("items",d.items),c=c.add(m),d.connectWith&&a(d.connectWith).add(this).data("connectWith",d.connectWith),l.attr("draggable","true").on("dragstart.h5s",function(c){if(c.stopImmediatePropagation(),d.handle&&!h)return!1;h=!1;var e=c.originalEvent.dataTransfer;e.effectAllowed="move",e.setData("Text","dummy"),d.dragImage&&e.setDragImage&&e.setDragImage(d.dragImage,0,0),i=(b=a(this)).addClass("sortable-dragging").index(),j=a(this).parent()}).on("dragend.h5s",function(){b&&(b.removeClass("sortable-dragging").show(),c.detach(),k=a(this).parent(),(i!==b.index()||j!==k)&&b.parent().triggerHandler("sortupdate",{item:b,oldindex:i,startparent:j,endparent:k}),b=null)}).not("a[href], img").on("selectstart.h5s",function(){return d.handle&&!h?!0:(this.dragDrop&&this.dragDrop(),!1)}).end().add([this,m]).on("dragover.h5s dragenter.h5s drop.h5s",function(e){if(!l.is(b)&&d.connectWith!==a(b).parent().data("connectWith"))return!0;if("drop"===e.type)return e.stopPropagation(),c.filter(":visible").after(b),b.trigger("dragend.h5s"),!1;if(e.preventDefault(),e.originalEvent.dataTransfer.dropEffect="move",l.is(this)){var f=b.outerHeight(),g=a(this).outerHeight();if(d.forcePlaceholderSize&&m.height(f),g>f){var h=g-f,i=a(this).offset().top;if(m.index()<a(this).index()&&e.originalEvent.pageY<i+h)return!1;if(m.index()>a(this).index()&&e.originalEvent.pageY>i+g-h)return!1}b.hide(),a(this)[m.index()<a(this).index()?"after":"before"](m),c.not(m).detach()}else c.is(this)||a(this).children(d.items).length||(c.detach(),a(this).append(m));return!1})})}}($);</script>

        <!-- EXTERNAL CSS -->
        <!-- <link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css"> -->
        <!-- <link rel="stylesheet" href="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css"  > -->
        <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900"               >
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.1.1/css/bootstrap.min.css" >
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.min.css"   >

        <link rel="icon" type="img/ico" href="data:<?php echo IMG_ICON; ?>">
<?php $style();  ?>
<?php $script(); ?>
        <title>phpsub</title>
    </head>
    <body>
<?php $body(); ?>
    </body>
</html>
<?php }

function htmlBody() { ?>
        <div class="leftPanelWrapper">
            <div class="leftPanel"></div>
        </div>
        <div class="centrePanelWrapper">
            <div class="centrePanel">&nbsp;</div>
        </div>
        <div class="rightPanelWrapper">
            <div class="rightPanel">
                <audio id="htmlAudio" controls autoplay></audio>
                <table id="playlistTable"><tbody></tbody></table>
                <div id="playerControls">
                    <button type="button" data-toggle="tooltip" data-placement="bottom" title="Previous Track" onclick="javascript:prevTrack();"     class="btn btn-default btn-s"><i class="fa fa-step-backward"></i></button>
                    <button type="button" data-toggle="tooltip" data-placement="bottom" title="Restart Track"  onclick="javascript:startTrack();"    class="btn btn-default btn-s"><i class="fa fa-rotate-right" ></i></button>
                    <button type="button" data-toggle="tooltip" data-placement="bottom" title="Next Track"     onclick="javascript:nextTrack();"     class="btn btn-default btn-s"><i class="fa fa-step-forward" ></i></button>
                    <button type="button" data-toggle="tooltip" data-placement="bottom" title="Clear Playlist" onclick="javascript:clearPlaylist();" class="btn btn-default btn-s"><i class="fa fa-trash-o"      ></i></button>
                </div>
            </div>
        </div>
        <div class="footerWrapper">
            <div class="footer">
                <a href="?/signout">Sign Out</a>
            </div>
        </div>
<?php }

// the raw HTML for a login form. This uses bootstrap heavily.
function htmlLoginForm() { ?>
    <div class="loginContainer">
      <form class="form-signin" method="post">
        <h2 class="form-signin-heading">Please sign in to phpsub</h2>
        <input type="text"     class="form-control" placeholder="Username" name="frmUsr" required autofocus>
        <input type="password" class="form-control" placeholder="Password" name="frmPwd" required>
        <?php postLoginFailAlert(); ?>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
      </form>
    </div>
<?php }

// create the raw HTML for a 'bad auth' page - this is just a login screen.
function htmlBadAuth() {
    webHome(
        'htmlStyle',
        '·',
        'htmlLoginForm'
    );
}

function postLoginFailAlert() {
    if (¿($GLOBALS['loginPostTried'])) { ?>
        <div class="alert alert-danger fade in">
            <strong>Bad work bro.</strong> Your username and password didn't work. Try again I guess.
        </div>
        <script type="text/javascript"> $(".alert").delay(4000).slideUp(2000); </script>
    <?php }
}

 #####   #####   #####
#     # #     # #     #
#       #       #
#        #####   #####
#             #       #
#     # #     # #     #
 #####   #####   #####


function htmlStyle() { ?>
        <style type="text/css">
            /* General */
            body {
                font-family: 'Roboto', sans-serif;
                font-weight: 100;
            }
            audio {
                width: 100%;
                display: block;
            }
            table {
                font-family:  inherit;
                font-size:    inherit;
                font-style:   inherit;
                font-variant: inherit;
                font-weight:  inherit;
            }
            div.loader {
                font-size: 300px;
                color: #FAFAFA;
                text-align: center;
                margin: 100px;
            }


            /* Layout */
            .leftPanelWrapper {
                position: absolute;
                left: 0px;
                top: 0px;
                bottom: 40px;
                width: 200px;
                overflow-y: scroll;

                background-color: #EEE;
                border-bottom-right-radius: 5px;
                padding: 5px;
                border-right: 1px solid #DDD;
                border-bottom: 1px solid #DDD;
            }
            .leftPanel {}
            .centrePanelWrapper {
                position: absolute;
                top: 0px;
                left: 200px;
                right: 300px;
                bottom: 40px;

                padding: 0px 0px 0px;
            }
            .centrePanel {
                height: 100%;
                background-color:#FFF;
                overflow-y: scroll;
            }
            .rightPanelWrapper {
                position: absolute;
                top: 0px;
                bottom: 40px;
                width: 300px;
                right: 0px;
                overflow-y: scroll;

                background-color: #EEE;
                border-bottom-left-radius: 5px;
                padding: 5px;
                border-left: 1px solid #DDD;
                border-bottom: 1px solid #DDD;
            }
            .rightPanel {}
            .footerWrapper {
                position: absolute;
                left: 0px;
                right: 0px;
                bottom: 0px;
                height: 35px;

                border-top: 1px solid #DDD;
            }
            .footer {
                background: #EEE;
                width: 100%;
                height: 100%;
                padding: 5px;
            }

            /* Indexes */
            div.indexGroupHeader {
                font-weight: 700;
                background-color: #BBB;
                padding-left: 8px;
                border-radius: 5px;
            }
            div.indexItem {
                font-size: 10pt;
                padding-left: 12px;
            }
            div.indexItem:hover {
                background-color: #eaeaea;
                padding-left: 9px;
                border-left:  3px solid #ccc;
                border-right: 3px solid #ccc;
            }
            a.indexItemAnchor {
                color: #000;
                text-decoration: none;
                width: 100%;
                display: block;
            }

            /* Header */
            h1 {
                background-color: #FCFCFC;
                /*background-color: rgba(0, 0, 0, 0.01);*/
                margin: 0px 0px;
                
                color: rgba(138, 138, 138, 0.8);
                text-shadow: 1px 4px 6px #eee, 0 0 0 #000, 1px 4px 6px #eee;
                border-bottom: 1px solid #DDD;

                padding: 5px;
            }
            #titleParent {
                font-size: 20pt;
                padding: 0px;
            }
            #titleParent a {
                color: inherit;
            }
            #titleWrapper {
                font-weight: 700;
                padding: 0px 0px 5px;

                display:table;
                width: 100%;
            }
            #directoryTitle {
                display:table-cell;
                width: 100%;
                padding-left: 8px;
            }
            #titleButton {
                display: table-cell;
                vertical-align: middle;
                text-align: right;
            }


            /* Login Screen */
            .loginContainer {
                width:             100%;
                height:            100%;
                background-color:  #EEE;
                padding-top:       40px;
                padding-bottom:    40px;
                border-radius:     5px;
            }
            .form-signin {
                max-width:  400px;
                padding:    25px;
                margin:     0 auto;
            }
            .form-signin .form-signin-heading,
            .form-signin .checkbox {
                margin-bottom:  10px;
            }
            .form-signin .checkbox {
                font-weight:  normal;
            }
            .form-signin .form-control {
                position:            relative;
                height:              auto;
                -webkit-box-sizing:  border-box;
                   -moz-box-sizing:  border-box;
                        box-sizing:  border-box;
                padding:             10px;
                font-size:           16px;
            }
            .form-signin .form-control:focus {
                z-index: 2;
            }
            .form-signin input[type="text"] {
                margin-bottom:              -1px;
                border-bottom-right-radius: 0;
                border-bottom-left-radius:  0;
            }
            .form-signin input[type="password"] {
                margin-bottom:              10px;
                border-top-left-radius:     0;
                border-top-right-radius:    0;
            }

            /* Directory List */
            .tracktable {
                width:         100%;
            }   
            tr.track {
                cursor: pointer;
            }
            tr.track:hover td:last-child::after {
                font-family: 'FontAwesome';
                content:     '\f0a9';
                display:     block;
                float:       right;
            }
            th.trackTrack {
                width: 1%;
            }
            th.trackDuration {
                width: 1%;
            }
            .dirtableWrapper {
                padding: 10px;
            }
            .dirtable {
                width: 100%;
                empty-cells: show;
            }
            .dirtable td:first-child {
                width: 0px;
            }
            .dirtable td:last-child {
            }
            .dirtable tr:hover {
                background-color: #EEE;
            }
            .dirtable td {
                padding: 5px;
            }
            .dirtable td a {
                color: inherit;
                font-size: 20px;
            }
            .dirtable td:first-child {
                width:  110px;
                height: 110px;
            }
            img.dirListCover {
                width:   100px;
                height:  100px;
                display: none;
            }
            .imgerror {
                display: none;
                font-family: 'FontAwesome';
                text-align: center;
                line-height: 100px;
                width: 100px;
                height: 100px;
                font-size: 80px;
                border: 1px solid #DDD;

                color: white;
                text-shadow:
                -1px -1px 0 #000,  
                 1px -1px 0 #000,
                -1px  1px 0 #000,
                 1px  1px 0 #000
            }


            /* Playlist */
            #playlistTable {
                font-size: 8pt;
                width: 100%;
            }
            #playlistTable .playingTrack {
                font-weight: 400;
            }
            #playlistTable .trackTR {
                border: 1px solid transparent;
                cursor: move;
            }
            #playlistTable .sortable-placeholder {
                border: 1px dashed #CCC;
                background: none;
            }
            #playlistTable .pliDuration {
                text-align: right;
                width: 20px;
            }
            #playlistTable .trackTR .pliPlay   ,
            #playlistTable .trackTR .pliDelete {
                font-family: 'FontAwesome';
                color: transparent;
                cursor: pointer;
            }
            #playlistTable .trackTR:hover .pliPlay   ,
            #playlistTable .trackTR:hover .pliDelete {
                color: #CCC;
            }
            #playlistTable .trackTR .pliPlay:hover   ,
            #playlistTable .trackTR .pliDelete:hover {
                color: #000;
            }
            #playlistTable .trackTR .pliDelete {
                text-align: right;
            }
            #playerControls {
                margin-top: 5px;
                text-align: center;
            }
            #playerControls button {
                padding: 5px 10px;
                font-size: 15px;
            }
        </style>
<?php }

      #                       #####
      #   ##   #    #   ##   #     #  ####  #####  # #####  #####
      #  #  #  #    #  #  #  #       #    # #    # # #    #   #
      # #    # #    # #    #  #####  #      #    # # #    #   #
#     # ###### #    # ######       # #      #####  # #####    #
#     # #    #  #  #  #    # #     # #    # #   #  # #        #
 #####  #    #   ##   #    #  #####   ####  #    # # #        #


function htmlScript() { ?>
        <script type="text/javascript">

            //settings via PHP
            var PLAYBACK_PRELOAD  = <?php echo tf(PLAYBACK_PRELOAD); ?>;
            var ALBUMART_BACKDROP = <?php echo tf(ALBUMART_BACKDROP); ?>;

            //semi-globals
            var currentTrackIndex = 0;
            var isPlaying = false;
            var altAudio = null;

            //code snippets
            var snippetLoader =       '<div class="loader"><i class="fa fa-cog fa-spin"></i></div>';
            var snippetErrorLoading = '<div class="loader"><i class="fa fa-wheelchair"></i></div>';
            var snippetTrackLoading = '<i class="fa fa-spinner fa-spin"></i>';

            $.fn.exists   = function () { return this.length !== 0; }
            $.fn.orreturn = function (def) { return this.exists() ? this : def; }
            $.fn.orrun    = function (foo) { return foo(); };


            // this executes when the document is 'ready'. Here we do a bunch of preparation for
            // later behaviour.
            $(function() {

                //bind hash listener, and process URL's hash
                $(window).bind('hashchange', hashResponse);
                hashResponseMaybe();

                // load the indexes (on the left)
                loadIndexes();

                // make the playlist sortable
                $('#playlistTable tbody').sortable({
                      items                : 'tr'
                    , placeholder          : '<tr><td colspan="3">&nbsp;</td></tr>'
                    , forcePlaceholderSize : true
                });

                // bind listeners to the audio element
                prepAudioElement();

                // setup tooltips on buttons
                $('#playerControls button').tooltip();

                prepButtons();
            });

            function prepButtons(selector) {
                if(typeof(selector)==='undefined') selector = 'button';

                $(selector).click(function(){
                    var that = this;
                    setTimeout(function() { $(that).blur(); }, 1000);
                });
            }

            function prepAudioElement() {
                $('#htmlAudio')
                    .on('ended',      audEnded)
                    .on('canplay',    audCanPlay)
                    .on('play',       audPlay)
                    .on('pause',      audPause)
                    .on('timeupdate', audTimeUpdate);
            }

            function doPlayPause() {
                ddAudio = $('#htmlAudio').get(0);
                if (ddAudio.paused)
                    ddAudio.play();
                else
                    ddAudio.pause();
            }

            function audEnded() {
                isPlaying = false;
                nextTrack();
            }

            function audPlay() {
                isPlaying = true;
            }

            function audCanPlay() {}
            function audPause() {}

            function clearPlaylist() {
                $('#playlistTable tbody').empty();
            }

            function nextTrack() {
                moveTrack(1);
            }

            function prevTrack() {
                moveTrack(-1);
            }

            function startTrack() {
                moveTrack(0);
            }

            function getCurTrack() {
                var cur = $('.trackTR.playingTrack').first();
                if (!cur.exists()) {
                    cur = $('.trackTR').first();
                    cur.addClass('playingTrack');
                }
                return cur;
            }

            function moveTrack(change) {

                // the currently playing track
                var oldTR = getCurTrack();
                oldTR.removeClass('playingTrack');

                // choose and select the next track
                var newTR = oldTR;
                while (change > 0) {
                    newTR = newTR.next('.trackTR');
                    change--;
                }
                while (change < 0) {
                    newTR = newTR.prev('.trackTR');
                    change++;
                }
                newTR.addClass('playingTrack');

                // play the track.
                var newTrack = newTR.attr('subid');

                if (altAudio!=null && altAudio.attr('subid') == newTrack) {
                    console.log('Playing. Preloaded.');

                    // set the (background loaded) audio element's id to htmlAudio, for later access
                    altAudio.attr('id', 'htmlAudio');

                    // replace the current audio element with the background-loaded one.
                    $('#htmlAudio').replaceWith(altAudio);

                    // wipe 'altAudio' from memory
                    altAudio = null;

                    // bind appropriate functions to the new audio element
                    prepAudioElement();

                    // make the new audio element start playing
                    $('#htmlAudio').get(0).play();
                } else {
                    // set the source of the current audio element to be the new source, and tell it to load and play
                    $('#htmlAudio')
                        .attr('src', '?/rest/stream.view?id='+newTrack)
                        .load();

                    $('#htmlAudio').get(0).play();
                    console.log('Playing. Not preloaded.')
                }
            }

            function audTimeUpdate() {
                if (!this.hasOwnProperty('preloadFlagged') && PLAYBACK_PRELOAD) {
                    var threshold = this.duration*0.75;
                    if (this.currentTime > threshold) {
                        startPreload();
                        this.preloadFlagged = true;
                    }
                }
            }

            function getNextTrackId() {
                var cur = $('.trackTR.playingTrack').first();
                cur = cur.next('.trackTR');
                if (cur) {
                    return cur.attr('subid');
                }
                return 0;
            }

            function startPreload() {
                // get the id for the track that should play next
                var nextid = getNextTrackId();

                // make a new audio element, which will preload the next mp3.
                altAudio = $('<audio>', {
                      src      : '?/rest/stream.view?id='+nextid
                    , preload  : 'auto'
                    , autoplay : 'false'
                    , controls : 'controls'
                    , subid    : nextid
                });

                // make sure that the new audio element doens't play in the background.
                altAudio.get(0).pause();

                console.log('preloading...');
            }


            var firstTimeSelected = true;
            function selectTrack(tid) {

                var dTrackTable = $('#playlistTable tbody');

                var dNewTrackTR = $('<tr>', {
                      class : 'trackTR'
                    , id    : 'track'+tid
                    , subid : tid
                    , html  : snippetTrackLoading
                });

                dTrackTable.append(dNewTrackTR);

                // enable the play 'button'
                dNewTrackTR.load('?/web/track_tr?id='+tid, function() {
                    dNewTrackTR.find('.pliPlay').click(function() {
                        dNewTrackTR.siblings().removeClass('playingTrack');
                        dNewTrackTR.addClass('playingTrack');
                        startTrack();
                    });
                    dNewTrackTR.find('.pliDelete').click(function() {
                        dNewTrackTR.remove();
                    });
                });

                // make sure the new item is sortable
                dTrackTable.sortable('reload');

                if (firstTimeSelected) {
                    startTrack();
                    firstTimeSelected = false;
                }

            }

            function lc(func) {
                return function(e) {
                    if (e.which === 1)
                        func.call(this);
                };
            }

            function loadDirectory(dirId) {
                $(".centrePanelWrapper").css('background-image', 'none');
                $(".centrePanel").css('opacity', '1.0');

                $(".centrePanel")
                    .html(snippetLoader)

                    .load("?/web/directory?id=" + dirId, function(d,s,r) {

                        // report that things didn't go well
                        if (s!='success') {
                            $(this).html(snippetErrorLoading);
                        }

                        // clicking on titles plays track
                        $("tr.track").click(function() {
                            var id = $(this).attr("subid");
                            selectTrack(id);
                        });

                        // turning directory rows into links
                        $(".dirtable tr").click(lc(function() {
                            window.location = "#/" + $(this).attr('subid');
                        }));

                        // the 'add all' button
                        $("#titleButton button").click(function() {
                            $("tr.track").each(function() {
                                var id = $(this).attr("subid");
                                selectTrack(id);
                            });
                        });
                        prepButtons("#titleButton button");

                        // broken images
                        $("img.dirListCover").error(function() {
                            var b = $(this).parent();
                            $(this).replaceWith('<div class="imgerror">&#xf025;</div>');
                            b.find('div.imgerror').fadeIn();
                        });
                        $("img.dirListCover").load(function() {
                            $(this).fadeIn();
                        });

                        // background image
                        if (ALBUMART_BACKDROP) {
                            $.get('?/rest/getCoverArt.view?id='+ dirId + '&size=100', function() {
                                //success
                                centrePanelLoadImage(dirId);
                            }).fail(function() {
                                //failure
                            });
                        }

                    });
            }

            function centrePanelLoadImage(dirId) {
                $(".centrePanelWrapper")
                    .css('background-size'     , 'cover')
                    .css('background-position' , 'center center')
                    .css('background-image'    , 'url(?/rest/getCoverArt.view?id='+ dirId +')');

                $(".centrePanel")
                    .stop(true)
                    .css('opacity', '1.0');

                $(".centrePanel")
                    .animate({'opacity':'0.95'}, 5000);
            }

            function loadIndexes() {
                $(".leftPanel").load("?/web/indexes");
            }

            function hashResponse() {

                // remove leading hash and slash
                var hash = location.hash.replace( /^#/, "" );
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
            function goToRoot() {
                window.location  = "#";
            }
        </script>
<?php }

   #                             #####
  # #        #   ##   #    #    #     #  ####  #    # ##### ###### #    # #####
 #   #       #  #  #   #  #     #       #    # ##   #   #   #      ##   #   #
#     #      # #    #   ##      #       #    # # #  #   #   #####  # #  #   #
#######      # ######   ##      #       #    # #  # #   #   #      #  # #   #
#     # #    # #    #  #  #     #     # #    # #   ##   #   #      #   ##   #
#     #  ####  #    # #    #     #####   ####  #    #   #   ###### #    #   #



function webIndexes() { 
    $indexes = splitIntoSubarrays(dbGetIndexes());

    foreach(array_keys($indexes) as $index) {
        »("<div class=`indexGroup`><div class=`indexGroupHeader`>$index</div>");

        // add artists to index
        foreach($indexes[$index] as $artist) {
            $id   =   $artist['id'];
            $name = §($artist['name']);

            »("<div class=`indexItem`><a class=`indexItemAnchor` href=`#/$id`>$name</a></div>");
        }
        »("</div>");
    }
}

function webDirectory() {
    // prepare data
    $id          = intval($_REQUEST['id']);
    $folders     = dbGetSubFolders($id);
    $files       = dbGetSubFiles($id);
    $grandparent = dbGetParent($id);
    $parentName = §(¿A($folders, 0, 'parentname') ?: ¿A($files, 0, 'parentname'));


    // grandparent link
    $snippetGrandParent = '';
    if ($grandparent) {
        $gpName = $grandparent['name'];
        $gpId   = $grandparent['id'];
        $snippetGrandParent = "<div id=`titleParent`><a href=`#/$gpId`>$gpName</a></div>";
    }

    // 'add all' button
    $snippetAddAll = '';
    if (count($files) > 0)
        $snippetAddAll = »s("<button class=`btn btn-default btn-s addAllButton`>
                                Add All <i class=`fa fa-arrow-circle-right`></i>
                            </button>");

    // title
    »("<h1>
        $snippetGrandParent
        <div id=`titleWrapper`>
            <div id=`titleName`>$parentName</div>
            <div id=`titleButton`>$snippetAddAll</div>
        </div>
    </h1>");

    // subfolders
    if (count($folders) > 0) {
        »("<div class=`dirtableWrapper`>");
        »("<table class=`dirtable`>");
        foreach ($folders as $folder) {
            $name = §($folder['name']);
            $id   =   $folder['id'];
            »("<tr subid=`$id`>
                <td><img class=`dirListCover` src=`?/rest/getCoverArt.view?id=$id&size=100`></td>
                <td><a href=`#/$id`>$name</a></td>
            </tr>");
        }
        »("</table>");
        »("</div>");
    }

    // files
    if (count($files) > 0) {
        »("<table class=`table table-striped table-condensed tracktable`>");
        »("<div class=`trackthing`></div>");
        »("<thead><tr>
            <th class=`trackTrack`   >#</th>
            <th class=`trackTitle`   >Title</th>
            <th class=`trackArtist`  >Artist</th>
            <th class=`trackAlbum`   >Album</th>
            <th class=`trackDuration`>Duration</th>
           </tr></thead>");

        »("<tbody>");
        foreach ($files as $file) {
            $id       = $file['id'];
            $track    = §($file['trackint']);
            $title    = §($file['title']);
            $artist   = §($file['artist']);
            $album    = §($file['album']);
            $duration = minSecs($file['duration']);

            »("<tr class=`track` subid=`$id`>
                <td>$track</td>
                <td>$title</td>
                <td>$artist</td>
                <td>$album</td>
                <td>$duration</td>
               </tr>");
        }
        »("</tbody>");
        »("</table>");
    }
}

function webTrackTR() {
    $id = intval($_REQUEST['id']);
    $track = dbGetTrack($id);
    
    $id       =       §($track['id']);
    $title    =       §($track['title']);
    $duration = minSecs($track['duration']);

    »("<td class=`pliPlay`>&#xf04b;</td>
       <td class=`pliName`>$title</td>
       <td class=`pliDuration`>$duration</td>
       <td class=`pliDelete`>&#xf00d;</td>");
}

function minSecs($time) {
    // seconds
    $secs = ½($time % 60);
    $time = floor($time / 60);

    // mins
    $mins = ½($time % 60);
    $time = floor($time / 60);

    // hrs
    $hrs  = $time;

    //output
    if ($hrs == 0) {
        return "$mins:$secs";
    } else {
        return "$hrs:$mins:$secs";
    }
}

   #
  # #   #    # ##### #    #
 #   #  #    #   #   #    #
#     # #    #   #   ######
####### #    #   #   #    #
#     # #    #   #   #    #
#     #  ####    #   #    #

function badAuth($requestPath) {
    if (startsWith($requestPath, '/rest/')) {
        restBadAuth();
    } else {
        htmlBadAuth();
    }
}

function checkAuth() {

    // check cookie
    $cookie_un = ¿($_COOKIE['phpsub_auth']['un']);
    $cookie_pw = ¿($_COOKIE['phpsub_auth']['pw']);

    if ($cookie_un && $cookie_pw)
        if(dbCheckUserPass($cookie_un, unhex($cookie_pw)))
            return true;
    

    // check POST    
    $post_un = ¿($_POST['frmUsr']);
    $post_pw = ¿($_POST['frmPwd']);

    if ($post_un && $post_pw) {
        if(dbCheckUserPass($post_un, $post_pw)) {
            makeAuthCookie($post_un, $post_pw);
            return true;
        }
        else {
            $GLOBALS['loginPostTried'] = true;
        }
    }


    // check GET
    $get_un = ¿($_REQUEST['u']);
    $get_pw = ¿($_REQUEST['p']);

    if ($get_un && $get_pw)
        if(dbCheckUserPass($get_un, unhex($get_pw)))
            return true;

    // fail
    return false;
}

function makeAuthCookie($un, $pw) {
    setcookie(
          'phpsub_auth[un]'
        , $un
        , TIME_2033
    );
    setcookie(
          'phpsub_auth[pw]'
        , hex($pw)
        , TIME_2033
    );

    // also we will force a refresh
    forceRefresh();
}

function authSignOut() {
    // delete the cookies
    setcookie(
          'phpsub_auth[un]'
        , ''
        , time()-1
    );
    setcookie(
          'phpsub_auth[pw]'
        , ''
        , time()-1
    );

    // also we will force a refresh
    forceRefresh();   
}

function unhex($pw) {
    if (startsWith($pw, 'enc:'))
        return my_hex2bin(substr($pw, 4));
    return $pw;
}

function hex($pw) {
    if (startsWith($pw, 'enc:'))
        return $pw;
    return 'enc:'.bin2hex($pw);
}

// versions of PHP less than 5.4 don't have hex2bin
function my_hex2bin($h) {
    if (!is_string($h)) return null;
    $r = '';
    for ($a = 0; $a < strlen($h); $a += 2) {
        $r .= chr(hexdec($h{$a}.$h{($a + 1)}));
    }
    return $r;
}


 #####
#     # #        ##    ####   ####  ######  ####
#       #       #  #  #      #      #      #
#       #      #    #  ####   ####  #####   ####
#       #      ######      #      # #           #
#     # #      #    # #    # #    # #      #    #
 #####  ###### #    #  ####   ####  ######  ####

class ResponseObject {
    private $name;
    public $properties;
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

    public function addProperty($key, $value) {
        $this->properties[$key] = $value;
    }

    public function setProperties($props) {
        if (is_array($props)) {
            $this->properties = $props;
        }
    }

    public function getName() {
        return $this->name;
    }

    public function render($status='ok') {
        $f = getAPIFormatType();
        if ($f=='xml')
            return $this->renderXML($status);
        if ($f=='json')
            return $this->renderJSON($status);
    }

    public function renderJSON($status='ok') {
        setContentType('JSON');

        $res = $this;
        if ($status)
            $res = $this->wrapInSubsonicResponse($status);

        return "{\n".$res->toJSON(1)."\n}";
    }

    public function renderXML($status='ok') {
        setContentType('XML');
        $res = $this;
        if ($status)
            $res = $this->wrapInSubsonicResponse($status);

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
            if (is_array($value)) {
                $safeValue = '['.implode(', ', array_map('safeJSONencode', $value)).']';
            } else {
                $safeValue = safeJSONencode($value);
            }
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

    private function wrapInSubsonicResponse($status='ok') {
        return new ResponseObject('subsonic-response', array
            ( 'xmlns'   => 'http://subsonic.org/restapi'
            , 'status'  => $status
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





######                            #    ######  ###
#     # ######  ####  #####      # #   #     #  #
#     # #      #        #       #   #  #     #  #
######  #####   ####    #      #     # ######   #
#   #   #           #   #      ####### #        #
#    #  #      #    #   #      #     # #        #
#     # ######  ####    #      #     # #       ###


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
    // $size = nonzero($_REQUEST['size'], 100);
    $size = ¿($_REQUEST['size']);
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

function scanNewFiles() {
    echo "Scanning Indexes...";
    scanIndexes();
    echo "Scanning folders...";
    scanAllFolders();
}

function restBadAuth() {
    $response = new ResponseObject('error', array(
          'code'    => 40
        , 'message' => 'Wrong username or password'
    ));
    echo $response->render('failed');
}

function scanTracks() {
    $response = new ResponseObject('mp3scan');
    scanMP3Info($response);
    echo '
<script language="javascript" type="text/javascript">
    setTimeout(function() {
        window.location.href=window.location.href;
    }, 800);
</script>
';
    echo $response->properties['count'];
    // echo $response->renderJSON(false);
}



 
#     #
##   ## ###### #####   ##
# # # # #        #    #  #
#  #  # #####    #   #    #
#     # #        #   ######
#     # #        #   #    #
#     # ######   #   #    #

function forceRefresh() {
    header('Location: '.parse_url($_SERVER['REQUEST_URI'])['path']);
    die;
}

function getPageMap() {
    return array
          // URIs for the RESTful API
        ( '/rest/getMusicDirectory.view'  => 'getMusicDirectory'
        , '/rest/getMusicFolders.view'    => 'getMusicFolders'
        , '/rest/getRandomSongs.view'     => 'getRandomSongs'
        , '/rest/getCoverArt.view'        => 'getCoverArt'
        , '/rest/getStarred.view'         => 'getStarredSongs'
        , '/rest/getIndexes.view'         => 'getIndexes'
        , '/rest/getLicense.view'         => 'getLicense'
        , '/rest/stream.view'             => 'stream'
        , '/rest/ping.view'               => 'ping'

        // URIs for the HTML frontend
        , '/home'           => 'webHome'
        , '/web/indexes'    => 'webIndexes'
        , '/web/directory'  => 'webDirectory'
        , '/web/track_tr'   => 'webTrackTR'

        // URIs for performing other functions
        , '/signout'        => 'authSignOut'
        , '/web/scan'       => 'scanNewFiles'
        , '/web/scan2'      => 'scanTracks'
        , '/web/createdb'   => 'createDB'

        // other
        , '/web/test'       => 'test'
        );
}

// for testing
function test() {
    // deployGZB64();

    prepGZB64();

    // file_put_contents('this.testing.php', gzuncompress(base64_decode($b64main)));
}




/* because pspsub is one file, and we need to deal with lots of different
 * URLs for the API, we define the root path (for apis) to be [foldername]/?/
 * this allows this one PHP file to process every request.
 */
function processURI() {
    // defaults
    $pageFunc    = 'webHome';
    $requestPath = null;

    // use the path to decide what to do
    $rawPath     = $_SERVER['REQUEST_URI'];
    if (strpos($rawPath, '/?/') !== false) {
        // discard the /?/
        $relPath = substr($rawPath, strpos($rawPath, '/?/')+2);

        // reparse the query string
        $pathInfo = parse_url($relPath);
        if (isset($pathInfo['query'])) {
            $requestQuery = $pathInfo['query'];
            parse_str($requestQuery, $a);
            $_REQUEST = array_merge($_REQUEST, $a);
        }

        // figure out which page to generate
        $requestPath = $pathInfo['path'];
        $pageFunc = getByMatch($requestPath, getPageMap());

        if (!$pageFunc) {
            invalidPage();
            return;
        }
    }

    // check for authentication before performing page generation
    if (checkAuth() || (!REQUIRE_AUTH)) {
        $pageFunc();
    } else {
        badAuth($requestPath);
    }
}

function getAPIFormatType() {
    $format = 'xml';
    if (rqst('f') == 'json')
        $format = 'json';
    return $format;

}

function streamMP3($id) {
    // get filename
    $fname = dbGetMP3filename($id);

    // stream the file.
    if (STREAM_ALLOW_PARTIAL) {
        // if (browser_info()['safari'])
            // serveFilePartial($fname, null, 'audio/mpeg');
        // else
            streamFile($fname, 'audio/mpeg');
    } else {
        $byteLength = filesize($fname);
        header("Content-Length: $byteLength");
        fpassthru(fopen($fname, 'rb'));
        exit;
    }
}



###
 #  #    #   ##    ####  ######
 #  ##  ##  #  #  #    # #
 #  # ## # #    # #      #####
 #  #    # ###### #  ### #
 #  #    # #    # #    # #
### #    # #    #  ####  ######

function streamCoverArt($id, $size) {

    // the path of the thumbnail, which may or may not exist currently
    $thumbPath = "thumbs/id".$id."_size".$size.".jpg";

    // check if the thumb already exists
    if (thumbFromFile($thumbPath))
        return true;

    // check if there is a jpeg in the music folder
    if (thumbFromFolderJPG($id, $size, $thumbPath))
        return true;

    // check if we can extract an image from an mp3
    if (thumbFromID3($id, $size, $thumbPath))
        return true;

    return false;
}
function thumbFromID3($id, $size, $thumbPath) {
    $filename = dbGetFirstFileChild($id);
    $art = getCoverArtFromTrack($filename);
    if ($art) {
        if ($size) {
            $image = imagecreatefromstring($art['data']);
            generateThumb($image, $size, $thumbPath);
        } else {
            header('Content-Type: '.$art['image_mime']);
            echo $art['data'];
            exit;
        }
        return true;
    }
    return null;
}
function getCoverArtFromTrack($filename) {

    // scan file for ID3 info
    $getid3 = mp3lib(true);
    $data = $getid3->analyze($filename);
    getid3_lib::CopyTagsToComments($data); 

    // return image tag, if it exists
    return ¿A($data, 'comments', 'picture', 0);
    
}
function thumbFromFile($thumbPath) {
    if (file_exists($thumbPath)) {
        setContentType('JPEG');
        fpassthru(fopen($thumbPath, 'rb'));
        exit;
        return true;
    }
    return false;
}
function thumbFromFolderJPG($id, $size, $thumbPath) {
    $dirPath = dbGetFolderPath($id);
    $imgName = reFileFind($dirPath, ALBUMART_REGEXP);
    $fname   = $dirPath.'/'.$imgName;

    if ($imgName && file_exists($fname)) {
        if ($size) {
            $image = imagecreatefromjpeg($fname);
            generateThumb($image, $size, $thumbPath);
        } else {
            thumbFromFile($fname);
        }
        return true;
    }
    return false;
}
function generateThumb($oldImage, $size, $thumbPath) {

    // get size
    $width  = imagesx($oldImage);
    $height = imagesy($oldImage);

    // resample
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

function mp3lib($attachments=false) {
    static $getid3;
    if (isset($getid3)) {
        return $getid3;
    } else {
        manual_require_once('GetID3/getid3.php');
        $getid3 = new getID3;
        $getid3->option_save_attachments = $attachments;
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
    global $OPT_MUSICDIR;
    $topLevelFolders = getSubDirectories($OPT_MUSICDIR);
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
    global $OPT_IGNOREDFOLDERS;
    // change the cwd to $parent
    $oldwd = getcwd();
    chdir($parent);

    $everything = scandir($parent);
    foreach ($everything as $item) {
        if (!in_array($item, $OPT_IGNOREDFOLDERS)) {
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
    global $OPT_MUSICDIR;
    $indexes = dbReadIndexes();
    foreach($indexes as $id => $name) {
        $fullPath = $OPT_MUSICDIR.$name;
        if (is_dir($fullPath)) {
            dbBeginTrans();
            $containsMusic = scanFolder($fullPath, $id, $name, NULL);
            if (!$containsMusic) {
                dbDeleteIndex($id);
            }
            dbEndTrans();
        } else {
            dbDeleteIndex($id);
        }
    }    echo "done scanning folders!";

}

function scanFolder($parentPath, $parentId, $parentName, $grandParentName) {
    global $OPT_MUSICDIR;
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
            $relPath = substr($filePath, strlen($OPT_MUSICDIR));
            dbWriteTrackData($filePath, $file, $fileExtn, $parentId, $parentName, $grandParentName, $relPath);
            $containsMusic = true;
        }
    }

    return $containsMusic;
}

function isRightFileType($filename) {
    global $OPT_ACCEPTED_FILE_TYPES;
    foreach ($OPT_ACCEPTED_FILE_TYPES as $fileType) {
        if (endsWith($filename, $fileType))
            return true;
    }
    return false;
}

function scanMP3Info($response) {
    dbBeginTrans();
    $count = dbGetUnscannedCount();
    $tracks  = dbGetUnscannedTracks(OPT_SCAN_COUNTPERREFRESH);
    foreach($tracks as $track) {
        $filepath = $track['fullpath'];
        $meta = getMP3Info($filepath);
        $meta = translate_GetID3_results($meta, $track);
        dbUpdateTrackInfo($filepath, $meta);
    }
    dbEndTrans();

    $response->addProperty('count', $count);

    // uncomment this if you want the JSON response to list the filenames scanned
    // $response->addProperty('mp3s' , $tracks );
}

function translate_GetID3_results($data, $dbinfo) {
    $new = array();
    $new['Filesize']      =       ¿($data['filesize']);
    $new['Title']         =       ¿($data['comments']['title'][0]);
    $new['Album']         =       ¿($data['comments']['album'][0]);
    $new['Author']        =       ¿($data['comments']['artist'][0]);
    $new['Track']         =       ¿($data['comments']['track_number'][0]);
    $new['Sampling Rate'] =       ¿($data['audio']['sample_rate']);
    $new['Encoding']      =       ¿($data['audio']['bitrate_mode']);
    $new['Bitrate']       = round(¿($data['audio']['bitrate'])/1000);
    $new['DurationFinal'] = round(¿($data['playtime_seconds']));

    // if there is no album/author, we use the parentname/grandparentname
    $new['Album']  = $new['Album'] ?: $dbinfo['parentname'];
    $new['Author'] = $new['Author'] ?: $dbinfo['grandparentname'];

    // if there is no Title, we use the filename (after processing it)
    $new['Title'] = $new['Title'] ?: filenameToTitle($dbinfo['filename'], $new['Author']);

    //how we deal with 'version' is weird
    $new['Version'] = isset($data['id3v2'])?2:(isset($data['id3v1'])?1:0);

    // this is an int version of the track number (for sorting)
    $new['TrackInt'] = intval($new['Track']);
    return $new;
}

function getMP3Info($filename) {
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
    global $OPT_SQL_FILENAME;
    static $db;
    if (isset($db)) {
        return $db;
    } else {
        if (!file_exists($OPT_SQL_FILENAME)) {
            dbCreateTables();
        }
        if ($db = new PDO("sqlite:$OPT_SQL_FILENAME")) {
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

function dbCancelTrans($sure = false) {
    if ($sure) {
        $db = dbConnect();
        $db->rollBack();
    }
}

function dbConnectSQLite3() {
    global $OPT_SQL_FILENAME;
    if ($db = new SQLite3($OPT_SQL_FILENAME)) {
        return $db;
    } else {
        die(getTemplate('DBACCESSERROR'));
    }
}

function dbCreateTables() {
    $db = dbConnectSQLite3();

    $db->exec('
        CREATE TABLE IF NOT EXISTS tblLibraries (
            id               INTEGER PRIMARY KEY AUTOINCREMENT,
            name             TEXT NOT NULL,
            dir              TEXT
        );'); echo "tblLibraries created.<br/>";
    // $db->exec('
    //     CREATE TABLE IF NOT EXISTS tblIndexes (
    //         id               INTEGER PRIMARY KEY AUTOINCREMENT,
    //         name             TEXT NOT NULL UNIQUE,
    //         letterGroup      TEXT
    //     );'); echo "tblIndexes created.<br/>";
    $db->exec('
        CREATE TABLE IF NOT EXISTS tblDirectories (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            isindex      INTEGER DEFAULT 0,
            lettergroup  TEXT,
            name         TEXT NOT NULL,
            parentid     INTEGER,
            parentname   TEXT NOT NULL,
            fullpath     TEXT NOT NULL UNIQUE
        );

    '); echo "tblDirectories created.<br/>";
    $db->exec('
        CREATE TABLE IF NOT EXISTS tblTrackData (
            id               INTEGER PRIMARY KEY AUTOINCREMENT,
            parentid         INTEGER NOT NULL,
            parentname       TEXT,
            grandparentname  TEXT,
            title            TEXT,
            artist           TEXT,
            album            TEXT,
            track            INTEGER,
            trackint         INTEGER,
            duration         INTEGER,
            encoding         TEXT,
            bitrate          INTEGER,
            samplerate       INTEGER,
            filename         TEXT,
            relpath          TEXT,
            filesize         INTEGER,
            fileextension    TEXT,
            fullpath TEXT    UNIQUE,
            coverart         INTEGER,
            albumid          INTEGER,
            artistid         INTEGER,
            id3version       STRING,
            created          INTEGER,
            id3imagechecked  INTEGER DEFAULT 0
        );'); echo "tblTrackData created.<br/>";
    $db->exec('
        CREATE TABLE IF NOT EXISTS tblUsers (
            id               INTEGER PRIMARY KEY AUTOINCREMENT,
            username         TEXT NOT NULL UNIQUE,
            md5pass          TEXT NOT NULL
        );'); echo "tblUsers.<br/>";
    $db->exec('
        CREATE VIEW IF NOT EXISTS vwTracks as
            SELECT * FROM tblTrackData WHERE (
                    title IS NOT NULL
                AND filesize IS NOT NULL)
        ;'); echo "vwTracks created.<br/>";
    $db->exec('
        CREATE VIEW IF NOT EXISTS vwUnscannedTracks as
            SELECT * FROM tblTrackData WHERE (
                title IS NULL
                AND filesize IS NULL)
        ;'); echo "vwTracks created.<br/>";
    $db->close();
}


function dbWriteIndexes($indexes) {
    global $OPT_MUSICDIR;
    $db = dbConnect();
    $db->beginTransaction();
    foreach($indexes as $letter => $folders) {
        foreach($folders as $folder) {
            $q=$db->prepare('INSERT INTO tblDirectories (
                      lettergroup
                    , name
                    , isindex
                    , parentname 
                    , fullpath
                ) VALUES (
                      :lettergroup
                    , :name
                    , 1
                    , :name
                    , :fullpath
                );');
            $q->execute(array(
                  ':lettergroup' => $letter
                , ':name'        => $folder
                , ':fullpath'    => $OPT_MUSICDIR.$folder
            ));
        }
    }
    $db->commit();
}

function dbReadIndexes() {
    $db = dbConnect();
    $data = $db->query('SELECT id, name FROM tblDirectories WHERE isindex=1;');
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
    $q=$db->prepare('DELETE FROM tblDirectories WHERE id = ?;');
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
    $q=$db->prepare('SELECT fullpath, parentname, grandparentname, filename FROM vwUnscannedTracks LIMIT ?;');
    $q->execute(array($limit));
    $data=$q->fetchAll();
    return $data;
}

function dbGetUnscannedCount() {
    $db = dbConnect();
    $q=$db->prepare('SELECT COUNT(*) FROM vwUnscannedTracks;');
    $q->execute();
    $count = $q->fetch();
    return $count[0];
}

function dbUpdateTrackInfo($fullpath, $data) {
    $db = dbConnect();
    $q=$db->prepare('UPDATE tblTrackData SET title=?
                                           , album=?
                                           , artist=?
                                           , track=?
                                           , trackint=?
                                           , duration=?
                                           , id3version=?
                                           , encoding=?
                                           , bitrate=?
                                           , samplerate=?
                                           , filesize=?
                                             WHERE fullpath=?');
    $q->execute(array
        ( $data['Title']
        , $data['Album']
        , $data['Author']
        , $data['Track']
        , $data['TrackInt']
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
    $q=$db->query('SELECT id, name, lettergroup FROM tblDirectories WHERE isindex=1
                   ORDER BY (lettergroup = "#"), lettergroup, name;');
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
    $q=$db->prepare('SELECT * FROM vwTracks WHERE parentid=? ORDER BY trackint, filename');
    $q->execute(array($id));
    $data = $q->fetchAll();
    return $data;
}

function dbGetFirstFileChild($id) {
    $db = dbConnect();
    $q=$db->prepare('SELECT fullpath FROM tblTrackData WHERE parentid=? ORDER BY trackint, filename LIMIT 1');
    $q->execute(array($id));
    $data = $q->fetch(PDO::FETCH_COLUMN, 0);
    return $data;
}

function dbGetTrack($id) {
    $db = dbConnect();
    $q=$db->prepare('SELECT * FROM tblTrackData WHERE id=?');
    $q->execute(array($id));
    $data = $q->fetch();
    return $data;
}

function dbGetParent($id) {
    $db = dbConnect();
    $q=$db->prepare('SELECT * FROM tblDirectories WHERE id IN 
                        (SELECT parentid FROM tblDirectories WHERE id=?)');
    $q->execute(array($id));
    $data = $q->fetch();
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
    $data = $q->fetch(PDO::FETCH_COLUMN, 0);
    return $data;
}

function dbCheckUserPass($un, $pw) {
    $db = dbConnect();
    $q=$db->prepare('SELECT * FROM tblUsers WHERE username=? AND md5pass=?');
    $q->execute(array(
          $un
        , hash('md5', $pw)
    ));
    $data = $q->fetch(PDO::FETCH_COLUMN, 0);

    if ($data) return true;
    return false;
}






#     #
#     # ###### #      #####  ###### #####   ####
#     # #      #      #    # #      #    # #
####### #####  #      #    # #####  #    #  ####
#     # #      #      #####  #      #####       #
#     # #      #      #      #      #   #  #    #
#     # ###### ###### #      ###### #    #  ####

function rqst($key) {
    if (isset($_REQUEST[$key]))
        return $_REQUEST[$key];
    return null;
}
function endsWith($haystack, $needle) {
    return substr($haystack, -strlen($needle)) === $needle;
}
function startsWith($haystack, $needle) {
    return strpos($haystack, $needle) === 0;
}
function safeXMLencode($input) {
    if (getType($input) == 'string')
        return htmlentities($input, ENT_XML1 | ENT_COMPAT | ENT_SUBSTITUTE);
    return var_export($input, true);
}
function safeJSONencode($input) {
    if (getType($input) == 'string')
        return '"'.htmlentities($input, ENT_SUBSTITUTE).'"';
    return var_export($input, true);
}
function nonzero($input, $default) {
    $val = intval($input);
    return $val==0?$default:$val;
}
function getByMatch($keyLong, $array) {
    foreach($array as $key => $value) {
        if ($keyLong === $key)
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
    global $OPT_ARTICLES;
    foreach ($OPT_ARTICLES as $article) {
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
    global $OPT_LETTERGROUPS, $OPT_NONALPHALETTERGROUP;
    $first = removeArticles($name);
    $first = strtoupper(substr($first, 0, 1));
    foreach($OPT_LETTERGROUPS as $lGroup) {
        if (strcontains($lGroup, $first)) {
            return $lGroup;
        }
    }
    return $OPT_NONALPHALETTERGROUP;
}
function removeTrailingSlash($path) {
    if (endsWith($path, '/'))
        return substr($path, 0, strlen($path)-1);
    return $path;
}
function splitByFirstLetter($items) {
    global $OPT_LETTERGROUPS, $OPT_IGNOREDFOLDERS;
    $splitItems = array();
    foreach($OPT_LETTERGROUPS as $lGroup) {
        $splitItems[$lGroup] = array();
    }
    foreach($items as $item) {
        if (!in_array($item, $OPT_IGNOREDFOLDERS)) {
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
        $letterGroup = $index['lettergroup'];
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

function filenameToTitle($filename, $artist) {

    // remove '.mp3' from the end
    if (strlen($filename) < 5) return $filename;
    $extPatt = '/'.preg_quote('.mp3').'$/i';
    $filename = preg_replace($extPatt, '', $filename);

    // remove the artist from the beginning
    if (strlen($filename) < (strlen($artist)+3)) return $filename;
    $artPatt = '/^'.preg_quote($artist).'/i';
    $filename = preg_replace($artPatt, '', $filename);

    // remove whitespace (etc) from start/end
    $filename = trim($filename, "- \t\n\r\0\x0B");

    return $filename;
}

function reFileFind($dir, $patt) {
    if($open = opendir($dir))
        while( ($file = readdir($open)) !== false ) {
            if (preg_match($patt, $file))
                return $file;
        }
    return false;
}

// true or false, as a string
function tf($bool) {
    return $bool ? 'true' : 'false';
}

// [right-pointing double angle quotation mark] echos input, with newline, and replaces ' with "
function »($str) {
    echo "\n".»s($str);
}

// merely replaces ' with "
function »s($str) {
    return str_replace("`", '"', $str);
}

// [section sign] makes a string html-safe, for web rendering
function §($inp) {
    return htmlentities($inp, ENT_SUBSTITUTE);
}

// [1on2] zero-pads a number
function ½($s, $n=2) {
    return str_pad($s, $n, '0', STR_PAD_LEFT);
}

// [middle dot] does nothing!
function ·() {}

// [inverted question mark] is the greatest. You give it an array and a bunch of indexes,
// and it will return the appropriate item if it exists,
// otherwise it will return null
function ¿A($arr) {
    $keys = func_get_args();
    
    for ($i=1; $i<count($keys); $i++) {
        $key = $keys[$i];
        if (!array_key_exists($key, $arr))
            return null;
        $arr = $arr[$key];
    }
    return $arr;
}

function ¿(&$value, $def=null) {
    if (isset($value))
        return $value;
    return $def;
}

// this is similar to ¿, except it allows you to specify the default value
function ¿AD($default, $arr) {
    $keys = func_get_args();
    
    for ($i=2; $i<count($keys); $i++) {
        $key = $keys[$i];
        if (!array_key_exists($key, $arr))
            return $default;
        $arr = $arr[$key];
    }
    return $arr;
}




###
 #  #    # # ##### #   ##   #
 #  ##   # #   #   #  #  #  #
 #  # #  # #   #   # #    # #
 #  #  # # #   #   # ###### #
 #  #   ## #   #   # #    # #
### #    # #   #   # #    # ######

processURI();





###
 #  #    # #####   ####  #####  #####  ####
 #  ##  ## #    # #    # #    #   #   #
 #  # ## # #    # #    # #    #   #    ####
 #  #    # #####  #    # #####    #        #
 #  #    # #      #    # #   #    #   #    #
### #    # #       ####  #    #   #    ####

// taken from http://stackoverflow.com/questions/11340276/make-mp3-seekable-php (modified)
// originally this was a little broken for safari, but I fixed it (content-length header).
/**
 * Stream-able file handler
 *
 * @param String $file_location
 * @param Header|String $content_type
 * @return content
 */
function streamFile($file, $content_type = 'application/octet-stream') {
    @error_reporting(0);

    // Make sure the files exists, otherwise we are wasting our time
    if (!file_exists($file)) {
        header("HTTP/1.1 404 Not Found");
        exit;
    }

    // Get file size
    $filesize = sprintf("%u", filesize($file));

    // Handle 'Range' header
    if(isset($_SERVER['HTTP_RANGE'])){
        $range = $_SERVER['HTTP_RANGE'];
    }elseif($apache = apache_request_headers()){
        $headers = array();
        foreach ($apache as $header => $val){
            $headers[strtolower($header)] = $val;
        }
        if(isset($headers['range'])){
            $range = $headers['range'];
        }
        else $range = FALSE;
    } else $range = FALSE;

    //Is range
    if($range){
        $partial = true;
        list($param, $range) = explode('=',$range);
        // Bad request - range unit is not 'bytes'
        if(strtolower(trim($param)) != 'bytes'){ 
            header("HTTP/1.1 400 Invalid Request");
            exit;
        }
        // Get range values
        $range = explode(',',$range);
        $range = explode('-',$range[0]); 
        // Deal with range values
        if ($range[0] === ''){
            $end = $filesize - 1;
            $start = $end - intval($range[0]);
        } else if ($range[1] === '') {
            $start = intval($range[0]);
            $end = $filesize - 1;
        }else{ 
            // Both numbers present, return specific range
            $start = intval($range[0]);
            $end = intval($range[1]);
            if ($end >= $filesize || (!$start && (!$end || $end == ($filesize - 1))))
                $partial = false; // Invalid range/whole file specified, return whole file
        }
        $length = $end - $start + 1;
    } else  {
        // No range requested
        $partial = false; 
    }

    // content-length isn't always filesize (scott)
    $contentLength = $partial ? $length : $filesize;

    // Send standard headers
    header("Content-Type: $content_type");
    header("Content-Length: $contentLength");
    header('Accept-Ranges: bytes');

    // send extra headers for range handling...
    if ($partial) {
        header('HTTP/1.1 206 Partial Content');
        header("Content-Range: bytes $start-$end/$filesize");
        if (!$fp = fopen($file, 'rb')) {
            header("HTTP/1.1 500 Internal Server Error");
            exit;
        }
        if ($start) fseek($fp,$start);
        while($length){
            set_time_limit(0);
            $read = ($length > 8192) ? 8192 : $length;
            $length -= $read;
            print(fread($fp,$read));
        }
        fclose($fp);
    }
    //just send the whole file
    else {
        readfile($file);
    }
    exit;
}





// taken from http://www.php.net/manual/en/function.get-browser.php (comments) (modified)
// not currently used
function browser_info($agent=null) {
  // Declare known browsers to look for
  $known = array('chrome', 'msie', 'firefox', 'safari', 'opera', 'netscape',
    'konqueror');

  // Clean up agent and build regex that matches phrases for known browsers
  // (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the major and minor
  // version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
  $agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
  $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';

  // Find all phrases (or return empty array if none found)
  if (!preg_match_all($pattern, $agent, $matches)) return array();

  // Since some UAs have more than one phrase (e.g Firefox has a Gecko phrase,
  // Opera 7,8 have a MSIE phrase), use the last one found (the right-most one
  // in the UA).  That's usually the most correct.
  $i = count($matches['browser'])-1;
  $i = 0; // I changed this becaose Chrome lists Safari last.
  return array($matches['browser'][$i] => $matches['version'][$i]);
}

// taken from https://github.com/pomle/php-serveFilePartial (modified)
// this seems to work with safari (only)
// not currently used
function serveFilePartial($fileName, $fileTitle = null, $contentType = 'application/octet-stream')
{
    if( !file_exists($fileName) )
        throw New \Exception(sprintf('File not found: %s', $fileName));

    if( !is_readable($fileName) )
        throw New \Exception(sprintf('File not readable: %s', $fileName));


    ### Remove headers that might unnecessarily clutter up the output
    header_remove('Cache-Control');
    header_remove('Pragma');


    ### Default to send entire file
    $byteOffset = 0;
    $byteLength = $byteEnd = $fileSize = filesize($fileName);

    header('Accept-Ranges: bytes', true);
    header(sprintf('Content-Type: %s', $contentType), true);

    if( $fileTitle )
        header(sprintf('Content-Disposition: attachment; filename="%s"', $fileTitle));

    ### Parse Content-Range header for byte offsets, looks like "bytes=11525-" OR "bytes=11525-12451"
    if( isset($_SERVER['HTTP_RANGE']) && preg_match('%bytes=(\d+)-(\d+)?%i', $_SERVER['HTTP_RANGE'], $match) )
    {
        ### Offset signifies where we should begin to read the file
        $byteOffset = (int)$match[1];

        ### Length is for how long we should read the file according to the browser, and can never go beyond the file size
        if( isset($match[2]) )
            $byteEnd = min( (int)$match[2], $byteEnd);

        header("HTTP/1.1 206 Partial content");
        header(sprintf('Content-Range: bytes %d-%d/%d', $byteOffset, $byteEnd, $fileSize));  ### Contrary to the original comment (below), the -1 actually broke functionality. -Scott
        // header(sprintf('Content-Range: bytes %d-%d/%d', $byteOffset, $byteLength - 1, $fileSize));  ### Decrease by 1 on byte-length since this definition is zero-based index of bytes being sent
    }

    $byteRange = $byteEnd - $byteOffset;

    header(sprintf('Content-Length: %d', $byteRange));

    header(sprintf('Expires: %s', date('D, d M Y H:i:s', time() + 60*60*24*90) . ' GMT'));


    $buffer = '';   ### Variable containing the buffer
    $bufferSize = 512 * 16; ### Just a reasonable buffer size
    $bytePool = $byteRange; ### Contains how much is left to read of the byteRange

    if( !$handle = fopen($fileName, 'r') )
        throw New \Exception(sprintf("Could not get handle for file %s", $fileName));

    if( fseek($handle, $byteOffset, SEEK_SET) == -1 )
        throw New \Exception(sprintf("Could not seek to byte offset %d", $byteOffset));


    while( $bytePool > 0 )
    {
        $chunkSizeRequested = min($bufferSize, $bytePool); ### How many bytes we request on this iteration

        ### Try readin $chunkSizeRequested bytes from $handle and put data in $buffer
        $buffer = fread($handle, $chunkSizeRequested);

        ### Store how many bytes were actually read
        $chunkSizeActual = strlen($buffer);

        ### If we didn't get any bytes that means something unexpected has happened since $bytePool should be zero already
        if( $chunkSizeActual == 0 )
        {
            ### For production servers this should go in your php error log, since it will break the output
            trigger_error('Chunksize became 0', E_USER_WARNING);
            break;
        }

        ### Decrease byte pool with amount of bytes that were read during this iteration
        $bytePool -= $chunkSizeActual;

        ### Write the buffer to output
        print $buffer;

        ### Try to output the data to the client immediately
        flush();
    }

    exit();
}


function manual_require_once($libPath) {
    if (!include_once($libPath)) {
        deployGZB64();
        require_once($libPath);
    }
}

function prepGZB64() {
    $libFolder = 'GetID3';

    echo "<pre>";
    foreach(scandir($libFolder) as $name) {
        if (is_file($file = "$libFolder/$name"))
            echo "\$file_gzb64['".$name."'] = '" . base64_encode(gzcompress(file_get_contents($file))) ."';\n";
    }
}

function deployGZB64() {
    $libFolder = 'GetID3';

    $file_gzb64 = array();
    $file_gzb64['getid3.lib.php'] = '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';
    $file_gzb64['getid3.php'] = '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';
    $file_gzb64['module.audio.mp3.php'] = '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';
    $file_gzb64['module.tag.apetag.php'] = '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';
    $file_gzb64['module.tag.id3v1.php'] = 'eJytWntT20gS/xs+xSxHRXgPG0vyA0jIHq8AOUIozO7lKrdFjaWxrUPSuKQRxNnNd7+eR49kW3ayl7h2IbR6evrX7xn51S/TyXRz73s/UgIZM3F15u80yHBG3tKE5eSSRWkWBRPyKkpH/B/AEIV+i2fj12T+oyQQQp9oFNNhzAgVZCLE9HBvzyzKeZEFbARrWStlgix+jITywzOU8Pz83Cq3Xlq5QgKNc64k5FKJSEyKYSvgyZ5ChsD2NOaqhO+3JBkwRjJGw4S1xCdBADRJeMZIyATYJ1+J4Efq8D0fLSHhYREDAjpugemf3BZE2v8jQeGnKY1nn6N0TMDcTy4Bqd9gB7DYlKUhS4OI5Yfk5v3N+V9F8R2fvR/ii83NIKZ5TnQEPyhTEvZJAC5LnNA0jFm2+cfm5sa0GMZRQEZFGoiIp+RYWY5BWv6xubGxLRORHJEX22IS5c3XWkDztSS/hNUb0Yjs/GTExtHw8DBKxW80LtigmE55Jli4o2R8dEZRzPLoM3N+b2jZG+bBM81S8JTz+8ffYSfn11RltOAkmLDgUblTO3HIAlrk4GCQRKKcxBSyOyMC4BCnlfEiDXduL28frm7uH94dfyB7xG33/X7H3fc6jZZzceK8lNtmTBRZSkaQsUwSvkgcBt8oZ+xxp+l1e7tkcH7+z4fzm7OGZNqeZkzb8oggr8y5Hdfb1wzqKcRZPYMxVV4Mc5HtWOZd0t4lfoMcAfD74wtHWqZiGvoUUkHBdY40zZIhSZOA9Jd6xS3NchYqS310RCRiyUBKbYJCjCIWhzU6EOKDFu2GArIgiWYiyoUU9U2S/DWS4mGRfLtOvdWSZoxmStC3SToASaRTKwkqdMJSocy7+KmT1Fc6vZTJSgKaOgIKb8KfGEmLGCrtjIldIjIKYQvFl+mM0kUlJ/BPMWHJshJjlsrgWlKCZ3VwXA+UIG5DB9UGKHI1IjkLeBo2IfMFtFPBCB8Rg40ou8h8kTpCZQzJV9h42pSsRjroLBWHB/Cf0rjlKinwYHmxt68EQ3JyKL7I5rfFRG8ILJKgbZQWyRDK0IbOjRLkH67X/QI5cUS2/vOp3d5qkBcvyNzj3hfyU/nY1JPFHJB7mHirmrI+AHaJd4B2XZa1GCdfkyXT2hQGqC9rxS0G8ArORl2aq8ipSrnm/LGYXkjyDQweOysCTSumajdLpmJWx1fW6Y0izZlYL+sLCozyFbzAqdy4bkPy55+k/pGqkL+mjyl/Tp3G1zUr9ZI/oYcwCnPlHCehOdl+ZDNy9JpsP8meVR9HxgU5NCjJ/vvHtjK4WlHZQyaiEitLtozzXHZAECNb2ZCRKQ1D+OMZRkNy8+v1NbQ3ChEqWJbvkmEhSM4TJgcV6Gm55Nas+ZQGLFeuv+A8fMOzhAq10VyvAZezjAqmntzT8Y4CYj61vWF3NQfW/HUsupiv4dBFeo5hfXD8UhPEV2eruA91A2+s0aDMxzklakPeFAulRW0VOSSOU9tCpF9hfHmAeDA1XGQmMGQ+lHOBLFg1PqwPumWpdl5ZNTgNZPzoEFSVJCchh2IuiJyZFiJOTVVmD6cSxAvY6fiBj2AmEg96BiF1U0iNTSoLcwHRpEvUetnVWUZvoSy3tNSObNV5CqezunlK5+Y9dJ1nOiPRfZFCe9IjcK4PBhFk4DOLY8jDjEZpM6QJHbOwhWktZJ+bxtDRYxAwTqGa5ATMDBpIlTyZ7aBBDsHWstsBbcyBD6rMiGbylzY3oSRlz0QfSsjP7AnaKwiCrghHNhrLmXEmBfKU/YyF5RiKST6FeQIKSMhhYmdNe64BKDnUF5aakYOFupxEUusCujLUHhiRRdnOyRaYZotEcNAREYRKJrejJGZwbI0gMmWtyoScDWCwPr49l2fj6xmcX3NfboiRvWz7A5ia97XxYRlscv4BPSC3NtKkziPOIQzBITISYW4HJWWNVtqp6UTB2wWr5QVEtYp9o3Y+S4OJiljCgFqvietCM+9pVa7/fXd1OphTpArnB+pS7iBxyuEH99F75Cod9SZNlKf9qTdZk91nxRTOadI5pefR4SBMDWg2ElSblH1nQqcwfOpOoiPfmSsh1QNG80hnoOnmKsfMWclUtC/leRHSWlSPjeX4Ap7QprZro8R6CH6oFIXfQT5NDQHHuMb6PY6zjM7ej1RjyHe2aRzz58Hpu9sP+njLwiPdEtTuZvG24tYdBcxIpQjVG9vq+PCaOCfQxnNHtQgXaafyCA2r7zjUfvXIs4/AeyKbaaqP1DOaBkzTOpYW5QHXtC7S3hSpEdhD0kVWpGOzto/Ey2javORTTd1H6lv6+bMmHSDpHRM0Ntq3De0GyssxinRdQ30fh5EF6iFRlh1D8w3tFvd1O4Zy9+LEULpIocjTQwobjynu2UeitZ+7b0j3LJikxizugSFepWEBkRAhFA+hHMeQNil48smI9hDO4JEaCmI5YxSivGIQzyLKaPpooHsIaiDTUHd3/QCxnRcZb1bV9BDjcTKM5EChqQjyPqs6y0Ogv/HAKnJQceDfyxjw2zYscghwQ3St3DKofARpIhMl+761INivkOOOfYRAjwOYIDQJIV5ymAkMDcFdwHnBkPpVE5HTODLYfMR2waEb4T4I7oZHKLRT8V+rkkUdxHYCKAzJKzczEjvWb9ZSHes1OQ4bGqJ5x8JIVGKk06sxCrFh3enXPa4oiSjPxSSNAkM8sNCh1BpiF2Ge0ezxmeL+XetBFUTNxejuIuTzGMp3xu0m3UoKNt/w2CjU7VQiMyyDoosGOIOhITG0XmlOmdtpBVgXgZ/CpBiaEtZFtKdFbCK7a7HSdAx1lNhs7yHgez4lnbYhItzTSQbHhgjafLnAKxHtVYofAn0LtS82cHoI80b5khwnDJonNWnRQ7CndEihtRgqwpU171/WAz1EepvPggkLWYwm7iHcu5IZ4Q4m/FmoHqmLsQULQ2GMZbKPaK95801kaJ7ljIbo5T5ilPlHylDuV/OSlCW937WmirG29cvqCnFiaIjtXVEpBH2LC9xNXoDXY3yC8C5pFlaiYd8WHxto+26FtFdh9Sqe4SlkS2UNwhw8y2lF0xDiGxifmtXito8gT9iQDw0NQV6DdOSzHYQ9RU8Ict/GKosFenQf8clGPs5sYTmwNeiJpmIM4I2/DxClTuWKSQ5stGZ8DDNGLsOw8thfjqrqY1ujZsl0IrO6+hCBD2BoqdIR/Ek0JicwMhqyTdUJzwpEZAsTzWfkGnKNpdbmBwc2rKC4W+u47bbxfpHwAlt928VqyqBEIdHDLWmao8Pctm8mhSnLKNI6llNe4BEVifisi/UH2rZd0JuzzAzJfYMdpuMZKXuC295HP0RJYYkHmB9Q1S4yzp/siGMwDiCAMkt0KwZ/i/XRdRFlXAyRZCDeQ7nDycTtoMhkiCjcbpkdMS836mFPi2MaIrGPyj6DgeYfGXB3k5mYJBAlZedzXYPxTcZYLmZYGl3PIDwrsPK5nls2yUpAuZ6HTaFIQHSMiDyD8picykMBKIQPOpW5pzIZuF63MtySS4oVxfV6mEBWRr+y6YuqK7390t7z0g/KshSU1vTb2DuzjNvR1LVzYmQZDc6TLBLlyOr72A/GLJvaouv6HVtc88dKNXb9LhYkipb1e4v9bKkRur4BfMno06w6dLr+PpYjecU998QgPs14nkP0IrpOG9tyKlgy5RnNZsRujkzuUpMtfd7xcBLKWDou0EQdH4M4ztFTHWOI+wmUyrlx2e0YUxynUWJFGFu8rZwK+pjMqZhMFVnPz2Y53tJqsnN65xh0FrFz98Ex1T2JPjlA0rfbNWc2db6zBzd1/6Zu1ZYPf/Jy2dzsLa63LxxrBFdp6uwLp/e5LwLwIBJwcGoFvPXf6d4ggqLP099gfzZrvqePMdTsPb/X6+zROBIiZq2JSOK/KannRjUjFv80J3h1lZmTKH2CCRSowxlRShnuGy70xQ2Rlt16S6cURhMYi6R3tggoMQJVRG6uiQXoIRv8ljH/Vi3gj16nrS8LaZCxUF8G1HG5iuuGZ3qIlLVhylaze4r9CkJzAuk/CLgQ8M/V/L5WAvik/VbzdRSfHsS/qkRXM6cwPa3k6Sme00kUh5Aq/3FyKJF4BVrH31f81viDx9lq3v15Xl0aVJauXHIwv+SM82T9im57fsXb5u3729Xc7oL6LJoVq7m9eW5z3CXmLLxymT+/7B1nCV/tpm5nnv0enuVU5Eavvb26Nd0FzVSFsjfB5qaptir8UlNODudoX7l1WnqlZt587ZK6Gyh5U2YuoCAv1Ssnw29qUEBBf1kAD8u/oESqvzaGcJB7VGYI2YjCgUyT1Yu6KH9IC3UkKkXinebSdxrMC7kN5ATrRamAQbpc+1K+w1ZXhPImnPjyltfxHfWr7Tvz+khpC1dpOYtHh4dfvZNTJRsdZN7/VAR9RH30q5faJ/Zdzzc7Sr42UqvrnVS5JvwOUNvXcrY7BQ/ecHUtMWA0CyYwuCRSksgEDF/AsQP/fMjYFOYBtuMQZ5c48L/WT79RKl9PVrVZ9XpS3Xh/TbpeoC7A1+iJ8WP8I7er3P8uhdUa4w9M/4ly/Qqyki/vs2gcwaFRkfSO+nW/cZU1+4ID59c11PuziudQuaW1elcjpvFyHsq81PWYlt6pbqtXpzKm1AtSFVzDIoHf8mUn+lSVBvPuEf6lrhiPHPP+YRuFEWJeUb2co7ZU5DxMabgzd3Fudlavt9qNhvyJ9+a7ZHB/93B7fPZwd3Vxed/4NoEI4ccJ1Kb4cQK1TZVA+e0d+XNZ4PX5m3s7FeKbXWVy86UR/U2T16Q99/erIzlV2bnwq7pYd+rvdTTkz3Xo5kVqPvtKeMyEmE2Z1VO+pJL3gzCH2JfBWs0jKJmpaJggqnybpCo9mGQoSgV75VXUXwP2LW77YrMXO8sraVr1vQ2kvCZwRrC2LTsQWFw1HacwJwQ9Ahup2C+tdRaanG6Vxk6V5gkGkmc900GXLLPY9szXe1Z02qXlMkpWKD3XHisVBkXo4vJl839IYBmV';
    $file_gzb64['module.tag.id3v2.php'] = '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';
    $file_gzb64['module.tag.lyrics3.php'] = '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';

    if(!is_dir($libFolder))
        mkdir($libFolder);

    foreach($file_gzb64 as $fname => $data) {
        file_put_contents("$libFolder/$fname", gzuncompress(base64_decode($data)));
    }

    unset($file_gzb64);

}

?>



