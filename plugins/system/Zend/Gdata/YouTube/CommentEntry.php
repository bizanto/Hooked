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
 * @package    Zend_Gdata
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: CommentEntry.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Media_Feed
 */
require_once 'Zend/Gdata/Media/Feed.php';

/**
 * The YouTube comments flavor of an Atom Entry
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_YouTube_CommentEntry extends Zend_Gdata_Entry
{

    /**
     * The classname for individual feed elements.
     *
     * @var string
     */
    protected $_entryClassName = 'Zend_Gdata_YouTube_CommentEntry';

    /**
     * Constructs a new Zend_Gdata_YouTube_CommentEntry object.
     * @param DOMElement $element (optional) The DOMElement on which to
     * base this object.
     */
    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($element);
    }

}
