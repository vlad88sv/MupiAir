<?php
error_reporting(E_STRICT | E_ALL);

/**
 * Project:     GoogleMapAPI: a PHP library inteface to the Google Map API
 * File:        GoogleMapAPI.class.php
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * Smarty mailing list. Send a blank e-mail to
 * smarty-general-subscribe@lists.php.net
 *
 * @link http://www.phpinsider.com/php/code/GoogleMapAPI/
 * @copyright 2005 New Digital Group, Inc.
 * @author Monte Ohrt <monte at ohrt dot com>
 * @package GoogleMapAPI
 * @version 2.5
 */

/* $Id: GoogleMapAPI.class.php,v 1.63 2007/08/03 16:29:40 mohrt Exp $ */

class GoogleMapAPI {

    var $dsn = null;
    var $api_key = '';
    var $map_id = null;
    var $sidebar_id = NULL;    
    var $app_id = null;
    var $onload = true;
    var $center_lat = null;
    var $center_lon = null;
	var $map_controls = true;
    var $control_size = 'large';
    var $type_controls = false;
    var $map_type = 'G_NORMAL_MAP';
    var $scale_control = false;
	var $disable_drag = false;
	var $referencias = false;
    var $overview_control = false;    
    var $zoom = 16;
    var $width = '500px';
    var $height = '500px';
    var $sidebar = false;    
    var $info_window = true;
    var $window_trigger = 'click';    
    var $lookup_service = 'GOOGLE';
	var $lookup_server = array('GOOGLE' => 'maps.google.com', 'YAHOO' => 'api.local.yahoo.com');               
    var $_version = '2.5';
    var $_markers = array();
    var $_max_lon = -1000000;
    var $_min_lon = 1000000;
    var $_max_lat = -1000000;
    var $_min_lat = 1000000;
    var $zoom_encompass = true;
    var $bounds_fudge = 0.01;
    var $use_suggest = false;
    var $_polylines = array();    
    var $_icons = array();
    var $_db_cache_table = 'GEOCODES';

    function GoogleMapAPI($map_id = 'map', $app_id = 'MyMapApp') {
        $this->map_id = $map_id;
        $this->sidebar_id = 'sidebar_' . $map_id;
        $this->app_id = $app_id;
    }
   
    /**
     * sets the PEAR::DB dsn
     *
     * @param string $dsn
     */
    function setDSN($dsn) {
        $this->dsn = $dsn;   
    }
    
    /**
     * sets YOUR Google Map API key
     *
     * @param string $key
     */
    function setAPIKey($key) {
        $this->api_key = $key;   
    }

    /**
     * sets the width of the map
     *
     * @param string $width
     */
    function setWidth($width) {
        if(!preg_match('!^(\d+)(.*)$!',$width,$_match))
            return false;

        $_width = $_match[1];
        $_type = $_match[2];
        if($_type == '%')
            $this->width = $_width . '%';
        else
            $this->width = $_width . 'px';
        
        return true;
    }

    /**
     * sets the height of the map
     *
     * @param string $height
     */
    function setHeight($height) {
        if(!preg_match('!^(\d+)(.*)$!',$height,$_match))
            return false;

        $_height = $_match[1];
        $_type = $_match[2];
        if($_type == '%')
            $this->height = $_height . '%';
        else
            $this->height = $_height . 'px';
        
        return true;
    }        

    /**
     * sets the default map zoom level
     *
     * @param string $level
     */
    function setZoomLevel($level) {
        $this->zoom = (int) $level;
    }    
            
    /**
     * enables the map controls (zoom/move)
     *
     */
    function enableMapControls() {
        $this->map_controls = true;
    }

    /**
     * disables the map controls (zoom/move)
     *
     */
    function disableMapControls() {
        $this->map_controls = false;
    }    
    
    /**
     * sets the map control size (large/small)
     *
     * @param string $size
     */
    function setControlSize($size) {
        if(in_array($size,array('large','small')))
            $this->control_size = $size;
    }            

    /**
     * enables the type controls (map/satellite/hybrid)
     *
     */
    function enableTypeControls() {
        $this->type_controls = true;
    }

    /**
     * disables the type controls (map/satellite/hybrid)
     *
     */
    function disableTypeControls() {
        $this->type_controls = false;
    }

    /**
     * set default map type (map/satellite/hybrid)
     *
     */
    function setMapType($type) {
        switch($type) {
            case 'hybrid':
                $this->map_type = 'G_HYBRID_MAP';
                break;
            case 'satellite':
                $this->map_type = 'G_SATELLITE_MAP';
                break;
            case 'map':
            default:
                $this->map_type = 'G_NORMAL_MAP';
                break;
        }       
    }    
    
