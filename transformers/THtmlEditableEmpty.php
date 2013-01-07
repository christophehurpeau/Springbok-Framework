<?php
class THtmlEditableEmpty extends THtmlEditable{
	public function isEditable($field,$value,$obj){
		return empty($value);
	}
}