<?php
/************************************************************************************
 mod_aidanews for Joomla 1.5 by Danilo A.

 @author: Danilo A. - dan@cdh.it

 ----- This file is part of the AiDaNews Module. -----

    AiDaNews Module is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    AiDaNews is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this module.  If not, see <http://www.gnu.org/licenses/>.
************************************************************************************/

// no direct access
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');

$filename = 'components/com_flexicontent/index.html';
			if (file_exists($filename)) {
				require_once (JPATH_SITE.DS.'components'.DS.'com_flexicontent'.DS.'helpers'.DS.'route.php');
			}else{
				require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
			}

class mod_aidanews_ItemHelper {
	function getContent(&$params) {
		$item_id = intval( $params->get( 'item_id') );
		return $item_id;
	}
}

function text_adapt($intro, $number, $type, $ending){
	if ($type){
		$cut = $number;
		$cut += 6;
		$intro = substr($intro, 0, $cut);
		$intro = substr($intro, 0, strrpos($intro," "));
		$intro .= $ending;
	}else{
		$array = explode(" ", $intro);
		if (count($array)<=$wordsreturned) {
			//Do nothing
        }else{
			array_splice($array, $number);
			$intro = implode(" ", $array) . $ending;
        }
	}
	return $intro;
}

//Get first or article's images
function getFirstImg($article) {
$img = "";
if (preg_match("#<img[^>]+src=['|\"](.*?)['|\"][^>]*>#i", $article, $matches)){$img = $matches[1];}
return $img;
}

//Get Youtube ID
function getYoutubeID($article) {
$vid = "";
if (preg_match("'{youtube}([^<]*){/youtube}'si", $article, $matches)){
	$vid = $matches[1];
	if (strlen($vid) > 11) {
		$vid = substr($vid, 0, 11);
	}
}
return $vid;
}

//Get Gallery Folder
function getGalFolder($article) {
$gal = "";
if (preg_match("'{gallery}([^<]*){/gallery}'si", $article, $matches)){$gal = $matches[1];}
return $gal;
}

// Output Functions

//Title
function OutputTitle($css, $link, $titolo, $title, $blank, $linktitle) {
	if ($blank == 1) {
		$target = ' target="_blank"';
	}else{
		$target = '';
	}
	if ($linktitle == 1) {
		$outputtitle = '<span style="' . $css . '"><a href="' . $link . '"' . $target . ' title="' . $titolo . '">' . $title . '</a></span> ';
	}else{
		$outputtitle = '<span style="' . $css . '">' . $title . '</span> ';
	}
return $outputtitle;
}

//Date
function OutputDate($uselang, $dprefix, $date, $css) {
	if ($uselang == 1) {
		$pr = JText::_('F_DATEPREFIX');
	}else{
		$pr = $dprefix;
	}
$outputdate = '<span style="' . $css . '">' . $pr . ' ' . $date . '</span> ';
return $outputdate;
}

//Author
function OutputAuthor($uselang, $aprefix, $profilesystem, $profilelink, $creator, $author, $css, $ac) {
	if ($uselang == 1) {
		$pr = JText::_('F_AUTHORPREFIX');
	}else{
		$pr = $aprefix;
	}
	if ($profilesystem != 0 && $ac != 1) {
		$outputauthor = '<span style="' . $css . '">' . $pr . ' <a href="' . $profilelink . $creator . '">' . $author . '</a></span> '; 
	}else{
		$outputauthor = '<span style="' . $css . '">' . $pr . ' ' . $author . '</span> ';
	}
return $outputauthor;
}

//Category
function OutputCategory($uselang, $catprefix, $showcat, $css) {
	if ($uselang == 1) {
		$pr = JText::_('F_CATHPREFIX');
	}else{
		$pr = $catprefix;
	}
$outputcategory = '<span style="' . $css . '">' . $pr . ' ' . $showcat . '</span> ';
return $outputcategory;
}

