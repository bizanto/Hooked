<?php
/**
 * CSV Import Component for Content and jReviews
 * Copyright (C) 2008 NakedJoomla and Alejandro Schmeichler
 * This is not free software. Do not distribute it.
 * For license information visit http://www.nakedjoomla.com/license/csv_import_license.html
 * or contact info@nakedjoomla.com
**/

// no direct access
(defined('_VALID_MOS') OR defined('_JEXEC')) or die('Direct Access to this location is not allowed.');

global $mainframe, $database, $character_map;

define('CMS_JOOMLA15','CMS_JOOMLA15');
define('CMS_JOOMLA10','CMS_JOOMLA10');
define('CMS_MAMBO46','CMS_MAMBO46');
if (!defined('DS')) 		define('DS', DIRECTORY_SEPARATOR);

# Ensure user has access to this function
switch(getPlatform()) {
	case 'CMS_JOOMLA10': 
	case 'CMS_MAMBO46':
		/* we are in Joomla 1.0 */	
		if (!($acl->acl_check( 'administration', 'edit', 'users', $my->usertype, 'components', 'all' )
		| $acl->acl_check( 'administration', 'manage', 'users', $my->usertype, 'components','com_csv_import' ))) {
			mosRedirect( 'index2.php', _NOT_AUTH );
		}
		
		define( 'PATH_ROOT', $mainframe->getCfg('absolute_path') . DS);
		define( 'WWW_ROOT', $mainframe->getCfg('live_site') . DS);
		
	break;
	case 'CMS_JOOMLA15':
		/* We are in Joomla 1.5 */
		$user = & JFactory::getUser();
		if (!$user->authorize('administration', 'manage')) {
			$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
		}
	
		$database = &JFactory::getDBO();	
		
		define( 'PATH_ROOT', JPATH_SITE . DS);
		
		define('WWW_ROOT',$mainframe->getSiteURL());
							
	break;
	default:
		die('Not authorized');
		break;
}

require_once( PATH_ROOT . 'administrator' . DS . 'components' . DS . 'com_csv_import' . DS . 'admin.csv_import.html.php' );
require_once( PATH_ROOT . 'administrator' . DS . 'components' . DS . 'com_csv_import' . DS . 'csv_import.class.php' );
require_once( PATH_ROOT . 'administrator' . DS . 'components' . DS . 'com_csv_import' . DS . 'character_conversion_map.php' );

# Require csv reader library
require_once(PATH_ROOT . "administrator/components/com_csv_import/csvLib.php");

if(file_exists(PATH_ROOT . 'components' . DS . 'com_jreviews')) 
{
	DEFINE('_JREVIEWS_INSTALLED',1);
} else {
	DEFINE('_JREVIEWS_INSTALLED',0);	
}

echo '<link href="'. WWW_ROOT .'/administrator/components/com_csv_import/csv_import.css" rel="stylesheet" type="text/css" />';
?>
<table class="csvimport_heading">
   <tr>
      <td><img src="../administrator/components/com_csv_import/csv_import_title.gif" alt="CSV Import for Joomla and jReviews" /></td>
   </tr>
</table>

<?php
if(@!is_dir(PATH_ROOT . 'images/csv_import/')) {
	if(@!mkdir(PATH_ROOT . '/images/csv_import/',755)) {
		echo '<div style="color:red;font-weight:bold;">It was not possible to create the csv_import folder, please create this folder in /images/csv_import';
	}
}

//Turn off notice, warning

error_reporting(0);

$cid = josGetArrayInts( 'cid' );

switch ($task) {	
	case "show_step1":
		showStep1($option);
		break;
	case "process_step1":
		processStep1($option);
		break;
	case "process_step2":
		processStep2($option);
		break;			
	case "process_step3":
		processStep3($option);
		break;
	case "process_step4":
		processStep4($option);
		break;		
	case "remove":
		removeProfilers($cid,$option);
		break;	
	default:   
		profilerList($option);		
		break;		
}

