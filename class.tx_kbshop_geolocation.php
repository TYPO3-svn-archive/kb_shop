<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Kraft Bernhard (kraftb@kraftb.at)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Geolocation FE view class.
 *
 * $Id$
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */


require_once(t3lib_extMgm::extPath('rggooglemap').'pi1/class.tx_rggooglemap_pi1.php');

class tx_kbshop_geolocation extends tx_rggooglemap_pi1	{


	function showMap($content, $conf)	{
		$this->cObj = &$conf['_pObj'];
    $this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);
    $GLOBALS['TSFE']->additionalHeaderData['rggooglemap.1'] = '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$this->confArr['googleKey'].'" type="text/javascript"></script>';    
    $GLOBALS['TSFE']->additionalHeaderData['rggooglemap.2'] = '<script type="text/javascript" src="'.t3lib_extMgm::siteRelpath('rggooglemap').'googleShort.js"></script>';
    $GLOBALS['TSFE']->additionalHeaderData['rggooglemap.3'] = '<script type="text/javascript" src="'.t3lib_extMgm::siteRelpath('rggooglemap').'gxmarker.js"></script>';        
		
//		$lat = $this->cObj->stdWrap($conf['userFunc.']['latField'], $conf['userFunc.']['latField.']);
//		$lng = $this->cObj->stdWrap($conf['userFunc.']['lngField'], $conf['userFunc.']['lngField.']);
		$lat = trim($conf['userFunc.']['latField']);
		$lng = trim($conf['userFunc.']['lngField']);

		$w = $this->cObj->stdWrap($conf['userFunc.']['width'], $conf['userFunc.']['width.']);
		$h = $this->cObj->stdWrap($conf['userFunc.']['height'], $conf['userFunc.']['height.']);
		$id = $this->cObj->stdWrap($conf['userFunc.']['mapid'], $conf['userFunc.']['mapid.']);
		if (!$id)	{
			$id = 'map_'.substr(md5(time().getmypid()), 0, 10);
		}

    $dynamicUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL').'index.php?id='.$GLOBALS["TSFE"]->id.'&type=500';

		$res = $this->cObj->exec_getQuery($conf['userFunc.']['table'], $conf['userFunc.']['select.']);

		$cnt = 0;
		$maxlat = -100000.0;
		$minlat = 100000.0;
		$maxlng = -100000.0;
		$minlng = 100000.0;
		$latsum = 0.0;
		$lngsum = 0.0;
		$js .= 'var gicon = Array();'.chr(10);
		$js .= 'var bounds = false;'.chr(10);
		$js .= 'var zoomLevel = 0;'.chr(10);
		$js .= 'var points = Array();'.chr(10);
		$js .= 'var markers = Array();'.chr(10);
		$js .= 'var preMakeMapZoom = false;'.chr(10);
		$js .= 'var preMakeMap = false;'.chr(10);
		$js .= 'var postMakeMap = false;'.chr(10);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$rows[] = $row;
		}
		$rown = count($rows);
		foreach ($rows as $idx => $row)	{
			if ($row[$lat]>$maxlat)	{
				$maxlat = $row[$lat];
			}
			if ($row[$lat]<$minlat)	{
				$minlat = $row[$lat];
			}
			if ($row[$lng]>$maxlng)	{
				$maxlng = $row[$lng];
			}
			if ($row[$lng]<$minlng)	{
				$minlng = $row[$lng];
			}
			$latsum = bcadd($latsum, $row[$lat], 15);
			$lngsum = bcadd($lngsum, $row[$lng], 15);
			$cnt++;
			$js .= 'points['.$row['uid'].'] = new GPoint('.$row[$lng].', '.$row[$lat].');'.chr(10);
			$js .= 'gicon['.$row['uid'].'] = new GIcon("'.t3lib_div::getIndpEnv('TYPO3_SITE_URL').'fileadmin/templates/default/images/marker.png");'.chr(10);
			$js .= 'markers['.$row['uid'].'] = new GMarker(points['.$row['uid'].']);'.chr(10);
		}
		$js .= '
			bounds = new GLatLngBounds(new GLatLng('.$minlat.', '.$minlng.'), new GLatLng('.$maxlat.', '.$maxlng.'));'.'
		';

		/*

			$js .= 'if ((!bounds)&&('.$cnt.'>=2)) { bounds = new GLatLngBounds(new GLatLng('.$minlat.', '.$minlng.'), new GLatLng('.$maxlat.', '.$maxlng.'));'.chr(10);
			$js .= '} else if ('.$cnt.'>2) { bounds.extend(new GLatLng('.$row[$lat].', '.$row[$lng].')); }'.chr(10);
		}
		*/
		if ($cnt)	{
			$js .= 'preMakeMapZoom = function(m) { zoomLevel = m.getBoundsZoomLevel(bounds); zoomLevel = zoomLevel>15?15:zoomLevel; }'.chr(10);
			$js .= '
			if (!rgMapOnLoadChain)	{
				var rgMapOnLoadChain = window.onload;
			}
			window.onload = function() {
			if ((typeof rgMapOnLoadChain)=="function")	{
				rgMapOnLoadChain();
			}
			makeMap();
			for (i in markers)	{
				map.addOverlay(markers[i]);
			}
			}'.chr(10);
			$slat = bcdiv($latsum, $cnt, 15);
			$slng = bcdiv($lngsum, $cnt, 15);
			$zoom = 'zoomLevel';
		} else	{
			$slat = $this->confArr['startLat'];
			$slng = $this->confArr['startLong'];
			$zoom = intval($this->confArr['startZoom']);
		}

		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		$control = 'show';
		$nav = 'large';
		$ov = 'show';
		if ($conf['userFunc.']['noControl'])	{
			$control = 'hide';
			$nav = 'none';
		$ov = 'hide';
		}

		$this->config = array(
			'mapLng' => $slng,
			'mapLat' => $slat,
			'mapZoom' => $zoom,
			'mapType' => $ov,
			'mapTypeControl' => $control,
			'mapNavControl' => $nav,
		);
		$this->conf = array(
			'mapDiv' => $id,
		);
    
		$GLOBALS["TSFE"]->additionalJavaScript["rggooglemap"] = $this->getJs().$js;


		$content .= '<div class="googlemap"><div id="'.$id.'" style="width: '.$w.'px; height: '.$h.'px; padding:-5px;"></div></div>';


		return $content;
	}


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kb_shop/class.tx_kbshop_geolocation.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kb_shop/class.tx_kbshop_geolocation.php']);
}

?>
