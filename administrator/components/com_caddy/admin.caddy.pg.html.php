<?php
/**
* @package SimpleCaddy 1.75 for Joomla 1.5
* @copyright Copyright (C) 2006-2011 Henk von Pickartz. All rights reserved.
*/
// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

class pgdisplay {
    function main() {
		JToolBarHelper::title( JText::_( 'SimpleCaddyPG' )); 
		JToolBarHelper::custom( 'control', 'back.png', 'back.png', 'Main', false,  false );
        ?>
		<form name="adminForm">
        <table class="pgtable" cellpadding="5">
        <tr>
        <td>Add SimpleCaddy plugin code to all images <a class="scbutton" href="#" onclick="javascript:submitbutton('showaddsctoall');">Do it!</a></td>
        </tr>
        <tr>
        <td>Add SimpleCaddy code to a specific category <a class="scbutton" href="#" onclick="javascript:submitbutton('showaddsctopgcatid');">Do it!</a></td>
        </tr>
        <tr>
        <td>Remove SimpleCaddy plugin code from all images <a class="scbutton" href="#" onclick="javascript:submitbutton('remscfromall');">Do it!</a></td>
        </tr>
        <tr>
        <td>Remove SimpleCaddy code from a specific category <a class="scbutton" href="#" onclick="javascript:submitbutton('showremscfrompgcatid');">Do it!</a></td>
        </tr>
        
        </table>
		<input type="hidden" name="task" />
		<input type="hidden" name="option" value="com_caddy" />
		<input type="hidden" name="action" value="scphocag" />
		</form>
        <?php
    }
    
    function addtoall($prodcodelist) {
		JToolBarHelper::title( JText::_( 'SimpleCaddyPG' )); 
		JToolBarHelper::custom( 'addsctoall', 'save.png', 'save.png', 'Save', false,  false );
		JToolBarHelper::custom( 'control', 'back.png', 'back.png', 'Main', false,  false );
        ?>
		<form name="adminForm">
        <table>
        <tr>
        <td>SimpleCaddy product code:&nbsp;
        <select name="prodcode">
        <?php
            foreach($prodcodelist as $product) {
                echo "\n<option value='{$product->prodcode}'>{$product->shorttext}</option>";
            }
        ?>
        </select>
        
        </td>
        
        </tr>
        </table>
		<input type="hidden" name="task" />
		<input type="hidden" name="option" value="com_caddy" />
		<input type="hidden" name="action" value="scphocag" />
		</form>
        
        <?php
        
        
    }
    
    function addtocat($prodcodelist, $pgcatlist) {
		JToolBarHelper::title( JText::_( 'SimpleCaddyPG' )); 
		JToolBarHelper::custom( 'addsctopgcatid', 'save.png', 'save.png', 'Save', false,  false );
		JToolBarHelper::custom( 'control', 'back.png', 'back.png', 'Main', false,  false );
        ?>
		<form name="adminForm">
        <table>
        <tr>
        <td>SimpleCaddy product code:&nbsp;
        <select name="prodcode">
        <?php
            foreach($prodcodelist as $product) {
                echo "\n<option value='{$product->prodcode}'>{$product->shorttext}</option>";
            }
        ?>
        </select>
        
        </td>
        
        </tr>
        <tr>
        <td>Phoca Gallery category:&nbsp;
        <select name="pgcatid">
        <?php
            foreach($pgcatlist as $pgcat) {
                echo "\n<option value='{$pgcat->id}'>{$pgcat->title}</option>";
            }
        ?>
        </select>
        
        </td>
        </tr></table>
		<input type="hidden" name="task" />
		<input type="hidden" name="option" value="com_caddy" />
		<input type="hidden" name="action" value="scphocag" />
		</form>
        
        <?php
        
    }

    function remfromcat($prodcodelist, $pgcatlist) {
		JToolBarHelper::title( JText::_( 'SimpleCaddyPG' )); 
		JToolBarHelper::custom( 'remscfrompgcatid', 'save.png', 'save.png', 'Save', false,  false );
		JToolBarHelper::custom( 'control', 'back.png', 'back.png', 'Main', false,  false );
        ?>
		<form name="adminForm">
        <table>
        <tr>
        <td>SimpleCaddy product code:&nbsp;
        <select name="prodcode">
        <?php
            foreach($prodcodelist as $product) {
                echo "\n<option value='{$product->prodcode}'>{$product->shorttext}</option>";
            }
        ?>
        </select>
        
        </td>
        
        </tr>
        <tr>
        <td>Phoca Gallery category:&nbsp;
        <select name="pgcatid">
        <?php
            foreach($pgcatlist as $pgcat) {
                echo "\n<option value='{$pgcat->id}'>{$pgcat->title}</option>";
            }
        ?>
        </select>
        
        </td>
        </tr></table>
		<input type="hidden" name="task" />
		<input type="hidden" name="option" value="com_caddy" />
		<input type="hidden" name="action" value="scphocag" />
		</form>
        
        <?php
        
    }
}
?>