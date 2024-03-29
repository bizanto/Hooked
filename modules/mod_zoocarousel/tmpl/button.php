<?php
/**
* @package   ZOO Carousel
* @file      button.php
* @version   2.1.0
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) 2007 - 2010 YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div class="<?php echo $theme ?>">
	<div id="<?php echo $carousel_id ?>" class="yoo-carousel" style="<?php echo $css_module_width . $css_module_height ?>">

		<div class="<?php echo $control_panel ?>">

			<ul class="tabs" style="<?php echo $css_module_width ?>">
				<?php $i = 0; ?>
				<?php foreach ($items as $item) : ?>
					<li class="button item<?php echo $i + 1 ?>">
						<a href="javascript:void(0)" title="<?php echo $item->name; ?>">
							<span><?php echo $item->name; ?></span>
						</a>
					</li>
					<?php $i++; ?>
				<?php endforeach; ?>
			</ul>
			
			<div class="frame-t1">
				<div class="frame-t2">
					<div class="frame-t3">
					</div>
				</div>
			</div>
	
			<div class="frame" style="<?php echo $css_module_width ?>">
				<div class="frame-container-1">
					<div class="frame-container-2" style="<?php echo $css_panel_height ?>">
							
							<div class="panel" style="<?php echo $css_panel_width ?>">
								<div style="<?php echo $css_total_panel_width ?>">
								<?php foreach ($items as $item) : ?>
									<div class="slide" style="<?php echo $css_panel_width ?><?php echo $css_slide_position ?>">
										<div class="item"><?php echo $renderer->render('item.'.$layout, compact('item', 'params')); ?></div>
									</div>
								<?php endforeach; ?>
								</div>
							</div>
							
					</div>
				</div>
			</div>
			
			<div class="frame-b1">
				<div class="frame-b2">
					<div class="frame-b3">
					</div>
				</div>
			</div>
	
		</div>
		
	</div>
</div>