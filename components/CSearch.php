<?php
class SearchException extends Exception{}

/**
 * Search component
 * 
 * @see CSearchResult
 * @see CSearchResultSuggestionnable
 */
class CSearch{
	public static $NB_RESULTS_PER_PAGE=10;
	
	private
		$searchWords,$searchWordsCount=0,$searchWordsInCondition,
		$finalSearchWords=array(),$foundKeywordIds=array(),
		$searchWordsPermutations,$searchWordsCombinaisons,$searchWordsCombinaisonsPermutations,
		
		$firstWords;
	
	
	public function set($query){
		$this->query($query);
		if($this->isEmpty()) throw new SearchException('Please refine your search');
	}
	
	public function isEmpty(){
		return $this->searchWords===false && !$this->hasFoundKeywords();
	}
	
	/** @return bool */
	public function hasSearchWords(){return $this->searchWords!==false;}
	/** @return bool */
	public function hasFoundKeywords(){ return !empty($this->foundKeywordIds); }
	
	/** @return array */
	public static function listKeywordIds($phraseCleaned){
		return SearchablesKeyword::listKeywordIds($phraseCleaned);
	}
	
	public function query($query){
		if(empty($query)){
			$this->searchWords=false;
			$this->searchWordsCount=0;
			return;
		}
		
		$phraseCleaned=SearchablesKeyword::cleanPhrase($query);
		$this->foundKeywordIds=static::listKeywordIds($phraseCleaned);
		
		$words=$this->searchWords=explode(' ',$phraseCleaned);
		
		
		if(empty($this->searchWords)) $this->searchWords=$this->whatWhoWords=false;
		else{
			$this->searchWords=array_unique($this->searchWords);
			$this->searchWordsCount=count($this->searchWords);
			
			if($this->searchWordsCount > 1 && $this->searchWordsCount < 4){
				$this->searchWordsPermutations=array(); UArray::permutations($this->searchWords,$this->searchWordsPermutations);
				unset($this->searchWordsPermutations[0]); // permutation d'origine
				
				
				$this->searchWordsCombinaisons=array();
				for($i=2;$i<=$this->searchWordsCount;$i++){
					$tmp=array();
					UArray::combinaisons($this->searchWords,$tmp,array(),$i);
					$this->searchWordsCombinaisons[$this->searchWordsCount-$i]=$tmp;
				}
				ksort($this->searchWordsCombinaisons);
				
				$this->searchWordsCombinaisonsPermutations=array();
				foreach($this->searchWordsCombinaisons as $key1=>&$array){
					$this->searchWordsCombinaisonsPermutations[$key1]=array();
					foreach($array as $key2=>&$a){
						$res=array();
						UArray::permutations($a,$res);
						$this->searchWordsCombinaisonsPermutations[$key1]=array_merge($this->searchWordsCombinaisonsPermutations[$key1],$res);
					}
				}
			}
			
			if(empty($this->foundKeywordIds)) $this->searchWordsInCondition=$this->searchWords;
			else{
				$this->searchWordsInCondition=array();
				foreach($this->searchWords as $word)
					if(!SearchablesTerm::QExist()->with('SearchablesKeywordTerm')->where(array('skt.keyword_id'=>$this->foundKeywordIds,SearchablesTerm::dbEscape(' '.$word.' ').' LIKE CONCAT("% ",st.term," %") ')))
						$this->searchWordsInCondition[]=$word;
				
			}
		}
	}


