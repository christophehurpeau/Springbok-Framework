<?php
class BSlugRedirectable_build{
	public static $beforeUpdate=array('_setOldSlug');
	public static $afterUpdate=array('_addSlugRedirect');
}