function profilerList($option)
{
	global $database, $mainframe, $mosConfig_list_limit;	
	$limit 		= intval( $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit ) );
	$limitstart = intval( $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 ) );
	$search 	= $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
	if (get_magic_quotes_gpc()) {
		$search	= stripslashes( $search );
	}

	$where = array();
	
	if ($search) {
		$where[] = "LOWER(a.name) LIKE '%" . $database->getEscaped( trim( strtolower( $search ) ) ) . "%'";
	}

	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__im_profiler AS a"
	. (count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : "")
	;
	
	$database->setQuery( $query );
	$total = $database->loadResult();

	require_once( PATH_ROOT . 'administrator/includes/pageNavigation.php' );
	
	$pageNav = new mosPageNav( $total, $limitstart, $limit  );

	$query = " Select * From #__im_profiler ";
	
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );

	$rows = $database->loadObjectList();
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}
	
	HTML_csv_import::showProfilers( $option, $rows,$search, $pageNav );	
}



function showStep1($option)
{
	global $database;
	$profilerId=mosGetParam($_REQUEST,'profiler_id',0);
	$sql="Select * From #__im_profiler Where id=$profilerId";
	$database->setQuery($sql);
	$database->loadObject($rowProfiler);
	HTML_csv_import::showStep1($option,$rowProfiler,$profilerId);		
}


function processStep1($option)
{
	global $database,$my;
	$separator=mosGetParam($_REQUEST,'csv_separator',',');
	$profilerId=mosGetParam($_REQUEST,'profiler_id',0);
	$convertUTF8 = mosGetParam($_REQUEST,'csv_utf8',0);
	
	error_reporting(1);
	
	$userId=$my->id;
	
	if($_FILES['csv_file'])
	{
		$fileName=$_FILES['csv_file']['name'];		
		//check file extensions
		$ext=substr($fileName, strrpos($fileName, '.') + 1);
		
		if($ext!='csv')
		{
			mosRedirect("index2.php?option=$option&task=show_step1","Invalid file extensions");
		}
		else 
		{
			//Save the file to server
			$fileName=$userId."_".time()."_".$fileName;
			move_uploaded_file($_FILES['csv_file']['tmp_name'], PATH_ROOT . "images/csv_import/$fileName");

			//Read the csv fields
												
			//cell separator, row separator, value enclosure
			$csv = new CSV($separator, "\r\n", '"');
			
			//parse the string content
			$csv->setContent(file_get_contents(PATH_ROOT  ."images/csv_import/$fileName"));
			
			//returns an array with the CSV data
			if($convertUTF8) {
				$csvArray = utf8_encode_mix($csv->getArray());
			} else {
				$csvArray = $csv->getArray();				
			}

			//Read the header
			$headers = current($csvArray); 
						
			//Get column mapping from profiler
			
			if($profilerId>0)
			{
				$sql="Select * From #__im_fields Where profiler_id=$profilerId order by id";
				$database->setQuery($sql);
				$rowFields=$database->loadObjectList();				
			}
	
			//Get jos_jreview field
			
			if(_JREVIEWS_INSTALLED) {
				$sql="Select name From #__jreviews_fields WHERE location = 'content' order by name";
				$database->setQuery($sql);
				$rowJReviewFields=$database->loadObjectList();
			} else {
				$rowJReviewFields = array();
			}

			HTML_csv_import::showStep2($option,$profilerId,$separator,$convertUTF8,$headers,$rowFields,$fileName,$rowJReviewFields);
									
		}		
		
	}
	else 
	{
		mosRedirect("index2.php?option=$option&task=show_step1","Please select a csv file to import");
	}	
}


