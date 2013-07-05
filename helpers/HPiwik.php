<?php
class HPiwik{
	/**
	 * piwikLink: example.com/piwik.php
	 */
	public static function tracker($piwikLink,$siteId=1){
		return HHtml::jsInline('var _paq=_paq||[];_paq.push(["trackPageView"]);_paq.push(["enableLinkTracking"]);'
			.'(function(d,t){var g,s,u=("https:"===d.location.protocol?"https":"http")+"://'.$piwikLink.'";'
			.'_paq.push(["setTrackerUrl",u+"piwik.php"]);_paq.push(["setSiteId",'.$siteId.']);'
			.'g=d.createElement(t);s=d.getElementsByTagName(t)[0];g.type="text/javascript";'
			.'g.defer=true;g.async=true;g.src=u+"piwik.js";s.parentNode.insertBefore(g,s);'
			.'})(document,"script")')
		.'<noscript><p><img src="http://'.$piwikLink.'?idsite='.$siteId.'" style="border:0" alt=""/></p></noscript>';
	}
	
	/**
	 * cf. http://www.statstory.com/multiple-trackers-in-google-analytics-piwik/
	 */
	public static function multiTracker($piwikLink,$siteIds){
		$s='var u=("https:"===document.location.protocol?"https":"http")+"://'.$piwikLink.'";document.write(unescape("%3Cscript src=" + u + "piwik.js type=\"text/javascript\"%3E%3C/script%3E"));';
		$s2='try{';
		foreach($siteIds as $siteId) {
			$s2.='var t_'.$siteId.'=Piwik.getTracker(u+"piwik.php",'.$siteId.');t_'.$siteId.'.trackPageView();t_'.$siteId.'.enableLinkTracking();';
		}
		$s2.='} catch(err){}';		
		return HHtml::jsInline($s).HHtml::jsInline($s2);
	}
}
