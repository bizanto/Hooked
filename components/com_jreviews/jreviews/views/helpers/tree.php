<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );
        
class TreeHelper extends MyHelper
{
    var $helpers = array('routes','html'); 
    
    function renderDirectory($tree) 
    {          
        $current_depth = 0;
        $counter = 0;
        $result = '';

        foreach($tree as $node)
        {
            $curr = $node['Category'];
            $node_depth = $curr['level'];
            $node_name = $curr['title'];
            $node_id = $curr['cat_id'];
            $node_count = $curr['listing_count'];

            if($node_depth == $current_depth)
            {
                if($counter > 0) $result .= '</li>';            
            }
            elseif($node_depth > $current_depth)
            {
                $result .= '<ul>';
                $current_depth = $current_depth + ($node_depth - $current_depth);
            }
            elseif($node_depth < $current_depth)
            {
                $result .= str_repeat('</li></ul>',$current_depth - $node_depth).'</li>';
                $current_depth = $current_depth - ($current_depth - $node_depth);
            }
            $result .= '<li';
            $result .= '>' . $this->Routes->category($node);
            $this->Config->dir_cat_num_entries and $result .= ' (' .$node_count . ')';
            ++$counter;
        }
        
        $result .= str_repeat('</li></ul>',$node_depth).'</li>';

        return $result;
    }
    
  function renderTree($tree, $options)
  {
        $current_depth = 0;
        $counter = 0;
        $result = '';

        foreach($tree as $node)
        {
            $curr = $node['Category'];
            $node_depth = $curr['level'];
            $node_name = $curr['title'];
            $node_id = $curr['cat_id'];
            $node_count = $curr['listing_count'];

            if($node_depth == $current_depth)
            {
                if($counter > 0) $result .= '</li>';            
            }
            elseif($node_depth > $current_depth)
            {
                $result .= '<ul>';
                $current_depth = $current_depth + ($node_depth - $current_depth);
            }
            elseif($node_depth < $current_depth)
            {
                $result .= str_repeat('</li></ul>',$current_depth - $node_depth).'</li>';
                $current_depth = $current_depth - ($current_depth - $node_depth);
            }
            $result .= '<li id="cat'.$node_id.'-'.$options['module_id'].'" class="closed"';
            $result .= '>' . $this->Routes->category($node);
            $this->Config->dir_cat_num_entries and $result .= ' (' .$node_count . ')';
            ++$counter;
        }
        
        $result .= str_repeat('</li></ul>',$node_depth).'</li>';

        return $result;
  }
}
