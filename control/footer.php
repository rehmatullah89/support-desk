<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err404();
}

// Footer..
$footer = '<b>' . $msg_script3 . '</b>: <a href="http://www.3-tree.com/" title="Triple Tree" onclick="window.open(this);return false">Triple Tree</a> ';
$footer .= '&copy;2016 <a href="http://www.3-tree.com" onclick="window.open(this);return false" title="Triple Tree">Triple Tree</a>. ' . $msg_script12 . '.';


// Commercial version..
/*if (LICENCE_VER == 'unlocked') {
  $footer = $SETTINGS->publicFooter;
  if ($footer == '') {
    $footer = $msg_script34;
  }
}*/

$tpl = new Savant3();
$tpl->assign('FOOTER', $footer);
$tpl->assign('TXT', array(
  $msg_script15,
  $msadminlang3_1[12],
  $msg_script55,
  $msadminlang3_1uploads[5]
));
$tpl->assign('FILES', $MSYS->jsCSSBlockLoader($ms_js_css_loader, 'foot'));

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/footer.tpl.php');

?>