    /**
     * enables onload
     *
     */
    function enableOnLoad() {
        $this->onload = true;
    }

    /**
     * disables onload
     *
     */
    function disableOnLoad() {
        $this->onload = false;
    }
    
    /**
     * enables sidebar
     *
     */
    function enableSidebar() {
        $this->sidebar = true;
    }

    /**
     * disables sidebar
     *
     */
    function disableSidebar() {
        $this->sidebar = false;
    }    

    /**
     * enables map directions inside info window
     *
     */
    function enableDirections() {
        $this->directions = true;
    }

    /**
     * disables map directions inside info window
     *
     */
    function disableDirections() {
        $this->directions = false;
    }    

    /**
     * enable map marker info windows
     */
    function enableInfoWindow() {
        $this->info_window = true;
    }
    
    /**
     * disable map marker info windows
     */
    function disableInfoWindow() {
        $this->info_window = false;
    }
    
    /**
     * set the info window trigger action
     *
     * @params $message string click/mouseover
     */
    function setInfoWindowTrigger($type) {
        switch($type) {
            case 'mouseover':
                $this->window_trigger = 'mouseover';
                break;
            default:
                $this->window_trigger = 'click';
                break;
            }
    }

    /**
     * enable zoom to encompass makers
     */
    function enableZoomEncompass() {
        $this->zoom_encompass = true;
    }
    
    /**
     * disable zoom to encompass makers
     */
    function disableZoomEncompass() {
        $this->zoom_encompass = false;
    }

    /**
     * set the boundary fudge factor
     */
    function setBoundsFudge($val) {
        $this->bounds_fudge = $val;
    }
    
    /**
     * enables the scale map control
     *
     */
    function enableScaleControl() {
        $this->scale_control = true;
    }

    /**
     * disables the scale map control
     *
     */
    function disableScaleControl() {
        $this->scale_control = false;
    }    
            
    /**
     * enables the overview map control
     *
     */
    function enableOverviewControl() {
        $this->overview_control = true;
    }

    /**
     * disables the overview map control
     *
     */
    function disableOverviewControl() {
        $this->overview_control = false;
     }    
    
    
    /**
     * set the lookup service to use for geocode lookups
     * default is YAHOO, you can also use GOOGLE.
     * NOTE: GOOGLE can to intl lookups, but is not an
     * official API, so use at your own risk.
     *
     */
    function setLookupService($service) {
        switch($service) {
            case 'GOOGLE':
                $this->lookup_service = 'GOOGLE';
                break;
            case 'YAHOO':
            default:
                $this->lookup_service = 'YAHOO';
                break;
        }       
    }
    
        
    /**
     * adds a map marker by address
     * 
     * @param string $address the map address to mark (street/city/state/zip)
     * @param string $title the title display in the sidebar
     * @param string $html the HTML block to display in the info bubble (if empty, title is used)
     */
    function addMarkerByAddress($address,$title = '',$html = '',$tooltip = '',$id = '') {
        if(($_geocode = $this->getGeocode($address)) === false)
            return false;
        return $this->addMarkerByCoords($_geocode['lon'],$_geocode['lat'],$title,$html,$tooltip);
    }

    /**
     * adds a map marker by geocode
     * 
     * @param string $lon the map longitude (horizontal)
     * @param string $lat the map latitude (vertical)
     * @param string $title the title display in the sidebar
     * @param string $html|array $html 
     *     string: the HTML block to display in the info bubble (if empty, title is used)
     *     array: The title => content pairs for a tabbed info bubble     
     */
    // TODO make it so you can specify which tab you want the directions to appear in (add another arg)
    function addMarkerByCoords($lon,$lat,$title = '',$html = '',$tooltip = '',$id = '', $html_pedidos = '') {
        $_marker['lon'] = $lon;
        $_marker['lat'] = $lat;
        $_marker['html'] = (is_array($html) || strlen($html) > 0) ? $html : $title;
        $_marker['title'] = $title;
        $_marker['tooltip'] = $tooltip;
        $_marker['id'] = $id;
		$_marker['html_pedidos'] = $html_pedidos;
		
        $this->_markers[] = $_marker;
        $this->adjustCenterCoords($_marker['lon'],$_marker['lat']);
        // return index of marker
        // HACK
        return count($this->_markers) - 1;
        // HACK
    }