function processStep2($option)
{
	//Get the data from posted form
	global $database;

	$profilerId=mosGetParam($_REQUEST,'profiler_id',0);
	$separator=mosGetParam($_REQUEST,'separator',',');
	$fileName=mosGetParam($_REQUEST,'filename','');
	
	
	$columns=mosGetParam($_REQUEST,'columns',null);
	$fields=mosGetParam($_REQUEST,'fields',null);
	
	$rowGlobalFields = array();
	$arrFields=array();
	
	for($i=0;$i<count($fields);$i++)
	{
		$field=$fields[$i];
		$arrField=explode(".",$field);
		$tableName=$arrField[0];
		$fieldName=$arrField[1];
		if($tableName=="jos_jreviews_fields" && _JREVIEWS_INSTALLED)
			$arrFields[]="'".$fieldName."'";
	}
	
	if(!empty($arrFields))	{
		$sql="Select name,type From #__jreviews_fields where location = 'content' AND name not in (".implode(",",$arrFields).") order by name";
	
		$database->setQuery($sql);
	
		$rowGlobalFields = $database->loadObjectList();
	}
		
	//Get profiler data
	
	if($profilerId>0)
	{
		$sql="Select * From #__im_profiler where id=$profilerId";
		$database->setQuery($sql);
		$database->loadObject($rowProfiler);
		//Get setting for value fields
		
		
		$sql="Select * From #__im_global Where profiler_id=$profilerId order by id";
		$database->setQuery($sql);
		$rowCustomGlobalSettings=$database->loadObjectList();
	}
	HTML_csv_import::showStep3($option,$columns,$fields,$rowGlobalFields,$profilerId,$separator,$convertUTF8,$fileName,$rowProfiler,$rowCustomGlobalSettings);	
	
}


function processStep3($option)
{
	global $database;
	
	$profilerId=mosGetParam($_REQUEST,'profiler_id',0);
	$separator=mosGetParam($_REQUEST,'separator',',');
	$fileName=mosGetParam($_REQUEST,'filename','');
	
	$columns=mosGetParam($_REQUEST,'columns',null);
	$fields=mosGetParam($_REQUEST,'fields',null);	
	
	$globalColumns=mosGetParam($_REQUEST,'global_columns',null);
	$globalValues=mosGetParam($_REQUEST,'global_value',null);
	
	
	//Get global setting	
	$authorId=mosGetParam($_REQUEST,'author_id',0);
	$sectionId=mosGetParam($_REQUEST,'section_id',0);
	$categoryId=mosGetParam($_REQUEST,'category_id',0);
	$state=mosGetParam($_REQUEST,'state',0);
	$metaKeys=mosGetParam($_REQUEST,'meta_keys','');
	$metaDes=mosGetParam($_REQUEST,'meta_des','');
	$createdDate=mosGetParam($_REQUEST,'created_date','');
	$publishUpDate=mosGetParam($_REQUEST,'publish_up_date','');
	$publishDownDate=mosGetParam($_REQUEST,'publish_down_date','');
	
	$access=mosGetParam($_REQUEST,'access',0);
		
	//Added params
	$params=mosGetParam($_POST,'params',null);
	
	//Also, upload file, check now
	
	if($_FILES["default_image"]["name"])
	{
		$fileName1=time()."_".$_FILES["default_image"]["name"];		
		move_uploaded_file($_FILES["default_image"]["tmp_name"], PATH_ROOT . "images/stories/jreviews/$fileName1");
		$images="jreviews/$fileName1|||0||bottom||";
	}
							
	HTML_csv_import::showStep4($option,$columns,$fields,$globalColumns,$globalValues,$profilerId,$separator,$convertUTF8,$fileName,$authorId,$sectionId,$categoryId,$state,$metaKeys,$metaDes,$createdDate,$publishUpDate,$publishDownDate,$access,$params,$images);	
}


