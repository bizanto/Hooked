<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class ThumbnailHelper extends HtmlHelper {
    
    var $quality = 85;
    var $path;
    var $path_tn;
    var $site;
    var $site_tn;
    var $image_size;
    var $catImage = false;
    var $noImage = false;
    
    function __construct() {
        
        App::import('Vendor', 'thumbnail' . DS . 'thumbnail.inc');

        $this->path = PATH_ROOT . _JR_WWW_IMAGES;
        $this->path_tn = PATH_ROOT . _JR_WWW_IMAGES .'jreviews'._DS.'tn'._DS;
        $this->www = WWW_ROOT . _JR_WWW_IMAGES;
        $this->www_tn = $this->www . 'jreviews'._DS.'tn'._DS;        
    }
        
    function lightbox($listing, $position=0, $action='scale', $location = '_', $dimensions = null, $attributes = array()) {
                
//        if(!isset($listing['Listing']['images'][$position]) || !file_exists($this->path.$listing['Listing']['images'][$position]['path'])) {
//            return '';
//        }
        
        if(!$dimensions) {
            $dimensions = array($this->Config->list_image_resize);
        }
        
        $listing_id = $listing['Listing']['listing_id'];
        $image = $listing['Listing']['images'][$position];
        $cat_image = $listing['Listing']['category_image'];

        $thumb = $this->thumb($listing, $position, $action, $location, $dimensions, $attributes);

        if($thumb) {    
            
            // If listing has no images then this is a category or no image and it shouldnt be lightboxed
            if(!isset($listing['Listing']['images'][$position]) || !file_exists($this->path.$listing['Listing']['images'][$position]['path'])) {
                return $thumb;
            }                    

            $lightbox = $this->link($thumb,$this->www.$image['path'],array('sef'=>false,'class'=>'fancybox','rel'=>'gallery','title'=>$image['caption']));
            
            return $lightbox;
        }
        
    }

    function grabImgFromText($text)
    {
        $doc = new DOMDocument();
        @$doc->loadHTML($text);
        $imageTags = $doc->getElementsByTagName('img');   
        if($imageTags->length > 0)
        {
            $src = $imageTags->item(0)->getAttribute('src');
            $img = array('path'=>str_replace('images/stories/','',$src));
            substr($src,0,4) == 'http' and !strstr($src,WWW_ROOT) and $img['skipthumb'] = true; // Display external images, no thumbs
            !strstr($src,'images/stories') and $img['basepath'] = true;
            return  $img;
        }
/*        $img_src = '/<img[^>]+src[\\s=\'"]+([^"\'>]+)/is';
        preg_match($img_src,$text,$matches);    
        if($matches){
            return array('path'=>str_replace('images/stories/','',urldecode($matches[1])));
        }*/
        return false;        
    }
    
    function thumb(&$listing, $position=0, $action='scale', $location = '_', $dimensions = null, $attributes = array()) 
    {       
        $image = null;
        // No JReviews uploaded images, so we search the summary for images
        if(!isset($listing['Listing']['images'][$position]) && isset($listing['Listing']['summary']) && strstr($listing['Listing']['summary'],"<img")){
            $img = $this->grabImgFromText($listing['Listing']['summary']);
            $img and $listing['Listing']['images'][0] = $img and $listing['Listing']['summary'] = Sanitize::stripImages($listing['Listing']['summary']);                
        }
                                                      
        if(!$dimensions) {
            $dimensions = array($this->Config->list_image_resize);
        }

        $listing_id = $listing['Listing']['listing_id'];
        
        if(isset($listing['Listing']['images'][$position]))    
        {
            $image = $listing['Listing']['images'][$position];
        }

        $cat_image = isset($listing['Listing']['category_image']) ? $listing['Listing']['category_image'] : '';

        $output = $this->makeThumb($listing_id, $image, $action, $location, $dimensions, $cat_image, $attributes);

        if($output) {
            if(isset($attributes['return_src'])) {
                return $output['thumbnail'];
            }
            return $this->image($output['thumbnail'],$attributes);
        } 
        
        return false;
    }
    
    /**
     * Creates a thumbnail if it doesn't already exist and returns an array with full paths to original image and thumbnail
     * returns false if thumbnail cannot be created
     *
     * @param int $listing_id listing id
     * @param array $image array of image path and caption
     * @param string $action can be 'scale' or 'crop'
     * @param string $location this variable is used to have different image sizes for lists, details and modules 
     * @param string $dimensions array of width and height
     * @param string $cat_image category image name
     * @param string $no_image noimage image name
     */
    function makeThumb($listing_id, $image, $action='scale', $location = '_', $dimensions, $cat_image)
     {                          
        $imageName = '';
        $this->catImage = false;        
        $this->noImage = false;
        
        if($location != '_') {
            $location = '_'.$location.'_';
        }

        if(isset($image['path'])) 
        {       
            if(isset($image['skipthumb']) && $image['skipthumb']===true) {
                return array('image'=>$image['path'],'thumbnail'=>$image['path']);
            }
            
            $temp = explode( '/', $image['path']);
            $imageName = $temp[count($temp)-1];
            $length = strlen($listing_id);
 
             if (substr($imageName,0,$length+1) == $listing_id.'_') {
                // Uploaded image already has entry id prepended so we remove it and put it before the content suffix
                $imageName = substr($imageName,$length+1);
            }
            
            $thumbnail = "tn_".$listing_id.$location.$imageName;
    
            $output = array(
                            'image'=>$this->www.$image['path'],
                            'thumbnail'=>$this->www_tn.$thumbnail
                        );                                                                                                
            
            $image_path = trim(isset($image['basepath']) && $image['basepath'] ? $image['path'] : $this->path.$image['path']);
            
            // If in administration, then can't use relative path because it will include /administrator
            defined('MVC_FRAMEWORK_ADMIN') and strpos($image_path,PATH_ROOT)===false and $image_path = PATH_ROOT . str_replace(_DS,DS,$image_path);

            if ($imageName != '' && file_exists($image_path)) 
            { 
                $this->image_size = getimagesize($image_path);
                         
                if(file_exists($this->path_tn.$thumbnail)) 
                { // Tbumbnail exists, so we check if current size is correct
    
                    $thumbnailSize = getimagesize($this->path_tn.$thumbnail);

                    // Checks the thumbnail width to see if it needs to be resized
                    if ($thumbnailSize[0] == $dimensions[0] 
                        || ($thumbnailSize[0] != $dimensions[0] && $this->image_size[0] < $dimensions[0] )
                        || ($action == 'crop' && $thumbnailSize[0] == $thumbnailSize[1] && $thumbnailSize[0] == $dimensions[0])
                    ) {
                        // No resizing is necessary
                        return $output;
                    }
                }

                // Create the thumbnail
                if($this->$action($image_path, $this->path_tn.$thumbnail, $dimensions)) {
                    return $output;
                }
                
            }
        }
        
        if ($this->Config->list_category_image && $cat_image != '') {
            
            $this->image_size = getimagesize($this->path.$cat_image);
            
            if($this->image_size[0] == min($this->image_size[0],trim(intval($dimensions[0])))) {
                // Image is smaller (narrower) than thumb so no thumbnailing is done
                return array(
                    'image'=>$this->www.$cat_image,
                    'thumbnail'=>$this->www.$cat_image
                );                        
            }

            // Create category thumb
            if ($this->$action($this->path.$cat_image, $this->path_tn.'tn'.$location.$cat_image, $dimensions)) {
                $this->catImage = true;
                return array(
                    'image'=>$this->www.$cat_image,
                    'thumbnail'=>$this->www_tn.'tn'.$location.$cat_image
                );
            }
        }

        // Create no image thumb         
        $this->viewSuffix = '';
        $noImagePath =     $this->locateThemeFile('theme_images',$this->Config->list_noimage_filename,''); 
        $noImageWww =  pathToUrl($noImagePath);
        $noImageThumbnailPath = $this->path_tn . 'tn'.$location.$this->Config->list_noimage_filename;        
         
        if ($this->Config->list_noimage_image && $this->Config->list_noimage_filename != '') 
        {            
            $thumbExists = file_exists($noImageThumbnailPath);
            
            if($thumbExists) 
            {
                $noImageSize = getimagesize($noImageThumbnailPath); // In v2.1.11 was reading original image size...
            
                if($this->image_size[0] == min($noImageSize[0],trim(intval($dimensions[0])))) {
                    // Image is smaller (narrower) than thumb so no thumbnailing is done
                    return array(
                        'image'=>$noImageWww,
                        'thumbnail'=>$noImageWww
                    );                    
                }

                if(($noImageSize[0]!=$dimensions[0])) {
                    $this->$action($noImagePath,$noImageThumbnailPath, $dimensions);                    
                }  
            } else {
                $this->$action($noImagePath,$noImageThumbnailPath, $dimensions);
            }

            $this->noImage = true;

            return array(
                'image'=>$noImageWww,
                'thumbnail'=> $this->www_tn . 'tn' . $location . $this->Config->list_noimage_filename
            );
        }

        
        return false;
    }
    
    function crop($imagePath, $thumbnailPath, $dimensions) {
             
        $crop = false;
        $newSize = trim(intval($dimensions[0])) > 0 ? trim(intval($dimensions[0])) : 100;

        $thumb = new Thumbnail($imagePath);

        if ($thumb->error) {
            echo $imagePath.":".$thumb->errmsg."<br />";
            return false;
        }

        $minLength = min($thumb->getCurrentWidth(), $thumb->getCurrentHeight());
        
        $maxLength = max($thumb->getCurrentWidth(), $thumb->getCurrentHeight());

        // Image is smaller than the specified size so we just rename it and save
        if ($maxLength <= $newSize) {

            $thumb->save($thumbnailPath, $this->quality); //Just rename and save without processing

        } else { // At least one side is larger than specified thumbnail size

            // Both sides are larger than resize length so first we scale and if image is not square we crop
            if ($minLength > $newSize) {
                // Scale smaller size to desired new size
                if ($thumb->getCurrentWidth() < $thumb->getCurrentHeight()) {
                    $thumb->resize($newSize,0);
                    $crop = true;
                } elseif ($thumb->getCurrentWidth() > $thumb->getCurrentHeight()) {
                    $thumb->resize(0,$newSize);
                    $crop = true;
                } else {
                    $thumb->resize($newSize,$newSize);
                }

                if ($crop) {
                       $thumb->cropFromCenter($newSize);
                }
            // One size is smaller than the new size, so we only crop the larger size to the new size
            } else {
                $cropX = intval(($thumb->getCurrentWidth() - $newSize) / 2);
                $cropY = intval(($thumb->getCurrentHeight()- $newSize) / 2);
                   $thumb->crop($cropX,$cropY,$newSize,$newSize);
            }

            $thumb->save($thumbnailPath, $this->quality);

        }

        $thumb->destruct();

        if (file_exists($thumbnailPath)) {
            return true;
        } 
        
        return false;

    }    
    
    function scale($imagePath, $thumbnailPath, $dimensions) 
    {   
        $imgMaxWidth = is_numeric($this->image_size[0]) ? min($this->image_size[0],trim(intval($dimensions[0]))) : trim(intval($dimensions[0]));
//        $imgMaxHeight = trim(intval($this->size));

        $thumb = new Thumbnail($imagePath);
        
        if ($thumb->error) {
            echo $imagePath.":".$thumb->errmsg."<br />";
            return false;
        }
//        $thumb->resize($imgMaxWidth,$imgMaxHeight);
        $thumb->resize($imgMaxWidth);

        $thumb->save($thumbnailPath, $this->quality);

        $thumb->destruct();

        if (file_exists($thumbnailPath)) {
            return true;
        } 
        return false;
    }

}