    // HACK
    function deleteMarkers() {
	unset ($this->_markers);
	return;
    }
    /**
     * adds a map polyline by address
     * if color, weight and opacity are not defined, use the google maps defaults
     * 
     * @param string $address1 the map address to draw from
     * @param string $address2 the map address to draw to
     * @param string $color the color of the line (format: #000000)
     * @param string $weight the weight of the line in pixels
     * @param string $opacity the line opacity (percentage)
     */
    function addPolyLineByAddress($address1,$address2,$color='',$weight=0,$opacity=0) {
        if(($_geocode1 = $this->getGeocode($address1)) === false)
            return false;
        if(($_geocode2 = $this->getGeocode($address2)) === false)
            return false;
        return $this->addPolyLineByCoords($_geocode1['lon'],$_geocode1['lat'],$_geocode2['lon'],$_geocode2['lat'],$color,$weight,$opacity);
    }

    /**
     * adds a map polyline by map coordinates
     * if color, weight and opacity are not defined, use the google maps defaults
     * 
     * @param string $lon1 the map longitude to draw from
     * @param string $lat1 the map latitude to draw from
     * @param string $lon2 the map longitude to draw to
     * @param string $lat2 the map latitude to draw to
     * @param string $color the color of the line (format: #000000)
     * @param string $weight the weight of the line in pixels
     * @param string $opacity the line opacity (percentage)
     */
    function addPolyLineByCoords($lon1,$lat1,$lon2,$lat2,$color='',$weight=0,$opacity=0) {
        $_polyline['lon1'] = $lon1;
        $_polyline['lat1'] = $lat1;
        $_polyline['lon2'] = $lon2;
        $_polyline['lat2'] = $lat2;
        $_polyline['color'] = $color;
        $_polyline['weight'] = $weight;
        $_polyline['opacity'] = $opacity;
        $this->_polylines[] = $_polyline;
        $this->adjustCenterCoords($_polyline['lon1'],$_polyline['lat1']);
        $this->adjustCenterCoords($_polyline['lon2'],$_polyline['lat2']);
        // return index of polyline
        return count($this->_polylines) - 1;
    }        
        
    /**
     * adjust map center coordinates by the given lat/lon point
     * 
     * @param string $lon the map latitude (horizontal)
     * @param string $lat the map latitude (vertical)
     */
    function adjustCenterCoords($lon,$lat) {
        if(strlen((string)$lon) == 0 || strlen((string)$lat) == 0)
            return false;
        $this->_max_lon = (float) max($lon, $this->_max_lon);
        $this->_min_lon = (float) min($lon, $this->_min_lon);
        $this->_max_lat = (float) max($lat, $this->_max_lat);
        $this->_min_lat = (float) min($lat, $this->_min_lat);
        
        $this->center_lon = (float) ($this->_min_lon + $this->_max_lon) / 2;
        $this->center_lat = (float) ($this->_min_lat + $this->_max_lat) / 2;
        return true;
    }

    /**
     * set map center coordinates to lat/lon point
     * 
     * @param string $lon the map latitude (horizontal)
     * @param string $lat the map latitude (vertical)
     */
    function setCenterCoords($lon,$lat) {
        $this->center_lat = (float) $lat;
        $this->center_lon = (float) $lon;
    }    