function processStep4($option)
{
	global $database;	
	//Get hidden data
	$separator=mosGetParam($_REQUEST,'separator',',');
	$convertUTF8 = mosGetParam($_REQUEST,'csv_utf8',0);	
		
	//fault here
	$fileName=mosGetParam($_REQUEST,'filename','');
	
	$columns=mosGetParam($_REQUEST,'columns',null);
	$fields=mosGetParam($_REQUEST,'fields',null);
	

	$globalColumns=mosGetParam($_REQUEST,'global_columns',null);
	$globalValues=mosGetParam($_REQUEST,'global_value',null);
	
	//Get global setting
	$authorId=mosGetParam($_REQUEST,'author_id',0);
	$sectionId=mosGetParam($_REQUEST,'section_id',0);
	$categoryId=mosGetParam($_REQUEST,'category_id',0);
	$published=mosGetParam($_REQUEST,'published',0);
	$metaKeys=mosGetParam($_REQUEST,'meta_keys','');
	$metaDes=mosGetParam($_REQUEST,'meta_des','');
	$createdDate=mosGetParam($_REQUEST,'created_date','');
	$publishUpDate=mosGetParam($_REQUEST,'publish_up_date','');
	$publishDownDate=mosGetParam($_REQUEST,'publish_down_date','');
	$access=mosGetParam($_REQUEST,'access',0);
	$state=mosGetParam($_REQUEST,'state',0);

	//Import data now, go to final step
	$rowContent=new mosContent($database);
	
	//Get all data
	$arrContentFieldName=array();
	$arrContentFieldSTT=array();
	
	$arrJreviewFieldName=array();
	$arrJreviewFieldSTT=array();
	
	$totalColumn=count($columns);
	
	//Mark the ignore field lists
	$arrIgnores=array();
	
	for($i=0;$i<$totalColumn;$i++)
	{
		$column=$columns[$i];
		$field=$fields[$i];
		$arrField=explode(".",$field);
		$tableName=$arrField[0];
		$fieldName=$arrField[1];
		
		if($fieldName!='ignore')
		{
			if($tableName=="jos_content")
			{
				$arrContentFieldName[$i]=$fieldName;
				$arrContentFieldSTT[]=$i;
			}
			else 
			{
				$arrJreviewFieldName[$i]=$fieldName;
				$arrJreviewFieldSTT[]=$i;
			}
		}
		else 
		{
			$arrIgnores[]=$i;
		}
	}
	
	//Set static content vaiable
	$arrGlobalSetting=array();
	$arrGlobalSetting["created_by"]=$authorId;
	$arrGlobalSetting["sectionid"]=$sectionId;
	$arrGlobalSetting["catid"]=$categoryId;
	$arrGlobalSetting["published"]=$published;
	$arrGlobalSetting["metakey"]=$metaKeys;
	$arrGlobalSetting["metadesc"]=$metaDes;
	$arrGlobalSetting["created"]=$createdDate;
	$arrGlobalSetting["publish_up"]=$publishUpDate;
	$arrGlobalSetting["publish_down"]=$publishDownDate;
	$arrGlobalSetting["access"]=$access;
	$arrGlobalSetting["state"]=$state;

	//CustomGlobla Varaible
	$totalGlobalFields=count($globalColumns);
		
	$arrJreviewSettings=array();
	
	for($i=0;$i<$totalGlobalFields;$i++)
	{
		$column=$globalColumns[$i];
		$value=$globalValues[$i];				
		$arrField=explode(".",$column);
						
		$field=$arrField[1];
		$arrJreviewSettings[$field]=$value;							
	}

	//Attributes
	$params = mosGetParam( $_POST, 'params', '' );
	if (is_array( $params )) {
		$txt = array();
		foreach ( $params as $k=>$v) {
			if (get_magic_quotes_gpc()) {
				$v = stripslashes( $v );
			}
			$txt[] = "$k=$v";
		}
		$attribs = implode( "\n", $txt );
	}
	
	//Read the file here
	
	//Open File and read the correlative data
	//cell separator, row separator, value enclosure
	$csv = new CSV($separator, "\r\n", '"');
	
	//parse the string content
	$csv->setContent(file_get_contents(PATH_ROOT . "images/csv_import/$fileName"));
	
	//returns an array with the CSV data
	if($convertUTF8) {
		$csvArray = utf8_encode_mix($csv->getArray());
	} else {
		$csvArray = $csv->getArray();		
	}

	//Read the header		
	$line=1;
	
	$arrErrorLine=array();
	$arrErrorMessage=array();
	$totalImported=0;
	
	$images=mosGetParam($_REQUEST,'images','');
	
	while( false != ( $cells = next($csvArray) ) )
	{		
		$totalCells=count($cells);
		
		//Check all the cell		
		$continue=false;
		
		for($k=0;$k<count($cells);$k++)
		{
			if($cells[$k])
			{
				$continue=true;
				break;
			}			
		}
		
		
		if($continue)
		{
			if($line>0)		
			{			
				$success=true;
				$errorLine=0;
				$errMsg="";
				
				$arrContentData=array();
				$arrJreviewData=array();
				
				for($i=0;$i<$totalCells;$i++)
				{
					$cell=$cells[$i];
					
					if(!in_array($i,$arrIgnores))
					{				
						if(in_array($i,$arrContentFieldSTT))
						{
							$contentField=$arrContentFieldName[$i];
							$arrContentData[$contentField]=convert_characters($cell);
						}
						else 
						{
							$jReviewField=$arrJreviewFieldName[$i];
							$arrJreviewData[$jReviewField]=convert_characters($cell);
						}
					}
				}

				$copyArrGlobalSetting = $arrGlobalSetting;
				
				// Overwrite global settings with CSV fields - added v1.0.9
				$csvColumns = array('catid','sectionid','created_by','metakey','metadesc','publish_up','publish_down','images');

				foreach($csvColumns AS $csvColumn) 
				{
					if(array_key_exists($csvColumn,$arrContentData) && $arrContentData[$csvColumn] != '')
					{
						unset($copyArrGlobalSetting[$csvColumn]);
					} elseif(array_key_exists($csvColumn,$arrContentData)) {
						unset($arrContentData[$csvColumn]);
					}
				}				

				$arrContentData=array_merge($arrContentData,$copyArrGlobalSetting);
				$arrJreviewData=array_merge($arrJreviewData,$arrJreviewSettings);
					
				//Save data to correlative table
				$rowContent=new mosContent($database);
				
				if(!$rowContent->bind($arrContentData,'id'))
				{
					$success=false;	
					$errMsg.=" ".$rowContent->getError();
				}			
				
				$rowContent->id=0;
					
				$rowContent->attribs=$attribs;
				
				if($rowContent->images==''){
				   $rowContent->images=$images;
				}

				if(!$rowContent->store())
				{
					$success=false;
					$errMsg.=" ".$rowContent->getError();
				}
				
				if($success && _JREVIEWS_INSTALLED)
				{
					$contentId=$rowContent->id;
					
					$arrJreviewData["contentid"]=$contentId;
						
					//Build query to insert into content table
					$err = insertObject("#__jreviews_content",$arrJreviewData);
								
					if($err)
					{
						$success=false;
						$errMsg.=" ".$err;
						$sql="Delete From #__content Where id=$rowContent->id";
						$database->setQuery($sql);
						$database->query();
					}							
				}
	
				if(!$success)
				{
					$arrErrorLine[]=$line+1;
					$arrErrorMessage[]=$errMsg;
				}
				else 
				{
					$totalImported++;
				}
			}
		}
		else 
		{
			break;
		}
		$line++;
	}
	
	//Save profiler
	$profileName=mosGetParam($_REQUEST,'profile_name');
	
	if($profileName)
	{
		$rowProfiler=new mosCSVProfiler($database);
		
		if(!$rowProfiler->bind($_POST))
		{
			echo "<script> alert('".$rowProfiler->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		
		$rowProfiler->name=$profileName;		
		$rowProfiler->number_run=1;
		$rowProfiler->last_run=date("Y-m-d");
		$rowProfiler->created=$rowProfiler->last_run;
		
		if(!$rowProfiler->store())
		{
			echo "<script> alert('".$rowProfiler->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}	
		
		//Insert data about other fields mapping
		$profilerId=$rowProfiler->id;
		for($i=0,$n=count($columns);$i<$n;$i++)
		{
			$column=$columns[$i];
			$field=$fields[$i];
			$columnNo=$i+1;
			$sql="Insert Into 
				 #__im_fields(
				 profiler_id,
				 column_no,
				 column_title,
				 `field`)
				 Values(
				 $profilerId,
				 $columnNo,
				 '$column',
				 '$field'				 
				 )
				";
			$database->setQuery($sql);
			
			if(!$database->query())
			{
				echo "<script> alert('".$database->getError()."'); window.history.go(-1); </script>\n";
				exit();
			}
							
		}
		
		//Insert into global setting tables

		for($i=0,$n=count($globalColumns);$i<$n;$i++)
		{
			$column=$globalColumns[$i];
			$value=$globalValues[$i];			
			$sql="Insert Into 
				 #__im_global(
				 profiler_id,
				 field_name,
				 field_value
				 )
				 Values(
				 $profilerId,				 
				 '$column',
				 '$value'				 
				 )
				";
			$database->setQuery($sql);
			
			if(!$database->query())
			{
				echo "<script> alert('".$database->getError()."'); window.history.go(-1); </script>\n";
				exit();
			}
							
		}			
	}
		
	//Save profile here
		
	HTML_csv_import::showImportResult($option,$totalImported,$arrErrorLine,$arrErrorMessage,$sectionId,$categoryId);	
}

