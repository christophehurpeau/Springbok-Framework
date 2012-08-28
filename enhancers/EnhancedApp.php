<?php
class EnhancedApp extends Enhanced{
	private $controllers,$controllersDeleted,$md5EnhanceConfig,$isJsApp;
	public $appConfig,$devConfig,$warnings;
	
	public function __construct($type,&$dirname){
		parent::__construct($type,$dirname);
		if(file_exists($this->getAppDir().'src/config/_.php')) $this->appConfig=include $this->getAppDir().'src/config/_.php';
		if(file_exists($this->getAppDir().'src/config/_'.ENV.'.php')) $this->devConfig=include $this->getAppDir().'src/config/_'.ENV.'.php';
		$this->md5EnhanceConfig=empty($this->config['config'])?'':md5(implode('~',$this->config['config']));
		$this->isJsApp=file_exists($this->getAppDir().'src/web/jsapp.js');
		
		if(empty($this->devConfig['pluginsPaths'])) $this->devConfig['pluginsPaths']=array();
		$this->devConfig['pluginsPaths']['SpringbokCore']=dirname(CORE).'/plugins/';
		
		
		//if($this->configNotEmpty('plugins')){
		if(empty($this->config['plugins'])) $this->config['plugins']=array();
		$this->config['plugins']['Springbok']=array('SpringbokCore','base');
		foreach($this->config['plugins'] as &$plugin){
			if(!isset($plugin[2])){
				$pluginPath=$this->pluginPath($plugin);
				if(file_exists($pluginPath.'config/defaultConfig.php'))
					foreach((include $pluginPath.'config/defaultConfig.php') as $k=>$v) if(!isset($this->config['config'][$k])) $this->config['config'][$k]=$v;
			}
		}
		//}
	}
	
	public function createLogger(){
		return CLogger::get('enhance-process-'.time());
	}
	
	public function getTmpDir(){
		return $this->getAppDir().'tmp/';
	}
	
	public function &appConfig($attr){ return $this->appConfig[$attr]; }
	public function appConfigExist($attr){ return isset($this->appConfig[$attr]); }
	
	public function &devConfig($attr){ return $this->devConfig[$attr]; }
	public function devConfigExist($attr){ return isset($this->devConfig[$attr]); }
	
	public function md5EnhanceConfig(){ return $this->md5EnhanceConfig; }
	
	
	public function isJsApp(){ return $this->isJsApp; }
	
	public function pluginPath($plugin){
		$pluginsPaths=$this->devConfig('pluginsPaths');
		return $pluginsPaths[$plugin[0]].$plugin[1].'/';
	}
	
	public function pluginPathFromKey($key){
		$pluginsPaths=$this->devConfig('pluginsPaths');
		$plugin=$this->config['plugins'][$key];
		return $pluginsPaths[$plugin[0]].$plugin[1].'/';
	}
}