//Comments
function OutputComments($uselang, $comprefix, $commenti, $comtitle, $comimg, $css) {
	if ($uselang == 1) {
		$pr = JText::_('F_COMMPREFIX');
		if ($commenti == 1) {
			$tit = JText::_('F_COMMTITLE_S');
		}else{
			$tit = JText::_('F_COMMTITLE_P');
		}
	}else{
		$pr = $comprefix;
		$tit = $comtitle;
	}
	if ($comimg == 1) {
		$outputcomments = '<span style="' . $css . '">' . $pr . ' ' . $commenti . ' <img src="modules/mod_aidanews/comment.png" title="' . $tit . '" alt="' . $tit . '"/></span> ';
	}else{
		$outputcomments = '<span style="' . $css . '">' . $pr . ' ' . $commenti . ' ' . $tit . '</span> ';
	}
return $outputcomments;
}

//Hits
function OutputHits($hitimg, $uselang, $hitprefix, $visite, $hittitle, $css) {
	if ($uselang == 1) {
		$pr = JText::_('F_HITPREFIX');
		if ($visite == 1) {
			$tit = JText::_('F_HITTITLE_S');
		}else{
			$tit = JText::_('F_HITTITLE_P');
		}
	}else{
		$pr = $hitprefix;
		$tit = $hittitle;
	}	
	if ($hitimg == 1) {
		$outputhits = '<span style="' . $css . '">' . $pr . ' ' . $visite . ' <img src="modules/mod_aidanews/hit.png" title="' . $tit . '" alt="' . $tit . '"/></span> ';
	}else{
		$outputhits = '<span style="' . $css . '">' . $pr . ' ' . $visite . ' ' . $tit . '</span> ';
	}
return $outputhits;
}

//Rating
function OutputRating($ratimg, $uselang, $ratprefix, $voti, $rattitle, $css) {
	if ($uselang == 1) {
		$pr = JText::_('F_RATINGPREFIX');
		if ($voti == 1) {
			$tit = JText::_('F_RATINGTITLE_S');
		}else{
			$tit = JText::_('F_RATINGTITLE_P');
		}
	}else{
		$pr = $ratprefix;
		$tit = $rattitle;
	}	
	if ($ratimg == 1) {
		$outputrating = '<span style="' . $css . '">' . $pr . ' ' . $voti . ' <img src="modules/mod_aidanews/rating.png" title="' . $tit . '" alt="' . $tit . '"/></span> ';
	}else{
		$outputrating = '<span style="' . $css . '">' . $pr . ' ' . $voti . ' ' . $tit . '</span> ';
	}
return $outputrating;
}

//Readmore
function OutputRM($uselang, $link, $keepon, $lire_suite, $titolo, $tit, $css){
	if ($uselang == 1) {
		$readmore = JText::_('F_READMORE');
	}else{
		$readmore = $lire_suite; 
	}
	if ($keepon == 2) {
		$outputreadmore = '<span style="' . $css . '"><a href="' . $link . '" class="readon"><span>' . $readmore . '</span></a> ';
	}elseif ($keepon == 1) {
		$outputreadmore = '<span style="' . $css . '"><a href="' . $link . '">' . $readmore . '</a></span> ';
	}else{
		$outputreadmore = '<span style="' . $css . '">' . $readmore . ' <a href="' . $link . '" title="' . $titolo . '">' . $tit . '</a></span> ';
	}
return $outputreadmore;
}

//Add Comment
function Outputaddcomm($uselang, $link, $addcomm, $css, $ct){
	if ($uselang == 1) {
		$c = JText::_('F_ADDCOMMENTS');
	}else{
		$c = $addcomm; 
	}
	if ($ct == 1) {
		$link .= "#addcomments";
	}
return '<span style="' . $css . '"><a href="' . $link . '">' . $c . '</a></span> ';
}

//Image
function OutputImage($float, $css, $link, $blank, $image, $tooltip, $title, $intro) {
	if ($blank == 1) {
		$artblank = 'target="_blank"';
	}else{
		$artblank = '';
	}
	if ($tooltip) {
		if ($tooltip == 2) {
			$outputimage = '<span class="hasTip" title="' . $title . '::' . $intro . '" style="float:' . $float . '; ' . $css . '"><a href="' . $link . '" ' . $artblank . ' >' . $image . '</a></span>';
		}else{
			$outputimage = '<span class="hasTip" title="' . $title . '" style="float:' . $float . '; ' . $css . '"><a href="' . $link . '" ' . $artblank . ' >' . $image . '</a></span>';
		}
	}else{	
		$outputimage = '<span style="float:' . $float . '; ' . $css . '"><a href="' . $link . '" ' . $artblank . ' >' . $image . '</a></span>';
	}
return $outputimage;
}

