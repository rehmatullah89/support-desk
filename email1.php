<?php
require_once 'control/classes/mailer/class.phpmailer.php';

$mail = new PHPMailer(true); //defaults to using php "mail()"; the true param means it will throw exceptions on errors, which we need to catch

try {
  $mail->AddReplyTo('rehmatullah@3-tree.com', 'Rehmat Ullah');
  $mail->AddAddress('rehmatullahbhatti@gmail.com', 'RehmatUllah Bhatti');
  $mail->SetFrom('admin@support.3-tree.com', 'Support');
  
  $mail->Subject = 'PHPMailer Test Subject via mail(), advanced';
  $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
  $mail->MsgHTML('<h1>Hi this is test</h1>');
  $mail->Send();
  echo "Message Sent OK<p></p>\n";

} catch (phpmailerException $e) {
	
	echo $e->errorMessage(); //Pretty error messages from PHPMailer
	
} catch (Exception $e) {
	  echo $e->getMessage(); //Boring error messages from anything else!
}
    

?>