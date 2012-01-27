// JavaScript Document

function mtp_checkemaddr(testaddr)
 {
  //var checkexp = /.+@.+/;
  var checkexp = /^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,4}$/;
  var validaddr=0;
  validaddr = checkexp.test(testaddr);
  if (!validaddr ) {
      return false;
  }
  else {return true}
 }
 
 function mtp_validateForm()
 {
	 var theForm = document.getElementById('mtp_form');
	 if (theForm.mtp_user_name.value=="")
	 {
		 window.alert( user_name_blank );
		 return false;
	 }
	 if (theForm.mtp_friend_name.value=="")
	 {
		 window.alert( friend_name_blank );
		 return false;
	 }	
	 if (!mtp_checkemaddr( theForm.mtp_user_email.value ))
	 {
		window.alert( invalid_user_mail );		 
		return false;
	 }
	 if (!mtp_checkemaddr( theForm.mtp_friend_email.value ))
	 {
		window.alert( invalid_friend_mail );		 		 
		return false;
	 }
	 return true;

	 
 }
