<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

// Department check for filter..
if (isset($_GET['dept']) && $_GET['dept'] > 0) {
  if (mswDeptPerms($MSTEAM->id, $_GET['dept'], $userDeptAccess) == 'fail') {
    $HEADERS->err403(true);
  }
}

// Call relevant classes..
include_once(REL_PATH . 'control/classes/class.tickets.php');
$MSPTICKETS           = new tickets();
$MSPTICKETS->settings = $SETTINGS;
$MSPTICKETS->datetime = $MSDT;
$title                = (isset($_GET['keys']) ? $msg_search6 : $msg_search2);
$loadiBox             = true;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/tickets/tickets-search.php');
include(PATH . 'templates/footer.php');

?>