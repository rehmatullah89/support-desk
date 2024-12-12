<?php if (!function_exists('mcrypt_decrypt')) {
    die('Software Load Failure.<br><br>PHP <b>must</b> be compiled with <a href="http://php.net/manual/en/book.mcrypt.php">mcrypt</a> support.<br><br>Try enabling the mcrypt extension in the PHP.ini file and rebooting the server or recompile with mcrypt support.');
}
if (!function_exists('mysqli_connect')) {
    die('Software Load Failure.<br><br>PHP <b>must</b> be compiled with <a href="http://php.net/manual/en/book.mysqli.php">mysqli_connect</a> support.');
}
define('LIC_PATH', substr(dirname(__file__), 0, strpos(dirname(__file__), 'control') - 1) . '/');
define('LIC_DOM', (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''));
define('LIC_DOM_HOST', substr((isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''), 4));
define('LIC_UNI', 'DCD7D36FF76C15FCCDE7AA53E5786B917D007520');
define('LIC_SOFTWARE', 'support');
define('RESTR_DEPTS', 5);
define('RESTR_IMAP', 1);
define('RESTR_FIELDS', 2);
define('RESTR_USERS', 5);
define('RESTR_RESPONSES', 30);
define('RESTR_FAQ_CATS', 3);
define('RESTR_FAQ_QUE', 30);
define('RESTR_ATTACH', 1);
define('RESTR_PAGES', 5);
define('DEV_BETA', 'no');
define('DEV_BETA_EXP', '');
if (defined('LOG_CRON_GLOBALS')) {
    @file_put_contents(LIC_PATH . 'logs/cron_info.txt', print_r($GLOBALS, true), FILE_APPEND);
}
class mswLic {
    private $cronFiles = array();
    private function mswLE() {
        $newline = "
";
        if (isset($_SERVER["HTTP_USER_AGENT"]) && strstr(strtolower($_SERVER["HTTP_USER_AGENT"]), 'win')) {
            $newline = "
";
        } else if (isset($_SERVER["HTTP_USER_AGENT"]) && strstr(strtolower($_SERVER["HTTP_USER_AGENT"]), 'mac')) {
            $newline = "
";
        } else {
            $newline = "
";
        }
        return (defined('PHP_EOL') ? PHP_EOL : $newline);
    }
    public function mswCheckLicence() {
        if (@file_exists(LIC_PATH . 'licence.lic')) {
            $Q = @mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `prodkey` FROM `" . DB_PREFIX . "settings` LIMIT 1");
            if ($Q == 'err') {
                $fatalErr = true;
            } else {
                $s = @mysqli_fetch_object($Q);
            }
            $licfile = @file_get_contents(LIC_PATH . 'licence.lic');
            $file = explode('------ MSW LIC FILE DATA -------', $licfile);
            $dec = mswLic::mswDecoder($file[1]);
            preg_match("/<mswlic>(.+)<\/mswlic>/si", $dec, $match);
            if (isset($fatalErr) || (!isset($fatalErr) && !isset($s->prodkey))) {
                echo mswLic::mswRuntimeFatalError();
                exit;
            }
            if (isset($match[1]) && isset($s->prodkey)) {
                if (strtolower($match[1]) == 'free-' . LIC_SOFTWARE) {
                    if (DEV_BETA != 'no' && !defined('LIC_DEV')) {
                        if (@strtotime(DEV_BETA_EXP) > 0 && DEV_BETA_EXP < date('Y-m-d')) {
                            echo mswLic::mswEventHandler(4, '', '', '', strtotime(DEV_BETA_EXP));
                            exit;
                        }
                        define('LIC_BETA', DEV_BETA);
                        define('LIC_BETA_VER', strtotime(DEV_BETA_EXP));
                        return 'unlocked';
                    }
                    return (defined('LIC_DEV') ? 'unlocked' : 'locked');
                } else {
                    if (isset($match[1])) {
                        $block = explode('|', $match[1]);
                        $alld = array();
                        $key = (isset($block[0]) ? strtoupper($block[0]) : '');
                        $uni = (isset($block[1]) ? strtoupper($block[1]) : '');
                        $scr = (isset($block[2]) ? $block[2] : '');
                        $dom = (isset($block[3]) ? strtolower($block[3]) : '');
                        $exp = (isset($block[4]) ? $block[4] : '');
                        $beta = (isset($block[5]) ? explode(',', $block[5]) : '');
                        if (strpos($dom, ',') !== false) {
                            $alld = explode(',', $dom);
                        } else {
                            $alld[] = $dom;
                        }
                        if (isset($beta[0], $beta[1]) && @strtotime($beta[0]) > 0) {
                            if (@strtotime($beta[0]) > 0 && $beta[0] < date('Y-m-d')) {
                                echo mswLic::mswEventHandler(4, '', '', '', strtotime($beta[0]));
                                exit;
                            }
                            define('LIC_BETA', $beta[0]);
                            define('LIC_BETA_VER', strtotime($beta[1]));
                            return 'unlocked';
                        } else {
                            if ($key && $uni && $scr && $dom) {
                                if ($exp && strtotime($exp) > 0) {
                                    if ($exp < date('Y-m-d')) {
                                        echo mswLic::mswEventHandler(7, '', '', '', strtotime($exp));
                                        exit;
                                    }
                                }
                                $cronHost = array('localhost', '127.0.0.1', '::1');
                                if (isset($_SERVER['argv'][0]) && in_array(basename($_SERVER['argv'][0]), $this->cronFiles)) {
                                    define('CRON_RUNNING', 1);
                                }
                                if (isset($_SERVER['_']) && !defined('CRON_RUNNING')) {
                                    define('CRON_RUNNING', 1);
                                }
                                if (defined('LOG_CRON_GLOBALS')) {
                                    $string = 'LICENCE VALUES' . mswLic::mswLE() . '= = = = = = = = = = = = = = =' . mswLic::mswLE();
                                    $string.= 'LIC_DOM VALUE: ' . LIC_DOM . mswLic::mswLE();
                                    $string.= 'LIC_DOM_HOST VALUE: ' . LIC_DOM_HOST . mswLic::mswLE();
                                    $string.= 'Localhost: ' . print_r($cronHost, true) . mswLic::mswLE();
                                    $string.= 'All Domains: ' . print_r($alld, true) . mswLic::mswLE();
                                    $string.= 'Dom Value: ' . $dom . mswLic::mswLE();
                                    $string.= 'Server Vars: ' . print_r($_SERVER, true) . mswLic::mswLE();
                                    @file_put_contents(LIC_PATH . 'logs/cron_info.txt', trim($string), FILE_APPEND);
                                }
                                if ($key != strtoupper($s->prodkey)) {
                                    echo mswLic::mswEventHandler(3, '', $key, $s);
                                    exit;
                                } else if (!defined('LIC_BYPASS') && !defined('CRON_RUNNING') && !in_array(LIC_DOM, $cronHost) && !in_array(strtolower(LIC_DOM), $alld) && !in_array(strtolower(LIC_DOM_HOST), $alld) && strpos(LIC_DOM, $dom) === false) {
                                    echo mswLic::mswEventHandler(2, $dom, '', $s);
                                    exit;
                                } else if (($uni != LIC_UNI) || ($scr != LIC_SOFTWARE)) {
                                    echo mswLic::mswEventHandler(6, '', '', $s);
                                    exit;
                                } else {
                                    return 'unlocked';
                                }
                            } else {
                                echo mswLic::mswEventHandler(5, '', '', $s);
                                exit;
                            }
                        }
                    } else {
                        echo mswLic::mswEventHandler(5, '', '', $s);
                        exit;
                    }
                }
            } else {
                echo mswLic::mswEventHandler(5, '', '', $s);
                exit;
            }
        } else {
            echo mswLic::mswEventHandler(1);
            exit;
        }
    }
    public function mswEventHandler($code, $domain = '', $key = '', $s = '', $exp = 0) {
        if (defined('LOG_CRON_GLOBALS')) {
            $string = 'CODE LOGGING' . mswLic::mswLE() . '= = = = = = = = = = = = = = =' . mswLic::mswLE();
            $string.= $code . mswLic::mswLE();
            @file_put_contents(LIC_PATH . 'logs/cron_info.txt', trim($string), FILE_APPEND);
        }
        switch ($code) {
            case '1':
                $e = 'This software requires a &quot;licence.lic&quot; file. It should be in the root of your software installation.';
            break;
            case '2':
                $e = 'The &quot;licence.lic&quot; file within this installation cannot run on this server because the domain specified in the licence instructions (' . $domain . ') is different to the installation domain (' . LIC_DOM . '). If you need to change the domain for your licence, please contact us.';
            break;
            case '3':
                $e = 'The &quot;licence.lic&quot; file within this installation contains an invalid product key (' . $key . ').<br><br>Check this value against the product key on your purchase page in your script admin area.<br><br>This may be due to entering the key incorrectly on licence creation or you may have re-installed the software again, which created a new key.<br><br>If you have re-installed, please <a href="https://www.maiangateway.com" onclick="window.open(this);return false">update your product key</a> and re-download the licence again.';
            break;
            case '4':
                $e = 'This beta version expired on ' . date('j/M/Y', $exp) . '. All beta versions are valid for 1 month only.<br><br>If you are an active beta tester, please contact us for a new licence file.<br><span style="font-weight:normal"><a href="mailto:support@maianscriptworld.co.uk?subject=Beta%20Licence">support@maianscriptworld.co.uk</a></span><br><br>Remember that beta versions should NOT be used in a production environment.';
            break;
            case '5':
                $e = 'This &quot;licence.lic&quot; file appears to be corrupt. Please re-download and try again.<br><br>If this persists, please contact us.';
            break;
            case '6':
                $e = 'This &quot;licence.lic&quot; file appears to be for different software. Please re-download and try again.<br><br>If this persists, please contact us.';
            break;
            case '7':
                $e = 'This licence expired on ' . date('j/M/Y', $exp) . '.';
            break;
            default:
                $e = 'Unknown error. Please contact us for assistance.';
            break;
        }
        $doctype = '<!DOCTYPE html><html lang="en">';
        $footer = '<p class="footer"><a href="https://www.maiangateway.com" onclick="window.open(this);return false">Maian Script World Licencing System</a> &copy;2007 -' . date('Y') . ' David Ian Bennett &amp; Maian Script World</p>';
        $help = 'If the above message wasn`t helpful, you should first see if a solution is in the software documentation ("docs" folder).<br><br>If that doesn`t help, please post on the <a href="http://www.maianscriptworld.co.uk/forums/" onclick="window.open(this);return false">support forums</a> at Maian Script World for <b>FREE</b> support.<br><br>If you have paid for a commercial licence, please send a message via the <a href="https://www.maiangateway.com" onclick="window.open(this);return false">Licence Centre</a>, thank you.<br><br>We apologise for any inconvenience and hope this issue is resolved as soon as possible.<br><br>David Ian Bennett<span class="leaddev">(Lead Developer - Maian Script World)</span>';
        return $doctype . '<head><meta charset="utf-8"><title>[' . SCRIPT_NAME . '] Licence Error</title><style type="text/css">body{background:#f8f8f8;font:15px arial;color:#555}a{color:#555}a:hover{text-decoration:none}p{margin:0;padding:0}.footer{font:11px arial;color:#fff;width:850px;margin:0 auto;text-align:right;padding:10px 0 0 0}.footer a{color:#fff}#wrapper{width:85%;margin:0 auto;padding:1px;margin-top:20px;background:#fff;border:1px solid #555;-webkit-border-radius: 5px 5px 5px 5px;-khtml-border-radius: 5px 5px 5px 5px;-moz-border-radius: 5px 5px 5px 5px;border-radius: 5px 5px 5px 5px}#wrapper .head {background:#ff9999;color:#fff;padding:20px;height:30px;font:normal 26px arial;-webkit-border-radius: 5px 5px 0 0;-khtml-border-radius: 5px 5px 0 0;-moz-border-radius: 5px 5px 0 0;border-radius: 5px 5px 0 0}.head span{float:right;color:#fff;font:26px arial;display:block}.msg{padding:20px;border-top:1px solid #555}.msg .error{display:block;background:#fff;margin:20px 0 20px 0;line-height:22px;padding:15px 0 15px 0;font-weight:bold;border-top:2px solid #ff9999;border-bottom:2px solid #ff9999}.leaddev{display:block;margin-top:5px;font-size:12px;font-style:italic}</style></head><body><div id="wrapper"><p class="head"><span>ERR CODE (' . ($code ? $code : 'N/A') . ')</span>' . strtoupper(SCRIPT_NAME) . '</p><p class="msg">The following licence error has occurred while running this software:<span class="error">' . $e . '</span>' . $help . '</p></div>' . $footer . '</body></html>';
    }
    public function mswSafe64Encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        return $data;
    }
    public function mswSafe64Decode($string) {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data.= substr('====', $mod4);
        }
        return base64_decode($data);
    }
    public function mswEncoder($value) {
        if (!$value) {
            return false;
        }
        $text = $value;
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, substr(LIC_UNI, 0, 32), $text, MCRYPT_MODE_ECB, $iv);
        return trim(mswLic::mswSafe64Encode($crypttext));
    }
    public function mswDecoder($value) {
        if (!$value) {
            return false;
        }
        $crypttext = mswLic::mswSafe64Decode($value);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, substr(LIC_UNI, 0, 32), $crypttext, MCRYPT_MODE_ECB, $iv);
        return trim($decrypttext);
    }
    public function mswSysCheck() {
        $err = array();
        $cnt = 0;
        if (phpVersion() < 5) {
            $err[] = '(' . (++$cnt) . ') Your PHP version of ' . phpVersion() . ' is too old to run this software. v5.0+ is required';
        } else {
            if (is_dir(LIC_PATH . 'install')) {
                $err[] = '(' . (++$cnt) . ') Please remove or rename the "install" folder in your helpdesk store directory.';
            }
            if (SECRET_KEY == 'secret-key-name123') {
                $err[] = '(' . (++$cnt) . ') Secret key in database connection file (control/connect.php) MUST be renamed for security.';
            }
            if (!function_exists('json_encode')) {
                $err[] = '(' . (++$cnt) . ') <a href="http://www.php.net//manual/en/book.json.php" onclick="window.open(this);return false">JSON</a> functions NOT enabled. Please recompile server with json support.';
            }
            if (!function_exists('mcrypt_decrypt')) {
                $err[] = '(' . (++$cnt) . ') <a href="http://php.net/manual/en/book.mcrypt.php" onclick="window.open(this);return false">MCRYPT</a> library NOT found. Please recompile server with mcrypt support.';
            }
            if (!function_exists('curl_init')) {
                $err[] = '(' . (++$cnt) . ') <a href="http://www.php.net/manual/en/book.curl.php" onclick="window.open(this);return false">CURL</a> functions NOT enabled. Please recompile server with curl support.';
            }
            if (!function_exists('imap_open')) {
                $err[] = '(' . (++$cnt) . ') <a href="http://www.php.net/manual/en/ref.imap.php" onclick="window.open(this);return false">IMAP</a> functions NOT enabled. Please recompile server with imap support.';
            }
            if (!function_exists('simplexml_load_string')) {
                $err[] = '(' . (++$cnt) . ') <a href="http://php.net/manual/en/book.simplexml.php" onclick="window.open(this);return false">SIMPLE XML</a> functions NOT enabled. Please recompile server with simple xml support.';
            }
        }
        if (!empty($err)) {
            $doctype = '<!DOCTYPE html><html lang="en">';
            $footer = '<p class="footer"><a href="https://www.' . SCRIPT_URL . '" onclick="window.open(this);return false">' . SCRIPT_NAME . '</a> &copy;2007 -' . date('Y') . ' David Ian Bennett &amp; Maian Script World</p>';
            $help = 'If any modules are missing and you don`t understand how to enable them, please contact your web host who should be able to assist as in most cases, root server access is required.<br><br>If you require information, please post on the <a href="http://www.maianscriptworld.co.uk/forums/" onclick="window.open(this);return false">support forums</a> at Maian Script World for <b>FREE</b> support.<br><br>If you have paid for a commercial licence, please send a message via the <a href="https://www.maiangateway.com" onclick="window.open(this);return false">Licence Centre</a>, thank you.<br><br>I apologise for any inconvenience.<br><br>David Ian Bennett<span class="leaddev">(Lead Developer - Maian Script World)</span>';
            return $doctype . '<head><meta charset="utf-8"><title>[' . SCRIPT_NAME . '] Runtime Error(s)</title><style type="text/css">body{background:#f8f8f8;font:15px arial;color:#555}a{color:#555}a:hover{text-decoration:none}p{margin:0;padding:0}.footer{font:11px arial;color:#fff;width:850px;margin:0 auto;text-align:right;padding:10px 0 0 0}.footer a{color:#fff}#wrapper{width:85%;margin:0 auto;padding:1px;margin-top:20px;background:#fff;border:1px solid #555;-webkit-border-radius: 5px 5px 5px 5px;-khtml-border-radius: 5px 5px 5px 5px;-moz-border-radius: 5px 5px 5px 5px;border-radius: 5px 5px 5px 5px}#wrapper .head {background:#ff9999;color:#fff;padding:20px;height:30px;font:normal 26px arial;-webkit-border-radius: 5px 5px 0 0;-khtml-border-radius: 5px 5px 0 0;-moz-border-radius: 5px 5px 0 0;border-radius: 5px 5px 0 0}.head span{float:right;color:#fff;font:26px arial;display:block}.msg{padding:20px;border-top:1px solid #555}.msg .error{display:block;background:#fff;margin:20px 0 20px 0;line-height:22px;padding:15px 0 15px 0;font-weight:bold;border-top:2px solid #ff9999;border-bottom:2px solid #ff9999}.leaddev{display:block;margin-top:5px;font-size:12px;font-style:italic}</style></head><body><div id="wrapper"><p class="head"><span>RUNTIME ERRORS</span>' . strtoupper(SCRIPT_NAME) . '</p><p class="msg">The following runtime errors have occurred while running this software:<span class="error">' . implode('<br><br>', $err) . '</span>' . $help . '</p></div>' . $footer . '</body></html>';
        }
    }
    public function mswRuntimeFatalError() {
        $err = array();
        $err[] = 'Database failed. Did you run the installer? <a href="install/">Attempt to Load Installer</a><br><br>If link fails, access the "/install/" directory in your installation.';
        $doctype = '<!DOCTYPE html><html lang="en">';
        $footer = '<p class="footer"><a href="https://www.' . SCRIPT_URL . '" onclick="window.open(this);return false">' . SCRIPT_NAME . '</a> &copy;2007 -' . date('Y') . ' David Ian Bennett &amp; Maian Script World</p>';
        $help = 'If you don`t understand the above message and require assistance, please post on the <a href="http://www.maianscriptworld.co.uk/forums/" onclick="window.open(this);return false">support forums</a> at Maian Script World for <b>FREE</b> support.<br><br>If you have paid for a commercial licence, please send a message via the <a href="https://www.maiangateway.com" onclick="window.open(this);return false">Licence Centre</a>, thank you.<br><br>I apologise for any inconvenience.<br><br>David Ian Bennett<span class="leaddev">(Lead Developer - Maian Script World)</span>';
        return $doctype . '<head><meta charset="utf-8"><title>[' . SCRIPT_NAME . '] Runtime Error(s)</title><style type="text/css">body{background:#f8f8f8;font:15px arial;color:#555}a{color:#555}a:hover{text-decoration:none}p{margin:0;padding:0}.footer{font:11px arial;color:#fff;width:850px;margin:0 auto;text-align:right;padding:10px 0 0 0}.footer a{color:#fff}#wrapper{width:85%;margin:0 auto;padding:1px;margin-top:20px;background:#fff;border:1px solid #555;-webkit-border-radius: 5px 5px 5px 5px;-khtml-border-radius: 5px 5px 5px 5px;-moz-border-radius: 5px 5px 5px 5px;border-radius: 5px 5px 5px 5px}#wrapper .head {background:#ff9999;color:#fff;padding:20px;height:30px;font:normal 26px arial;-webkit-border-radius: 5px 5px 0 0;-khtml-border-radius: 5px 5px 0 0;-moz-border-radius: 5px 5px 0 0;border-radius: 5px 5px 0 0}.head span{float:right;color:#fff;font:26px arial;display:block}.msg{padding:20px;border-top:1px solid #555}.msg .error{display:block;background:#fff;margin:20px 0 20px 0;line-height:22px;padding:15px 0 15px 0;font-weight:bold;border-top:2px solid #ff9999;border-bottom:2px solid #ff9999}.leaddev{display:block;margin-top:5px;font-size:12px;font-style:italic}</style></head><body><div id="wrapper"><p class="head"><span>RUNTIME ERRORS</span>' . strtoupper(SCRIPT_NAME) . '</p><p class="msg">The following runtime errors have occurred while running this software:<span class="error">' . implode('<br><br>', $err) . '</span>' . $help . '</p></div>' . $footer . '</body></html>';
    }
    public function mswSysFreeCheck() {
        $err = array();
        $cnt = 0;
        if (mswRowCount('departments') > RESTR_DEPTS) {
            $err[] = '(' . (++$cnt) . ') The free version permits the following number of departments: ' . RESTR_DEPTS;
        }
        if (mswRowCount('imap') > RESTR_IMAP) {
            $err[] = '(' . (++$cnt) . ') The free version permits the following number of imap accounts: ' . RESTR_IMAP;
        }
        if (mswRowCount('cusfields') > RESTR_FIELDS) {
            $err[] = '(' . (++$cnt) . ') The free version permits the following number of custom fields: ' . RESTR_FIELDS;
       }
        if (mswRowCount('users') > RESTR_USERS) {
            $err[] = '(' . (++$cnt) . ') The free version permits the following number of support team staff: ' . RESTR_USERS;
        }
        if (mswRowCount('responses') > RESTR_RESPONSES) {
            $err[] = '(' . (++$cnt) . ') The free version permits the following number of standard responses: ' . RESTR_RESPONSES;
        }
        if (mswRowCount('categories') > RESTR_FAQ_CATS) {
            $err[] = '(' . (++$cnt) . ') The free version permits the following number of FAQ categories: ' . RESTR_FAQ_CATS;
        }
        if (mswRowCount('faq') > RESTR_FAQ_QUE) {
            $err[] = '(' . (++$cnt) . ') The free version permits the following number of FAQ questions: ' . RESTR_FAQ_QUE;
        }
        if (!empty($err)) {
            $doctype = '<!DOCTYPE html><html lang="en">';
            $footer = '<p class="footer"><a href="https://www.' . SCRIPT_URL . '" onclick="window.open(this);return false">' . SCRIPT_NAME . '</a> &copy;2007 - ' . date('Y') . ' David Ian Bennett &amp; Maian Script World</p>';
            $help = 'If you made manual changes in your database, please revert these changes back to remove this error. If you have paid for a commercial licence, please generate your licence at the <a href="https://www.maiangateway.com" onclick="window.open(this);return false">Licence Centre</a> to remove this error, thank you.<br><br>A commercial licence offers the following benefits:<br><br><span style="color:#225d6d;font-style:italic;display:block">+ ALL Future upgrades FREE of Charge<br>
      + Notifications of new version releases<br>
      + All features unlocked and unlimited<br>
      + Copyright removal included in price<br>
      + No links in email footers<br>
      + One off payment, no subscriptions<br>
      + 12 Months priority support (renewable)</span><br>Click the purchase link in your admin area for more information.<br><br>I apologise for any inconvenience.<br><br>David Ian Bennett<span class="leaddev">(Lead Developer - Maian Script World)</span>';
            return $doctype . '<head><meta charset="utf-8"><title>[' . SCRIPT_NAME . '] Free Version Error(s)</title><style type="text/css">body{background:#f8f8f8;font:15px arial;color:#555}a{color:#555}a:hover{text-decoration:none}p{margin:0;padding:0}.footer{font:11px arial;color:#fff;width:850px;margin:0 auto;text-align:right;padding:10px 0 0 0}.footer a{color:#fff}#wrapper{width:85%;margin:0 auto;padding:1px;margin-top:20px;background:#fff;border:1px solid #555;-webkit-border-radius: 5px 5px 5px 5px;-khtml-border-radius: 5px 5px 5px 5px;-moz-border-radius: 5px 5px 5px 5px;border-radius: 5px 5px 5px 5px}#wrapper .head {background:#ff9999;color:#fff;padding:20px;height:30px;font:normal 26px arial;-webkit-border-radius: 5px 5px 0 0;-khtml-border-radius: 5px 5px 0 0;-moz-border-radius: 5px 5px 0 0;border-radius: 5px 5px 0 0}.head span{float:right;color:#fff;font:26px arial;display:block}.msg{padding:20px;border-top:1px solid #555}.msg .error{display:block;background:#fff;margin:20px 0 20px 0;line-height:22px;padding:15px 0 15px 0;font-weight:bold;border-top:2px solid #ff9999;border-bottom:2px solid #ff9999}.leaddev{display:block;margin-top:5px;font-size:12px;font-style:italic}</style></head><body><div id="wrapper"><p class="head"><span>FREE VERSION ERRORS</span>' . strtoupper(SCRIPT_NAME) . '</p><p class="msg">The following errors have occurred while running this software:<span class="error">' . implode('<br><br>', $err) . '</span>' . $help . '</p></div>' . $footer . '</body></html>';
       }
    }
}
$MSWLIC = new mswLic();
define('LICENCE_VER', $MSWLIC->mswCheckLicence());
if (!defined('LIC_DEV')) {
    $err = $MSWLIC->mswSysCheck();
    if ($err) {
        echo $err;
        exit;
    }
}
if (!defined('LIC_DEV') && !defined('ADMIN_PANEL')) {
    if (LICENCE_VER == 'locked') {
        $err = $MSWLIC->mswSysFreeCheck();
        if ($err) {
            echo $err;
            exit;
        }
    }
};
