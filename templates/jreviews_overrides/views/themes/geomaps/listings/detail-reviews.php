<div id="jr_editor_reviews"></div><!-- required to display post save messages for editor reviews -->

<? /****************************************************
   *    BEGIN EDITOR REVIEWS
   *****************************************************/?>

    <?php if($editorReviewShow):?>
    <!-- EDITOR REVIEW HEADER -->
        <?php if(Sanitize::getString($this->params,'reviewtype')=='editor' || $this->name == 'com_content'):?>
            <h3 id="jr_reviewEditorSummaryMainTitle"><?php echo $this->Config->author_review == 2 ? __t("Editor reviews",true) : __t("Editor review",true);?></h3>
        <?php endif;?>

        <?php if($this->action=='com_content_view' && $listing['Review']['editor_review_count'] > $this->Config->editor_limit):?>
        <!-- view all reviews -->
            <span style="display:block;float:right;text-align:right;margin-top:-25px;"><?php echo $Routes->listing(__t("View all editor reviews",true),$listing,'editor',array('class'=>'jr_buttonLink'));?></span>
        <?php elseif(Sanitize::getString($this->params,'reviewtype')=='editor' && $this->name == 'listings'):?>
        <!-- go back to listing -->
            <span style="display:block;float:right;text-align:right;margin-top:-25px;"><?php echo $Html->sefLink(__t("Back to Listing",true),$listing['Listing']['url'],array('class'=>'jr_buttonLink'));?></span>
        <?php endif;?>
    <?php endif;?>
                
	<?php if( $this->name != 'listings' && $editorReviewShow):?>
                  
			<?php if($listing['Review']['editor_rating_count'] > 1 || ($editorReviewSubmitMultiple || $editorReviewSubmitSingle)):?>                 
                 <!-- BEGIN EDITOR REVIEW SUMMARY -->
	            <div class="roundedPanel jr_review" id="jr_reviewEditorSummary">
		            <div class="box_upperOuter">
			            <div class="box_upperInner">
				            
                            <?php if($listing['Review']['editor_rating_count'] > 1): ?>
				            <h4 class="jr_reviewTitle"><?php echo sprintf(__t("Average editor rating from: %s user(s)",true), $listing['Review']['editor_rating_count']);?></h4>
				            <?php endif;?>														
				            
                            <div class="jr_reviewContainer">
					            <?php if($editorReviewSubmitSingle || $editorReviewSubmitMultiple):?>					            
					                <!--Add review button with duplicate check and button effects-->
					                <button type="button" id="review_button" class="jr_addReview" <?php echo $User->duplicate_review ? 'disabled="disabled" ' : ''; ?> 
                                        onclick="jreviews.review.showForm(this);">
                                        <?php echo !$User->duplicate_review ? __t("Add new review",true).' ('. __t("Editor review",true).')' : __t("You already submitted a review.",true);?>
                                    </button>                                        					            
                                <?php endif;?>
								
								<?php 
								if( 
										$listing['Criteria']['state'] == 1
									&&	!empty($listing['Review']['editor_rating'])
									&&	$this->Config->author_review + $listing['Review']['editor_rating_count'] > 2 # copied from earlier, display total rating either when in single-editor-review mode with more than one rating, or in multi-editor-review mode with at least one rating
								): ?>
					            <!-- BEGIN DETAILED EDITOR RATINGS SUMMARY -->
                                <div class="jr_reviewContainerSidebar">
						            <?php echo $this->element('detailed_ratings',array('review'=>$editor_ratings_summary,'reviewType'=>'editor'));?>
					            </div>
                                <!-- END DETAILED EDITOR RATINGS SUMMARY -->
                                <?php endif;?>
				            </div>
                            <div class="clear"></div>                            
			            </div>
		            </div>
		            <div class="box_lowerOuter">
			            <div class="box_lowerInner">&nbsp;</div>
		            </div>
	            </div><br />
            <!-- END EDITOR REVIEW SUMMARY -->
            <?php endif;?>

            <?php if($editorReviewForm):?>
            <!-- BEGIN EDITOR REVIEW FORM -->        
	        <?php echo $this->renderControllerView('reviews','create',array('criteria'=>$listing['Criteria']))?>
            <!-- END EDITOR REVIEW FORM -->
            <?php endif;?>
            
            <?php if($listing['Review']['editor_review_count'] > 0 && !is_numeric(key($editor_review))):?>
               
            <? /****************************************************
               *    SINGLE EDITOR REVIEW
               *****************************************************/?>

                <!-- BEGIN SINGLE EDITOR REVIEW -->                     
                 <div class="roundedPanel jr_review" id="jr_reviewEditor">
                    <div class="box_upperOuter">
                        <div class="box_upperInner">
                            <!-- BEGIN REVIEW INNER -->    
                            <div id="jr_review_<?php echo $editor_review['Review']['review_id']?>">

                                <?php if($editor_review['Review']['title']!=''):?>                                                
                                <h4 class="jr_reviewTitle"><?php echo $editor_review['Review']['title'];?>
                                <?php endif;?>
                               
                                <?php if($Access->canEditReview($editor_review['User']['user_id'])): // Edit icon?>
                                    <?php echo $Routes->reviewEdit($Html->image($this->viewImages.'edit.png',array('width'=>29,'height'=>11,'alt'=>'Edit')),$editor_review,array('class'=>'thickbox'));?>
                                <?php endif;?></h4>
                               
                                <div class="jr_reviewContainer">
                                    <div class="jr_reviewContainerSidebar">                        
                                            <!-- DETAILED RATINGS EDITOR REVIEW -->
                                          <?php echo $listing['Criteria']['state'] == 1  && !empty($listing['Review']['editor_rating']) ? $this->element('detailed_ratings',array('review'=>$editor_review,'reviewType'=>'editor')) : '&nbsp;';?>
                                                            
                                        <?php if($this->Config->author_vote && $this->Access->canVoteHelpful()):?>
                                            <!-- VOTING WIDGET -->
                                            <?php echo $this->element('voting_widget',array('review_id'=>$editor_review['Review']['review_id']));?>
                                        <?php endif;?> 
                                
                                        <div class="reviewInfo">
                                            <?php if(isset($editor_review['Community']['avatar_path'])):?>
                                                <?php echo $Community->avatar($editor_review);?>
                                            <?php endif;?>
                                            
                                            <?php __t("Reviewed by");?> <?php echo $Community->screenName($editor_review);?><br />
                                            <?php echo $Time->nice($editor_review['Review']['created']);?><br />

                                            <?php if($this->Config->user_rank_link && $editor_review['User']['user_id']>0):?><?php echo $Routes->reviewers($editor_review['User']['review_rank'],$editor_review['User']['user_id'])?><br /><?php endif;?>
                                
                                            <div class="clr"></div>
                                            
                                            <?php if($this->Config->user_myreviews_link && $this->action!='myreviews'):?>
                                            <!-- BEGIN VIEW MY REVIEWS -->                            
                                            <?php echo $Routes->myReviews(__t("View all my reviews",true), $editor_review['User'],array('class'=>'jr_myReviews'));?><br />
                                            <!-- END VIEW MY REVIEWS -->                            
                                            <?php endif;?>                            
                                                            
                                            <?php if($this->Config->author_report):?>
                                            <!-- BEGIN REVIEW REPORT -->
                                                <?php echo $Routes->reportThis(__t("Report this review",true),array('listing_id'=>$editor_review['Review']['listing_id'],'review_id'=>$editor_review['Review']['review_id']));?>
                                            <!-- END REVIEW REPORT -->
                                            <?php endif;?>    

                                            <div class="clr">&nbsp;</div>
                                            
                                            <?php if($editor_review['Review']['modified'] != '' && NULL_DATE != $editor_review['Review']['modified']):?>
                                                <?php __t("Last updated");?>: <?php echo $Time->nice($editor_review['Review']['modified']);?><br />
                                            <?php endif;?>                                

                                            <?php if($Access->gid == 25):?>
                                            <!-- EXTRA INFO FOR ADMINS -->
                                                <?php echo $editor_review['User']['ipaddress'];?><br />
                                                <?php echo $editor_review['User']['email'];?><br />
                                            <?php endif;?>                            
                                        </div>
                                    </div>
                                                        
                                    <?php if($this->Config->author_vote):?>
                                    <!-- USEFULNESS SUMMARY -->
                                        <div class="jr_helpfulSummary"><?php echo sprintf(__t("%s of %s people found the following review helpful",true),(int)$editor_review['Vote']['yes'],$editor_review['Vote']['yes']+$editor_review['Vote']['no']);?></div>
                                    <?php endif;?>
                                    
                                    <?php echo nl2br($editor_review['Review']['comments']);?>
                                    
                                    <?php if(isset($editor_review['Field']['groups'])):?>
                                        <?php echo $this->element('custom_fields',array('entry'=>$editor_review,'page'=>'content'), false)?>
                                    <?php endif;?>
                                    <div class="clear"></div>
                                </div>
                            </div>                                
                            <!-- END REVIEW INNER -->                                    
                        </div>
                    </div>
                    <div class="box_lowerOuter">
                        <div class="box_lowerInner">&nbsp;</div>
                    </div>
                 </div>
                 <!-- END SINGLE EDITOR REVIEW -->                                 
                        
            <?php elseif($listing['Review']['editor_review_count']>0):?>    
		    
            <? /****************************************************
               *    MULTIPLE EDITOR REVIEWS
               *****************************************************/?>
               
               <?php echo $this->renderControllerView('reviews','reviews',array('reviews'=>$editor_review,'reviewType'=>'editor'))?>
            
            <?php endif;?>
			    
		    <div class="clr">&nbsp;</div>
	    
		    <?php // View all editor reviews for a listing shown on details page
		    if(in_array($this->action,array('com_content_view')) && $listing['Review']['editor_review_count'] > $this->Config->editor_limit):?>
			    <?php echo $Routes->listing(__t("View all editor reviews",true),$listing,'editor',array('class'=>'jr_buttonLink'));?>
			    <div class="clr">&nbsp;</div>
		    <?php endif;?>
			    
		    <br /><br />
			
    <!-- END EDITOR REVIEWS -->      
	<?php endif;?>
		
