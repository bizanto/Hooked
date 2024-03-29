<?php defined( '_JEXEC') or die( 'Restricted Access' );
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service_ShortUrl
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */

/**
 * @category   Zend
 * @package    Zend_Service_ShortUrl
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Service_ShortUrl_Shortener
{
    /**
     * This function shortens long url
     * 
     * @param  string $url URL to Shorten
     * @return string Shortened Url
     */
    function shorten($shortenedUrl);
    
    /**
     * Reveals target for short URL
     *
     * @param  string $shortenedUrl URL to reveal target of
     * @return string Unshortened Url
     */
    function unshorten($shortenedUrl);
}
