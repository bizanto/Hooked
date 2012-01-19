<?php

defined('_JEXEC') or die( 'Restricted access' );

?>


<div style="text-align: left">

<p>

<?php 

$params = &JComponentHelper::getParams( 'com_resetpassword' );
$random = $params->get( 'randompassword' , 'no' ) ;

if($random == 'yes') {
	echo JText::_('RANDOM_REQUEST_PASSWORD') ;
}
else { 
	echo JText::_('REQUEST_PASSWORD') ; 
}

?>
</p>

<br/>

	<form action="<?php echo JRoute::_('index.php?option=com_resetpassword&task=submitemail') ;  ?>" method="post">
		
		<table>
			<tr>
				<td>
					<?php echo JText::_('EMAIL_ADDRESS'); ?>
					
				</td>
				<td>
					<input type="text" class="inputbox" name="emailaddr" value="" size="30"  />
				</td>
				<td>
					<input type="submit" class="submit button" name="submit" value="<?php echo JText::_('SUBMIT')?>" />
				</td>
			</tr>
		
		</table>
		
		<?php 
			if( isset( $_SERVER['HTTP_REFERER'] ) &&  trim($_SERVER['HTTP_REFERER']) != ''  ) { 
		?>
				<input type="hidden" name="returnurl" value="<?php echo urlencode( $_SERVER['HTTP_REFERER'] ) ?>" />
	
		<?php 
			} 
		?>
	
	</form>

</div>