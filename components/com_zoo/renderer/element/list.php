<?php
/**
* @package   ZOO Component
* @file      list.php
* @version   2.3.0 December 2010
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) 2007 - 2011 YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// create label
$label = '';
if (isset($params['showlabel']) && $params['showlabel']) {
	$label .= '<strong>';
	$label .= ($params['altlabel']) ? $params['altlabel'] : $element->getConfig()->get('name');
	$label .= ': </strong>';
}

// create class attribute
$class = 'element element-'.$element->getElementType().' '.($params['first'] ? ' first' : '').($params['last'] ? ' last' : '');

?>
<li class="<?php echo $class; ?>">
	<?php echo $label.$element->render($params); ?>
</li>