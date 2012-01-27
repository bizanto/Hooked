<?php 

defined('_JEXEC') or die('Restricted Access');

global $jaxFuncNames;
if (!isset($jaxFuncNames) or !is_array($jaxFuncNames)) $jaxFuncNames = array();

$jaxFuncNames[] = 'relate,ajaxEditRelations';

