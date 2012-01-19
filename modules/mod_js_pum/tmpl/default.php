<?php
/**
* @package JomSocial People You Many Know
* @copyright Copyright (C) 2010-2011 Techjoomla, Tekdi Web Solutions . All rights reserved.
* @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
* @link     http://www.techjoomla.com
*/  		
		
defined( '_JEXEC' ) or die( 'Restricted Access' );
//$k  = 0;
$nou  = 0;
$doc = &JFactory::getDocument();
$doc->addStyleSheet( JURI::base().'modules/mod_js_pum/css/style.css' );

$no_of_rows = $params->get('no_of_rows',5);
	$rows=$no_of_rows;
	$no_of_columns = $params->get('no_of_columns',1);
	$col=$no_of_columns;
	$total=$rows+$col;
	
	$col1=$col-1;

//Output the users
if($users)
{
	?>
	<!-- pum_main start here -->
	<div class="pum_main" id="pum_main">				
	<?php //foreach($users as $usera) 
	
		for($j=0;$j<$total;$j++)
		{
		//echo $j."j";
		//for($j=0;$j<$col;$j++)
			//for($j;$j<$col;$j++)
			//{
			//$cnt++;
			/*******/
			if($rows==$col)
				{
					if($j==$col1)
					{ ?>
						<!-- <br/>-->
					<?php
					}
					
				}
			
			else if($j==$col)
				{ ?>
					<!--<br/>-->
				
		<?php 
				}
			$user =& CFactory::getUser($users[$j]->id);
		
			if($most_popular[$users[$j]->id] > 1)
			{
				$seeall  = 0;
				$nou++; 
			?>
			<!-- pum_case start here -->
			<div style="width:<?php echo $width;?>%;" class="pum_case">
				<!-- pum_avatar start here -->
				<div class="pum_avatar">
	    			<?php 
	    				$link    = CRoute::_('index.php?option=com_community&view=profile&userid='.$users[$j]->id);
						$uimage = $user->getThumbAvatar(); 
					?>   
					
					<div class="pum_avatar_spac">
					<?php 
						if($image==1)
						{ 
							?>
							<a class="pum_link" href="<?php echo $link; ?>">
							<a class="pum_link" href="<?php echo $link; ?>">
								<img class="avatar jomTips" src="<?php echo $uimage; ?>" title="<?php echo cAvatarTooltip($user); ?>" alt="<?php echo 'Avatar Image'; ?>" border="0" width="48" height="48" />
							</a>
							<?php 
						} 
							?>
					</div>
				</div>
		        <!-- pum_avatar end here -->
				
				<!-- pum_user_info start here -->
				<div class="pum_user_info">
        			<form class="pum_but" method="post" action="">
						<input type="hidden" name="js_ignore_id" value="<?php echo $users[$j]->id; ?>" />
						<input class="pum_but" type="button" align="middle" onclick="ignoreAjaxpum(this)" value="Ignore this User" />
					</form>
		          	<!-- pum_user_name start here -->
					<div class="pum_user_name">
            			<span>
							<?php 
								if($disp_name == 0)
								{
									?>
									<a class="pum_link" href="<?php echo $link; ?>"><b><?php echo $users[$j]->username; ?></b></a> 		
									<?php 
								} 
								if($disp_name == 1)
								{ 
									?>
									<a class='pum_link' href="<?php echo $link; ?>"><b><?php echo $users[$j]->name; ?></b> </a> 		
									<?php 
								} 	?>
            			</span>
            			
            			<?php  if($int_jbolo)
						{
							
							/*********** If User Online the Show Green Icon! **********/
							if($users[$j]->session_id) { ?>
							<span class="mf_chaton"><a onclick="javascript:chatWith(<?php echo $users[$j]->id; ?>)" href="javascript:void(0)"><?php echo JText::_('CHAT') ; ?></a></span>
							<?php }
							/*********** If User Offline the Show Black Icon! **********/
							else {?>
							<span class="mf_chatoff"><a href='javascript:void(0)'><?php echo JText::_('OFF_CHAT') ; ?></a></span>
							<?php }
						}	  	?>
						
						<span class="pum_friends">
							<?php echo " ".$most_popular[$users[$j]->id]." ".JText::_('M_FRND'); ?>
							
                		</span>
						
						<span>
							<a class="pum_button" onclick="gjconnect('<?php echo $users[$j]->id ; ?>',this)" href="javascript:void(0)">
								<?php echo JText::_('ADD_FRND') ; ?>
							</a>
						</span>
	        	    </div>
		         	<!-- pum_user_name end here -->
			</div>
			<!-- pum_user_info end here -->            
		</div>
    	<!-- pum_case end here -->
		
		<?php 
			if($nou==$no_of_users)
			{
				break; 
			} 
		} //if most pop end
	//$k++;
	} 	//for	
		?>
		<div style="clear:both;"></div>
	</div>
	<!-- pum_main end here -->
	
	<?php 
}//if
		if($nou == 0 )
		{
			?>
			<div>
				<?php echo JText::_('MAT_MSGN'); ?>
			</div>
			<?php	
		} 
	?>