//FLEXI Field
function OutputField($css, $field, $ft) {
	if ($field) {	
		if ($ft == 'weblink') {
			$x = 0;
			while ($x < 3) {
				$field = strstr($field, '"');
				$field = substr($field, 1);
				$x++;
			} 
			list($url)  = explode('"', $field);
			$x = 0;
			while ($x < 4) {
				$field = strstr($field, '"');
				$field = substr($field, 1);
				$x++;
			}
			list($title)  = explode('"', $field);
			$outputfield = '<span style="' . $css . '"><a href="' . $url . '">' . $title . '</a></span>';
		}else{
			$outputfield = '<span style="' . $css . '">' . $field . '</span>';
		}
	}else{ $outputfield = ''; }
return $outputfield;
}

//Get FLEXIcontent Fields

function FLEXIfield($id, $field_id, $db) {
	$query = 'SELECT value FROM #__flexicontent_fields_item_relations WHERE item_id = ' . $id . ' AND field_id = ' . $field_id . ' ORDER BY valueorder';
		$db->setQuery($query);
		$flexifield = $db->loadObjectList();
		$fr = "";
		foreach ($flexifield as $ff) {
			$fr = $fr . $ff->value . ", ";
		}
		$fr = substr($fr,0,-2);
	return $fr;
}

//Get FLEXIcontent Fields Type

function FieldType ($db, $field_id) {
	$query = 'SELECT field_type FROM #__flexicontent_fields WHERE id = ' . $field_id;
		$db->setQuery($query);
		$fieldtype = $db->loadResult();	
	return $fieldtype;
}

//CSS
class modANHelper {
	function addAN_CSS(&$params) {
		
		$link   = $params->get('link_css');
		if ($link) {
			$doc =& JFactory::getDocument();
			$class	= $params->get('moduleclass_sfx');
			
			$css =  " AiDaNews CSS -->" .
				"\n\n .aidanews" . $class . " {}" .
				"\n .aidanews" . $class . " a {" . $link . " }" .
				"<!-- AiDaNews CSS END ";
		
			return $doc->addStyleDeclaration($css);		 
		}
	}
}

//Get Tiny URLs (Used to Tweet articles)

function get_tiny_url($url)  
{  
	$ch = curl_init();  
	$timeout = 5;  
	curl_setopt($ch,CURLOPT_URL,'http://tinyurl.com/api-create.php?url='.$url);  
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);  
	$data = curl_exec($ch);  
	curl_close($ch);  
 
	return $data;  
}


//Thumbnails - Resize and Crop

class ThumbAndCrop {

		private $handleimg;
		private $original = "";
		private $handlethumb;
		private $oldoriginal;

		/*
			Apre l'immagine da manipolare
		*/
		public function openImg($file)
		{
			$this->original = $file;

			if($this->extension($file) == 'jpg' || $this->extension($file) == 'jpeg')
			{
				$this->handleimg = imagecreatefromjpeg($file);
			}
			elseif($this->extension($file) == 'png')
			{
				$this->handleimg = imagecreatefrompng($file);
			}
			elseif($this->extension($file) == 'gif')
			{
				$this->handleimg = imagecreatefromgif($file);
			}
			elseif($this->extension($file) == 'bmp')
			{
				$this->handleimg = imagecreatefromwbmp($file);
			}
		}

		/*
			Ottiene la larghezza dell'immagine
		*/
		public function getWidth()
		{
			return imageSX($this->handleimg);
		}

		/*
			Ottiene la larghezza proporzionata all'immagine partendo da un'altezza
		*/
		public function getRightWidth($newheight)
		{
			$oldw = $this->getWidth();
			$oldh = $this->getHeight();

			$neww = ($oldw * $newheight) / $oldh;

			return $neww;
		}

