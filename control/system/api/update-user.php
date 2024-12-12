<?php
/*********************************************
UPDATE USER API
 **********************************************/

if (!defined('PARENT') || !defined('MS_PERMISSIONS') || !defined('API_LOADER')) {
  $HEADERS->err403();
}


// Ticket data array from API..
$added      = 0;
$userData = $MSAPI->account($read, array_keys($timezones));

if (!empty($userData['accounts'])) {
  
  $countOfAccounts = count($userData['accounts']);
  $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] ' . $countOfAccounts . ' account(s) found in incoming data. Preparing to loop account(s)..');
  
  for ($i = 0; $i < $countOfAccounts; $i++) {
    $email    = trim($userData['accounts'][$i]['email']);
    $password = trim($userData['accounts'][$i]['password']);
    $status   = trim($userData['accounts'][$i]['status']);

    // Does account exist?
    $LI_ACC = mswGetTableData('portal', 'email', mswSafeImportString($email));

    if (isset($LI_ACC->id)) {

        //change password of user
        if (!empty($email) && !empty($password)) {      
            $MSACC->updatePassword($email, $password);      
        }
        
        //update status of user
        if (!empty($email) && !empty($status)) {
            
            if($status == 'A')
                $MSACC->activateUser($LI_ACC->id);
            else
                $MSACC->deActivateUser($LI_ACC->id);
        }
        
        $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] User account updated for name ' . $email . ' <' . $email . '>');   
        $MSAPI->response('OK', str_replace('{count}', 1, "updated successfully"));
    }
    else
    {
        $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Fatal error, user could not be updated to database: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
        $MSAPI->response('ERROR', $msg_api4);
    }
  }
}
?>