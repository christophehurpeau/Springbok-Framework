<?php
/**
 * A QueryAll Iterator
 * 
 */
class QFindAllIterator implements Iterator{
	private $query,$start,$size,$limit,$currentIterator,$i=0,
			$key,$current;
	
	public function __construct($query,$size=50,$limit=false){
		$this->query=$query;
		$this->size=$size;
		$this->limit=$limit;
	}
	
	public function execute(){
		display('execute: '.$this->start.' '.$this->limit);
		if($this->limit <= $this->start) return $this->currentIterator=false;
		$res=$this->query->limit($this->size,$this->start)->reexecute();
		display('execute res: '.count($res));
		$this->currentIterator=$res===false?false:new ArrayIterator($res);
	}
	
	public function rewind(){
		display('rewind');
		$this->start=0;
		$this->execute();
	}
	
	public function valid(){
		display('valid: '.json_encode($this->currentIterator !== false && $this->currentIterator->valid()));
		if($this->currentIterator===false || !$this->currentIterator->valid()) return false;
		$this->key=$this->currentIterator->key();
		$this->current=$this->currentIterator->current();
		display('key: '.$this->key);
	}
	
	public function next(){
		display('next');
		if(++$this->i!==$this->size)
			return $this->currentIterator->next();
		
		$this->start+=$this->size;
		$this->execute();
	}
	
	
	public function key(){ return $this->key; }
	public function current(){ return $this->current; }
}

/*
 * while($iterator->valid()) {
	echo $iterator->key() . ' => ' . $iterator->current() . "\n";

	$iterator->next();
}
 */