<?php
/*
 * Created on Jan 19, 2011
 *
 */ 
 
 defined('_JEXEC') or die( 'Restricted access' );
 
 $params = &JComponentHelper::getParams( 'com_resetpassword' );

 $authtype = $params->get( 'authentication' , 'username' );
 
?>


<form action="<?php echo JRoute::_('index.php?option=com_resetpassword&task=checknewpassword') ;  ?>" method="post">
<br>
<h1>Endre passord</h1>
<br>
<br>
<table>
<tr>
	<td>
		
	    <?php if($authtype == 'username' ) { echo JText::_('USER_NAME') ; } else {echo JText::_('EMAIL_ADDRESS') ; } ?>
		
	</td>
	
	<td>
		<input type="text" class="inputbox" name="username" value=""  />
	</td>

</tr>

<tr>

	<td><?php echo JText::_('USER_PASSWORD') ; ?></td>
	<td><input type="password" class="inputbox" name="password1"  /></td>
</tr>

<tr>


	<td><?php echo JText::_('REPEAT_PASSWORD') ; ?></td>
	<td><input type="password" class="inputbox" name="password2"  /></td>
</tr>

<tr>
	<td></td>
	<td><input type="submit" class="submit button" name="submitbutton" value="<?php echo  JText::_('SUBMIT') ?>" /></td>
</tr>

</table>



<input type="hidden"  name="token" value="<?php echo JRequest::getVar('token' , '' )  ;?>"  />

</form>