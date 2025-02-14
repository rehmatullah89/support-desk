<?php

class msDownload {

  public function ticketAttachment($id, $s, $admin = false) {
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments`
         WHERE `id` = '{$id}'
         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $F = mysqli_fetch_object($q);
    if (isset($F->id)) {
      $split = explode('-', $F->addDate);
      $base  = $s->attachpath . '/';
      // Check for newer folder structure..
      // Earlier versions had no sub folders..
      if (file_exists($s->attachpath . '/' . $split[0] . '/' . $split[1] . '/' . $F->fileName)) {
        $base = $s->attachpath . '/' . $split[0] . '/' . $split[1] . '/';
      }
      // If file exists, attempt to force save as dialogue..
      if (file_exists($base . $F->fileName)) {
        $m = msDownload::mime($base . $F->fileName, $F->mimeType);
        msDownload::dl($base . $F->fileName, $m, 'no');
      } else {
        $H = new htmlHeaders();
        $H->err404($admin);
      }
    } else {
      $H = new htmlHeaders();
      $H->err403($admin);
    }
  }

  public function faqAttachment($id, $s, $admin = false) {
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "faqattach`
         WHERE `id` = '{$id}'
         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $F = mysqli_fetch_object($q);
    if (isset($F->id)) {
      $base = $s->attachpathfaq . '/';
      // Remote or not..
      if ($F->remote) {
        header("Location: " . $F->remote);
        exit;
      } else {
        if (file_exists($base . $F->path)) {
          $m = msDownload::mime($base . $F->path, $F->mimeType);
          msDownload::dl($base . $F->path, $m, 'no');
        } else {
          $H = new htmlHeaders();
          $H->err404($admin);
        }
      }
    } else {
      $H = new htmlHeaders();
      $H->err403($admin);
    }
  }

  public function mime($file, $mime) {
    // If mime is calculated at upload, we have it already..
    if ($mime) {
      return $mime;
    }
    // For older versions with no mime type, attempt to get mime type..
    $e = substr(strrchr(strtolower($file), '.'), +1);
    $a = msDownload::browser();
    $t = msDownload::mime_types();
    // Check for PECL extension..
    if (function_exists('finfo_file') && file_exists($file)) {
      $info = @finfo_open(FILEINFO_MIME_TYPE);
      $type = @finfo_file($info, $file);
      if ($type) {
        return $type;
      }
    }
    // Check mime array..
    if (isset($t[$e])) {
      return $t[$e];
    }
    // Fallback..
    return (in_array($a, array(
      'IE',
      'OPERA'
    )) ? 'application/octetstream' : 'application/octet-stream');
  }

  public function write($file, $data) {
    file_put_contents($file, $data, FILE_APPEND);
  }

