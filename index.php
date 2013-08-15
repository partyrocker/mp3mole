<?php
    /*
        This is free and unencumbered software released into the public domain.

        Anyone is free to copy, modify, publish, use, compile, sell, or
        distribute this software, either in source code form or as a compiled
        binary, for any purpose, commercial or non-commercial, and by any
        means.

        In jurisdictions that recognize copyright laws, the author or authors
        of this software dedicate any and all copyright interest in the
        software to the public domain. We make this dedication for the benefit
        of the public at large and to the detriment of our heirs and
        successors. We intend this dedication to be an overt act of
        relinquishment in perpetuity of all present and future rights to this
        software under copyright law.

        THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
        EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
        MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
        IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
        OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
        ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
        OTHER DEALINGS IN THE SOFTWARE.

        For more information, please refer to <http://unlicense.org/> 
    */
    
    /* we use a little bit of session magic */
    session_start();

    /* The class where all the magic happens */
    class Mole {
        
        /* redirect back to itself */
        private function redirect() {
            /* let's support https!! */
            $url = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
            
            /* redirect back */
            header("Location: ".$url);
            
            /* exit for good measure */
            exit();
        }
        
        /* constructor function that checks if the user submitted a url, else show the form */
        public function __construct() {
            /* set the instance variables we need */
            $this->ignore = array(".", "..", "img", basename(__FILE__));
                                            
            /* values that are set on the first run */
            if(!isset($_SESSION['firstRun'])) {
                $_SESSION['firstRun'] = true;
                /* set the download option to use the memory by default */
                $_SESSION['option'] = "memory";
            }
                                    
            /* check if any url was set */
            if(isset($_SESSION['url'])) {
                $before = microtime(true);
                
                /* store a temporary copy of the url since $this->download() will unset it */
                $url = $_SESSION['url'];
                
                /* download and send the file back */
                $this->download($_SESSION['url']);
                
                $after = microtime(true);
                $_SESSION['msg'] = "Downloaded <strong>$url</strong> in ".($after-$before)." sec";
            } else if(!isset($_POST['submit'])) {
                /* there was nothing set so display the form */
                $this->showMole();
            } else {
                /* check if the url is valid */
                if(!(filter_var($_POST['url'], FILTER_VALIDATE_URL) === FALSE)) {
                    /* move the url to a session variable */
                    $_SESSION['url'] = $_POST['url'];
                    
                    /* check which method is to be used */
                    if(isset($_POST['option'])) {
                        switch($_POST['option']) {
                            case 'file':
                                $_SESSION['option'] = "file";
                                break;
                            case 'memory':
                            default: /* this is the default method of downloading */
                                $_SESSION['option'] = "memory";
                                break;
                        }
                    }
                } else {
                    /* check if there was any data sent */
                    if(empty($_POST['url'])) {
                        $_SESSION['msg'] = "Please enter a valid URL.";
                    } else {
                        $_SESSION['msg'] = "<strong>Invalid URL:</strong> ".$_POST['url'];
                    }
                }
                /* redirect back */
                $this->redirect();
            }
        }
        
        /* format the bytes to human readable values 
         * @param bytes the number of bytes to operate on 
         * @param precision the number of digits after the decimal point */
        public function formatBytes($bytes, $precision = 2) {
            /* compute the size values */
            $kilobyte = 1024;
            $megabyte = $kilobyte * 1024;
            $gigabyte = $megabyte * 1024;
            $terabyte = $gigabyte * 1024;

            if(($bytes >= 0) && ($bytes < $kilobyte)) {
                return $bytes.'<br />B';
            } else if(($bytes >= $kilobyte) && ($bytes < $megabyte)) {
                return round($bytes / $kilobyte, $precision).'<br />KB';
            } else if(($bytes >= $megabyte) && ($bytes < $gigabyte)) {
                return round($bytes / $megabyte, $precision).'<br />MB';
            } else if(($bytes >= $gigabyte) && ($bytes < $terabyte)) {
                return round($bytes / $gigabyte, $precision).'<br />GB';
            } else if ($bytes >= $terabyte) {
                return round($bytes / $terabyte, $precision).'<br />TB';
            } else {
                return $bytes . '<br />B';
            }
            
        }
        
        /* display mp3Mole */
        private function showMole() {
            $msg = NULL;
            /* check if any message was set */
            if(isset($_SESSION['msg'])) {
                /* print the message and destroy it */
                $msg = $_SESSION['msg'];
                unset($_SESSION['msg']);
            }
            
            print "
            <html>
                <head>
                    <title>mp3Mole</title>
                    
                    <link href='http://fonts.googleapis.com/css?family=Finger+Paint' rel='stylesheet' type='text/css'>
                    
                    <style type='text/css'>
                        body {
                            background: url('img/bg.jpg');
                        }
                        a {
                            text-decoration: none;
                            color: inherit;
                        }
                        #bugimg {
                            width: 72px;
                        }
                        #bug {
                            position: fixed;
                            right: 0;
                            top: 0;
                            transform:rotate(-136deg);
                            -ms-transform:rotate(-136deg);
                            -webkit-transform:rotate(-136deg);
                            margin: -30px;
                        }
                        #bug:hover {
                            margin: -8px;
                        }
                        #logo {
                            font-family: 'Finger Paint', cursive;
                            font-size: 42px;
                            color: #FFFFFF;
                            float: left;
                        }
                        #url {
                            width: 800px;
                            height: 42px;
                            font-size: 18px;
                            padding: 0px 42px 0px 10px;
                            margin: 24px 0px 0px 6px;
                            float: left;
                            border: 0;
                            line-height: 1.4em;
                        }
                        #submit {
                            text-indent: -9999px;
                            margin: 28px 0px 0px 0px;
                            width: 34px;
                            height: 34px;
                            display: block;
                            background: transparent url('img/download.png') 0 0 no-repeat;
                            float: left;
                            position: relative;
                            left: -40px;
                            border: 0;
                        }
                        #container {
                            margin-left: 30px;
                            margin-top: 30px;
                            display: block;
                            color: #FFFFFF;
                            float: left;
                        }
                        #logoimg {
                            width: 128px;
                            vertical-align: middle;
                        }
                        #options {
                            float: left;
                            color: #FFF;
                            margin-left: 4px;
                        }
                        #howto {
                            display: block;
                            left: 10px;
                            position: absolute;
                            top: 200px;
                            width: 500px;
                        }
                        #filelist {
                            border: 1px solid black;
                            margin-right: 70px;
                            top: 120px;
                            float: right;
                            width: 560px;
                            height: 360px;
                            overflow: auto;
                            background: url('img/fbg.png');
                            padding-left: 10px;
                            padding-right: 10px;
                            padding-top: 5px;
                        }
                        #filelistheader {
                            float: left;
                            margin-top: -22px;                  
                        }
                        #actions {
                            float: right;
                        }
                        #files {
                            float: left;
                            width: 100%;
                        }
                        #msg {
                            border: 1px solid #B5822B;
                            background: #FEEFB3;
                            color: #B5822B;
                            position: absolute;
                            margin-left: 130px;
                            margin-top: 100px;
                            min-width: 200px;
                            max-width: inherit;
                            word-wrap: break-word;
                            z-index: 1;
                            -webkit-border-top-left-radius: 220px 120px;
                            -webkit-border-top-right-radius: 220px 120px;
                            -webkit-border-bottom-right-radius: 220px 120px;
                            -webkit-border-bottom-left-radius: 220px 120px;
                            border-radius: 220px / 120px;
                            padding: 50px 40px;
                        }
                        #msgwrapper {
                            position: absolute;
                            float: left;
                            width: 1024px;
                            max-width: 1024px;
                            background: #FEEFB3;
                        }
                        #wrapper {
                            position: absolute;
                        }
                        #msgwrapper:before {
                            content: '';
                            position: absolute;
                            z-index: 3;
                            margin-left: 100px;
                            margin-top: 100px;
                            height: 30px;
                            width: 40px;
                            border-right: 50px solid #FEEFB3;
                            background: #FEEFB3;
                            border-bottom-right-radius: 80px 50px;
                            border-top-right-radius: 0px 110px;
                            -webkit-transform: translate(0, -2px);
                            transform:rotate(-136deg);
                            -ms-transform:rotate(-136deg);
                            -webkit-transform:rotate(-136deg);
                        }
                        
                        #msgbox {
                            position: absolute;
                            background: #FEEFB3;
                            margin-left: 10px;
                            margin-top: 40px;
                            display: none;
                        }
                        #close {
                            vertical-align: middle;
                            position: relative;
                        }
                        #close img {
                            width: 24px;
                            float: right;
                            opacity: 0.8;
                            margin-top: -40px;
                            margin-right: 20px;
                        }
                        .fileimg {
                            vertical-align: middle;
                            width: 56px;
                        }
                        .file {
                            float: left;
                            position: relative;
                            clear: both;
                            display: block;
                            margin-bottom: 40px;
                        }
                        .filename {
                            position: absolute;
                            width: 800%;
                            float: left;
                            word-wrap: break-word;
                            margin-left: 2px;
                        }
                        .filename:hover {
                            text-decoration: underline;
                        }
                        .filesize {
                            font-size: 0.6em;
                            display: block;
                            width: 30px;
                            text-align: center;
                            color: #000;
                            margin-top: -54px;
                            margin-left: 8px; /* 8px */
                            border: 1px solid black;
                        }
                    </style>
                </head>
                <body>
                    <div id='container'>
                        <form method='post'>
                            <div id='wrapper'>
                                <div id='msgwrapper'>
                                    <div id='msg'>
                                        <div id='close'><a href='javascript:;' onclick='document.getElementById(\"msgwrapper\").style.display = \"none\";'><img src='img/close.png' alt='close' /></a></div>
                                        $msg
                                    </div>
                                </div>
                            </div>
                            <label name='logo' id='logo' for='url'>
                                <img src='img/rufus.gif' id='logoimg' name='logoimg' alt='Logo' />mp3Mole
                            </label>
                            <input type='text' name='url' id='url' />
                            <input type='submit' name='submit' id='submit' value='Download' />
                            <br />
                            <div id='options' name='options'>
                                <input name='option' id='memory' type='radio' value='memory' /> <label for='option1'>Memory</label>
                                <input name='option' id='file' type='radio' value='file' /> <label for='option2'>File</label>
                            </div>
                        </form>
                        <br />
                        <div id='howto'>
                        <h3>How To Use:</h3>
                        <ul>
                            <li>Obtain the link to the file you want to download.</li>
                            <li>Paste the link in the bar above and click the download button.</li>
                            <li>If the <strong>memory</strong> option does not work for you, use the <strong>file</strong> method.</li>
                            <br />
                            <li>In the file method, the downloaded file will be stored on this server.<br />
                            The file will be stored as <strong>originalfile.ext.jpg</strong>. <br />Use the panel on the right to download the file.</li>
                            <br />
                            <li>Remove the <strong>.jpg</strong> from the downloaded file.</li>
                        </ul>
                        </div>
                        <div id='filelist'>
                            <div id='filelistheader'>
                                <h1>Directory Listing:</h1>
                            </div>
                            <div id='files'>
                    ";
                    /* list all files in this folder */
                    $this->listFiles();
                    print "
                            </div>
                        </div>
                    </div>
                    <div id='bug'>
                        <a href='https://github.com/partyrocker/mp3mole/issues' target='_blank' title='Report a bug' />
                            <img src='img/bug.png' id='bugimg' alt='bug' />
                        </a>
                    </div>
                    ";
                    if($msg === NULL) {
                        print "<style type='text/css'>#msgwrapper { display: none; }</style>";
                    }
                    print "
                    <script>
                        document.getElementById('url').focus();
                        document.getElementById('$_SESSION[option]').checked = true;
                    </script>
                </body>
            </html>";
        }
        
        /* list all the files in the current directory (except the ignored files) and link to them */
         private function listFiles() {
             /* open the current directory */
             if($handle = opendir('.')) {
                 /* read the directory until no files are found */
                while(FALSE !== ($entry = readdir($handle))) {
                    /* only display the entry if it was not in the ignore array */
                    if(!in_array($entry, $this->ignore)) {
                        print "<span class='file'><a href='$entry' title='Download \"$entry\"'><img src='img/file.png' alt='Download \"$entry\"' class='fileimg' /><span class='filename'>$entry</span></a><span class='filesize'>";
                        print $this->formatBytes(filesize($entry));
                        print "</span></span><br />";
                    }
                }
                closedir($handle);
            }
         }
        
        /* download the given url using the appropriate method
         * @param url the url to be downloaded */
        private function download($url) {
            /* delete the url from the session */
            unset($_SESSION['url']);
            
            /* decide which download mechanism to use */
            if($_SESSION['option'] == "file") {
                /* download the file to the server */
                
                /* initalize the curl instance */
                $ch = curl_init($url);
                /* don't return the headers */
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                /* return the headers as a string */
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                /* follow any redirects */
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                
                /* execute the curl */
                $data = curl_exec($ch);
                
                /* close the curl instance */
                curl_close($ch);
                
                if($data !== false) {
                    /* write the data to a file */
                    if(file_put_contents(basename($url).".jpg", $data) === false) {
                        /* we could not write to the file */
                        $_SESSION['msg'] = "Failed to create the file: <strong>".basename($url).".jpg</strong>";
                        $this->redirect();
                    } else {
                        /* successfully created the file */
                        $_SESSION['msg'] = "Downloaded the url: <strong>$url</strong> to <strong>".basename($url).".jpg</strong>";
                        $this->redirect();
                    }
                } else {
                    /* we got nothing back */
                    $_SESSION['msg'] = "Failed to connect to the given url.";
                    $this->redirect();
                }
            } else {
                /* attempt to open the url as a binary file */
                $stream = fopen($url, "rb");
                if(!$stream) {
                    /* unable to open that url */
                    $_SESSION['msg'] = "Unable to open the requested url.";
                    $this->redirect();
                    /* exit for good measure */
                    exit();
                }
                
                /* send the file directly to the user only if the url could be opened */
                /* use all the headers we need to */
                header('Pragma: public'); 	
                /* disallow cache */
                header('Expires: 0');
                /* must revalidate to rimge-download since we are using post data */
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                /* Downloading is public after all :P */
                header('Cache-Control: private', FALSE);
                /* This is what lets you get around */
                header('Content-Type: image/jpeg');
                /* We NEED to change the extension since blocking is done on a extension basis */
                header('Content-Disposition: attachment; filename="'.basename($url).'".jpg');
                /* Everything in binary!! <3 */
                header('Content-Transfer-Encoding: binary');
                /* Enough of headers */
                header('Connection: close');
                
                /* get the limit of memory available to php */
                $limit = intval(ini_get('memory_limit'));
                
                if($limit == -1) {
                    /* unlimited memory. let's use 100mb */
                    $limit = 1024*100;
                } else {
                    /* don't use the full limit as it may cause errors */
                    $limit = $limit/1.3;
                }
                
                /* print chunks of the data stream as long as the data stream did not get over */
                while(!feof($stream)) {
                    print fread($stream, $limit);
                }
                
                /* close the data stream */
                fclose($stream);
            }
        }
    }

    /* abra kadabra */
    $mole = new Mole();
?>

