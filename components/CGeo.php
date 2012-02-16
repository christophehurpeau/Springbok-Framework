<?php
class CGeo{
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
	
	public static function distance($latLng,$latLng2){
		return round(((acos(sin($latLng['lat'] * pi() / 180) * sin($latLng2['lat'] * pi() / 180) + cos($latLng['lat'] * pi() / 180) * cos($latLng2['lat'] * pi() / 180) * cos(($latLng['lng'] - $latLng2['lng']) * pi() / 180)) * 180 / pi()) * 60 * 1.1515 * 1.609344),2);
	}
}