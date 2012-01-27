<?php
 defined('_JEXEC') or die( 'Restricted access' );
 
 JToolBarHelper::preferences( 'com_resetpassword' );
 
?>
<p>

This component lets users choose a new password without typing in 
an authentication token. Instead a direct link in the confirmation email is provided.<br/>
For security no super administrators are allowed to change their password through this component.

</p>

<p>

I created this component as a fix for invalid token errors I had with joomla 1.5.
Also users don't have to copy and paste tokens around.
<br/>

</p>

<p>
This component requires a plugin to be installed and enabled. The plugin will change the
standard joomla reset password link and link to this component instead. 
</p>

<p>

Hope it will provide useful. For any problems or feedbak you can contact me through my website: http://www.subclosure.com


</p>

 <h2> Reset Password Log (<?php  echo $this->total ; ?>) </h2>
 <div>
 <form action="index.php" method="post" name="adminForm">

	 <table class="adminlist">
	    <thead>
	        <tr>
	            <th>
	                Reset Date
	            </th>
	            <th>
	                Name
	            </th>
	            <th>
	                Username
	            </th>	            
	            <th>
	                Email
	            </th>	
	            <th>
	                Blocked
	            </th>		            
	            <th>
	                Register Date
	            </th>	
	            <th>
	                Last Visit Date
	            </th>	                                 
	        </tr>            
	    </thead>
	    <?php
	
	    foreach( $this->items as $item)
	    {
	    	?>
	    	
	    	<tr>
	    	
	    		<td>
	    			<?php echo $item->date ; ?>
	    		</td>
	    		
	    		
	    		<td>
	    			<?php echo $item->name ; ?>
	    		</td>
	    		
	    		<td>
	    			<?php echo $item->username ; ?>
	    		</td>
	    		
	    		<td>
	    			<?php echo $item->email ; ?>
	    		</td>
	    		<td>
	    			<?php echo $item->block ; ?>
	    		</td>	    		
	    		<td>
	    			<?php echo $item->registerDate ; ?>
	    		</td>	   
	    		<td>
	    			<?php echo $item->lastvisitDate ; ?>
	    		</td>	    		 		
	    	
	    	</tr>
	    	
	    	
	    	<?php
	    	
	    }
	 
	    ?>
	    
	      <tfoot>
		    <tr>
		      <td colspan="9"><?php echo $this->pagination->getListFooter(); ?></td>
		    </tr>
		  </tfoot>
	
	 </table> 
	 
	 <input type="hidden" class="button" name="option" value="com_resetpassword" />
	 
	 </form>  
 </div>