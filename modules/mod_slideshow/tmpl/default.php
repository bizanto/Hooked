<?php
/**
 * @author     Kevin Strong
 * @email      kevin.strong@wearehathway.com
 * @website    http://www.wearehathway.com
 * @copyright  Copyright (C) 2011 Hathway
*/

// no direct access
defined('_JEXEC') or die('Restricted access'); 

?>

<script type="text/javascript" src="<?php echo JURI::base().'modules/mod_slideshow/tmpl/mod_slideshow.js'; ?>"></script>
<script type="text/javascript">
jQuery(function ($) {
	$('.hss-slideshow').slideshow({
		autocycle: <?php echo $params->get('autocycle'); ?>,
		transtime: <?php echo $params->get('ttime'); ?>,
		transtype: '<?php echo $params->get('transition'); ?>'
	});
});
</script>

<div class="hss-slideshow">
    <div class="hss-slides">
		<?php foreach ($items as $item): ?>
        <div class="hss-slide">
            <div class="hss-caption">
                <h1><?php echo $item->title; ?></h1>
                <span><?php echo $item->tagline; ?></span>
            </div>
            <a style="background: url(<?php echo $item->image; ?>) 50% 50% no-repeat;" class="slide-image" href="<?php echo ContentHelperRoute::getArticleRoute($item->id, $item->catid, $item->sectionid); ?>">
            <?php /*
			<img src="<?php echo $item->image; ?>" />
			*/ ?>
            </a>
        </div>
        <?php endforeach; ?>
    </div>        
    <div class="hss-nav">
        <span><a class="hss-prev" href="#">&lt;</a></span>
        <ul class="hss-nav-items">
        <?php foreach ($items as $item): ?>
        	<li><img src="<?php echo $item->thumbnail; ?>" /></li>
        <?php endforeach; ?>
        </ul>
        <span><a class="hss-next" href="#">&gt;</a></span>
    </div>
</div>
