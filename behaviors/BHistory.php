<?php
/**
 * Follow history of model events
 * 
 * Auto set on insert and update the fields : owner,created_by ; created_source,created_from ; updated_by ; updated_source,updated_from
 * 
 * 
 * @method void static addHistory() addHistory(int $objId,int $type,int $relId=null,int $userId=true,int $source=null)
 * @method string detailOperation()
 * 
 */
trait BHistory{
}