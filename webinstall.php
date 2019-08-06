<?php

define("INI_SYSTEM_CHECK_DISABLED", ini_get('disable_functions'));


if (!strstr(INI_SYSTEM_CHECK_DISABLED, 'ini_set')) {
    ini_set("memory_limit", "160M");
    ini_set("set_time_limit", 0);
}

if (!strstr(INI_SYSTEM_CHECK_DISABLED, 'date_default_timezone_set')) {
    date_default_timezone_set('America/Los_Angeles');
}


function getfile($requestUrl, $save_to_file = false)
{

    $url = $requestUrl;
    set_time_limit(0);
    $fp = fopen($save_to_file, 'w+');
    $ch = curl_init(str_replace(" ", "%20", $url));
    curl_setopt($ch, CURLOPT_TIMEOUT, 600);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
}

$y = site_url();

$y = str_replace(basename(__FILE__), '', $y);
$y = str_replace('?/', '', $y);

//$y = str_replace('//', '/', $y);

$do = false;
$done = false;
if (isset($_REQUEST['action'])) {
    $do = $_REQUEST['action'];
}


$dir = dirname(__FILE__);
switch ($do) {

    case 'download' :
    case 'download_and_unzip' :

        $latest_url = "http://microweber.com/download.php";
        $fn = ($dir . DIRECTORY_SEPARATOR . 'mw-latest.zip');
        getfile($latest_url, $fn);

        if ($do == 'download_and_unzip') {
            header('Location: ' . $y . basename(__FILE__) . '?action=unzip');
            exit();

        }


        break;

    case 'unzip' :


        if (!strstr(INI_SYSTEM_CHECK_DISABLED, 'set_time_limit')) {
            set_time_limit(0);
        }


        $dir = dirname(__FILE__);
        $fn = ('mw-latest.zip');

        $zip_dir = basename('mw-latest.zip');

        $file = $fn;

        $path = pathinfo(realpath($file), PATHINFO_DIRNAME);

        $zip = new ZipArchive;
        $res = $zip->open($file);
        if ($res === TRUE) {
            unlink(__FILE__);
            // extract it to the path we determined above
            $zip->extractTo($dir);
            $zip->close();
            $done = true;
            //  echo "WOOT! $file extracted to $path";

            unlink($fn);



        } else {
            exit("Doh! I couldn't open $file");
        }




        break;


    default :


        break;
}


?>



<?php // unlink(__FILE__); ?>




<?php if ($done == false):

$check_pass = true;
$server_check_errors = array();

if (version_compare(phpversion(), "5.4.0", "<=")) {
    $check_pass = false;
    $server_check_errors['php_version'] = 'You must run PHP 5.4 or greater';
}
if (!function_exists('curl_init')) {
    $check_pass = false;

    $server_check_errors['curl_version'] = 'You must enable the curl extension from php.ini';
}
$here = dirname(__FILE__) . DIRECTORY_SEPARATOR . uniqid();
if (is_writable($here)) {
    $check_pass = false;

    $server_check_errors['not_wrtiable'] = 'The current directory is not writable';
}
/*if (!ini_get('short_open_tag')) {
	$check_pass = false;

	$server_check_errors['short_open_tag'] =  'You must enable short_open_tag from php.ini';
}*/

if (function_exists('apache_get_modules')) {


    if (!in_array('mod_rewrite', apache_get_modules())) {
        $check_pass = false;
        $server_check_errors['mod_rewrite'] = 'mod_rewrite is not enabled on your server';
    }
}


?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Welcome to Microweber Web Install</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          crossorigin="anonymous">

</head>

<body>

