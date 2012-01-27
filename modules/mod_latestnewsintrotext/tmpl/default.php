<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<?php
	$show_date	= $params->get( 'show_date', 0 );
	$show_date_type	= $params->get( 'show_date_type', 0 );
?>
<ul class="<?php echo $params->get('ullistcss'); ?>">
<?php foreach ($list as $item) :  ?>
	<li class="<?php echo $params->get('lilistcss'); ?>">
		<a href="<?php echo $item->link; ?>" class="<?php echo $params->get('titlecss'); ?>"><?php echo strip_tags($item->text) ?></a><br />
        <?php
		if($show_date==1) {
			switch($show_date_type) {
				case 1:
					echo '<span class="'.$params->get('datecss').'">'.date("d F Y", strtotime($item->created)).'</span>';
					break;
				case 2:
					echo '<span class="'.$params->get('datecss').'">'.date("H:i", strtotime($item->created)).'</span>';
					break;
				default:
					echo '<span class="'.$params->get('datecss').'">'.date("d F Y H:i", strtotime($item->created)).'</span>';
					break;
			}
		}
?>
         <a href="<?php echo $item->link; ?>" class="<?php echo $params->get('introcss'); ?>"><?php echo strip_tags(substr($item->intro, 0, $params->get('characters'))); ?><?php if ($params->get('truncated') == 0){ echo $params->get('truncatedtext');}?></a><br />
    <?php if ($params->get('readmoreoption') == 0){ echo '<a href="'. $item->link .'"  class="'. $params->get('readmorecss') . '">'.$params->get('readmore').'</a>';}?>
    </li>
<?php endforeach; ?>
</ul>