	/** @return CSearchResult */
	public function findResults(){
		$pagination=$this->createPagination();
		return new CSearchResult($this,$pagination);
	}
	
	
	/** @return CPagination */
	public function createPagination(){
		$query=static::createQuery()->calcFoundRows();
		
		if($this->searchWords===false) $sqlScore='(0)'; //espace nécessaire sinon considéré comme un int
		else{
			$db=&Searchable::$__modelDb;
			
			$sqlScore='CASE';
			$score=0;
			
			if($this->firstWords!==null){
				$suffix=$this->searchWordsCount===1 ? $this->searchWords[0] : '';
				$sqlScore.=' WHEN sb.normalized LIKE '.implode(' OR sb.normalized LIKE ',array_map(function(&$firstWord) use(&$suffix){return Searchable::dbEscape($firstWord.' %'.$suffix);},$this->firstWords)).' THEN '.($score++);
				if($this->searchWordsCount===1) $sqlScore.=' WHEN sb.normalized LIKE '.implode(' OR sb.normalized LIKE ',array_map(function(&$firstWord) use(&$suffix){return Searchable::dbEscape('%'.$firstWord.' %'.$suffix);},$this->firstWords)).' THEN '.($score++);
			}
			
			if($this->searchWordsCount>1){
				// Les résultats contenant tous les termes de [QQ] dans l'ordre
				$sqlScore.=' WHEN sb.normalized LIKE '.$db->escape(implode(' ',$this->searchWords)).' THEN '.($score++);
				
				
				// Les résultats contenant tous les termes de [QQ] dans le désordre
				if(!empty($this->searchWordsPermutations)){
					$sqlScore.=' WHEN ';
					foreach($this->searchWordsPermutations as &$perm){
						$param=$db->escape(implode(' ',$perm));
						$sqlScore.='sb.name LIKE '.$param.' OR ';
					}
					$sqlScore=substr($sqlScore,0,-3).'THEN '.($score++);
				}

				//Les résultats strictement égaux aux termes de [QQ] dans l’ordre de saisie avec d'autres
				$sqlScore.=' WHEN sb.normalized LIKE '.$db->escape('%'.implode('%',$this->searchWords).'%').' THEN '.($score++);;
				
				// Les résultats contenant tous les termes de [QQ] et d'autres dans le désordre
				if(!empty($this->searchWordsPermutations)){
					$sqlScore.=' WHEN ';
					foreach($this->searchWordsPermutations as &$perm)
						$sqlScore.='sb.normalized LIKE '.$db->escape('%'.implode('%',$perm).'%').' OR ';
					$sqlScore=substr($sqlScore,0,-3).'THEN '.($score++);
				}
			}
			
			if($this->firstWords===null){
				//Les résultats commençant par exactement par les premiers termes saisis mais ne contenant pas tous les termes
				$i=min($this->searchWordsCount,4)+1;
				while(--$i>0)
					$sqlScore.=' WHEN sb.normalized LIKE '.$db->escape(implode(' ',array_slice($this->searchWords,0,$i)).'%').' THEN '.($score++);
			}
			
			$sqlScore.=' ELSE '.($score++).' END ';
			
			
			$wordsId=SearchablesWord::QValues()->fields('id')->where(array('word LIKE'=>$this->searchWords));
			if(!empty($wordsId)){
				$countWordsId=count($wordsId);
				if(empty($this->foundKeywordIds)){
					$addSqlScoreWords=$countWordsId!==1;
					$query->withForce('SearchableWord')->addCondition('sw.word_id',$wordsId)->groupBy('sb.id');
				}else{
					$addSqlScoreWords=true;
					$query->withForce('SearchableWord',array('onConditions'=>array('sw.word_id'=>$wordsId)))->groupBy('sb.id');
					if(!empty($this->searchWordsInCondition)){
						$wordConditionIds=SearchablesWord::QValues()->fields('id')->where(array('word LIKE'=>$this->searchWordsInCondition));
						if(!empty($wordConditionIds)) $query->withForce('SearchableWord',array('alias'=>'swcond'))->addCondition('swcond.word_id',$wordConditionIds);
					}
				}
				$sqlScore.='+'.$countWordsId.'-COUNT(DISTINCT sw.word_id)';
			}
		}
		
		
		if(!empty($this->score)) $sqlScore.='+'.implode('+',$this->score);
		if(!empty($this->foundKeywordIds)){
			$countFoundKeywordsIds=count($this->foundKeywordIds);
			$sqlScore.='+'.$countFoundKeywordsIds.'-COUNT(DISTINCT skwd.keyword_id)';
		}
		
		$query->addFieldWithAlias($sqlScore,'score');
		
		if(!empty($this->foundKeywordIds)){
			$query->withForce('SearchableKeyword',array('alias'=>'skwd'))->addCondition('skwd.keyword_id',$this->foundKeywordIds)->groupBy('sb.id');
		}
		
		if(!empty($this->searchWordsInCondition) && empty($wordsId)){
			// should search anything now
			//if($exactName) $query->addCondition('op.last_name LIKE',$implodedSearchWords);
			//elseif($exactFullName) $query->addCondition('normalized LIKE',$implodedSearchWords);
			/*else */$query->addCondition('sb.normalized LIKE',array_map(function(&$word){return '%'.$word.'%';},$this->searchWordsInCondition));
		}
		
		
		$query->orderBy(array('score'));
		
		return $query->paginate()->pageSize(self::$NB_RESULTS_PER_PAGE);
	}
	
	/** @return QAll */
	protected static function createQuery(){
		return /**/Searchable::QAll()->addCondition('sb.visible',true);
	}

}