<div class="box-holder">


    <div class="vSpace"></div>

    <div class="box">


        <form name="installer">
            <?php if ($check_pass == false): ?>
                <?php if (!empty($server_check_errors)): ?>
                <h3>Server check</h3>
                <h4>There are some errors on your server that will prevent Microweber from working properly</h4>
                <ol class="error">
                    <?php foreach ($server_check_errors as $server_check_error): ?>
                        <li>
                            <?php print $server_check_error; ?>
                        </li>
                    <?php endforeach ?>
                </ol>
            <?php endif; ?>
            <?php else: ?>


                <div class="container">
                    <div class="card-deck mb-3 mt-3 text-center">

                        <div class="card mb-4 shadow-sm">
                            <div class="card-header">
                                <a href="https://microweber.org" target="_blank" id="logo"><img
                                            src="https://microweber.com/userfiles/media/microweber.com/logo_microweber.png"
                                            alt="Microweber"/></a>

                            </div>
                            <div class="card-body">

                                <h2 class="my-0 font-weight-normal">Welcome to Microweber Web Install</h2>
                                <p>This file will download the latest version and redirect you to the install page.</p>


                                <p class="agreement"> By downloading and installing Microweber you agree to the
                                    <a href="http://microweber.com/license" id="license">License Agreement</a></p>


                                <iframe id="license_text" frameborder="0" scrolling="auto"
                                        style="width:100%; display: none;"></iframe>


                                <div id="mw-dowload-button">

                                    <input type="hidden" name="action" value="download_and_unzip">
                                    <input type="submit" class="btn btn-lg btn-block btn-primary" name="submit"
                                           value="Download and install Microweber">

                                </div>

                                <div id="mw-dowload-button-loading" style="display:none">

                                    <button class="btn btn-primary" type="button" disabled>
                                        <span class="spinner-border spinner-border-sm" role="status"
                                              aria-hidden="true"></span>
                                        Downloading...
                                    </button>

                                    <br>

                                    <div class=" mb-3 mt-3" id="videoframe"></div>

                                </div>

                            </div>
                        </div>

                    </div>


                </div>


                <!--
                <input type="radio" name="action" value="download">

                <input type="radio" name="action" value="unzip">
                   <input type="radio" name="action"  value="download_and_unzip">-->


                <script>
                    var doc = document,
                        link = doc.getElementById('license'),
                        frame = doc.getElementById('license_text');
                    lactivated = false;
                    link.onclick = function () {
                        if (!lactivated) {
                            lactivated = true;
                            frame.src = this.href;
                        }
                        if (frame.style.display == 'none') {
                            frame.style.display = 'block';
                        }
                        else {
                            frame.style.display = 'none';
                        }
                        return false;
                    }

                    doc.forms['installer'].onsubmit = function () {
                        doc.querySelector('.box').className += ' installing';
                        document.querySelector('#mw-dowload-button-loading').style.display = "block";
                        document.querySelector('#mw-dowload-button').style.display = "none";


                        var html = '<iframe width="560" height="315" src="https://www.youtube.com/embed/-ius5MMpKY4?autoplay=1" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>\n';
                        document.querySelector('#videoframe').innerHTML = html;


                    }

                </script>

                <div class="preloader" style="display:none;"><span>Downloading...</span></div>

            <?php endif; ?>
        </form>


        <?php else: ?>


            <script>  window.location.href = "index.php"; </script>

            <?php unlink(__FILE__); ?>
        <?php endif; ?>













        <?php

        function site_url($add_string = false)
        {
            static $u1;
            if ($u1 == false) {
                $pageURL = 'http';
                if (isset($_SERVER["HTTPS"]) and ($_SERVER["HTTPS"] == "on")) {
                    $pageURL .= "s";
                }

                $subdir_append = false;
                if (isset($_SERVER['PATH_INFO'])) {
                    // $subdir_append = $_SERVER ['PATH_INFO'];
                } else {
                    $subdir_append = $_SERVER['REQUEST_URI'];
                }

                //  var_dump($_SERVER);
                $pageURL .= "://";
                if ($_SERVER["SERVER_PORT"] != "80") {
                    $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
                } else {
                    $pageURL .= $_SERVER["SERVER_NAME"];
                }
                $pageURL_host = $pageURL;
                $pageURL .= $subdir_append;
                if (isset($_SERVER['SCRIPT_NAME'])) {
                    $d = dirname($_SERVER['SCRIPT_NAME']);
                    $d = trim($d, '/');
                }

                if (isset($_SERVER['QUERY_STRING'])) {
                    $pageURL = str_replace($_SERVER['QUERY_STRING'], '', $pageURL);
                }

                //$url_segs1 = str_replace($pageURL_host, '',$pageURL);
                $url_segs = explode('/', $pageURL);
                $i = 0;
                $unset = false;
                foreach ($url_segs as $v) {
                    if ($unset == true) {
                        //unset($url_segs [$i]);
                    }
                    if ($v == $d) {

                        $unset = true;
                    }

                    $i++;
                }
                $url_segs[] = '';
                $u1 = implode('/', $url_segs);
            }
            return $u1 . $add_string;
        }


        ?>
    </div>
</div>
</body>

</html>