<?php
/**
 * @category	Core
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<form name="adminForm" id="adminForm" action="index.php?option=com_community" method="POST" enctype="multipart/form-data">
<table width="100%">
	<tr>
		<td valign="top" width="40%">
			<fieldset>
				<legend><?php echo JText::_('CC DETAILS');?></legend>
				<p><?php echo JText::_('CC DETAILS INFO');?></p>
				<table class="admintable" cellspacing="1">
					<tbody>
						<tr>
							<td width="300" class="key">
								<span class="hasTip" title="<?php echo JText::_( 'CC TITLE' ); ?>::<?php echo JText::_('CC MULTIPLE PROFILE TITLE TIPS'); ?>"><?php echo JText::_( 'CC TITLE' ); ?></span>
							</td>
							<td valign="top">
								<input type="text" title="A name identifier for this multiple profile type" maxlength="50" size="50" id="name" name="name" class="text_area" value="<?php echo $this->multiprofile->name;?>">
							</td>
						</tr>
						<tr>
							<td width="300" class="key" valign="top">
								<span class="hasTip" title="<?php echo JText::_( 'CC DESCRIPTION' ); ?>::<?php echo JText::_('CC MULTIPLE PROFILE DESCRIPTION TIPS'); ?>"><?php echo JText::_( 'CC DESCRIPTION' ); ?></span>
							</td>
							<td valign="top">
								<textarea name="description" id="description" rows="10" cols="50"><?php echo $this->multiprofile->description;?></textarea>
							</td>
						</tr>
						<tr>
							<td width="300" class="key" valign="top">
								<span class="hasTip" title="<?php echo JText::_( 'CC PUBLISHED' ); ?>::<?php echo JText::_('CC MULTIPLE PROFILE PUBLISHED TIPS'); ?>"><?php echo JText::_( 'CC PUBLISHED' ); ?></span>
							</td>
							<td valign="top">
								<?php echo JHTML::_('select.booleanlist' , 'published' , null , is_null( $this->multiprofile->published) ? true : $this->multiprofile->published , JText::_('CC YES') , JText::_('CC NO') ); ?>
							</td>
						</tr>
						<tr>
							<td width="300" class="key" valign="top">
								<span class="hasTip" title="<?php echo JText::_( 'CC REQUIRE APPROVALS' ); ?>::<?php echo JText::_('CC MULTIPLE PROFILE REQUIRE APPROVALS TIPS'); ?>"><?php echo JText::_( 'CC REQUIRE APPROVALS' ); ?></span>
							</td>
							<td valign="top">
								<?php echo JHTML::_('select.booleanlist' , 'approvals' , null , $this->multiprofile->approvals , JText::_('CC YES') , JText::_('CC NO') ); ?>
							</td>
						</tr>
						<tr>
							<td width="300" class="key" valign="top">
								<span class="hasTip" title="<?php echo JText::_( 'CC ALLOW GROUP CREATION' ); ?>::<?php echo JText::_('CC ALLOW GROUP CREATION TIPS'); ?>"><?php echo JText::_( 'CC ALLOW GROUP CREATION' ); ?></span>
							</td>
							<td valign="top">
								<?php echo JHTML::_('select.booleanlist' , 'create_groups' , null , $this->multiprofile->create_groups , JText::_('CC YES') , JText::_('CC NO') ); ?>
							</td>
						</tr>
						<tr>
							<td width="300" class="key" valign="top">
								<span class="hasTip" title="<?php echo JText::_( 'CC WATERMARK' ); ?>::<?php echo JText::_('CC MULTIPLE PROFILE WATERMARK TIPS'); ?>"><?php echo JText::_( 'CC WATERMARK' ); ?></span>
							</td>
							<td valign="top">
								<div style="float: left;">
									<div style="font-weight:700;text-decoration: underline;margin-bottom: 5px;"><?php echo JText::_('CC WATERMARK');?></div>
									<?php if( !empty( $this->multiprofile->watermark) ){ ?>
										<img src="<?php echo $this->multiprofile->getWatermark();?>" style="border: 1px solid #eee;" />
									<?php } else { ?>
										<?php echo JText::_('N/A');?>
									<?php } ?>
								</div>
								<div style="float: left;margin-left: 20px;">
									<div style="font-weight:700;text-decoration: underline;margin-bottom: 5px;"><?php echo JText::_('CC PREVIEW');?></div>
									<?php if( !empty( $this->multiprofile->thumb) ){ ?>
										<img src="<?php echo $this->multiprofile->getThumbAvatar();?>" style="border: 1px solid #eee;" />
									<?php } else { ?>
										<?php echo JText::_('N/A');?>
									<?php } ?>
								</div>
								<div style="clear: both;"></div>
								<div style="margin-top: 5px;">
									<input type="file" name="watermark" id="watermark" />
									<div><?php echo JText::_('CC MAXIMUM WATERMARK IMAGE SIZE');?></div>
								</div>
							</td>
						</tr>
						<tr>
							<td width="300" class="key" valign="top">
								<span class="hasTip" title="<?php echo JText::_( 'CC WATERMARK POSITION' ); ?>::<?php echo JText::_('CC WATERMARK POSITION TIPS'); ?>"><?php echo JText::_( 'CC WATERMARK POSITION' ); ?></span>
							</td>
							<td valign="top">
								<div class="watermark-position" style="position:relative; width:64px; height:64px; border:1px solid #ccc; z-index:1">
									<img src="<?php echo $this->multiprofile->getThumbAvatar();?>" width="64" height="64" />
									<input type="radio" value="top" id="watermark_top" name="watermark_location" style="position:absolute; margin:0;padding:0; top:0;    left: 25px;"<?php echo ($this->multiprofile->watermark_location == 'top' ) ? ' checked="checked"' : '';?> />
									<input type="radio" value="right" id="watermark_right" name="watermark_location" style="position:absolute; margin:0;padding:0; right:0;  top:  25px;"<?php echo ($this->multiprofile->watermark_location == 'right' ) ? ' checked="checked"' : '';?>>
									<input type="radio" value="bottom" id="watermark_bottom" name="watermark_location" style="position:absolute; margin:0;padding:0; bottom:0; left: 25px;"<?php echo ($this->multiprofile->watermark_location == 'bottom' ) ? ' checked="checked"' : '';?> >
									<input type="radio" value="left" id="watermark_left" name="watermark_location" style="position:absolute; margin:0;padding:0; left:0;   top:  25px;"<?php echo ($this->multiprofile->watermark_location == 'left' ) ? ' checked="checked"' : '';?> >
								</div>
							</td>
						</tr>
					</table>
			</fieldset>
		</td>
		<td valign="top">
			<fieldset>
				<legend><?php echo JText::_( 'CC FIELDS');?></legend>
				<p><?php echo JText::_('CC FIELDS INFO'); ?></p>
				<div>
					<span style="color: red;font-weight:700;"><?php echo JText::_('CC NOTE');?>:</span>
					<span><?php echo JText::_('CC MULTIPROFILE NOTE INFO');?></span>
				</div>
				<table class="adminlist" cellspacing="1">
					<thead>
						<tr class="title">
							<th width="1%">#</th>
							<th style="text-align: left;">
								<?php echo JText::_('CC NAME');?>
							</th>
							<th width="15%" style="text-align: center;">
								<?php echo JText::_('CC FIELD CODE');?>
							</th>
							<th width="15%" style="text-align: center;">
								<?php echo JText::_('CC TYPE');?>
							</th>
							<th width="1%" align="center">
								<?php echo JText::_('CC INCLUDE');?>
							</th>
						</tr>
					</thead>
					<?php
					$count	= 0;
					$i		= 0;
		
					foreach( $this->fields as $field )
					{
						if($field->type == 'group')
						{
		?>
					<tr class="parent">
						<td  style="background-color: #EEEEEE;">&nbsp;</td>
						<td colspan="4" style="background-color: #EEEEEE;">
							<strong><?php echo JText::_('CC GROUP');?>
								<span><?php echo $field->name;?></span>
							</strong>
							<div style="clear: both;"></div>
							<input type="hidden" name="parents[]" value="<?php echo $field->id;?>" />
						</td>
					</tr>
						<?php
							$i	= 0;	// Reset count
						}
						else if($field->type != 'group')
						{
							// Process publish / unpublish images
							++$i;
						?>
					<tr class="row<?php echo $i%2;?>" id="rowid<?php echo $field->id;?>">
						<td><?php echo $i;?></td>
						<td><span><?php echo $field->name;?></span></td>
						<td align="center"><?php echo $field->fieldcode; ?></td>
						<td align="center"><?php echo $field->type;?></td>
						<td align="center" id="publish<?php echo $field->id;?>">
							<input type="checkbox" name="fields[]" value="<?php echo $field->id;?>"<?php echo $this->multiprofile->isChild($field->id) ? ' checked="checked"' : '';?> />
						</td>
					</tr>
				<?php
						}
					$count++;
				}
				?>
				</table>
			</fieldset>
		</td>
	</tr>
</table>

<input type="hidden" name="view" value="multiprofile" />
<input type="hidden" name="task" value="save" />
<input type="hidden" name="id" value="<?php echo $this->multiprofile->id;?>" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_( 'form.token' ); ?>	
</form>