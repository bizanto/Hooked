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

class HTML_csv_import {	
	
	function showStep1($option,$rowProfiler,$profilerId)
	{
		if($rowProfiler->column_separator)
			$separator=$rowProfiler->column_separator;
		else 
			$separator=",";	
	?>

<form method="POST" name="adminForm" id="adminForm" action="index2.php" enctype="multipart/form-data">
   <table class="csvimport_subheading">
      <tr>
         <td><h1>Step 1 : Choose CSV File</h1>
            <p>Browse to the CSV file that contains the content you would like to import. Please ensure that the first row of your CSV file is a header row that specifies the column names; these will be used to to align the columns with Joomla and/or jReviews fields.</p></td>
      </tr>
   </table>
   <table class="adminform" width="100%">
      <tr>
         <th colspan="2"> File to Import </th>
      </tr>
      <tr>
         <td nowrap="nowrap">CSV File to Import:</td>
         <td width="100%"><input type="file" name="csv_file" size="60" /></td>
      </tr>
      <tr>
         <td nowrap="nowrap">Fields Terminated by:</td>
         <td><input type="text" name="csv_separator" size="10" value="<?php echo $separator?>"/>
         </td>
      </tr>
      <tr>
         <td nowrap="nowrap">Convert CSV File to UTF-8:</td>
         <td><select name="csv_utf8" size="1">
         	<option value="1" selected="selected">Yes</option>
         	<option value="0">No</option>
         	</select>
         </td>
      </tr>      
   </table>
   <input type="hidden" name="option" value="<?php echo $option?>" />
   <input type="hidden" name="task" value="" />
   <input type="hidden" name="profiler_id" value="<?php echo $profilerId?>"
		
</form>
<?php	
	}
	function showStep2($option,$profilerId,$separator,$csv_utf8,$headers,$rowFields,$fileName,$rowJReviewFields)
	{
		if(getPlatform() == CMS_JOOMLA15) {
			$alias_column = 'alias';
		} else {
			$alias_column = 'title_alias';
		}		
		
	?>
<form method="POST" name="adminForm" id="adminForm" action="index2.php">
   <table class="csvimport_subheading">
      <tr>
         <td><h1>Step 2: Associate Columns and Fields </h1>
            <p>Specify the Joomla or jReviews field to which each column in your CSV file should be associated. To skip a column, choose the &quot;Ignore&quot; option in the right-hand column.</p>
            </th>
         </td>
      </tr>
   </table>
   <table class="adminform" width="100%">
      <tr>
         <th>CSV Field/Column</th>
         <th>&nbsp;</th>
         <th width="100%">Joomla/jReviews Field </th>
      </tr>
      <?php
	$totalFields=0;
	for($i=0,$n=count($headers);$i<$n;$i++)
	{
		$header=$headers[$i];
		if($rowFields)
		{
			$rowField=$rowFields[$i];
			$columnNo=$rowField->column_no;
			$columnTitle=$rowField->column_title;
			$field=$rowField->field;
			$arrFields=explode(".",$field);
			$tableName=$arrFields[0];
			$columnName=$arrFields[1];										
		}
		if($header)
		{			
			$totalFields++;						
	?>
      <tr class="row<?php echo $i%2?>">
         <td><input type="text" name="columns[]2" value="<?php echo $header?>" size="60" style="width:250px;" readonly="true" /></td>
         <td><img src="components/com_csv_import/arrow.gif" alt="Associate with..." /></td>
         <td><select name="fields[]2" style="width:250px;" id="field_<?php echo $totalFields-1?>">
               <optgroup label="jos_content">
               <!-- Added ignore field-->
               <option value="jos_jreviews_fields.ignore" <?php if($columnName=="ignore") echo "selected";  ?>>Ignore</option>
               <!-- End of ignore field-->
               <option value="jos_content.created_by" <?php if(($columnName=="created_by")&&($tableName=="jos_content")) echo "selected" ;?>>Author ID</option>
               <option value="jos_content.title" <?php if(($columnName=="title")&&($tableName=="jos_content")) echo "selected" ;?>>Title</option>
               <option value="jos_content.<?php echo $alias_column;?>" <?php if(($columnName==$alias_column)&&($tableName=="jos_content")) echo "selected" ;?>>Title Alias</option>
               <option value="jos_content.introtext" <?php if(($columnName=="introtext")&&($tableName=="jos_content")) echo "selected" ;?>>Intro text</option>
               <option value="jos_content.fulltext" <?php if(($columnName=="fulltext")&&($tableName=="jos_content")) echo "selected" ;?>>Fulltext</option>
               <option value="jos_content.images" <?php if(($columnName=="images")&&($tableName=="jos_content")) echo "selected" ;?>>Images</option>
               <option value="jos_content.sectionid" <?php if(($columnName=="sectionid")&&($tableName=="jos_content")) echo "selected" ;?>>Section ID</option>
               <option value="jos_content.catid" <?php if(($columnName=="catid")&&($tableName=="jos_content")) echo "selected" ;?>>Category ID</option>
               <option value="jos_content.metakey" <?php if(($columnName=="metakey")&&($tableName=="jos_content")) echo "selected" ;?>>Meta Keywords</option>
               <option value="jos_content.metadesc" <?php if(($columnName=="metadesc")&&($tableName=="jos_content")) echo "selected" ;?>>Meta Description</option>
               <option value="jos_content.created" <?php if(($columnName=="created")&&($tableName=="jos_content")) echo "selected" ;?>>Created Date</option>
               <option value="jos_content.publish_up" <?php if(($columnName=="publish_up")&&($tableName=="jos_content")) echo "selected" ;?>>Publish Start Date</option>
               <option value="jos_content.publish_down" <?php if(($columnName=="publish_down")&&($tableName=="jos_content")) echo "selected" ;?>>End Start Date</option>
               </optgroup>
               <optgroup label="jos_jreviews_fields">
               <?php
														for($k=0,$m=count($rowJReviewFields);$k<$m;$k++)
														{
															$rowJreviewField=$rowJReviewFields[$k];
														?>
               <option value="jos_jreviews_fields.<?php echo $rowJreviewField->name?>" <?php if(($columnName==$rowJreviewField->name)&&($tableName=="jos_jreviews_fields")) echo "selected" ;?>>
               <?php echo $rowJreviewField->name?>
               </option>
               <?php	
														}			
													?>
               </optgroup>
            </select></td>
      </tr>
      <?php	
		}
	}
	?>
   </table>
   <input type="hidden" name="option" value="<?php echo $option;?>" />
   <input type="hidden" name="task" value="" />
   <input type="hidden" name="filename" value="<?php echo $fileName;?>" />
   <input type="hidden" name="profiler_id" value="<?php echo $profilerId;?>" />
   <input type="hidden" name="separator" value="<?php echo $separator;?>" />
   <input type="hidden" name="csv_utf8" value="<?php echo $csv_utf8;?>" />
</form>
<script language="javascript">
			var totalField=<?php echo $totalFields?>;
			function submitbutton(pressButton)
			{
				
				
				
				//Update class Name
				
				for(var i=0;i<totalField-1;i++)
				{
					var select=document.getElementById("field_"+i);
					select.className="inputbox";
				}
				
				if(pressButton=="")
				{
					submitform(pressButton);
				}
				else
				{
					//Check to see if there are any columns are duplicate
					var success=true;
					for(var i=0;i<totalField-1;i++)
					{
						var parentSelect=document.getElementById("field_"+i);
						
						if(parentSelect.value!="jos_jreviews_fields.ignore")
						{						
							for(var j=i+1;j<totalField;j++)
							{
								childSelect=document.getElementById("field_"+j);							
								if(parentSelect.value==childSelect.value)
								{
									success=false;
									parentSelect.className="error";
									childSelect.className="error";
									return;
								}
							}
						}
					}
					
					
					if(!success)
					{
						alert("One CSV column must match one field on database");
					}
					else
					{
						submitform(pressButton);
					}
				}
			}
		</script>
<?php		
	}

	
	