function removeProfilers( $cid, $option ) {	
	
	global $database;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('Select an item to delete'); window.history.go(-1);</script>\n";
		exit;
	}
	
	//Delete data from related table
	if (count( $cid )) {
		
		mosArrayToInts( $cid );

		$cids = 'profiler_id=' . implode( ' OR profiler_id=', $cid );

		//Delete the related field
				
		$sql="Delete From #__im_fields Where ($cids)";
		
		$database->setQuery($sql);
		if (!$database->query()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		}
		
		
		//Delete global setting
		
		$sql="Delete From #__im_global Where ($cids)";
		$database->setQuery($sql);
		if (!$database->query()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		}
		
		//Delete the global data				
		$cids = 'id=' . implode( ' OR id=', $cid );
		
		$query = "DELETE FROM #__im_profiler "
		. "\n WHERE ( $cids )"
		;
		$database->setQuery( $query );
		
		if (!$database->query()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		}
	}

	mosRedirect( "index2.php?option=$option" );
}

//Build the help function for store data


function insertObject($tableName,$arr)
{
	global $database;
		
	$arrFields=array();
	$arrValues=array();
	foreach ($arr as $key => $value)
	{
		if($key)
		{
			$arrFields[]=$database->NameQuote($key);
			$arrValues[]=$database->Quote($value);
		}
	}	
	$sql="Insert Into $tableName(".implode(",",$arrFields).") Values(".implode(",",$arrValues).")";
	$database->setQuery($sql);
	$database->query();
	return $database->getErrorMsg();
}


