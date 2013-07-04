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
}
