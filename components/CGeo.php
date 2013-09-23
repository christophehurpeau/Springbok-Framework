<?php
/** Geographic Coordinates utils */
class CGeo{
	/**
	 * Geocode an address using Google Maps API
	 * 
	 * @return array
	 */
	public static function googleMapGeocoding($address,$type=false,$near=false){
		$res=json_decode(file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address='.urlencode($address)));
		if(!empty($res->results)) foreach($res->results as $result){
			if(!$type || in_array($type,$result->types)){
				if(!$near || self::distance(array('lat'=>$result->geometry->location->lat,'lng'=>$result->geometry->location->lng),$near) < 5)
					return $result->geometry->location;
			}
		}
		return NULL;
	}
	
	/**
	 * Compute a distance between 2 coordinates
	 * 
	 * @param array ['lat'=>,'lng'=>]
	 * @param array ['lat'=>,'lng'=>]
	 * @param float
	 */
	public static function distance($latLng,$latLng2){
		return round(((acos(sin($latLng['lat'] * pi() / 180) * sin($latLng2['lat'] * pi() / 180) + cos($latLng['lat'] * pi() / 180) * cos($latLng2['lat'] * pi() / 180) * cos(($latLng['lng'] - $latLng2['lng']) * pi() / 180)) * 180 / pi()) * /* EVAL 60*1.1515*1.609344 /EVAL */0),2);
	}
	
	/**
	 * Returns MySQL expression to compute distance in kilometers
	 * 
	 * @param float|string latitude
	 * @param float|string longitude
	 * @param float|string latitude
	 * @param float|string longitude
	 * @return string 
	 */
	public static function mysqlDistanceKm($lat1,$long1,$lat2='c.latitude',$long2='c.longitude'){
		return 'round(((ACOS(SIN('.$lat2.'*PI()/180)*SIN('.$lat1.'*PI()/180)+COS('.$lat2.'*PI()/180)*COS('.$lat1.'*PI()/180)*COS(('.$long2.'-'.$long1.')*PI()/180))*180/PI())*/* EVAL 60*1.1515*1.609344 /EVAL */),2)';
	}

	/**
	 * Returns MySQL expression to compute distance
	 * 
	 * @param float|string latitude
	 * @param float|string longitude
	 * @param float|string latitude
	 * @param float|string longitude
	 * @return string 
	 */
	public static function mysqlDistanceCoord($lat1,$long1,$lat2='c.latitude',$long2='c.longitude'){
		return 'POW(('.$lat2.'-'.$lat1.'),2 )+POW(('.$long2.'-'.$long1.'),2)';
	}
	
	
	const EARTH_RADIUS=6371;
	
	/**
	 * Computes the bounding coordinates of all points on the surface
	 * of a sphere that have a great circle distance to the point represented
	 * by the latitude and longitude parameters that is less or equal to the distance
	 * argument.
	 * For more information about the formulae used in this method visit
	 * http://JanMatuschek.de/LatitudeLongitudeBoundingCoordinates
	 *
	 * @param latitude latitude in degrees
	 * @param longitude longitude in degrees
	 * @param distance the distance in Km from the point represented by the two previous arguments
	 * @return an array containing the minimum and maximum latitude and longitude which define the
	 * bounding coordinates.
	 */
	public static function boundingCoordinates($latitude, $longitude, $distance){
		$pi = pi();
		$_minLat=-$pi*0.5;
		$_maxLat=$pi*0.5;
		$_minLon=-$pi;
		$_maxLon=$pi;
		
		// angular distance in radians on a great circle
		$radDist = $distance / CGeo::EARTH_RADIUS;
		
		/*  latitude & longitude in radians*/
		$radLat= deg2rad($latitude);/* = $point['lat']* pi() / 180 */
		$radLon= deg2rad($longitude);
		
		$minLat = $radLat - $radDist;
		$maxLat = $radLat + $radDist;
		
		//double minLon, maxLon;
		if ($minLat > $_minLat && $maxLat < $_maxLat) {
			$deltaLon =  asin(sin($radDist) / cos($radLat));
			$minLon = $radLon - $deltaLon;
			if ($minLon < $_minLon)
				$minLon += 2 * $pi;
			$maxLon = $radLon + $deltaLon;
			if ($maxLon > $_maxLon)
				$maxLon -= 2 * $pi;
		}
		else {
			// a pole is within the distance
			$minLat = max($minLat, $_minLat);
			$maxLat = min($maxLat, $_maxLat);
			$minLon = $_minLon;
			$maxLon = $_maxLon;
		}
		return array(	'minLat'=>rad2deg($minLat),
						'maxLat'=>rad2deg($maxLat),
						'minLon'=>rad2deg($minLon),
						'maxLon'=>rad2deg($maxLon));
	}
}