/**
 * Returns CMS version
**/
function getPlatform(){
	
	if(class_exists('JFactory') && defined('_JEXEC'))
	{
		return CMS_JOOMLA15;
	
	} else if(defined('_VALID_MOS') && class_exists('joomlaVersion'))
	{
	    return CMS_JOOMLA10;
	    
	}
	elseif(defined('_VALID_MOS') && class_exists('mamboCore'))
	{
	    return CMS_MAMBO46;
	}
	
}

class cmsCompat {
    
    function calendar($value, $name, $id, $format = '%Y-%m-%d', $class) {
        
        switch(getPlatform()) {
            case 'CMS_JOOMLA10': 
            case 'CMS_MAMBO46':
                $html = '<input class="'.$class.'" type="text" name="'.$name.'" id="'.$id.'" size="25" maxlength="19" value="'.$value.'"/>';
                $html .= '<input name="reset" type="reset" class="button" onclick="return showCalendar(\''.$id.'\', \''.$format.'\');" value="..." />';
                return $html;
            break;
            case 'CMS_JOOMLA15':
                 return JHTML::_('calendar', $value, $name, $id, $format, array('class' => $class));    
            break;                    
        }
        
    }    
}

function checkedOut($row,$i) {

	if(getPlatform() == CMS_JOOMLA15) {
		return JHTML::_('grid.checkedout',  $row, $i);
	} else {
		return mosCommonHTML::CheckedOutProcessing( $row, $i );
	}

}

function loadCalendar() {
	
	if(getPlatform() == CMS_JOOMLA15) {
		JHTML::_('behavior.calendar');
	} else {
		mosCommonHTML::loadCalendar();
	}

}

function convert_characters($string) 
{ 
	global $character_map;

    $string = str_replace(array_keys($character_map), array_values($character_map), trim($string));

    return $string;
}

/**
* Encodes an ISO-8859-1 mixed variable to UTF-8 (PHP 4, PHP 5 compat)
* @param    mixed    $input An array, associative or simple
* @param    boolean  $encode_keys optional
* @return    mixed     ( utf-8 encoded $input)
*/

function utf8_encode_mix($input, $encode_keys=false)
{
    if(is_array($input))
    {
        $result = array();
        foreach($input as $k => $v)
        {               
            $key = ($encode_keys)? utf8_encode($k) : $k;
            $result[$key] = utf8_encode_mix( $v, $encode_keys);
        }
    }
    else
    {
        $result = utf8_encode($input);
    }

    return $result;
}

function prx($var){
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}