    /**
     * generate an array of params for a new marker icon image
     * iconShadowImage is optional
     * If anchor coords are not supplied, we use the center point of the image by default. 
     * Can be called statically. For private use by addMarkerIcon() and setMarkerIcon()
     *
     * @param string $iconImage URL to icon image
     * @param string $iconShadowImage URL to shadow image
     * @param string $iconAnchorX X coordinate for icon anchor point
     * @param string $iconAnchorY Y coordinate for icon anchor point
     * @param string $infoWindowAnchorX X coordinate for info window anchor point
     * @param string $infoWindowAnchorY Y coordinate for info window anchor point
     */
    function createMarkerIcon($iconImage,$iconShadowImage = '',$iconAnchorX = 'x',$iconAnchorY = 'x',$infoWindowAnchorX = 'x',$infoWindowAnchorY = 'x') {
        $_icon_image_path = $iconImage;

        if(!($_image_info = @getimagesize($_icon_image_path))) {
            die('GoogleMapAPI:createMarkerIcon: Error reading image [1]: ' . $iconImage);   
        }

        if($iconShadowImage) {
            $_shadow_image_path = $iconShadowImage;
            if(!($_shadow_info = @getimagesize($_shadow_image_path))) {
                die('GoogleMapAPI:createMarkerIcon: Error reading image [2]: ' . $iconShadowImage);
            }
        }
        

        if($iconAnchorX === 'x') {
            $iconAnchorX = (int) ($_image_info[0] / 2);
        }
        if($iconAnchorY === 'x') {
            $iconAnchorY = (int) ($_image_info[1] / 2);
        }
        if($infoWindowAnchorX === 'x') {
            $infoWindowAnchorX = (int) ($_image_info[0] / 2);
        }
        if($infoWindowAnchorY === 'x') {
            $infoWindowAnchorY = (int) ($_image_info[1] / 2);
        }
                        

        $icon_info = array(
                'image' => $iconImage,
                'iconWidth' => $_image_info[0],
                'iconHeight' =>  $_image_info[1],
                'iconAnchorX' => $iconAnchorX,
                'iconAnchorY' => $iconAnchorY,
                'infoWindowAnchorX' => $infoWindowAnchorX,
                'infoWindowAnchorY' => $infoWindowAnchorY
                );

/*
        if($iconShadowImage) {
            $icon_info = array_merge($icon_info, array('shadow' => $iconShadowImage,
                                                       'shadowWidth' =>  '100', $_shadow_info[0],
                                                       'shadowHeight' => '100', $_shadow_info[1]));
        }
*/

        return $icon_info;
    }
    
    /**
     * set the marker icon for ALL markers on the map
     */
    function setMarkerIcon($iconImage,$iconShadowImage = '',$iconAnchorX = 'x',$iconAnchorY = 'x',$infoWindowAnchorX = 'x',$infoWindowAnchorY = 'x') {
        $this->_icons = array($this->createMarkerIcon($iconImage,$iconShadowImage,$iconAnchorX,$iconAnchorY,$infoWindowAnchorX,$infoWindowAnchorY));
    }
    
    /**
     * add an icon to go with the correspondingly added marker
     */
    function addMarkerIcon($iconImage,$iconShadowImage = '',$iconAnchorX = 'x',$iconAnchorY = 'x',$infoWindowAnchorX = 'x',$infoWindowAnchorY = 'x') {
        $this->_icons[] = $this->createMarkerIcon($iconImage,$iconShadowImage,$iconAnchorX,$iconAnchorY,$infoWindowAnchorX,$infoWindowAnchorY);
        return count($this->_icons) - 1;
    }

 
    /**
     * print map javascript (put just before </body>, or in <header> if using onLoad())
     * 
     */
    function printMapJS() {
        echo $this->getMapJS();
    }    

