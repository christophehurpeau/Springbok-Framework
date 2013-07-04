<?php
class HPiwik{
	/**
	 * piwikLink: example.com/piwik.php
	 */
	public static function tracker($piwikLink,$siteId=1){
		return HHtml::jsInline('var _paq=_paq||[];_paq.push(["trackPageView"]);_paq.push(["enableLinkTracking"]);'
			.'(function(d){var g,s,u=("https:"===document.location.protocol?"https":"http")+"://'.$piwikLink.'";'
			.'_paq.push(["setTrackerUrl",u]);_paq.push(["setSiteId",'.$siteId.']);'
			.'g=d.createElement("script");s=d.getElementsByTagName("script")[0];g.type="text/javascript";'
			.'g.defer=true; g.async=true; g.src=u; s.parentNode.insertBefore(g,s);'
			.'})(document)')
		.'<noscript><p><img src="http://'.$piwikLink.'?idsite='.$siteId.'" style="border:0" alt=""/></p></noscript>';
	}
	
	/**
	 * cf. http://www.statstory.com/multiple-trackers-in-google-analytics-piwik/
	 */
	public static function multiTracker($piwikLink,$siteIds){
		$paq="";		
		foreach($siteIds as $key => $value) {
			$paq.='var _paq_'.$key.'=_paq_'.$key.'||[];_paq_'.$key.'.push(["trackPageView"]);_paq_'.$key.'.push(["enableLinkTracking"]);';
		}		
		$paq.='(function(d){var g,s,u=("https:"===document.location.protocol?"https":"http")+"://'.$piwikLink.'";';
		foreach($siteIds as $key => $value) {
			$paq.='_paq_'.$key.'.push(["setTrackerUrl",u]);_paq_'.$key.'.push(["setSiteId",'.$value.']);';
		}
		$paq.='g=d.createElement("script");s=d.getElementsByTagName("script")[0];g.type="text/javascript";'
		.'g.defer=true; g.async=true; g.src=u; s.parentNode.insertBefore(g,s);'
		.'})(document)';
		
		return HHtml::jsInline($paq);
	}
}