		/*
			Ottiene l'altezza dell'immagine
		*/
		public function getHeight()
		{
			return imageSY($this->handleimg);
		}

		/*
			Ottiene l'altezza proporzionata all'immagine partendo da una larghezza
		*/
		public function getRightHeight($newwidth)
		{
			$oldw = $this->getWidth();
			$oldh = $this->getHeight();

			$newh = ($oldh * $newwidth) / $oldw;

			return $newh;
		}

		/*
			Crea una miniatura dell'immagine
		*/
		public function creaThumb($newWidth, $newHeight)
		{
			$oldw = $this->getWidth();
			$oldh = $this->getHeight();

			$this->handlethumb = imagecreatetruecolor($newWidth, $newHeight);

			return imagecopyresampled($this->handlethumb, $this->handleimg, 0, 0, 0, 0, $newWidth, $newHeight, $oldw, $oldh);
		}

		/*
			Ritaglia un pezzo dell'immagine
		*/
		public function cropThumb($width, $height, $x, $y)
		{
			$oldw = $this->getWidth();
			$oldh = $this->getHeight();

			$this->handlethumb = imagecreatetruecolor($width, $height);

			return imagecopy($this->handlethumb, $this->handleimg, 0, 0, $x, $y, $width, $height);
		}

		/*
			Salva su file la Thumbnail
		*/
		public function saveThumb($path, $qualityJpg = 100)
		{
			if($this->extension($this->original) == 'jpg' || $this->extension($this->original) == 'jpeg')
			{
				return imagejpeg($this->handlethumb, $path, $qualityJpg);
			}
			elseif($this->extension($this->original) == 'png')
			{
				return imagepng($this->handlethumb, $path);
			}
			elseif($this->extension($this->original) == 'gif')
			{
				return imagegif($this->handlethumb, $path);
			}
			elseif($this->extension($this->original) == 'bmp')
			{
				return imagewbmp($this->handlethumb, $path);
			}
		}

		/*
			Stampa a video la Thumbnail
		*/
		public function printThumb()
		{
			if($this->extension($this->original) == 'jpg' || $this->xtension($this->original) == 'jpeg')
			{
				header("Content-Type: image/jpeg");
				imagejpeg($this->handlethumb);
			}
			elseif($this->extension($this->original) == 'png')
			{
				header("Content-Type: image/png");
				imagepng($this->handlethumb);
			}
			elseif($this->extension($this->original) == 'gif')
			{
				header("Content-Type: image/gif");
				imagegif($this->handlethumb);
			}
			elseif($this->extension($this->original) == 'bmp')
			{
				header("Content-Type: image/bmp");
				imagewbmp($this->handlethumb);
			}
		}

		/*
			Distrugge le immagine per liberare le risorse
		*/
		public function closeImg()
		{
			imagedestroy($this->handleimg);
			imagedestroy($this->handlethumb);
		}

		/*
			Imposta la thumbnail come immagine sorgente,
			in questo modo potremo combinare la funzione crea con la funzione crop
		*/
		public function setThumbAsOriginal()
		{
			$this->oldoriginal = $this->handleimg;
			$this->handleimg = $this->handlethumb;
		}

		/*
			Resetta l'immagine originale
		*/
		public function resetOriginal()
		{
			$this->handleimg = $this->oldoriginal;
		}

		/*
			Estrae l'estensione da un file o un percorso
		*/
		private function extension($percorso)
		{
			if(eregi("[\|\\]", $percorso))
			{
				// da percorso
				$nome = $this->nomefile($percorso);

				$spezzo = explode(".", $nome);

				return strtolower(trim(array_pop($spezzo)));
			}
			else
			{
				//da file
				$spezzo = explode(".", $percorso);

				return strtolower(trim(array_pop($spezzo)));
			}
		}

		/*
			Estrae il nome del file da un percorso
		*/
		public function nomefile($path, $ext = true)
		{
			$diviso = spliti("[/|\\]", $path);

			if($ext)
			{
				return trim(array_pop($diviso));
			}
			else
			{
				$nome = explode(".", trim(array_pop($diviso)));

				array_pop($nome);

				return trim(implode(".", $nome));
			}
		}
	}

?>