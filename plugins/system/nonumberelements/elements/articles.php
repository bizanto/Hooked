<?php
/**
 * Element: Articles
 * Displays an article id field with a button
 *
 * @package     NoNumber! Elements
 * @version     2.1.1
 *
 * @author      Peter van Westen <peter@nonumber.nl>
 * @link        http://www.nonumber.nl
 * @copyright   Copyright © 2010 NoNumber! All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Articles Element
 */
class JElementArticles extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Articles';

	function fetchElement( $name, $value, &$node, $control_name )
	{
		JHTML::_( 'behavior.modal', 'a.modal' );

		$_size		= $node->attributes( 'size' );
		$_multiple	= $this->def( $node->attributes( 'multiple'), 1 );

		$_doc =& JFactory::getDocument();
		$_js = "
			function jSelectArticle( id, title, object )
			{
				if ( document.getElementById(object+'_name') ) {
					document.getElementById(object+'_id').value = id;
					document.getElementById(object+'_name').value = title;
				} else {
					// multiple
					document.getElementById(object+'_id').value = document.getElementById(object+'_id').value.trim();
					if ( document.getElementById(object+'_id').value ) {
						 document.getElementById(object+'_id').value += ',';
					}
					document.getElementById(object+'_id').value += id;
				}
				document.getElementById('sbox-window').close();
			}";
		$_doc->addScriptDeclaration( $_js );

		$_link = 'index.php?option=com_content&amp;task=element&amp;tmpl=component&amp;object='.$control_name.$name;

		$html = "\n".'<div style="float: left;">';
		if( !$_multiple ) {
			$value_name = $value;
			if ( $value ) {
				$db =& JFactory::getDBO();
				// load the list of menu types
				$query = 'SELECT title' .
						' FROM #__content' .
						' WHERE id = '.$value.
						' LIMIT 1';
				$db->setQuery( $query );
				$value_name = $db->loadResult();
				$value_name .= ' ['.$value.']';
			}
			$html 	.= '<input type="text" id="'.$control_name.$name.'_name" value="'.$value_name.'" class="inputbox" size="'.$_size.'" disabled="disabled" />';
			$html 	.= '<input type="hidden" name="'.$control_name.'['.$name.']" id="'.$control_name.$name.'_id" value="'.$value.'" />';
		} else {
			$html 	.= '<input type="text" name="'.$control_name.'['.$name.']" id="'.$control_name.$name.'_id" value="'.$value.'" class="inputbox" size="'.$_size.'" />';
		}
		$html .= '</div>';
		$html .= '<div class="button2-left"><div class="blank"><a class="modal" title="'.JText::_('NN_SELECT_AN_ARTICLE').'"  href="'.$_link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}">'.JText::_('NN_SELECT').'</a></div></div>'."\n";

		return $html;
	}

	function def( $val, $default )
	{
		return ( $val == '' ) ? $default : $val;
	}
}