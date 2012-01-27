<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2009 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class JomsocialComponent extends S2Component {
    
    var $plugin_order = 100;
    
    var $name = 'jomsocial';
    
    var $type = 'user';
    
    var $published = true;
    
    var $points = false;

    var $activities = array(); // Defined below to use the translation function
                                                                                   
    var $inAdmin = false;
    
    var $jomsocial_tnsize = 64;
    
    var $jomsocial_trimwords = 25; 
         
    function startup(&$controller) 
    {
        $this->inAdmin = defined('MVC_FRAMEWORK_ADMIN');

        $this->c = & $controller;
        
        $this->activities = array(
          "listing_new"=>__t("{actor} added new listing %1\$s in %2\$s.",true), // 1: listing title; 2: listing category
          "listing_edit"=>__t("{actor} updated listing %1\$s.",true),
          "review_new"=>__t("{actor} reviewed %1\$s.",true),
          "review_edit"=>__t("{actor} updated review for %1\$s.",true),
          "favorite_add"=>__t("{actor} added %1\$s to favorites.",true),
          "favorite_remove"=>__t("{actor} removed %1\$s from favorites.",true),
          "vote_yes"=>__t("{actor} voted as helpful a review, %1\$s, written by %2\$s.",true), // 1: review title; 2: reviewer
          "vote_no"=>__t("{actor} voted as not helpful a review, %1\$s, written by %2\$s.",true), // 1: review title; 2: reviewer
          "comment_new"=>__t("{actor} commented on a %1\$s for %2\$s.",true),
          "comment_edit"=>__t("{actor} updated comment on a %1\$s for %2\$s.",true)
        );    
                                   
        $path = PATH_ROOT . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php';
        
        if(file_exists($path))
            {
                if(file_exists(PATH_ROOT . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'userpoints.php'))
                    {
                        $this->points = true;                        
                    }
                App::import('Helper',array('routes','html','thumbnail','text'),'jreviews');
                $this->Routes = RegisterClass::getInstance('RoutesHelper');                              
                $this->Routes->app = 'jreviews';
                $this->Html = RegisterClass::getInstance('HtmlHelper');
                $this->Html->app = 'jreviews';
                $this->Thumbnail = RegisterClass::getInstance('ThumbnailHelper');
                isset($controller->Config) and $this->Thumbnail->Config = $controller->Config;    
                $this->Thumbnail->app = 'jreviews';
                $this->Text = RegisterClass::getInstance('TextHelper');
                $this->c = & $controller;  
            } 
        else 
            {
                $this->published = false;
            }
    }     
    
    function plgAfterSave(&$model)
    {    
        appLogMessage('**** BEGIN JomSocial Plugin AfterSave', 'database');
        
        if($this->c->_user->id == 0) 
        {
            $this->activities = str_replace('{actor}',__t("A guest",true),$this->activities);            
        }
                
        include_once( PATH_ROOT . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php');             
        
        if($this->points)
            {
                include_once( PATH_ROOT . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'userpoints.php');                
            }

        switch($model->name)
            {
                case 'Discussion':
                    $this->_plgDiscussionAfterSave($model);
                break;   
                case 'Favorite':
                    $this->_plgFavoriteAfterSave($model);
                break;             
                case 'Listing':     
                    $this->_plgListingAfterSave($model);
                break;  
                case 'Review':
                    $this->_plgReviewAfterSave($model);
                break;  
                case 'Vote':
                    $this->_plgVoteAfterSave($model);
                break;  
            }
    }      
    
    function plgBeforeDelete(&$model)
    {
        include_once( PATH_ROOT . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php'); 
        if($this->points)
            {
                include_once( PATH_ROOT . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'userpoints.php');                
            }

        switch($model->name)
        {
            case 'Discussion': 
                $this->_plgDiscussionBeforeDelete($model);
            break;   
            case 'Listing':
                $this->_plgListingBeforeDelete($model);
            break;  
            case 'Review':
                $this->_plgReviewBeforeDelete($model);
            break;  
        }
    }          
    
    function _plgDiscussionBeforeDelete(&$model)
    {
       $post_id = Sanitize::getInt($model->data,'post_id');
            
       // Get the post before deleting to make the info available in plugin callback functions
       $post = $model->findRow(array('conditions'=>array('Discussion.discussion_id = ' . $post_id)),array());            
        
        // Begin deduct points
        if($this->points && $post['Discussion']['user_id'] > 0  && $post['Discussion']['approved'] == 1)
        {
            CuserPoints::assignPoint('jreviews.discussion.delete',$post['Discussion']['user_id']);            
        }
    }
        
    function _plgDiscussionAfterSave(&$model)
    {
        $content = '';
        $activity_thumb = '';
        $stream = Sanitize::getInt($this->c->Config,'jomsocial_discussions');
        
        if($stream || $this->points)
        {
            $post = $this->_getReviewPost($model);
        }
         
        if($stream)
        {
            // Treat moderated reviews as new
            $this->inAdmin and Sanitize::getBool($model->data,'moderation') and $model->isNew = true; 

            if($stream == 1 && (!isset($model->isNew) || !$model->isNew)) return; // Don't run for edits
            if($stream == 1 && $post['Discussion']['modified'] != NULL_DATE) return; // Don't run for edits
            if($stream == 2 && (!isset($model->isNew) || !$model->isNew) && $this->c->_user->id != $post['User']['user_id']) return; // Don't run for edits by users other than the owner of this post
             
            if(isset($model->isNew) && $post['Discussion']['approved'] == 1)
            {
                $listing = $this->_getListingEverywhere($post['Listing']['listing_id'],$post['Listing']['extension']);
                              
                $review_link = $this->Routes->reviewDiscuss(__t("review",true),$post,array('listing'=>$listing));
                
                $HtmlHelper = RegisterClass::getInstance('HtmlHelper');
                $listing_link = $HtmlHelper->sefLink($listing['Listing']['title'],$listing['Listing']['url']);
                !empty($listing['Listing']['images']) and $activity_thumb = $this->Thumbnail->thumb($listing, 0, $this->c->Config->jomsocial_tnmode, 'activity', array($this->jomsocial_tnsize),array('border'=>0,'alt'=>$listing['Listing']['title'],'title'=>$listing['Listing']['title']));                
                $thumb_link = ($activity_thumb) ? $HtmlHelper->sefLink($activity_thumb,$listing['Listing']['url']) : '';  

                if($model->isNew && $post['Discussion']['modified'] == NULL_DATE)
                    {
                        $title = sprintf(__t($this->activities['comment_new'],true),$review_link, $listing_link);
                    }
                 else 
                    {
                        $title = sprintf(__t($this->activities['comment_edit'],true),$review_link, $listing_link);
                    }
                    
                if($activity_thumb || $listing['Listing']['summary'] != '' )
                {
                    $content = '<ul class="cDetailList clrfix">';
                    $thumb_link and $content .=  '<li class="avatarWrap">'.$thumb_link.'</li>';
                    $thumb_link and $content .= '<li class="detailWrap">';
                    $post['Discussion']['text'] != '' and $content .= '<div class="newsfeed-quote">'.$this->Text->truncateWords($post['Discussion']['text'],$this->jomsocial_trimwords).'</div>';
                    $thumb_link and $content .= '</li>';
                    $content .='</ul>';
                }                

                //begin activity stream
                $act = new stdClass();
                $act->cmd      = 'wall.write';
                $act->actor    = $post['User']['user_id'];
                $act->target   = 0; // no target
                $act->title    = $title;
                $act->content  = $content;
                $act->app      = 'wall';
                $act->cid      = 0;
                CFactory::load('libraries', 'activities');
                CActivityStream::add($act);       
            }
        }

        // Begin add points
        if($this->points && $model->isNew && $post['Discussion']['approved'] == 1)
        {
            CuserPoints::assignPoint('jreviews.discussion.add',$post['User']['user_id']);             
        }
    }
        
    function _plgFavoriteAfterSave(&$model)
    {
        if($stream = Sanitize::getInt($this->c->Config,'jomsocial_favorites'))
        {        
            $content = '';
            $activity_thumb = '';
            
            $listing = $this->_getListing($model);

            $listing_link = $this->Routes->content($listing['Listing']['title'],$listing);
            !empty($listing['Listing']['images']) and $activity_thumb = $this->Thumbnail->thumb($listing, 0, 'scale', 'activity', array(65),array('border'=>0,'alt'=>$listing['Listing']['title'],'title'=>$listing['Listing']['title']));                
            $thumb_link = ($activity_thumb) ? $this->Routes->content($activity_thumb,$listing) : '';  
           
           if($stream == 1 && $this->c->action == '_favoritesDelete') return; // Don't run for removals
            
           if($this->c->action == '_favoritesDelete')
                {
                    $title = sprintf(__t($this->activities['favorite_remove'],true),$listing_link);
                }
             else 
                {
                    $title = sprintf(__t($this->activities['favorite_add'],true),$listing_link);
                }
                
            if($activity_thumb || $listing['Listing']['summary'] != '' )
            {
                $content = '<ul class="cDetailList clrfix">';
                $thumb_link and $content .=  '<li class="avatarWrap">'.$thumb_link.'</li>';
                $thumb_link and $content .= '<li class="detailWrap">';
                $listing['Listing']['summary'] != '' and $content .= $this->Text->truncateWords($listing['Listing']['summary'],25/*$this->c->Config->list_abstract_trim*/);
                $thumb_link and $content .= '</li>';
                $content .='</ul>';
            }            
        
            //begin activity stream
            $act = new stdClass();
            $act->cmd       = 'wall.write';
            $act->title     = $title;
            $act->actor     = $this->c->_user->id;
            $act->target    = 0; // no target
            $act->content   = $content;
            $act->app       = 'wall';
            $act->cid       =  0;
            
            CFactory::load('libraries', 'activities');
            CActivityStream::add($act);
        }
    }
    
    function _plgListingBeforeDelete(&$model)
    {
        if($listing = $this->_getListing($model))
        {
            if($this->points && $listing['Listing']['user_id'] > 0 && $listing['Listing']['state'] == 1)
            {        
                // Begin deduct points
                CuserPoints::assignPoint('jreviews.listing.delete',$listing['Listing']['user_id']);    
            }
        }
    }
        
    function _plgListingAfterSave(&$model)
    {     
        $content = '';
        $activity_thumb = '';
        $stream = Sanitize::getInt($this->c->Config,'jomsocial_listings');
         
        if($stream || $this->points)
        {
            $listing = $this->_getListing($model);
        }

        if($stream)
        {          
            // Treat moderated listings as new
            $this->inAdmin and Sanitize::getBool($model->data,'moderation') and $model->isNew = true; 

            if($stream == 1 && (!isset($model->isNew) || !$model->isNew)) return; // Don't run for edits
            if($stream == 1 && $listing['Listing']['modified'] != NULL_DATE) return; // Don't run for edits
            if($stream == 2 && (!isset($model->isNew) || !$model->isNew) && $this->c->_user->id != $listing['User']['user_id']) return; // Don't run for edits by users other than the owner of this post

            if(isset($model->isNew) && $listing['Listing']['state'] == 1)
            {    
                $listing_link = $this->Routes->content($listing['Listing']['title'],$listing);
                !empty($listing['Listing']['images']) and $activity_thumb = $this->Thumbnail->thumb($listing, 0, 'scale', 'activity', array(65),array('border'=>0,'alt'=>$listing['Listing']['title'],'title'=>$listing['Listing']['title']));                

                $thumb_link = $activity_thumb ? $this->Routes->content($activity_thumb,$listing) : '';  
                
                if($model->isNew && $listing['Listing']['modified'] == NULL_DATE)
                    {
                        $title = sprintf(__t($this->activities['listing_new'],true),$listing_link,$listing['Category']['title']);
                    }
                 else 
                    {
                        $title = sprintf(__t($this->activities['listing_edit'],true),$listing_link);
                    }
                
                if($activity_thumb || $listing['Listing']['summary'] != '' )
                {
                    $content = '<ul class="cDetailList clrfix">';
                    $thumb_link and $content .=  '<li class="avatarWrap">'.$thumb_link.'</li>';
                    $thumb_link and $content .= '<li class="detailWrap">';
                    $listing['Listing']['summary'] != '' and $content .= $this->Text->truncateWords($listing['Listing']['summary'],25/*$this->c->Config->list_abstract_trim*/);
                    $thumb_link and $content .= '</li>';
                    $content .='</ul>';
                }

                //begin activity stream
                $act = new stdClass();
                $act->cmd       = 'wall.write';
                $act->title     = $title;
                $act->actor     = $listing['User']['user_id'];
                $act->target    = 0; // no target
                $act->content   = $content;
                $act->app       = 'wall';
                $act->cid       =  0;
                
                CFactory::load('libraries', 'activities');
                CActivityStream::add($act);
            }
        }
        
        if($this->points && isset($model->isNew) && $model->isNew && $listing['Listing']['state'] == 1)
        {
            // Begin add points
            CuserPoints::assignPoint('jreviews.listing.add',$listing['User']['user_id']);          
        }
    }
    
    function _plgReviewBeforeDelete(&$model)
    {
        $review_id = Sanitize::getInt($model->data,'review_id');
            
        $review = $model->findRow(array('conditions'=>array('Review.id = ' . $review_id)),array());     
               
        // Begin deduct points
        if($this->points && $review['Review']['published'] == 1 && $review['User']['user_id'] > 0)
        {          
            CuserPoints::assignPoint('jreviews.review.delete',$review['User']['user_id']);    
        }
    }
        
    function _plgReviewAfterSave(&$model)    
    {    
        $content = '';
        $activity_thumb = '';
        
        $stream = Sanitize::getInt($this->c->Config,'jomsocial_reviews');
        
        /**
        * Check if there's something to do and run the query only if necessary. Then set it in the
        * controller (viewVars) to make it available in other plugins
        */
        if($stream || $this->points)
        {
            $review = $this->_getReview($model);     
        }

        /**
        * Publish activity to JomSocial stream
        */
        if($stream)
        {              
            // Treat moderated reviews as new
            $this->inAdmin and Sanitize::getBool($model->data,'moderation') and $model->isNew = true; 

            if($stream == 1 && (!isset($model->isNew) || !$model->isNew)) return; // Don't run for edits
            if($stream == 1 && $review['Review']['modified'] != NULL_DATE) return; // Don't run for edits
            if($stream == 2 && (!isset($model->isNew) || !$model->isNew) && $this->c->_user->id != $review['User']['user_id']) return; // Don't run for edits by users other than the owner of this post

            if(isset($model->isNew) && $review['Review']['published'] == 1)        
            {
                $listing_link = $this->Html->sefLink($review['Listing']['title'],$review['Listing']['url']);

                !empty($review['Listing']['images']) and $activity_thumb = $this->Thumbnail->thumb($review, 0, 'scale', 'activity', array(65),array('border'=>0,'alt'=>$review['Listing']['title'],'title'=>$review['Listing']['title']));                
                $thumb_link = ($activity_thumb) ? $this->Html->sefLink($activity_thumb ,$review['Listing']['url']) : '';

                if(isset($model->isNew) && $model->isNew  && $review['Review']['modified'] == NULL_DATE)
                {
                    $title = sprintf(__t($this->activities['review_new'],true),$listing_link);
                }
                 else 
                {
                    $title = sprintf(__t($this->activities['review_edit'],true),$listing_link);
                }

                if($activity_thumb || $review['Review']['comments'] != '' )
                {
                    $content = '<ul class="cDetailList clrfix">';
                    $thumb_link and $content .=  '<li class="avatarWrap">'.$thumb_link.'</li>';
                    $thumb_link and $content .= '<li class="detailWrap">';
                    $review['Review']['comments'] != '' and $content .= '<div class="newsfeed-quote">'.$this->Text->truncateWords($review['Review']['comments'],25/*$this->c->Config->list_abstract_trim*/).'</div>';
                    $thumb_link and $content .= '</li>';
                    $content .= '</ul>';
                }
                    
                //begin activity stream
                $act = new stdClass();
                $act->cmd      = 'wall.write';
                $act->actor    = $review['User']['user_id'];
                $act->target   = 0; // no target
                $act->title    = $title;
                $act->content  = $content;
                $act->app      = 'wall';
                $act->cid      = 0;
                CFactory::load('libraries', 'activities');
                CActivityStream::add($act); 
            }
        }
        
        
        if($this->points)
        {
            if(isset($model->isNew) && $model->isNew && $review['Review']['published'] == 1)
            {        
                // Begin add points
                CuserPoints::assignPoint('jreviews.review.add',$review['User']['user_id']);         
            }
        }
    }
    
    function _plgVoteAfterSave(&$model)
    {
        if($stream = Sanitize::getInt($this->c->Config,'jomsocial_votes'))
        {                
            if($stream == 1 && !$model->data['Vote']['vote_yes']) return; // Yes votes only
            
            $content = '';
            $activity_thumb = '';
            
            !class_exists('ReviewModel') and App::import('Model','review','jreviews');

            $ReviewModel = RegisterClass::getInstance('ReviewModel');
            $review_id = $model->data['Vote']['review_id'];
            $review = $ReviewModel->findRow(array('conditions'=>array('Review.id = ' . $review_id)),array());
            $listing = $this->_getListingEverywhere($review['Review']['listing_id'],$review['Review']['extension']);      
            
            $review_link = $this->Routes->reviewDiscuss($review['Review']['title'],$review,array('listing'=>$listing));
           
            $target = $review['User']['user_id'] == 0 ? __t("Guest",true) : '{target}';
           
            !empty($listing['Listing']['images']) and $activity_thumb = $this->Thumbnail->thumb($listing, 0, 'scale', 'activity', array(65),array('border'=>0,'alt'=>$listing['Listing']['title'],'title'=>$listing['Listing']['title']));                
            $thumb_link = ($activity_thumb) ? $this->Routes->reviewDiscuss($activity_thumb ,$review, array('listing'=>$listing)) : '';
            
            if($activity_thumb || $review['Review']['comments'] != '' )
            {
                $content = '<ul class="cDetailList clrfix">';
                $thumb_link and $content .=  '<li class="avatarWrap">'.$thumb_link.'</li>';
                $thumb_link and $content .= '<li class="detailWrap">';
                $review['Review']['comments'] != '' and $content .= '<div class="newsfeed-quote">'.$this->Text->truncateWords($review['Review']['comments'],25/*$this->c->Config->list_abstract_trim*/).'</div>';
                $thumb_link and $content .= '</li>';
                $content .= '</ul>';
            }

           if($model->data['Vote']['vote_yes'] == 1)
                {
                    $title = sprintf(__t($this->activities['vote_yes'],true),$review_link,$target);
                }
             else 
                {
                    $title = sprintf(__t($this->activities['vote_no'],true),$review_link,$target);
                }
        
            //begin activity stream
            $act = new stdClass();
            $act->cmd       = 'wall.write';
            $act->title     = $title;
            $act->actor     = $this->c->_user->id;
            $act->target    = $review['User']['user_id'];
            $act->content   = $content;
            $act->app       = 'wall';
            $act->cid       =  0;
            
            CFactory::load('libraries', 'activities');
            CActivityStream::add($act);
        }
    }
    
    function _getListing(&$model)
    {
        if(isset($this->c->viewVars['listing'])) 
        {
            $listing = $this->c->viewVars['listing'];
        } 
        else 
        {
            $listing_id = isset($model->data['Listing']) ? Sanitize::getInt($model->data['Listing'],'id') : false;
            !$listing_id and $listing_id = Sanitize::getInt($this->c->data,'listing_id');
            if(!$listing_id) return false;
            $listing = $this->c->Listing->findRow(array('conditions'=>array('Listing.id = '. $listing_id)),array('afterFind' /* Only need menu id */));        
            $this->c->set('listing',$listing);    
        } 
        
        if(isset($model->data['Listing']) && Sanitize::getInt($model->data['Listing'],'state')) 
        {
            $listing['Listing']['state'] =  $model->data['Listing']['state'];          
        }
        
        return $listing;
    }
    
    function _getListingEverywhere($listing_id,$extension)
    {
        if(isset($this->c->viewVars['listing_'.$extension]))
        {
           $listing = $this->c->viewVars['listing_'.$extension];
        }
        else
        {
            // Automagically load and initialize Everywhere Model
            App::import('Model','everywhere_'.$extension,'jreviews');
            $class_name = inflector::camelize('everywhere_'.$extension).'Model';
            
            if(class_exists($class_name)) {
                $ListingModel = new $class_name();
                $listing = $ListingModel->findRow(array('conditions'=>array('Listing.'.$ListingModel->realKey.' = ' . $listing_id)));
                $this->c->set('listing_'.$extension,$listing);           
            }
        }   
        return $listing;      
    }

    function _getReview(&$model)
    {
        if(isset($this->c->viewVars['review']))
        {
            $review = $this->c->viewVars['review'];
        }
        elseif(isset($this->c->viewVars['reviews']))
        {         
            $review = current($this->c->viewVars['reviews']);                    
        }
        else
        {            
            // Get updated review info for non-moderated actions and plugin callback
            $fields = array(
                'Criteria.id AS `Criteria.criteria_id`',
                'Criteria.criteria AS `Criteria.criteria`',
                'Criteria.state AS `Criteria.state`', 
                'Criteria.tooltips AS `Criteria.tooltips`',
                'Criteria.weights AS `Criteria.weights`'            
            );
            
            $joins = $this->c->Listing->joinsReviews;
             
             // Triggers the afterFind in the Observer Model
            $this->c->EverywhereAfterFind = true;
                                
            $review = $model->findRow(array(
                'fields'=>$fields,
                'conditions'=>'Review.id = ' . $model->data['Review']['id'],
                'joins'=>$joins
                ), array('plgAfterFind' /* limit callbacks */) 
            );  
            
            $this->c->set('review',$review);            
        }
        
        return $review;                    
    }
    
    function _getReviewPost(&$model)
    {
        if(isset($this->c->viewVars['post']))
        {
            $post = $this->c->viewVars['post'];
        }
        else 
        {
            $post = $model->findRow(array(
                'conditions'=>array(
                    'Discussion.type = "review"',
                    'Discussion.discussion_id = ' . $model->data['Discussion']['discussion_id']
                    ))
            );
            $this->c->set('post',$post);            
        }
        return $post;        
    }
}
