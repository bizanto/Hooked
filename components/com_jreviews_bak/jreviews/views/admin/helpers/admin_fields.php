<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class AdminFieldsHelper extends S2Object
{	
	var $type;
	var $params;
	
	function advancedOptions($type,$params,$location) 
	{
		$this->type = $type;
		$this->params = $params;
		$this->location = $location;	
        
        App::import('Helper',array('form','html'));
        $Form = new FormHelper();
        $Form->Html = new HtmlHelper();

		switch($this->type) {
			
			case 'date':
				?>
				<table class="admin_list">
					<tr>
						<td><strong>Date output format:</strong>&nbsp;Uses <a href="http://www.php.net/strftime" target="_blank">PHP's strftime function</a> format
						<br /><input size="20" type="text" id="params[date_format]" name="data[Field][params][date_format]" value="<?php $this->dateFormat()?>" />
						<i>Default: %B %d, %Y</i>
						</td>
					</tr>
				</table>				
				<?php $this->click2search();?>
				<?php $this->outputFormat();?>			
				<?php
				break;
			case 'text':
			case 'textarea':
				?>
				<table class="admin_list">
					<tr>
						<td>
							<strong>Regex pattern for input validation:</strong>
							<br /><input id="params[valid_regex]" name="data[Field][params][valid_regex]" type="text" size="130" value="<?php $this->regex()?>" />
						</td>
					</tr>
				<?php $this->allowHtml()?>
				</table>																
				<?php $this->click2search()?>
				<?php $this->outputFormat()?>
				<?php
				break;
			case 'code':
				?>
				<table class="admin_list">
					<tr><td>There are no advanced options for the code enabled text area field.</td></tr>
				</table>				
				<?php
				break;
			case 'decimal':
			case 'integer':
				?>
				<table class="admin_list">
					<tr>
						<td>
							<strong>Regex pattern for input validation:</strong>
							<br /><input id="params[valid_regex]" name="data[Field][params][valid_regex]" type="text" size="130" value="<?php $this->regex()?>" />
						</td>
					</tr>
					<?php $this->currencyFormat()?>
					<?php $this->click2search()?>
					<?php $this->outputFormat()?>
				</table>				
				<?php
				break;
			case 'select':
			case 'selectmultiple':
			case 'radiobuttons':
			case 'checkboxes':
				?>
				<table class="admin_list">
                    <tr>
                        <td>                 
                            <strong>Show option images in field ouput</strong> - if disabled text will show even if images are assigned</strong><br />
                            <?php echo $Form->radioYesNo( "data[Field][params][option_images]", "", ($this->params->option_images ? $this->params->option_images : 1));?>
                        </td>
                    </tr>
					<tr>
						<td>
							<strong>Ordering:</strong> <i>setting used when submitting/editing an entry.</i><br />
							<select name="data[Field][params][option_ordering]" id="params[option_ordering]">
								<option value="0">Ordering</option>
								<option value="1">A-Z</option>
							</select>
						</td>
					</tr>
					<?php $this->click2search()?>
					<?php $this->outputFormat()?>
				</table>
				<?php
				break;
			case 'email':
				?>
				<table class="admin_list">
					<tr>
						<td>
							<strong>Regex pattern for input validation:</strong>
							<br /><input id="params[valid_regex]" name="data[Field][params][valid_regex]" type="text" size="150" value="<?php $this->regex()?>" />
						</td>
					</tr>
				</table>				
				<?php
				break;			
			case 'website':
				?>
				<table class="admin_list">
					<tr>
						<td>
							<strong>Regex pattern for input validation:</strong>
							<br /><input id="params[valid_regex]" name="data[Field][params][valid_regex]" type="text" size="150" value="<?php $this->regex()?>" />
						</td>
					</tr>
					<tr>
						<td>
							<strong>Output Format:</strong>&nbsp;-&nbsp;Enter any text and valid tags: {FIELDTEXT},{FIELDTITLE},{JR_NAME} <= will not work for select multiple or checkboxes.
						</td>
					</tr>
					<tr>
						<td>
							<textarea class="outputformat" id="params[output_format]" name="data[Field][params][output_format]"><?php $this->outputFormat()?></textarea>
							<br /><i>Default: &lt;a href="{FIELDTEXT}" target="_blank">{FIELDTEXT}&lt;/a></i>
						</td>
					</tr>
				</table>		
				<?php				
				break;			
		}
		
	}
	
	function allowHtml() {
		
		?>
			<tr>
				<td>
					<strong>Allow HTML:</strong><i>Users will be able to use html tags in this field.</i><br />
					<select name="data[Field][params][allow_html]" id="params[allow_html]">
						<option value="0">No</option>
						<option value="1">Yes</option>
					</select>
				</td>
			</tr>
		<?php

	}
	
	
	function click2search() {
		if($this->location != 'review') {
		?>
			<tr>
				<td>
					<strong>Click2Search URL:</strong>&nbsp;-&nbsp;You can use these tags {CRITERIAID},{CATID},{FIELDTEXT},{FIELDNAME},{ITEMID}
					<br /><i>Default: <?php echo 'index.php?option='.S2Paths::get('jreviews','S2_CMSCOMP').'&amp;Itemid={ITEMID}&amp;url=tag/{FIELDNAME}/{FIELDTEXT}/criteria'._PARAM_CHAR.'{CRITERIAID}/';?></i>
					<br /><input id="params[click2searchlink]" name="data[Field][params][click2searchlink]" type="text" size="180" value="<?php $this->click2searchLink()?>" />
				</td>
			</tr>
		<?php
		}

	}	
	
	function click2searchLink() {

		if(!isset($this->params->click2searchlink) || $this->params->click2searchlink == '') {
		
			echo 'index.php?option='.S2Paths::get('jreviews','S2_CMSCOMP').'&amp;Itemid={ITEMID}&amp;url=tag/{FIELDNAME}/{FIELDTEXT}/criteria'._PARAM_CHAR.'{CRITERIAID}/';
				
		} else {
			
			echo $this->params->click2searchlink;
		
		}
			
	}
	
	function currencyFormat() {
		?>
			<tr>
				<td>
					<strong>Currency Format:</strong> <i>Set to YES if you want numbers formatted with thousands and decimal point (i.e. x,xxx.xx).</i><br />
					<select name="data[Field][params][curr_format]" id="params[curr_format]">
						<option value="0">No</option>
						<option value="1">Yes</option>
					</select>
				</td>
			</tr>		
		<?php				
	}
	
	function dateFormat() {
		if(!isset($this->params->date_format) || $this->params->date_format == '') {
			echo '%B %d, %Y';
		} else {
			echo $this->params->date_format;
		}
	}
		
	function regex() {
		
		switch($this->type) {
			case 'website':
				$regex = '^(ftp|http|https)+(:\/\/)+[a-z0-9_-]+\.+[a-z0-9_-]';
			break;
			case 'decimal':
				$regex = '^(\.[0-9]+|[0-9]+(\.[0-9]+)|-{0,1}[0-9]*.{0,1}[0-9]+)$'; // 0.1, .1, -0.1
				break;
			case 'integer':
				$regex = '^[0-9]+$';
				break;
			case 'email':
				$regex = '.+@.*';
				break;	
			default:
				$regex = $this->params->valid_regex != '' ? $this->params->valid_regex : '';			
				break;	
		}
		
		echo $this->params->valid_regex != '' ? $this->params->valid_regex : $regex;
		
	}
	
	function outputFormat() 
    {
        App::import('Helper',array('form','html'));
        $Form = new FormHelper();
        $Form->Html = new HtmlHelper();
        		
		switch($this->type) {
			case 'website':
				if(!isset($this->params->output_format) || $this->params->output_format == '') {				
					echo '<a href="{FIELDTEXT}" target="_blank">{FIELDTEXT}</a>';
				} else {
					echo $this->params->output_format;
				}
				break;
			default:	
			$format = $this->params->output_format == '' ? '{FIELDTEXT}' : $this->params->output_format;
			?>
				<tr>
					<td>
						<strong>Output Format:</strong>&nbsp;-&nbsp;Enter any text and valid tags: {TITLE},{SECTION},{CATEGORY},{FIELDTEXT},{FIELDTITLE},{JR_NAME}<= will not work for select multiple or checkboxes.
						<br />The {OPTIONVALUE} tag can also be used for select lists, checkboxes and radiobuttons
						<br /><textarea class="outputformat" id="params[output_format]" name="data[Field][params][output_format]"><?php echo $format?></textarea>
	                     <br />
                         <strong>Apply Output Format Before Click2Search:</strong> <?php echo $Form->radioYesNo( "data[Field][params][formatbeforeclick]", "", (Sanitize::getInt($this->params,'formatbeforeclick',0)));?>
                    </td>
                </tr>   
			<?php
			break;						
		}
		
	}
}