  public function dl($path, $mime, $delete = 'yes') {
    // Output compression can sometimes cause issues..
    // Attempt to turn it off..
    if (@ini_get('zlib.output_compression')) {
      @ini_set('zlib.output_compression', 'Off');
    }
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Cache-Control: private', false);
    header('Content-Type: ' . $mime);
    // Include force download header for save as dialogue..
    //header('Content-Type: application/force-download');
    header('Content-Disposition: attachment; filename="' . basename($path) . '";');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . @filesize($path));
    // Attempt to flush the buffers..
    @ob_clean();
    flush();
    // Read file into memory, then delete..
    switch ($delete) {
      case 'yes':
        if (readfile($path)) {
          @unlink($path);
        }
        break;
      default:
        readfile($path);
        break;
    }
    exit;
  }

  public function browser() {
    $agent = 'IE';
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
      if (strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), 'OPERA') !== FALSE) {
        $agent = 'OPERA';
      } elseif (strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), 'MSIE') !== FALSE) {
        $agent = 'IE';
      } elseif (strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), 'OMNIWEB') !== FALSE) {
        $agent = 'OMNIWEB';
      } elseif (strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), 'MOZILLA') !== FALSE) {
        $agent = 'MOZILLA';
      } elseif (strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), 'KONQUEROR') !== FALSE) {
        $agent = 'KONQUEROR';
      } else {
        $agent = 'OTHER';
      }
    }
    return $agent;
  }

  public function mime_types() {
    return array(
      '3dm' => 'x-world/x-3dmf',
      '3dmf' => 'x-world/x-3dmf',
      'a' => 'application/octet-stream',
      'aab' => 'application/x-authorware-bin',
      'aam' => 'application/x-authorware-map',
      'aas' => 'application/x-authorware-seg',
      'abc' => 'text/vnd.abc',
      'acgi' => 'text/html',
      'afl' => 'video/animaflex',
      'ai' => 'application/postscript',
      'aif' => 'audio/aiff',
      'aifc' => 'audio/aiff',
      'aiff' => 'audio/aiff',
      'aim' => 'application/x-aim',
      'aip' => 'text/x-audiosoft-intra',
      'ani' => 'application/x-navi-animation',
      'aos' => 'application/x-nokia-9000-communicator-add-on-software',
      'aps' => 'application/mime',
      'arc' => 'application/octet-stream',
      'arj' => 'application/arj',
      'art' => 'image/x-jg',
      'asf' => 'video/x-ms-asf',
      'asm' => 'text/x-asm',
      'asp' => 'text/asp',
      'asx' => 'application/x-mplayer2',
      'au' => 'audio/basic',
      'au' => 'audio/x-au',
      'avi' => 'video/avi',
      'avs' => 'video/avs-video',
      'bcpio' => 'application/x-bcpio',
      'bin' => 'application/x-binary',
      'bm' => 'image/bmp',
      'bmp' => 'image/bmp',
      'boo' => 'application/book',
      'book' => 'application/book',
      'boz' => 'application/x-bzip2',
      'bsh' => 'application/x-bsh',
      'bz' => 'application/x-bzip',
      'bz2' => 'application/x-bzip2',
      'c' => 'text/plain',
      'c++' => 'text/plain',
      'cat' => 'application/vnd.ms-pki.seccat',
      'cc' => 'text/plain',
      'ccad' => 'application/clariscad',
      'cco' => 'application/x-cocoa',
      'cdf' => 'application/cdf',
      'cer' => 'application/pkix-cert',
      'cer' => 'application/x-x509-ca-cert',
      'cha' => 'application/x-chat',
      'chat' => 'application/x-chat',
      'class' => 'application/java',
      'com' => 'application/octet-stream',
      'conf' => 'text/plain',
      'cpio' => 'application/x-cpio',
      'cpp' => 'text/x-c',
      'cpt' => 'application/x-cpt',
      'crl' => 'application/pkcs-crl',
      'crt' => 'application/pkix-cert',
      'csh' => 'application/x-csh',
      'csh' => 'text/x-script.csh',
      'css' => 'text/css',
      'csv' => 'text/csv',
      'cxx' => 'text/plain',
      'dcr' => 'application/x-director',
      'deepv' => 'application/x-deepv',
      'def' => 'text/plain',
      'der' => 'application/x-x509-ca-cert',
      'dif' => 'video/x-dv',
      'dl' => 'video/dl',
      'doc' => 'application/msword',
      'dot' => 'application/msword',
      'dp' => 'application/commonground',
      'drw' => 'application/drafting',
      'dump' => 'application/octet-stream',
      'dv' => 'video/x-dv',
      'dvi' => 'application/x-dvi',
      'dwf' => 'model/vnd.dwf',
      'dwg' => 'application/acad',
      'dxf' => 'application/dxf',
      'dxr' => 'application/x-director',
      'el' => 'text/x-script.elisp',
      'elc' => 'application/x-elc',
      'env' => 'application/x-envoy',
      'eps' => 'application/postscript',
      'es' => 'application/x-esrehber',
      'etx' => 'text/x-setext',
      'evy' => 'application/envoy',
      'exe' => 'application/octet-stream',
      'f' => 'text/plain',
      'f' => 'text/x-fortran',
      'f77' => 'text/x-fortran',
      'f90' => 'text/plain',
      'f90' => 'text/x-fortran',
      'fdf' => 'application/vnd.fdf',
      'fif' => 'image/fif',
      'fli' => 'video/fli',
      'flo' => 'image/florian',
      'flx' => 'text/vnd.fmi.flexstor',
      'fmf' => 'video/x-atomic3d-feature',
      'for' => 'text/plain',
      'fpx' => 'image/vnd.fpx',
      'fpx' => 'image/vnd.net-fpx',
      'frl' => 'application/freeloader',
      'funk' => 'audio/make',
      'g' => 'text/plain',
      'g3' => 'image/g3fax',
      'gif' => 'image/gif',
      'gl' => 'video/gl',
      'gl' => 'video/x-gl',
      'gsd' => 'audio/x-gsm',
      'gsm' => 'audio/x-gsm',
      'gsp' => 'application/x-gsp',
      'gss' => 'application/x-gss',
      'gtar' => 'application/x-gtar',
      'gz' => 'application/x-gzip',
      'gzip' => 'application/x-gzip',
      'h' => 'text/plain',
      'hdf' => 'application/x-hdf',
      'help' => 'application/x-helpfile',
      'hgl' => 'application/vnd.hp-hpgl',
      'hh' => 'text/plain',
      'hh' => 'text/x-h',
      'hlb' => 'text/x-script',
      'hlp' => 'application/hlp',
      'hpg' => 'application/vnd.hp-hpgl',
      'hpgl' => 'application/vnd.hp-hpgl',
      'hqx' => 'application/binhex',
      'hta' => 'application/hta',
      'htc' => 'text/x-component',
      'htm' => 'text/html',
      'html' => 'text/html',
      'htmls' => 'text/html',
      'htt' => 'text/webviewhtml',
      'htx' => 'text/html',
      'ice' => 'x-conference/x-cooltalk',
      'ico' => 'image/x-icon',
      'idc' => 'text/plain',
      'ief' => 'image/ief',
      'iefs' => 'image/ief',
      'iges' => 'application/iges',
      'igs' => 'application/iges',
      'igs' => 'model/iges',
      'ima' => 'application/x-ima',
      'imap' => 'application/x-httpd-imap',
      'inf' => 'application/inf',
      'ins' => 'application/x-internett-signup',
      'ip' => 'application/x-ip2',
      'isu' => 'video/x-isvideo',
      'it' => 'audio/it',
      'iv' => 'application/x-inventor',
      'ivr' => 'i-world/i-vrml',
      'ivy' => 'application/x-livescreen',
      'jam' => 'audio/x-jam',
      'jav' => 'text/plain',
      'java' => 'text/plain',
      'jcm' => 'application/x-java-commerce',
      'jfif' => 'image/jpeg',
      'jfif' => 'image/pjpeg',
      'jpe' => 'image/jpeg',
      'jpe' => 'image/pjpeg',
      'jpeg' => 'image/jpeg',
      'jpg' => 'image/jpeg',
      'jps' => 'image/x-jps',
      'js' => 'text/javascript',
      'jut' => 'image/jutvision',
      'kar' => 'audio/midi',
      'ksh' => 'application/x-ksh',
      'la' => 'audio/nspaudio',
      'la' => 'audio/x-nspaudio',
      'lam' => 'audio/x-liveaudio',
      'latex' => 'application/x-latex',
      'lha' => 'application/lha',
      'lhx' => 'application/octet-stream',
      'list' => 'text/plain',
      'lma' => 'audio/nspaudio',
      'log' => 'text/plain',
      'lsp' => 'application/x-lisp',
      'lst' => 'text/plain',
      'lsx' => 'text/x-la-asf',
      'ltx' => 'application/x-latex',
      'lzh' => 'application/x-lzh',
      'lzx' => 'application/lzx',
      'm' => 'text/plain',
      'm1v' => 'video/mpeg',
      'm2a' => 'audio/mpeg',
      'm2v' => 'video/mpeg',
      'm3u' => 'audio/x-mpequrl',
      'man' => 'application/x-troff-man',
      'map' => 'application/x-navimap',
      'mar' => 'text/plain',
      'mbd' => 'application/mbedlet',
      'mc$' => 'application/x-magic-cap-package-1.0',
      'mcd' => 'application/mcad',
      'mcf' => 'image/vasa',
      'mcf' => 'text/mcf',
      'mcp' => 'application/netmc',
      'me' => 'application/x-troff-me',
      'mht' => 'message/rfc822',
      'mhtml' => 'message/rfc822',
      'mid' => 'audio/midi',
      'midi' => 'audio/midi',
      'mif' => 'application/x-frame',
      'mime' => 'www/mime',
      'mjf' => 'audio/x-vnd.audioexplosion.mjuicemediafile',
      'mjpg' => 'video/x-motion-jpeg',
      'mm' => 'application/base64',
      'mme' => 'application/base64',
      'mod' => 'audio/mod',
      'moov' => 'video/quicktime',
      'mov' => 'video/quicktime',
      'movie' => 'video/x-sgi-movie',
      'mp2' => 'audio/mpeg',
      'mp3' => 'audio/mpeg3',
      'mpa' => 'audio/mpeg',
      'mpa' => 'video/mpeg',
      'mpc' => 'application/x-project',
      'mpe' => 'video/mpeg',
      'mpeg' => 'video/mpeg',
      'mpg' => 'audio/mpeg',
      'mpg' => 'video/mpeg',
      'mpga' => 'audio/mpeg',
      'mpp' => 'application/vnd.ms-project',
      'mpt' => 'application/x-project',
      'mpv' => 'application/x-project',
      'mpx' => 'application/x-project',
      'mrc' => 'application/marc',
      'ms' => 'application/x-troff-ms',
      'mv' => 'video/x-sgi-movie',
      'my' => 'audio/make',
      'mzz' => 'application/x-vnd.audioexplosion.mzz',
      'nap' => 'image/naplps',
      'naplps' => 'image/naplps',
      'nc' => 'application/x-netcdf',
      'ncm' => 'application/vnd.nokia.configuration-message',
      'nif' => 'image/x-niff',
      'niff' => 'image/x-niff',
      'nix' => 'application/x-mix-transfer',
      'nsc' => 'application/x-conference',
      'nvd' => 'application/x-navidoc',
      'o' => 'application/octet-stream',
      'oda' => 'application/oda',
      'omc' => 'application/x-omc',
      'omcd' => 'application/x-omcdatamaker',
      'omcr' => 'application/x-omcregerator',
      'p' => 'text/x-pascal',
      'p10' => 'application/pkcs10',
      'p12' => 'application/pkcs-12',
      'p7a' => 'application/x-pkcs7-signature',
      'p7c' => 'application/pkcs7-mime',
      'p7c' => 'application/x-pkcs7-mime',
      'p7m' => 'application/x-pkcs7-mime',
      'p7r' => 'application/x-pkcs7-certreqresp',
      'p7s' => 'application/pkcs7-signature',
      'part' => 'application/pro_eng',
      'pas' => 'text/pascal',
      'pbm' => 'image/x-portable-bitmap',
      'pcl' => 'application/vnd.hp-pcl',
      'pct' => 'image/x-pict',
      'pcx' => 'image/x-pcx',
      'pdb' => 'chemical/x-pdb',
      'pdf' => 'application/pdf',
      'pfunk' => 'audio/make',
      'pgm' => 'image/x-portable-graymap',
      'pgm' => 'image/x-portable-greymap',
      'pic' => 'image/pict',
      'pict' => 'image/pict',
      'pkg' => 'application/x-newton-compatible-pkg',
      'pko' => 'application/vnd.ms-pki.pko',
      'pl' => 'text/plain',
      'plx' => 'application/x-pixclscript',
      'pm' => 'image/x-xpixmap',
      'pm4' => 'application/x-pagemaker',
      'pm5' => 'application/x-pagemaker',
      'png' => 'image/png',
      'pnm' => 'application/x-portable-anymap',
      'pnm' => 'image/x-portable-anymap',
      'pot' => 'application/mspowerpoint',
      'pov' => 'model/x-pov',
      'ppa' => 'application/vnd.ms-powerpoint',
      'ppm' => 'image/x-portable-pixmap',
      'pps' => 'application/mspowerpoint',
      'ppt' => 'application/mspowerpoint',
      'ppz' => 'application/mspowerpoint',
      'pre' => 'application/x-freelance',
      'prt' => 'application/pro_eng',
      'ps' => 'application/postscript',
      'psd' => 'application/octet-stream',
      'pvu' => 'paleovu/x-pv',
      'pwz' => 'application/vnd.ms-powerpoint',
      'py' => 'text/x-script.phyton',
      'pyc' => 'applicaiton/x-bytecode.python',
      'qcp' => 'audio/vnd.qcelp',
      'qd3' => 'x-world/x-3dmf',
      'qd3d' => 'x-world/x-3dmf',
      'qif' => 'image/x-quicktime',
      'qt' => 'video/quicktime',
      'qtc' => 'video/x-qtc',
      'qti' => 'image/x-quicktime',
      'qtif' => 'image/x-quicktime',
      'ra' => 'audio/x-pn-realaudio',
      'ram' => 'audio/x-pn-realaudio',
      'ras' => 'application/x-cmu-raster',
      'rast' => 'image/cmu-raster',
      'rexx' => 'text/x-script.rexx',
      'rf' => 'image/vnd.rn-realflash',
      'rgb' => 'image/x-rgb',
      'rm' => 'application/vnd.rn-realmedia',
      'rmi' => 'audio/mid',
      'rmm' => 'audio/x-pn-realaudio',
      'rmp' => 'audio/x-pn-realaudio',
      'rng' => 'application/ringing-tones',
      'rnx' => 'application/vnd.rn-realplayer',
      'roff' => 'application/x-troff',
      'rp' => 'image/vnd.rn-realpix',
      'rpm' => 'audio/x-pn-realaudio-plugin',
      'rt' => 'text/richtext',
      'rtf' => 'application/rtf',
      'rtx' => 'application/rtf',
      'rtx' => 'text/richtext',
      'rv' => 'video/vnd.rn-realvideo',
      's' => 'text/x-asm',
      's3m' => 'audio/s3m',
      'saveme' => 'application/octet-stream',
      'sbk' => 'application/x-tbook',
      'scm' => 'application/x-lotusscreencam',
      'sdml' => 'text/plain',
      'sdp' => 'application/sdp',
      'sdp' => 'application/x-sdp',
      'sdr' => 'application/sounder',
      'sea' => 'application/sea',
      'set' => 'application/set',
      'sgm' => 'text/sgml',
      'sgml' => 'text/sgml',
      'sgml' => 'text/x-sgml',
      'sh' => 'application/x-bsh',
      'shar' => 'application/x-bsh',
      'shar' => 'application/x-shar',
      'shtml' => 'text/html',
      'sid' => 'audio/x-psid',
      'sit' => 'application/x-sit',
      'skd' => 'application/x-koan',
      'skm' => 'application/x-koan',
      'skp' => 'application/x-koan',
      'skt' => 'application/x-koan',
      'sl' => 'application/x-seelogo',
      'smi' => 'application/smil',
      'smil' => 'application/smil',
      'snd' => 'audio/basic',
      'sol' => 'application/solids',
      'spc' => 'application/x-pkcs7-certificates',
      'spl' => 'application/futuresplash',
      'spr' => 'application/x-sprite',
      'sprite' => 'application/x-sprite',
      'src' => 'application/x-wais-source',
      'ssi' => 'text/x-server-parsed-html',
      'ssm' => 'application/streamingmedia',
      'sst' => 'application/vnd.ms-pki.certstore',
      'step' => 'application/step',
      'stl' => 'application/sla',
      'stp' => 'application/step',
      'svf' => 'image/vnd.dwg',
      'svr' => 'application/x-world',
      'swf' => 'application/x-shockwave-flash',
      't' => 'application/x-troff',
      'talk' => 'text/x-speech',
      'tar' => 'application/x-tar',
      'tbk' => 'application/toolbook',
      'tbk' => 'application/x-tbook',
      'tcl' => 'application/x-tcl',
      'tcsh' => 'text/x-script.tcsh',
      'tex' => 'application/x-tex',
      'texi' => 'application/x-texinfo',
      'text' => 'text/plain',
      'tgz' => 'application/x-compressed',
      'tif' => 'image/tiff',
      'tiff' => 'image/tiff',
      'tr' => 'application/x-troff',
      'tsi' => 'audio/tsp-audio',
      'tsp' => 'audio/tsplayer',
      'tsv' => 'text/tab-separated-values',
      'turbot' => 'image/florian',
      'txt' => 'text/plain',
      'uil' => 'text/x-uil',
      'uni' => 'text/uri-list',
      'unis' => 'text/uri-list',
      'unv' => 'application/i-deas',
      'uri' => 'text/uri-list',
      'uris' => 'text/uri-list',
      'ustar' => 'application/x-ustar',
      'uu' => 'application/octet-stream',
      'uue' => 'text/x-uuencode',
      'vcd' => 'application/x-cdlink',
      'vcs' => 'text/x-vcalendar',
      'vda' => 'application/vda',
      'vdo' => 'video/vdo',
      'vew' => 'application/groupwise',
      'viv' => 'video/vivo',
      'vivo' => 'video/vivo',
      'vmd' => 'application/vocaltec-media-desc',
      'vmf' => 'application/vocaltec-media-file',
      'voc' => 'audio/voc',
      'vos' => 'video/vosaic',
      'vox' => 'audio/voxware',
      'vqe' => 'audio/x-twinvq-plugin',
      'vqf' => 'audio/x-twinvq',
      'vql' => 'audio/x-twinvq-plugin',
      'vrml' => 'application/x-vrml',
      'vrt' => 'x-world/x-vrt',
      'vsd' => 'application/x-visio',
      'vst' => 'application/x-visio',
      'vsw' => 'application/x-visio',
      'w60' => 'application/wordperfect6.0',
      'w61' => 'application/wordperfect6.1',
      'w6w' => 'application/msword',
      'wav' => 'audio/wav',
      'wb1' => 'application/x-qpro',
      'wbmp' => 'image/vnd.wap.wbmp',
      'web' => 'application/vnd.xara',
      'wiz' => 'application/msword',
      'wk1' => 'application/x-123',
      'wmf' => 'windows/metafile',
      'wml' => 'text/vnd.wap.wml',
      'wmlc' => 'application/vnd.wap.wmlc',
      'wmls' => 'text/vnd.wap.wmlscript',
      'wmlsc' => 'application/vnd.wap.wmlscriptc',
      'word' => 'application/msword',
      'wp' => 'application/wordperfect',
      'wp5' => 'application/wordperfect',
      'wp6' => 'application/wordperfect',
      'wpd' => 'application/wordperfect',
      'wq1' => 'application/x-lotus',
      'wri' => 'application/mswrite',
      'wrl' => 'application/x-world',
      'wrz' => 'model/vrml',
      'wsc' => 'text/scriplet',
      'wsrc' => 'application/x-wais-source',
      'wtk' => 'application/x-wintalk',
      'xbm' => 'image/x-xbitmap',
      'xdr' => 'video/x-amt-demorun',
      'xgz' => 'xgl/drawing',
      'xif' => 'image/vnd.xiff',
      'xl' => 'application/excel',
      'xla' => 'application/excel',
      'xlb' => 'application/excel',
      'xlc' => 'application/excel',
      'xld' => 'application/excel',
      'xlk' => 'application/excel',
      'xll' => 'application/excel',
      'xlm' => 'application/excel',
      'xls' => 'application/excel',
      'xlt' => 'application/excel',
      'xlv' => 'application/excel',
      'xlw' => 'application/excel',
      'xm' => 'audio/xm',
      'xml' => 'application/xml',
      'xmz' => 'xgl/movie',
      'xpix' => 'application/x-vnd.ls-xpix',
      'xpm' => 'image/x-xpixmap',
      'xpm' => 'image/xpm',
      'x-png' => 'image/png',
      'xsr' => 'video/x-amt-showrun',
      'xwd' => 'image/x-xwd',
      'xyz' => 'chemical/x-pdb',
      'z' => 'application/x-compress',
      'zip' => 'application/zip',
      'zoo' => 'application/octet-stream',
      'zsh' => 'text/x-script.zsh'
    );
  }

}

?>