	function showStep3($option,$columns,$fields,$rowGlobalFields,$profilerId,$separator,$csv_utf8,$fileName,$rowProfiler,$rowCustomGlobalSettings)
	{
//		mosCommonHTML::loadOverlib();
		loadCalendar();
	?>
<form name="adminForm" method="POST" id="adminForm" action="index2.php" enctype="multipart/form-data">
   <table class="csvimport_subheading">
      <tr>
         <td><h1>Step 3: Global Settings for New Content Items </h1>
            <p>For your convenience, you can set basic  display parameters (such as those found in a content item's Parameters tab) for all newly created content items using the options directly below. Additional Joomla and jReviews fields (if applicable) are also available below: choose a field, then specify a value. <strong>The values on this page will be applied to all new content items. </strong></p></td>
      </tr>
   </table>
   <br>
   <p>* Used only if not specified in CSV file.</p>
   <table class="adminform" width="100%">
      <tr>
         <th colspan="2">Basic Joomla Parameters for All New Content Items </th>
      </tr>
      <tr>
         <td nowrap="nowrap"> Author User ID: </td>
         <td width="100%"><input type="text" name="author_id" size="10" value="<?php echo $rowProfiler->author_id?>">
            <a href="index3.php?option=com_users&task=view" target="_blank">View User List</a> </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Section ID*: </td>
         <td><input type="text" name="section_id" size="10" value="<?php echo $rowProfiler->section_id?>">
            <a href="index3.php?option=com_sections&scope=content" target="_blank">View Section List</a> </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Category ID*: </td>
         <td><input type="text" name="category_id" size="10" value="<?php echo $rowProfiler->category_id?>">
            *Used if not specified in CSV file. <a href="index3.php?option=com_categories&section=content" target="_blank">View Category List</a></td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Published: </td>
         <td><select name="state">
               <option value="1" <?php if($rowProfiler->state) echo "selected"; ?>>Yes</option>
               <option value="0" <?php if(!$rowProfiler->state) echo "selected"; ?>>No</option>
            </select>
         </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Meta Keywords*: </td>
         <td><input type="text" name="meta_keys" size="50" value="<?php echo $rowProfiler->meta_keys?>">
         </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Meta Description*: </td>
         <td><input type="text" name="meta_des" size="50" value="<?php echo $rowProfiler->meta_des?>">
         </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Created Date*:</td>        
         <td><?php echo cmsCompat::calendar($rowProfiler->created_date,'created_date','created_date','%Y-%m-%d','textarea');?></td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Publish Start Date*: </td>
         <td><?php echo cmsCompat::calendar($rowProfiler->publish_up_date,'publish_up_date','publish_up_date','%Y-%m-%d','textarea');?></td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Publish End Date*: </td>
         <td><?php echo cmsCompat::calendar($rowProfiler->publish_down_date,'publish_down_date','publish_down_date','%Y-%m-%d','textarea');?></td>
      </tr>
      <tr>
         <td valign="top" nowrap="nowrap"> Access Level: </td>
         <td><select size="3" class="inputbox" name="access">
               <option <?php if($rowProfiler->access_level==0) echo "selected" ;?> value="0">Public</option>
               <option <?php if($rowProfiler->access_level==1) echo "selected" ;?> value="1">Registered</option>
               <option <?php if($rowProfiler->access_level==2) echo "selected" ;?> value="2">Special</option>
            </select>
         </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Back Button: </td>
         <td><select class="inputbox" name="params[back_button]">
               <option selected="selected" value="">Use Global</option>
               <option value="0">Hide</option>
               <option value="1">Show</option>
            </select>
         </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Author Names: </td>
         <td><select class="inputbox" name="params[author]">
               <option selected="selected" value="">Use Global</option>
               <option value="0">Hide</option>
               <option value="1">Show</option>
            </select>
         </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Created Date and Time: </td>
         <td><select class="inputbox" name="params[createdate]">
               <option selected="selected" value="">Use Global</option>
               <option value="0">Hide</option>
               <option value="1">Show</option>
            </select>
         </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Modified Date and Time: </td>
         <td><select class="inputbox" name="params[modifydate]">
               <option selected="selected" value="">Use Global</option>
               <option value="0">Hide</option>
               <option value="1">Show</option>
            </select>
         </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Default Image: </td>
         <td><input type="file" name="default_image" size="50" />
         </td>
      </tr>
      <tr>
         <th colspan="2">Set global default values for up to 15 custom fields</th>
      </tr>
      <tr>
         <td colspan="2" class="container"><table>
               <tr>
                  <td nowrap="nowrap"><strong>For this field...</strong> </td>
                  <td nowrap="nowrap">&nbsp;</td>
                  <td width="100%" nowrap="nowrap"><strong>Set value to...</strong> </td>
               </tr>
               <?php
                        $n=count($rowGlobalFields);
                        for($i=0;$i<15;$i++)
                        {         
                           if(count($rowGlobalFields)-1<$i){break;}
                           $rowGlobal=$rowGlobalFields[$i];
                           
                           $customField="";
                           $customValue="";
                           
                           if($rowCustomGlobalSettings)
                           {
                              if($i<count($rowCustomGlobalSettings))
                              {
                                 $rowCustomGlobalSetting=$rowCustomGlobalSettings[$i];
                                 $customField=$rowCustomGlobalSetting->field_name;
                                 $customValue=$rowCustomGlobalSetting->field_value;
                              }
                           }
                           
                        ?>
               <tr>
                  <td><select name="global_columns[]" id="field_<?php echo $i?>" style="width:250px;">
                        <optgroup label="jos_jreviews_fields">
                        <?php
                                       for($j=0;$j<$n;$j++)
                                       {
                                          $rowCurrent=$rowGlobalFields[$j];
                                          if($rowCurrent->name==$customField)
                                          {
                                             ?>
                        <option value="jos_jreviews_fields.<?php echo $rowCurrent->name?>" selected>
                        <?php echo $rowCurrent->name?>
                        </option>
                        <?php	
                                          }
                                          else 
                                          {
                                             ?>
                        <option value="jos_jreviews_fields.<?php echo $rowCurrent->name?>">
                        <?php echo $rowCurrent->name?>
                        </option>
                        <?php	
                                          }
                                          
                                          
                                       }
                                    ?>
                        </optgroup>
                     </select>
                  </td>
                  <td><img src="components/com_csv_import/arrow.gif" alt="Set to..." /></td>
                  <td><input type="text" name="global_value[]" size="20" value="<?php echo $customValue?>" id="global_<?php echo $i?>" style="width:250px;" />
                  </td>
               </tr>
               <?php							
                        }
                     ?>
            </table></td>
      </tr>
   </table>
   <!-- Set hidden variable-->
   <?php
				for($i=0;$i<count($columns);$i++)
				{
				?>
   <input type="hidden" name="columns[]" value="<?php echo $columns[$i]?>">
   <input type="hidden" name="fields[]" value="<?php echo $fields[$i]?>">
   <?php	
				}
			?>
   <!-- Other hidden variable-->
   <input type="hidden" name="profiler_id" value="<?php echo $profilerId?>" />
   <input type="hidden" name="separator" value="<?php echo $separator?>" />
   <input type="hidden" name="csv_utf8" value="<?php echo $csv_utf8;?>" />   
   <input type="hidden" name="filename" value="<?php echo $fileName?>" />
   <input type="hidden" name="option" value="<?php echo $option?>" />
   <input type="hidden" name="task" value="" />
</form>
<script language="javascript">
var totalCustomField=<?php echo $n?>;
</script>
<?php	
	}
	
	
	
	function showStep4($option,$columns,$fields,$globalColumns,$globalValues,$profilerId,$separator,$csv_utf8,$fileName,$authorId,$sectionId,$categoryId,$state,$metaKeys,$metaDes,$createdDate,$publishUpDate,$publishDownDate,$access,$params,$images)
	{
//		mosCommonHTML::loadOverlib();
//		mosCommonHTML::loadCalendar();
	?>
<form name="adminForm" method="POST" id="adminForm" action="index2.php">
   <table class="csvimport_subheading">
      <tr>
         <td><h1>Step 4: Confirm Import Settings</h1>
            <p>Review the associations and global settings below. If you are satisfied with the configuration, click Next to import the content of the CSV file.</p></td>
      </tr>
   </table>
   <table class="adminform" width="100%">
      <tr>
         <th colspan="2">Column-to-Field Associations</th>
      </tr> 
      <tr>
         <td colspan="2" class="container">
         	<table>
               <tr>
                  <td nowrap="nowrap" style="padding-left: 5px;"><strong>CSV Field/Column</strong></td>
                  <td>&nbsp;</td>
                  <td width="100%" nowrap="nowrap"><strong> Joomla/jReviews Field </strong></td>
               </tr>
					<?php
                        for($i=0;$i<count($columns);$i++)
                        {
                           $column=$columns[$i];
                           $field=$fields[$i];
                        ?>
               <tr>
                  <td><?php echo $column?></td>
                  <td><img src="components/com_csv_import/arrow.gif" alt="Associate with..." /></td>
                  <td><?php echo $field?></td>
               </tr>
               <?php		
					}
				?>
            </table>          </td>
      </tr>
      <tr>
         <th colspan="2">Basic Joomla Settings for All New Content Items</th>
      </tr>
      <tr>
         <td nowrap="nowrap"> Author User ID:</td>
         <td width="100%"><?php echo $authorId?>         </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Section ID:</td>
         <td><?php echo $sectionId?>         </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Category ID:</td>
         <td><?php echo $categoryId?>         </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Published:</td>
         <td><?php
							if($state)
								echo "Yes";
							else 
								echo "No";	
						?>         </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Meta Keywords: </td>
         <td><?php echo $metaKeys?>         </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Meta Description: </td>
         <td><?php echo $metaDes?>         </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Created Date: </td>
         <td><?php echo $createdDate?>         </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Publish Start Date: </td>
         <td><?php echo $publishUpDate?>         </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Publish End Date: </td>
         <td><?php echo $publishDownDate?>         </td>
      </tr>
      <tr>
         <td nowrap="nowrap"> Access Level: </td>
         <td><?php
							switch ($access)
							{
								case "0":
									echo "Public";
									break;
								case "1":
									echo "Registered";
									break;
								case "2":
									echo "Special";
									break;		
							}
						?>         </td>
      </tr>
      <tr>
         <th colspan="2">Other Global Settings for All New Content Items</th>
      </tr>
      <tr>
         <td colspan="2" class="container">
         	<table>
					<?php
                        $totalCustomSettings=count($globalColumns);
                        for($i=0;$i<$totalCustomSettings;$i++)
                        {
                           $column=$globalColumns[$i];
                           $value=$globalValues[$i];
                           if($value)
                           {
                           ?>
               <tr>
                  <td><?php echo $column?></td>
                  <td><img src="components/com_csv_import/arrow.gif" alt="Set to..." /></td>
                  <td><?php echo $value?></td>
               </tr>
               <?php	
                           }
                        }
                     ?>
            </table>         </td>
      </tr>
      <tr>
         <th colspan="2">Save Import Profile (Optional)<br />
         <span style="font-weight:normal;">If you would like to save this profile for future use, specify a name below.</span></th>
      </tr>
      <tr>
         <td nowrap="nowrap">Save Import Profile as:</td>
         <td><input type="text" name="profile_name" size="35" />         </td>
      </tr>
   </table>
   <!-- Set hidden variable-->
   <?php
				for($i=0;$i<count($columns);$i++)
				{
				?>
   <input type="hidden" name="columns[]" value="<?php echo $columns[$i]?>">
   <input type="hidden" name="fields[]" value="<?php echo $fields[$i]?>">
   <?php	
				}
			?>
   <?php
					$totalCustomSettings=count($globalColumns);
					for($i=0;$i<$totalCustomSettings;$i++)
					{
						$column=$globalColumns[$i];
						$value=$globalValues[$i];
						if($value)
						{
						?>
   <input type="hidden" name="global_columns[]" value="<?php echo $column?>" />
   <input type="hidden" name="global_value[]" value="<?php echo $value?>" />
   <?php	
						}
					}
			?>
   <!-- Other hidden variable-->
   <input type="hidden" name="profiler_id" value="<?php echo $profilerId?>" />
   <input type="hidden" name="separator" value="<?php echo $separator?>" />
   <input type="hidden" name="csv_utf8" value="<?php echo $csv_utf8;?>" />   
   <input type="hidden" name="filename" value="<?php echo $fileName?>" />
   <input type="hidden" name="option" value="<?php echo $option?>" />
   <input type="hidden" name="task" value="" />

   <input type="hidden" name="author_id" value="<?php echo $authorId?>" />
   <input type="hidden" name="section_id" value="<?php echo $sectionId?>" />
   <input type="hidden" name="category_id" value="<?php echo $categoryId?>" />
   <input type="hidden" name="state" value="<?php echo $state?>" />
   <input type="hidden" name="meta_keys" value="<?php echo $metaKeys?>" />
   <input type="hidden" name="meta_des" value="<?php echo $metaDes?>" />
   <input type="hidden" name="created_date" value="<?php echo $createdDate?>" />
   <input type="hidden" name="publish_up_date" value="<?php echo $publishUpDate?>" />
   <input type="hidden" name="publish_down_date" value="<?php echo $publishDownDate?>" />
   <input type="hidden" name="access" value="<?php echo $access?>" />
   <input type="hidden" name="images" value="<?php echo $images?>" />
   <!-- Params hidden fields-->
   <?php
				if(is_array($params))
				{
					foreach ($params as $key=>$value)
					{
					?>
   <input type="hidden" name="params[<?php echo $key?>]" value="<?php echo $value?>" />
   <?php	
					}
				}				
			?>
</form>
<?php	
	}
	

	function showImportResult($option, $totalImported,$arrErrorLine,$arrErrorMessage,$sectionId,$categoryId)
	{
//		mosCommonHTML::loadOverlib();
//		mosCommonHTML::loadCalendar();
	?>
<form name="adminForm" method="POST" id="adminForm" action="index2.php">
   <table class="csvimport_subheading">
      <tr>
         <td><h1>Results of Import</h1></td>
      </tr>
   </table>
   <table class="adminform" width="100%">
      <tr>
         <td colspan="2"><?php echo $totalImported?>
            new content items were created successfully. </td>
      </tr>
      <?php
					if(count($arrErrorLine))
					{
						$totalErrors=count($arrErrorLine);
					?>
      <tr>
         <td colspan="2"><?php echo $totalErrors?>
         &nbsp; not imported (check errors below) </td>
      </tr>
      <?php
						for($i=0;$i<$totalErrors;$i++)
						{
						?>
      <tr>
         <td>Line &nbsp;
         <?php echo $arrErrorLine[$i]?></td>
         <td>Line &nbsp;
         <?php echo $arrErrorMessage[$i]?></td>
      </tr>
      <?php	
						}
					}
				?>
   </table>
   <input type="hidden" name="option" value="<?php echo $option?>" />
   <input type="hidden" name="task" value="" />
</form>
<?php		
	}
		
	function showProfilers( $option, $rows,$search, $pageNav )
	{
		?>
		<form action="index2.php" method="post" name="adminForm">
		   <table class="csvimport_instructions" >
		      <tr>
		         <td><p style="padding-right: 30%;">This component is used to import data from CSV files into  Joomla and, if installed, jReviews. Easily create hundreds or thousands of new content items from the data stored in your existing spreadsheets without any reformatting.  Simply point to the a CSV file, associate the column titles with Joomla and/or jReviews fields (or exclude columns), then import to create new content items.  You can even save the import profile for future use.</p>
		            <p style="padding-right: 30%;">Click the New button above to import a new CSV file.</p></td>
		      </tr>
		   </table>
		   <table class="csvimport_subheading">
		      <tr>
		         <td><h1>Saved Import Profiles</h1></td>
		      </tr>
		   </table>
		   <table class="adminlist">
		      <tr>
		         <th width="5"> # </th>
		         <th width="20"> <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
		         </th>
		         <th class="title"> Title </th>
		         <th align="center"> Created </th>
		         <th align="center"> # of Runs </th>
		         <th align="center"> Last Run </th>
		         <th align="center"> Run Now </th>
		      </tr>
		      <?php
				$k = 0;
				for ($i=0, $n=count( $rows ); $i < $n; $i++) {
					$row = &$rows[$i];
					$link 	= 'index2.php?option=com_csv_import&task=show_step1&hidemainmenu=1&profiler_id='. $row->id;			
								
					$checked = checkedOut( $row, $i );
					?>
		      <tr class="<?php echo "row$k"; ?>">
		         <td><?php echo $pageNav->rowNumber( $i ); ?> </td>
		         <td><?php echo $checked; ?> </td>
		         <td><a href="<?php echo $link; ?>" title="Run profile"> <?php echo $row->name; ?> </a> </td>
		         <td align="center"><?php echo $row->created ;?> </td>
		         <td align="center"><?php echo $row->number_run ;?> </td>
		         <td align="center"><?php echo $row->last_run ;?> </td>
		         <td align="center"><a href="<?php echo $link; ?>" title="Run profile"><strong> Run Now</strong></a> </td>
		      </tr>
		      <?php
					$k = 1 - $k;
				}
				?>
		   </table>
		   <?php echo $pageNav->getListFooter(); ?>
		   <input type="hidden" name="option" value="<?php echo $option;?>" />
		   <input type="hidden" name="task" value="" />
		   <input type="hidden" name="boxchecked" value="0" />
		   <input type="hidden" name="hidemainmenu" value="0">
		</form>
		<?php
	}
	

	/**
	* Writes the edit form for new and existing record
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* @param mosWeblink The weblink object
	* @param array An array of select lists
	* @param object Parameters
	* @param string The option
	*/
	function editProfiler( $row,$option,$rowContentColumns,$rowJReviewColumns,$rowColumnsData ) {
		
		$totalColumns=count($rowContentColumns)+count($rowJReviewColumns);
				
//		mosCommonHTML::loadOverlib();
		?>
<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if (form.name.value == ""){
				alert( "Profile must have a title" );			
			} else {
				submitform( pressbutton );
			}
		}
		</script>
<form action="index2.php" method="post" name="adminForm" id="adminForm">
   <table class="adminheading">
      <tr>
         <th> Import Profile: <small> <?php echo $row->id ? 'Edit' : 'New';?> </small> </th>
      </tr>
   </table>
   <table width="100%" class="adminform">
      <tr>
         <th colspan="2"> Details </th>
      </tr>
      <tr>
         <td width="20%" align="right"> Name: </td>
         <td width="80%"><input class="text_area" type="text" name="name" size="50" maxlength="250" value="<?php echo $row->name;?>" />
         </td>
      </tr>
      <tr>
         <td width="20%" align="right" valign="top"> Description: </td>
         <td width="80%"><textarea name="description" rows="8" cols="50"><?php echo $row->description; ?></textarea>
         </td>
      </tr>
      <tr>
         <th colspan="2"> Columns map </th>
      </tr>
      <tr>
         <td valign="top" width="50%"><table width="100%" class="adminform">
               <tr>
                  <th colspan="2">jos_content column maps</th>
               </tr>
               <?php
								for($i=0;$i<count($rowContentColumns);$i++)
								{		
									$columnName="";																							
									if($rowColumnsData)
									{
										$rowData=$rowColumnsData[$i];
										$columnNo=$rowData->column_no;
										$columnName=$rowData->column_name;
										$tableName=$rowData->table_name;								
									}
									if(!$columnName)
									{
										$columnName=$rowContentColumns[$i]->Field;
										$tableName="jos_content";
										$columnNo="";
									}
								?>
               <tr>
                  <td><input type="text" name="columns[]" size="10" value="<?php echo $columnNo?>">
                  </td>
                  <td><select name="fields[]">
                        <optgroup label="jos_content table">
                        <?php
													for($j=0;$j<count($rowContentColumns);$j++)
													{
														$rowContentColumn=$rowContentColumns[$j];												
														if(($tableName=="jos_content")&&($columnName==$rowContentColumn->Field))
														{
															echo "<option value='jos_content.$rowContentColumn->Field' selected>$rowContentColumn->Field</option>";
														}
														else 
														{
															echo "<option value='jos_content.$rowContentColumn->Field'>$rowContentColumn->Field</option>";
														}
													}													
												?>
                        </optgroup>
                     </select>
                  </td>
               </tr>
               <?php																											
								}
							?>
            </table></td>
         <!-- Jreview column map --->
         <td valign="top" width="50%"><table width="100%" class="adminform">
               <tr>
                  <th colspan="2">jos_jreview_fields column maps</th>
               </tr>
               <?php
								$contentColumns=count($rowContentColumns);
								$jReviewColumn=count($rowJReviewColumns);
								$count=$contentColumns+$jReviewColumn;
								for($i=$contentColumns;$i<$count;$i++)
								{																									
									$columnName="";
									if($rowColumnsData)
									{
										$rowData=$rowColumnsData[$i];
										$columnNo=$rowData->column_no;
										$columnName=$rowData->column_name;
										$tableName=$rowData->table_name;								
									}
									if(!$columnName)
									{
										$columnName=$rowJReviewColumns[$i-$contentColumns]->name;
										$tableName="jos_jreview_fields";
										$columnNo="";
									}
								?>
               <tr>
                  <td><input type="text" name="columns[]" size="10" value="<?php echo $columnNo?>">
                  </td>
                  <td><select name="fields[]">
                        <optgroup label="jos_jreview_fields table">
                        <?php													
													for($j=0;$j<count($rowJReviewColumns);$j++)
													{
														$rowContentColumn=$rowJReviewColumns[$j];												
														if(($tableName=="jos_jreview_fields")&&($columnName==$rowContentColumn->name))
														{
															echo "<option value='jos_jreview_fields.$rowContentColumn->name' selected>$rowContentColumn->name</option>";
														}
														else 
														{
															echo "<option value='jos_jreview_fields.$rowContentColumn->name'>$rowContentColumn->name</option>";
														}
													}													
												?>
                        </optgroup>
                     </select>
                  </td>
               </tr>
               <?php																											
								}
							?>
            </table></td>
      </tr>
   </table>
   <input type="hidden" name="id" value="<?php echo $row->id; ?>" />
   <input type="hidden" name="option" value="<?php echo $option;?>" />
   <input type="hidden" name="task" value="" />
</form>
<?php
	}
}
?>