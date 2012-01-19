<?php
require_once JPATH_SITE.DS.'templates'.DS.'jreviews_overrides'.DS.'views'.DS.'themes'.DS.'geomaps'.DS.'listings'.DS.'rel-summary.php';
require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';


				//time
				$datetime="";
				
				$jr_startdate = $CustomFields->field('jr_startdate', $listing, false, false);
				$jr_time = $CustomFields->field('jr_time', $listing, false, false);
				if ($jr_startdate) {
					$datetime = $jr_startdate;
					if ($jr_time) {
						if ($jr_startdate) {
							$datetime .= ' - ';
						}
					$datetime .= $jr_time;
					$datetime = htmlentities($datetime, ENT_QUOTES, "UTF-8");
					}
				}
				
				
				$relatedThumb = getRelatedThumb($listing['Listing']['listing_id'],3);
				$preview_html = $listing['Listing']['title'].'::';
				foreach ($relatedThumb as $thumb) {
					if ($thumb->storage == "file") {
						$tnlink = "";
					}
					else {
						$tnlink = "http://hooked.no.s3.amazonaws.com/"; //todo prefix with amazon url
					}
					$tnlink .= $thumb->thumbnail;
					$preview_html .= "&lt;img src=&quot;".$tnlink."&quot; class=&quot;fl&nbsp;jomtips&quot; /&gt;";
				}
				$preview_html .= ' '.$datetime;
				$preview_html .= '&lt;br /&gt;Click to see catch details &raquo;';
				$link = ContentHelperRoute::getArticleRoute($listing['Listing']['listing_id'],$listing['Category']['cat_id'],$listing['Section']['section_id']);
			?>
                <tr class="<?php $featured = $listing['Listing']['featured']; if ($featured) echo ' featured';?>" title="">
                    <?php if ($all_cols) : ?>
                    <td>
					<a href="<?php echo $link; ?>"><?php echo $datetime; ?></a>
                    </td>
                    <?php endif; ?>
                    <td>
                    <?php
					$jr_catchanonymous = $CustomFields->fieldValue('jr_catchanonymous', $listing, false, false);
					$jr_catchanonymous = $jr_catchanonymous[0];
					if ($jr_catchanonymous=="ja")
						$anon_location = 1;
					else
						$anon_location = "";
$user =& JFactory::getUser();
if ($user->id) {
		$loggedin_user = $user->id;	
		$loggedin = 1;
}
else { 
	$loggedin_user = "";
	$loggedin = "";
}


if ($listing["User"]["user_id"]==$loggedin_user)
	$isOwner = 1;
else
	$isOwner = 0;
					if (!$anon_location || $anon_location && $isOwner) {
						
						$catchspot = getRelatedList($listing['Listing']['listing_id'],array(3,4),1);
						$spots="";
						if ($catchspot) {
							foreach ($catchspot as $spot) {
								$spot_link = ContentHelperRoute::getArticleRoute($spot->id,$spot->catid,1);
								echo '<a href="'.$link.'" class="jomTips" title="'.$preview_html.'">'.$spot->title;
									$parent_spot = getParentLocation($spot->id);
									if ($parent_spot && $all_cols) echo ' @ '.$parent_spot->title;
								echo '</a>';
							}
						}
						else {
							$catchspot = getRelatedList($listing['Listing']['listing_id'],array(1,2,100),1);
							$spots="";
							if ($catchspot) {
								foreach ($catchspot as $spot) {
									$spot_link = ContentHelperRoute::getArticleRoute($spot->id,$spot->catid,1);
									echo '<a href="'.$link.'" class="jomTips" title="'.$preview_html.'">'.$spot->title;
									echo '</a>';
								}
							}
						}
					}
					?>
                    </td>
                    <td>
                        <?php
						$fishcaught = getRelatedList($listing['Listing']['listing_id'], 17, 5);
						$fc = 0;
					
						if ($fishcaught) {
							echo '<a href="'.$link.'" class="jomTips" title="'.$preview_html.'">';
							foreach ($fishcaught as $fish) {
								if ($fc < 1) {
									echo $fish->title;
								}
								$fc++;
							}
							echo '</a>';
						}
                        ?>
                    </td>
                    <td><a href="<?php echo $link; ?>" class="jomTips" title="<?php echo $preview_html; ?>"><?php echo $CustomFields->field('jr_catchweight', $listing, false, false); ?></a></td>
                    <?php if ($all_cols) : ?>
                    <td><a href="<?php echo $link; ?>" class="jomTips" title="<?php echo $preview_html; ?>"><?php echo $CustomFields->field('jr_catchlength', $listing, false, false); ?></a></td>
                    <?php endif; ?>
                    <td>
                    <?php
						// bait
						$baitused = getRelatedList($listing['Listing']['listing_id'], array(101,102), 5);
						$bc = 0;
						if ($baitused) {
							echo '<a href="'.$link.'">';
							foreach ($baitused as $bait) {
								if ($bc < 1) {
									echo $bait->title;
								}
								else echo '...';
								$bc++;
							}
							echo '</a>';
						}
						else echo '-';
                        ?>
                    </td>
                    <td><?php echo '<a href="'.$link.'" >'; echo getRelatedCount($listing['Listing']['listing_id'], "photos"); echo '</a>'; ?></td>
                    <td><?php echo '<a href="'.$link.'">'; echo getRelatedCount($listing['Listing']['listing_id'], "videos"); echo '</a>'; ?></td>
                    <td>
                    <span style="cursor:help;" title="<?php __t("User reviews");?>"><?php echo '<a href="'.$link.'" >'; echo (int) $listing['Review']['review_count']; echo '</a>'; ?></span>
                    </td>
                    <td class="catch-avatar"><?php if ($isOwner) : ?><a href="/index.php?option=com_jreviews&Itemid=&url=listings/edit/id:<?php echo $listing['Listing']['listing_id']; ?>/"><img src="/templates/jreviews_overrides/views/themes/geomaps/theme_css/images/jr_edit.gif" alt="Endre" class="fr" title="Endre" style="width: 16px;" /></a><?php endif; ?>
					<?php  $avtt = htmlentities($Community->screenName($listing), ENT_QUOTES, "UTF-8");?><div class="jomTips" title="<?php __t("Angler"); ?>::<?php echo $avtt; ?>"><?php echo $Community->avatar($listing); // Listing owner avatar?></div></td>

                </tr>            