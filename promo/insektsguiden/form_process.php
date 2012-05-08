<?php
//Function that sends the email to the person who submitted the form
function send_email(){
    //This sends an email to the person who submitted the form
    // email fields: to, from, subject. Put your values here!
   	$sendermail = 'YOUREMAILHERE@DOMAIN.COM';
    $from = "YOUR NAME HERE<".$sendermail.">";
    $to = $_POST['email'];
    $subject = 'YOUR SUBJECT LINE HERE';
    $message = "Put the content of your email body here. Use the slash n values example to the right to create line breaks"."\n\n"."Best Regards,"."\n\n"."Your name";
    $headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/plain; charset=iso-8859-1' . "\n";
	$headers .= 'From: '. $from . "\r\n" .
	'Reply-To: '. $sendermail. "\r\n" .
	'X-Mailer: PHP/' . phpversion();

    $ok = @mail($to, $subject, $message, $headers);
    
	//This sends the contents of the form submission to the website owner
	$sendermail2 = $_POST['email'];
    $from2 = $_POST['first-name'].' '.$_POST['last-name'].' <'.$sendermail2.'>';
    $to2 = 'YOUREMAILHERE@DOMAIN.COM';
    $subject2 = 'A form submission from your website';
    $message2 = "Name: ". $_POST['first-name'] ." ". $_POST['last-name'] ."\n\n". "Email: ". $_POST['first-name'] ."\n\n"."Comments: ".$_POST['comments'];
    $headers2  = 'MIME-Version: 1.0' . "\r\n";
	$headers2 .= 'Content-type: text/plain; charset=iso-8859-1' . "\n";
	$headers2 .= 'From: '. $from2 . "\r\n" .
	'Reply-To: '. $sendermail2. "\r\n" .
	'X-Mailer: PHP/' . phpversion();

    $ok2 = @mail($to2, $subject2, $message2, $headers2);
}
return send_email();
?>