<?php
class MergeTxtFile extends EnhancerFile{
	public static $CACHE_PATH=false,$defaultExtension='php';
	
	protected function loadContent($srcContent){
		$sortedVersions=array();
		
		if($this->enhanced->configNotEmpty('plugins')){
			foreach($this->enhanced->config['plugins'] as $key=>$plugin){
				$devPluginPath=$this->enhanced->pluginPath($plugin);
				if(file_exists($pluginConfigPath=($devPluginPath.'dbEvolutions/Versions.php')))
					$srcContent.="\n".file_get_contents($pluginConfigPath);
			}
		}
		
		
		foreach(explode("\n",trim($srcContent)) as $version){
			$version=trim($version);
			if(empty($version)) continue;
			$sortedVersions[strpos($version,'-')===false ? $version : strtotime($version)]=$version;
		}
		ksort($sortedVersions);
		
		$this->_srcContent=implode("\n",$sortedVersions);
	}
	public function enhanceContent(){}
	
	public function getEnhancedDevContent(){ return $this->_srcContent; }
	public function getEnhancedProdContent(){ return $this->_srcContent; }
}