    /**
     * return map javascript
     * 
     */
    function getMapJS() {
        $_output = '<script type="text/javascript" charset="utf-8">' . "\n";
        $_output .= '//<![CDATA[' . "\n";
        $_output .= "/*************************************************\n";
        $_output .= " * Created with GoogleMapAPI " . $this->_version . "\n";
        $_output .= " * Author: Monte Ohrt <monte AT ohrt DOT com>\n";
        $_output .= " * Copyright 2005-2006 New Digital Group\n";
        $_output .= " * http://www.phpinsider.com/php/code/GoogleMapAPI/\n";
        $_output .= " *************************************************/\n";
        
		$_output .= 'function fix6ToString(n) { return n.toFixed(6).toString();} ';
        $_output .= 'var points = [];' . "\n";
        $_output .= 'var markers = [];' . "\n";
        $_output .= 'var counter = 0;' . "\n";
        if($this->sidebar) {        
            $_output .= 'var sidebar_html = "";' . "\n";
            $_output .= 'var marker_html = [];' . "\n";
        }

        if(!empty($this->_icons)) {
            $_output .= 'var icon = [];' . "\n";
            for($i = 0, $j = count($this->_icons); $i<$j; $i++) {
                $info = $this->_icons[$i];

                // hash the icon data to see if we've already got this one; if so, save some javascript
                $icon_key = md5(serialize($info));
                if(!isset($exist_icn[$icon_key])) {

                    $_output .= "icon[$i] = new GIcon();\n";   
                    $_output .= sprintf('icon[%s].image = "%s";',$i,$info['image']) . "\n";   
                    if(isset($info['shadow'])) {
                        $_output .= sprintf('icon[%s].shadow = "%s";',$i,$info['shadow']) . "\n";
                        $_output .= sprintf('icon[%s].shadowSize = new GSize(%s,%s);',$i,$info['shadowWidth'],$info['shadowHeight']) . "\n";   
                    }
                    $_output .= sprintf('icon[%s].iconSize = new GSize(%s,%s);',$i,$info['iconWidth'],$info['iconHeight']) . "\n";   
                    $_output .= sprintf('icon[%s].iconAnchor = new GPoint(%s,%s);',$i,$info['iconAnchorX'],$info['iconAnchorY']) . "\n";   
                    $_output .= sprintf('icon[%s].infoWindowAnchor = new GPoint(%s,%s);',$i,$info['infoWindowAnchorX'],$info['infoWindowAnchorY']) . "\n";
                } else {
                    $_output .= "icon[$i] = icon[$exist_icn[$icon_key]];\n";
                }
            }
        }
                           
        $_output .= 'var map = null;' . "\n";
                     
        if($this->onload) {
           $_output .= 'function onLoad() {' . "\n";   
        }

        $_output .= sprintf('var mapObj = document.getElementById("%s");',$this->map_id) . "\n";
        $_output .= 'if (mapObj != "undefined" && mapObj != null) {' . "\n";
        $_output .= sprintf('map = new GMap2(document.getElementById("%s"));',$this->map_id) . "\n";
        if(isset($this->center_lat) && isset($this->center_lon)) {
			// Special care for decimal point in lon and lat, would get lost if "wrong" locale is set; applies to (s)printf only
			$_output .= sprintf('map.setCenter(new GLatLng(%s, %s), %d, %s);', number_format($this->center_lat, 6, ".", ""), number_format($this->center_lon, 6, ".", ""), $this->zoom, $this->map_type) . "\n";
        }
        
        // zoom so that all markers are in the viewport
        if($this->zoom_encompass && count($this->_markers) > 1) {
            // increase bounds by fudge factor to keep
            // markers away from the edges
            $_len_lon = $this->_max_lon - $this->_min_lon;
            $_len_lat = $this->_max_lat - $this->_min_lat;
            $this->_min_lon -= $_len_lon * $this->bounds_fudge;
            $this->_max_lon += $_len_lon * $this->bounds_fudge;
            $this->_min_lat -= $_len_lat * $this->bounds_fudge;
            $this->_max_lat += $_len_lat * $this->bounds_fudge;

            $_output .= "var bds = new GLatLngBounds(new GLatLng($this->_min_lat, $this->_min_lon), new GLatLng($this->_max_lat, $this->_max_lon));\n";
            $_output .= 'map.setZoom(map.getBoundsZoomLevel(bds));' . "\n";
        }
        
        if($this->map_controls) {
          if($this->control_size == 'large')
              $_output .= 'map.addControl(new GLargeMapControl());' . "\n";
          else
              $_output .= 'map.addControl(new GSmallMapControl());' . "\n";
        }
        if($this->type_controls) {
            $_output .= 'map.addControl(new GMapTypeControl());' . "\n";
        }
        
        if($this->scale_control) {
            $_output .= 'map.addControl(new GScaleControl());' . "\n";
        }

        if($this->overview_control) {
            $_output .= 'map.addControl(new GOverviewMapControl());' . "\n";
        }
        
		// HACK -> DRAG
		if($this->disable_drag) {
			$_output .= 'map.disableDragging();'."\n";
		}

		// HACK -> New Mupi
			$_output .= 'function mapSingleRightClick(point, src, overlay)
							{
							var point = map.fromContainerPixelToLatLng(point);
							menu(\'<a onclick="menu()">Cerrar este menú</a><hr /><b>Latidud: </b>\'+fix6ToString( point.lat() )+\'<br /> <b>Longitud: </b>\'+fix6ToString( point.lng() )+\'<hr /><a target="_blank" href="./?accion=gestionar+mupis&crear=1&lat=\'+fix6ToString( point.lat() )+\'&lng=\'+fix6ToString( point.lng() )+\'&calle=\'+$("#combo_calles").val()+\'">Crear Mupi</a><br /><a target="_blank" href="./?accion=gestionar+referencias&crear=1&lat=\'+fix6ToString( point.lat() )+\'&lng=\'+fix6ToString( point.lng() )+\'&calle=\'+$("#combo_calles").val()+\'">Crear Referencia</a>\');
							}';

			$_output .= 'GEvent.addListener(map, "singlerightclick", mapSingleRightClick);' . "\n";
		
        $_output .= $this->getAddMarkersJS();

        $_output .= $this->getPolylineJS();

        if($this->sidebar) {
		// HACK HACK
            //$_output .= sprintf('document.getElementById("%s").innerHTML = "<ul class=\"gmapSidebar\">"+ sidebar_html +"<\/ul>";', $this->sidebar_id) . "\n";
            $_output .= sprintf('document.getElementById("%s").innerHTML = "<b>Ver Eco Mupis:</b><br /><select id=\"combobox_mupis\" class=\"gmapSidebar\">"+ sidebar_html +"</select>";', 'lista_mupis') . "\n";
			$_output .= 'click_sidebar($("#combobox_mupis").val())'."\n";
			$_output .= '$("#combobox_mupis").change(function (){click_sidebar($("#combobox_mupis").val());})'."\n";
        }

        $_output .= '}' . "\n";        
       
        if($this->onload) {
           $_output .= '}' . "\n";
        }

        $_output .= $this->getCreateMarkerJS();

        // Utility functions used to distinguish between tabbed and non-tabbed info windows
        $_output .= 'function isArray(a) {return isObject(a) && a.constructor == Array;}' . "\n";
        $_output .= 'function isObject(a) {return (a && typeof a == \'object\') || isFunction(a);}' . "\n";
        $_output .= 'function isFunction(a) {return typeof a == \'function\';}' . "\n";

        if($this->sidebar) {
            $_output .= 'function click_sidebar(idx) {' . "\n";
            //$_output .= '  alert(idx);' . "\n";
            $_output .= '  if(isArray(marker_html[idx])) { markers[idx].openInfoWindowTabsHtml(marker_html[idx]); }' . "\n";
            $_output .= '  else { markers[idx].openInfoWindowHtml(marker_html[idx]); }' . "\n";
			$_output .= '  GEvent.trigger(markers[idx],"'.$this->window_trigger.'");' . "\n";			
            $_output .= '}' . "\n";
        }
/*
        $_output .= 'function showInfoWindow(idx,html) {' . "\n";
        $_output .= 'map.centerAtLatLng(points[idx]);' . "\n";
        $_output .= 'markers[idx].openInfoWindowHtml(html);' . "\n";
        $_output .= '}' . "\n";
*/
        $_output .= '//]]>' . "\n";
        $_output .= '</script>' . "\n";
        return $_output;
    }

    /**
     * overridable function for generating js to add markers
     */
    function getAddMarkersJS() {
        $SINGLE_TAB_WIDTH = 88;    // constant: width in pixels of each tab heading (set by google)
        $i = 0;
        $_output = '';
        foreach($this->_markers as $_marker) {
            if(is_array($_marker['html'])) {
                // warning: you can't have two tabs with the same header. but why would you want to?
                $ti = 0;
                $num_tabs = count($_marker['html']);
                $tab_obs = array();
                foreach($_marker['html'] as $tab => $info) {
                    if($ti == 0 && $num_tabs > 2) {
                        $width_style = sprintf(' style=\"width: %spx\"', $num_tabs * $SINGLE_TAB_WIDTH);
                    } else {
                        $width_style = '';
                    }
                    $tab = str_replace('"','\"',$tab);
                    $info = str_replace('"','\"',$info);
					$info = str_replace(array("\n", "\r"), "", $info);
                    $tab_obs[] = sprintf('new GInfoWindowTab("%s", "%s")', $tab, '<div id=\"gmapmarker\"'.$width_style.'>' . $info . '</div>');
                    $ti++;
                }
                $iw_html = '[' . join(',',$tab_obs) . ']';
            } else {
                $iw_html = sprintf('"%s"',str_replace('"','\"','<div id="gmapmarker">' . str_replace(array("\n", "\r"), "", $_marker['html']) . '</div>'));
            }
	    // HACK ID
            $_output .= sprintf('var point = new GLatLng(%s,%s);',$_marker['lat'],$_marker['lon']) . "\n";         
            $_output .= sprintf('var marker = createMarker(point,"%s",%s, %s,"%s","%s","%s");',
                                str_replace('"','\"',$_marker['title']),
                                str_replace('/','\/',$iw_html),
                                $i,
                                str_replace('"','\"',$_marker['tooltip']),
								$_marker['id'],
								$_marker['html_pedidos']) . "\n";
            //TODO: in above createMarker call, pass the index of the tab in which to put directions, if applicable
            $_output .= 'map.addOverlay(marker);' . "\n";
            $i++;
        }
        return $_output;
    }

    /**
     * overridable function to generate polyline js
     */
    function getPolylineJS() {
        $_output = '';
        foreach($this->_polylines as $_polyline) {
            $_output .= sprintf('var polyline = new GPolyline([new GLatLng(%s,%s),new GLatLng(%s,%s)],"%s",%s,%s);',
                    $_polyline['lat1'],$_polyline['lon1'],$_polyline['lat2'],$_polyline['lon2'],$_polyline['color'],$_polyline['weight'],$_polyline['opacity'] / 100.0) . "\n";
            $_output .= 'map.addOverlay(polyline);' . "\n";
        }
        return $_output;
    }

    /**
     * overridable function to generate the js for the js function for creating a marker.
     */
     // IMPORTANTE!, acá tiene que ir el hack mayor!.
    function getCreateMarkerJS() {
        $_SCRIPT_ = '$("#datos_mupis").load(\'contenido/mupis+ubicaciones+dinamico.php?accion=mupi&MUPI=\'+id);';
        $_output = '';
		$_output .= 'function createMarker(point, title, html, n, tooltip, id, html_pedidos) {' . "\n";
        $_output .= 'if(n >= '. sizeof($this->_icons) .') { n = '. (sizeof($this->_icons) - 1) ."; }\n";
        if(!empty($this->_icons)) {
            $_output .= 'var marker = new GMarker(point,{\'icon\': icon[n], \'title\': tooltip, \'draggable\': true, \'bouncy\': false});' . "\n";
        } else {
            $_output .= 'var marker = new GMarker(point,{\'title\': tooltip});' . "\n";
        }
        
        if($this->info_window) {
			$_output .= 'if (id.indexOf(\'REF\') == -1) {' . "\n";
			$_output .= 'GEvent.addListener(marker, "'.$this->window_trigger.'", function() { '.$_SCRIPT_.'; marker.openInfoWindowHtml(html,{\'maxTitle\': \'Edición de pedidos\', \'maxContent\': html_pedidos}) });' . "\n";
			$_output .= 'GEvent.addListener(marker, "infowindowclose", function() { $("#datos_mupis").html(""); });' . "\n";
			$_output .= '}' . "\n";
			}
			if (!$this->disable_drag) {
			$_output .= 'GEvent.addListener(marker, "dragstart", function() { map.closeInfoWindow();});' . "\n";
			//$_output .= 'GEvent.addListener(marker, "dragend", function() { var point = marker.getPoint(); alert( id + \' \' + fix6ToString( point.lat() ) + \',\' + fix6ToString( point.lng() ) ); $("#datos_mupis").load(\'contenido/mupis+ubicaciones+dinamico.php?accion=drag&id=\'+id+\'&lat=\'+fix6ToString( point.lat() )+\'&lng=\'+fix6ToString( point.lng() )); });' . "\n";
			$_output .= 'GEvent.addListener(marker, "dragend", function() { var point = marker.getPoint(); $("#datos_mupis").load(\'contenido/mupis+ubicaciones+dinamico.php?accion=drag&id=\'+id+\'&lat=\'+fix6ToString( point.lat() )+\'&lng=\'+fix6ToString( point.lng() )); });' . "\n";
			}
        $_output .= 'points[counter] = point;' . "\n";
        $_output .= 'markers[counter] = marker;' . "\n";
        if($this->sidebar) {
			$_output .= 'if (id.indexOf(\'REF\') == -1) {' . "\n";
            $_output .= 'marker_html[counter] = html;' . "\n";
            $_output .= 'sidebar_html += \'<option class="gmapSidebarItem" id="gmapSidebarItem" value="\'+counter+\'">\' + title + \'</option>\';' . "\n";
			$_output .= '}' . "\n";
        }
        $_output .= 'counter++;' . "\n";
        $_output .= 'return marker;' . "\n";
        $_output .= '}' . "\n";
        return $_output;
    }

    /**
     * print map (put at location map will appear)
     * 
     */
    function printMap() {
        echo $this->getMap();
    }

    /**
     * return map
     * 
     */
    function getMap() {
	$_output = '';
        if(strlen($this->width) > 0 && strlen($this->height) > 0) {
            $_output .= sprintf('<div id="%s" style="width: %s; height: %s"></div>',$this->map_id,$this->width,$this->height) . "\n";
        } else {
            $_output .= sprintf('<div id="%s"></div>;',$this->map_id) . "\n";     
        }
        return $_output;
    }

    
    /**
     * print sidebar (put at location sidebar will appear)
     * 
     */
    function printSidebar() {
        echo $this->getSidebar();
    }    

    /**
     * return sidebar html
     * 
     */
    function getSidebar() {
        return sprintf('<div id="%s"></div>',$this->sidebar_id) . "\n";
    }    
            
    /**
     * get the geocode lat/lon points from given address
     * look in cache first, otherwise get from Yahoo
     * 
     * @param string $address
     */
    function getGeocode($address) {
        if(empty($address))
            return false;

        $_geocode = false;

        if(($_geocode = $this->getCache($address)) === false) {
            if(($_geocode = $this->geoGetCoords($address)) !== false) {
                $this->putCache($address, $_geocode['lon'], $_geocode['lat']);
            }
        }
        
        return $_geocode;
    }
   
    /**
     * get the geocode lat/lon points from cache for given address
     * 
     * @param string $address
     */
    function getCache($address) {
        if(!isset($this->dsn))
            return false;
        
        $_ret = array();
        
        // PEAR DB
        require_once('DB.php');          
        $_db =& DB::connect($this->dsn);
        if (PEAR::isError($_db)) {
            die($_db->getMessage());
        }
		$_res =& $_db->query("SELECT lon,lat FROM {$this->_db_cache_table} where address = ?", $address);
        if (PEAR::isError($_res)) {
            die($_res->getMessage());
        }
        if($_row = $_res->fetchRow()) {            
            $_ret['lon'] = $_row[0];
            $_ret['lat'] = $_row[1];
        }
        
        $_db->disconnect();
        
        return !empty($_ret) ? $_ret : false;
    }
    
    /**
     * put the geocode lat/lon points into cache for given address
     * 
     * @param string $address
     * @param string $lon the map latitude (horizontal)
     * @param string $lat the map latitude (vertical)
     */
    function putCache($address, $lon, $lat) {
        if(!isset($this->dsn) || (strlen($address) == 0 || strlen($lon) == 0 || strlen($lat) == 0))
           return false;
        // PEAR DB
        require_once('DB.php');          
        $_db =& DB::connect($this->dsn);
        if (PEAR::isError($_db)) {
            die($_db->getMessage());
        }
        
        $_res =& $_db->query('insert into '.$this->_db_cache_table.' values (?, ?, ?)', array($address, $lon, $lat));
        if (PEAR::isError($_res)) {
            die($_res->getMessage());
        }
        $_db->disconnect();
        
        return true;
        
    }
   
    /**
     * get geocode lat/lon points for given address from Yahoo
     * 
     * @param string $address
     */
    function geoGetCoords($address,$depth=0) {
        
        switch($this->lookup_service) {
                        
            case 'GOOGLE':
                
                $_url = sprintf('http://%s/maps/geo?&q=%s&output=csv&key=%s',$this->lookup_server['GOOGLE'],rawurlencode($address),$this->api_key);

                $_result = false;
                
                if($_result = $this->fetchURL($_url)) {

                    $_result_parts = explode(',',$_result);
                    if($_result_parts[0] != 200)
                        return false;
                    $_coords['lat'] = $_result_parts[2];
                    $_coords['lon'] = $_result_parts[3];
                }
                
                break;
            
            case 'YAHOO':
            default:
                        
                $_url = 'http://%s/MapsService/V1/geocode';
                $_url .= sprintf('?appid=%s&location=%s',$this->lookup_server['YAHOO'],$this->app_id,rawurlencode($address));

                $_result = false;

                if($_result = $this->fetchURL($_url)) {

                    preg_match('!<Latitude>(.*)</Latitude><Longitude>(.*)</Longitude>!U', $_result, $_match);

                    $_coords['lon'] = $_match[2];
                    $_coords['lat'] = $_match[1];

                }
                
                break;
        }         
        
        return $_coords;       
    }
    
    

    /**
     * fetch a URL. Override this method to change the way URLs are fetched.
     * 
     * @param string $url
     */
    function fetchURL($url) {

        return file_get_contents($url);

    }

    /**
     * get distance between to geocoords using great circle distance formula
     * 
     * @param float $lat1
     * @param float $lat2
     * @param float $lon1
     * @param float $lon2
     * @param float $unit   M=miles, K=kilometers, N=nautical miles, I=inches, F=feet
     */
    function geoGetDistance($lat1,$lon1,$lat2,$lon2,$unit='K') {
        
      // calculate miles
      $M =  69.09 * rad2deg(acos(sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lon1 - $lon2)))); 

      switch(strtoupper($unit))
      {
        case 'K':
          // kilometers
          return $M * 1.609344;
          break;
        case 'N':
          // nautical miles
          return $M * 0.868976242;
          break;
        case 'F':
          // feet
          return $M * 5280;
          break;            
        case 'I':
          // inches
          return $M * 63360;
          break;            
        case 'M':
        default:
          // miles
          return $M;
          break;
      }
      
    }    
    
}    
?>
