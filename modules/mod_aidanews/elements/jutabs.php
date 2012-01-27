<?php
/************************************************************************************
 mod_aidanews for Joomla 1.5 by Danilo A.

 @author: Danilo A. - dan@cdh.it

This file is part of the JU News Ultra Joomla Module.
JU News Ultra is released under the GNU/GPL License.

 ----- This file is part of the AiDaNews Module. -----

    AiDaNews Module is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    AiDaNews is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this module.  If not, see <http://www.gnu.org/licenses/>.
************************************************************************************/

defined( 'JPATH_BASE' ) or die( 'Restricted access' );

require_once JPATH_LIBRARIES.DS.'joomla'.DS.'html'.DS.'pane.php';

class JElementJUtabs extends JElement {

    var    $_name = 'JUtabs';

    function fetchElement($name, $default, &$xmlNode, $control_name='')
    {
        $text = $xmlNode->_attributes['description'];
        $html  = '';
        $html .= '</td></tr></table>';
        $html .= JPaneSliders::endPanel();
        $html .= JPaneSliders::startPanel( ''.JText::_($text), $text );
        $html .= '<table width="100%" class="paramlist admintable" cellspacing="1">';
        $desc='';
        $html .= '<tr><td class="paramlist_description">'.$desc.'</td>';
        $html .= '<td class="paramlist_value">';

        return $html;
    }
}

?>