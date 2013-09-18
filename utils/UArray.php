<?php
/**
 * Utils for arrays
 */
class UArray {

	/**
	 * Explode any single-dimensional array into a full blown tree structure,
	 * based on the delimiters found in it's keys.
	 *
	 * @author  Kevin van Zonneveld<kevin@vanzonneveld.net>
	 * @author  Lachlan Donald
	 * @author  Takkie
	 * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
	 * @version   SVN: Release: $Id: explodeTree.inc.php 89 2008-09-05 20:52:48Z kevin $
	 * @link	http://kevin.vanzonneveld.net/
	 * @link		http://kevin.vanzonneveld.net/techblog/article/convert_anything_to_tree_structures_in_php/
	 *
	 * @param array   $array
	 * @param string  $delimiter
	 * @param boolean $baseval
	 *
	 * @return array
	 */
	public static function explodeTree($array, $delimiter ='_', $baseval =false) {
		if(!is_array($array)) return false;
		$splitRE='/'.preg_quote($delimiter,'/').'/';
		$returnArr=array();
		foreach($array as $key=>$val) {
			// Get parent parts and the current leaf
			$parts=preg_split($splitRE, $key, -1, PREG_SPLIT_NO_EMPTY);
			$leafPart=array_pop($parts);

			// Build parent structure
			// Might be slow for really deep and large structures
			$parentArr=&$returnArr;
			foreach($parts as $part) {
				if(!isset($parentArr[$part])){
					$parentArr[$part] = array();
				}elseif(!is_array($parentArr[$part])){
					if($baseval)
						$parentArr[$part] = array('__base_val' => $parentArr[$part]);
					else
						$parentArr[$part] = array();
				}
				$parentArr = &$parentArr[$part];
			}

			// Add the final part to the structure
			if(empty($parentArr[$leafPart]))
				$parentArr[$leafPart] = $val;
			elseif($baseval && is_array($parentArr[$leafPart]))
				$parentArr[$leafPart]['__base_val'] = $val;
		}
		return $returnArr;
	}

	/**
	 * Create a tree from a list of elements with ids
	 * 
	 * @return array
	 */
	public static function createTree(&$list, $parent){
		$tree = array();
		foreach ($parent as $k=>$l){
			if(isset($list[$l['id']])){
				$l['children'] = createTree($list, $list[$l['id']]);
			}
			$tree[] = $l;
		}
		return $tree;
	}
	
	/**
	 * @param array
	 * @param array
	 * @param array
	 */
	public static function permutations($items,&$result,$perms=array()){
		if(empty($items)){
			$result[]=$perms;
		}else{
			for($i=count($items)-1;$i>=0;--$i){
				$newitems=$items;
				$newperms=$perms;
				list($foo) = array_splice($newitems,$i,1);
				array_unshift($newperms,$foo);
				self::permutations($newitems,$result,$newperms);
			}
		}
	}
	
	/**
	 * use :
	 * <code>
	 * $searchWordsCombinaisons = array();
	 * for($i=2; $i <= $searchWordsCount; $i++){
	 * 	$tmp = array();
	 * 	UArray::combinaisons($searchWords,$tmp,array(),$i);
	 * 	$searchWordsCombinaisons[$searchWordsCount-$i] = $tmp;
	 * }
	 * ksort($searchWordsCombinaisons);
	 * </code>
	 * @param array
	 * @param array
	 * @param array
	 * @param int
	 */
	public static function combinaisons($items,&$result,$tempResult,$deep) {
		if($deep == 0) {
			$result[]=$tempResult;
			return;
		}
		$i=1;
		foreach($items as &$item){
			$tempResult2=$tempResult; $tempResult2[]=&$item;
			self::combinaisons(array_slice($items,$i++),$result,$tempResult2,$deep-1);
		}
	}
	
	public static function union_recursive($array1,$array2){
		foreach($array2 as $key=>&$value){
			if(isset($array1[$key])){
				if(is_array($value))
					$array1[$key] = self::union_recursive($array1[$key],$value);
			}else $array1[$key]=$value;
		}
		return $array1;
	}
	
	/**
	 * natural sort by keys
	 * 
	 * @param array
	 * @return bool
	 */
	public static function knatsort(&$karr){
		$kkeyarr = array_keys($karr);
		natsort($kkeyarr);
		$ksortedarr=array();
		foreach($kkeyarr as $kcurrkey)
			$ksortedarr[$kcurrkey] = $karr[$kcurrkey];
		$karr = $ksortedarr;
		return true;
	}
	
	/**
	 * Find a key the value of a property : a[key][propName] === value
	 * 
	 * <code>
	 * $array = array(
	 * 	'a'=>array('prop'=>'test'),
	 * 	'b'=>array('prop'=>'test2')
	 * );
	 * assert(UArray::findKeyBy($array,'prop','test') === 'a');
	 * </code>
	 * 
	 * @param array
	 * @param mixed
	 * @param mixed
	 * @return mixed false if not found or the key
	 */
	public static function findKeyBy($a,$propName,$val){
		foreach($a as $k=>$v){
			if($v[$propName] == $val) return $k;
		}
		return false;
	}
	
	/**
	 * Add elements in the middle of an array using the + operator (! with the keys)
	 * 
	 * @param array
	 * @param int Index at which to start changing the array
	 * @param array values to add
	 * @return array
	 */
	public static function splice($array,$offset,$values){
		return array_slice($array,0,$offset,true) + $values + array_slice($array,$offset,null,true);
	}
	
	/**
	 * Return if an array as a value among an array of values
	 * 
	 * @param array
	 * @param array
	 * @return bool
	 */
	public static function hasAmong($array1,$array2){
		foreach($array1 as $v1){
			if(in_array($v1,$array2)) return true;
		}
		return false;
	}
	
	/**
	 * return the first value of an array or false if the array is empty
	 * @return mixed 
	 */
	public static function firstValue($array){
		foreach($array as $elt) return $elt;
		return false;
	}
	
	/**
	 * Call the callback for each elements in the array and set the result into a new array
	 * 
	 * @param array
	 * @param callable a callback
	 * @return array
	 */
	public static function map($array,$callback){
		$newArray = array();
		foreach($array as $key=>$value)
			$newArray[$key] = $callback($key,$value);
		return $newArray;
	}
}