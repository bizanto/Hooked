<?php
defined('_JEXEC') or die('Restricted Access');

class HTML_saasy
{
	function showContent($option,$rows)
	{
		?>		
		<form action="index.php" method="post" name="adminForm">
		  <table class="adminlist">
		    <thead>
		      <tr>
			<th width="20">&nbsp;</th>
			<th class="title">Title</th>
		      </tr>
		    </thead>
		<?php

		jimport('joomla.filter.output');

		$k = 0;
		for($i = 0, $n=count($rows); $i < $n; $i++)
		{
			$row =& $rows[$i];
			$checked = JHTML::_("grid.id", $i, $row->id);
						$link = JFilterOutput::ampReplace('index.php?option='.$option.'&task=edit&cid[]='.$row->id);
			?>
			<tr class="<?php echo "row{$k}";?>">
			  <td><?php echo $checked;?></td>
			  <td><a href="<?php echo $link;?>"><?php echo ($row->title);?></a></td>
			</tr>
			<?php
		}
		?>
		</table>
		<input type="hidden" name="option" value="<?php echo $option;?>"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		</form>
		<?php
	}

	function editContent($row,$option)
	{
		$saasyConfig = &JComponentHelper::getParams( 'com_saasy' );
		$company = $saasyConfig->get('company');
		$product = $saasyConfig->get('product');
		$contact = $saasyConfig->get('contact');
		$submit = $saasyConfig->get('submit');
		$myaccount = $saasyConfig->get('myaccount');
		$mylistings = $saasyConfig->get('mylistings');
		
		$editor =& JFactory::getEditor();
		
		?>
		<fieldset>
		<p>
		Company Name (<?php echo $company; ?>) <br />
		{{company}}
		</p><p>
		Product Name (<?php echo $product; ?>) <br />
		{{product}}
		</p><p>
		Contact us URL (<?php echo $contact; ?>) <br />
		{{contact}}
		</p><p>
		Submit listing (<?php echo $submit; ?>) <br />
		{{submit}}
		</p><p>
		Manage account (<?php echo $myaccount; ?>) <br />
		{{myaccount}}
		</p><p>
		My listings (<?php echo $mylistings; ?>) <br />
		{{mylistings}}
		</p>
		</fieldset>
		<form action="index.php" method="post" name="adminForm" id="adminForm">
		  <input type="hidden" name="option" value="<?php echo $option;?>"/>
		  <input type="hidden" name="task" value=""/>
		  <input type="hidden" name="id" value="<?php echo $row->id;?>"/>
		  <fieldset class="adminform">
		  <legend>Edit <?php echo $row->title;?></legend>
		  <table class="admintable">
		    <tr>
			<td><?php 
				echo $editor->display('content',$row->content, '100%','100%',70,20);
			?></td>
		    </tr>
		  </table>
		  </fieldset>
		</form>
		<?php	
	}	
}
?>