<? /****************************************************
   *    BEGIN USER REVIEWS
   *****************************************************/?>
        
	<?php if($userReviewShow):?>
    <!-- BEGIN USER REVIEWS -->
        <!-- BEGIN USER REVIEW SUMMARY -->
        <?php if(Sanitize::getString($this->params,'reviewtype')=='user' || $this->name == 'com_content'):?>		
		<h3 id="jr_reviewUserSummaryMainTitle"><?php __t("Comments");?></h3>
        <?php endif;?>
        
        <?php // View all reviews
        if($this->action=='com_content_view' && $listing['Review']['review_count'] > $this->Config->user_limit):?>
            <span style="display:block;float:right;text-align:right;margin-top:-25px;"><?php echo $Routes->listing(__t("View all user reviews",true),$listing,'user',array('class'=>'jr_buttonLink'));?></span>
		<?php // Back to listing
		elseif(Sanitize::getString($this->params,'reviewtype')=='user' && $this->name == 'listings'):?>
			<span style="display:block;float:right;text-align:right;margin-top:-25px;">
			<?php echo $Html->sefLink(__t("Back to Listing",true),$listing['Listing']['url'],array('class'=>'jr_buttonLink'));?>
			</span>
        <?php endif;?>
				
		<?php if($this->name != 'listings'): // Dont show unless in content page ?>
			<div class="roundedPanel jr_review" id="jr_reviewUserSummary">
				<div class="box_lowerOuter">
					<div class="box_lowerInner">&nbsp;</div>
				</div>
			</div>
        <!-- END USER REVIEW SUMMARY -->
		<?php endif;?>	
				
		<?php if($userReviewForm):?>
        <!-- USER REVIEW FORM -->        
		<?php echo $this->renderControllerView('reviews','create',array('criteria'=>$listing['Criteria']))?>
		<?php endif;?>

		<div id="jr_user_reviews"><?php // div required to display post save messages - DO NOT REMOVE ?>
			<?php if($listing['Review']['review_count']>0):?>	
			<!-- BEGIN USER REVIEWS -->		
				<?php echo $this->renderControllerView('reviews','reviews',array('reviews'=>$reviews,'reviewType'=>'user'))?>
			<!-- END USER REVIEWS -->		
			<?php endif;?>
		</div>		
			
		<div class="clr">&nbsp;</div>
		<!-- END USER REVIEWS-->
	
		<?php // View all reviews - shown on listing detail page
		if(in_array($this->action,array('com_content_view')) && $listing['Review']['review_count'] > $this->Config->user_limit):?>
			<?php echo $Routes->listing(__t("View all user reviews",true),$listing,'user',array('class'=>'jr_buttonLink'));?>
		<?php endif;?>	
	<?php endif;?>