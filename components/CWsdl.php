<?php
/** Wsdl, based on Zend */
class CWsdl{
	private $_uri,$_dom,$_wsdl,$_schema;
	public function __construct($name,$uri){
		$this->_uri=&$uri;
		$wsdl = "<?xml version='1.0' ?>
				<definitions name='$name' targetNamespace='$uri'
					xmlns='http://schemas.xmlsoap.org/wsdl/'
					xmlns:tns='$uri'
					xmlns:soap='http://schemas.xmlsoap.org/wsdl/soap/'
					xmlns:xsd='http://www.w3.org/2001/XMLSchema'
					xmlns:soap-enc='http://schemas.xmlsoap.org/soap/encoding/'
					xmlns:wsdl='http://schemas.xmlsoap.org/wsdl/'></definitions>";
		$this->_dom = new DOMDocument();
		if (!$this->_dom->loadXML($wsdl)) {
			throw new Exception('Unable to create DomDocument');
		} else {
			$this->_wsdl=$this->_dom->documentElement;
		}
	}
	
	public function getDom(){ return $this->_dom; }
	public function getSchema(){
		if($this->_schema == null) $this->addSchemaTypeSection();
		return $this->_schema;
	}
	public function toXML(){ return $this->_dom->saveXML(); }
	/**
	 * Echo the WSDL as XML
	 *
	 * @return boolean
	 */
	public function dump($filename = false){
		if(!$filename) {
			echo $this->toXML();
			return true;
		}else return file_put_contents($filename,$this->toXML());
	}
	
	
	public function addSchemaTypeSection(){
		if ($this->_schema === null) {
			$this->_schema = $this->_dom->createElement('xsd:schema');
			$this->_schema->setAttribute('targetNamespace', $this->_uri);
			$types = $this->_dom->createElement('types');
			$types->appendChild($this->_schema);
			$this->_wsdl->appendChild($types);
		}
		return $this;
	}
	
	
	/**
	 * Add a {@link http://www.w3.org/TR/wsdl#_porttypes portType} element to the WSDL
	 *
	 * @param string $name portType element's name
	 * @return object The new portType's XML_Tree_Node for use in {@link function addPortOperation} and {@link function addDocumentation}
	 */
	public function addPortType($name){
		$portType = $this->_dom->createElement('portType');
		$portType->setAttribute('name', $name);
		$this->_wsdl->appendChild($portType);

		return $portType;
	}
	
	
	/**
	 * Add an {@link http://www.w3.org/TR/wsdl#_request-response operation} element to a portType element
	 *
	 * @param object $portType a portType XML_Tree_Node, from {@link function addPortType}
	 * @param string $name Operation name
	 * @param string $input Input Message
	 * @param string $output Output Message
	 * @param string $fault Fault Message
	 * @return object The new operation's XML_Tree_Node for use in {@link function addDocumentation}
	 */
	public function addPortOperation($portType, $name, $input = false, $output = false, $fault = false){
		$operation = $this->_dom->createElement('operation');
		$operation->setAttribute('name', $name);

		if (is_string($input) && (strlen(trim($input)) >= 1)) {
			$node = $this->_dom->createElement('input');
			$node->setAttribute('message', $input);
			$operation->appendChild($node);
		}
		if (is_string($output) && (strlen(trim($output)) >= 1)) {
			$node= $this->_dom->createElement('output');
			$node->setAttribute('message', $output);
			$operation->appendChild($node);
		}
		if (is_string($fault) && (strlen(trim($fault)) >= 1)) {
			$node = $this->_dom->createElement('fault');
			$node->setAttribute('message', $fault);
			$operation->appendChild($node);
		}

		$portType->appendChild($operation);

		return $operation;
	}
	
	
	/**
	 * Add a {@link http://www.w3.org/TR/wsdl#_soap:operation SOAP operation} to an operation element
	 *
	 * @param object $operation An operation XML_Tree_Node returned by {@link function addBindingOperation}
	 * @param string $soap_action SOAP Action
	 * @return boolean
	 */
	public function addSoapOperation($binding, $soap_action)
	{
		if ($soap_action instanceof Zend_Uri_Http) {
			$soap_action = $soap_action->getUri();
		}
		$soap_operation = $this->_dom->createElement('soap:operation');
		$soap_operation->setAttribute('soapAction', $soap_action);

		$binding->insertBefore($soap_operation, $binding->firstChild);

		return $soap_operation;
	}
	
	/**
	 * Add a {@link http://www.w3.org/TR/wsdl#_bindings binding} element to WSDL
	 *
	 * @param string $name Name of the Binding
	 * @param string $type name of the portType to bind
	 * @return object The new binding's XML_Tree_Node for use with {@link function addBindingOperation} and {@link function addDocumentation}
	 */
	public function addBinding($name, $portType){
		$binding = $this->_dom->createElement('binding');
		$binding->setAttribute('name', $name);
		$binding->setAttribute('type', $portType);

		$this->_wsdl->appendChild($binding);

		return $binding;
	}
	
	 /**
	 * Add a {@link http://www.w3.org/TR/wsdl#_soap:binding SOAP binding} element to a Binding element
	 *
	 * @param object $binding A binding XML_Tree_Node returned by {@link function addBinding}
	 * @param string $style binding style, possible values are "rpc" (the default) and "document"
	 * @param string $transport Transport method (defaults to HTTP)
	 * @return boolean
	 */
	public function addSoapBinding($binding,$style='document',$transport='http://schemas.xmlsoap.org/soap/http'){
		$soap_binding = $this->_dom->createElement('soap:binding');
		$soap_binding->setAttribute('style',$style);
		$soap_binding->setAttribute('transport',$transport);

		$binding->appendChild($soap_binding);

		return $soap_binding;
	}
	
	/**
	 * Add an operation to a binding element
	 *
	 * @param object $binding A binding XML_Tree_Node returned by {@link function addBinding}
	 * @param array $input An array of attributes for the input element, allowed keys are: 'use', 'namespace', 'encodingStyle'. {@link http://www.w3.org/TR/wsdl#_soap:body More Information}
	 * @param array $output An array of attributes for the output element, allowed keys are: 'use', 'namespace', 'encodingStyle'. {@link http://www.w3.org/TR/wsdl#_soap:body More Information}
	 * @param array $fault An array of attributes for the fault element, allowed keys are: 'name', 'use', 'namespace', 'encodingStyle'. {@link http://www.w3.org/TR/wsdl#_soap:body More Information}
	 * @return object The new Operation's XML_Tree_Node for use with {@link function addSoapOperation} and {@link function addDocumentation}
	 */
	public function addBindingOperation($binding, $name,$input=false,$output=false,$fault=false)
	{
		$operation = $this->_dom->createElement('operation');
		$operation->setAttribute('name',$name);

		if (is_array($input)) {
			$node = $this->_dom->createElement('input');
			$soap_node = $this->_dom->createElement('soap:body');
			foreach($input as $name=>$value) {
				$soap_node->setAttribute($name, $value);
			}
			$node->appendChild($soap_node);
			$operation->appendChild($node);
		}

		if (is_array($output)) {
			$node = $this->_dom->createElement('output');
			$soap_node = $this->_dom->createElement('soap:body');
			foreach($output as $name => $value){
				$soap_node->setAttribute($name,$value);
			}
			$node->appendChild($soap_node);
			$operation->appendChild($node);
		}

		if (is_array($fault)) {
			$node = $this->_dom->createElement('fault');
			/**
			 * Note. Do we really need name attribute to be also set at wsdl:fault node???
			 * W3C standard doesn't mention it (http://www.w3.org/TR/wsdl#_soap:fault)
			 * But some real world WSDLs use it, so it may be required for compatibility reasons.
			 */
			if (isset($fault['name'])) {
				$node->setAttribute('name', $fault['name']);
			}

			$soap_node = $this->_dom->createElement('soap:fault');
			foreach ($fault as $name => $value) {
				$soap_node->setAttribute($name, $value);
			}
			$node->appendChild($soap_node);
			$operation->appendChild($node);
		}

		$binding->appendChild($operation);

		return $operation;
	}
	
	
	
	/**
	 * Add a {@link http://www.w3.org/TR/wsdl#_services service} element to the WSDL
	 *
	 * @param string $name Service Name
	 * @param string $port_name Name of the port for the service
	 * @param string $binding Binding for the port
	 * @param string $location SOAP Address for the service
	 * @return object The new service's XML_Tree_Node for use with {@link function addDocumentation}
	 */
	public function addService($name, $port_name, $binding, $location){
		$service = $this->_dom->createElement('service');
		$service->setAttribute('name', $name);

		$port = $this->_dom->createElement('port');
		$port->setAttribute('name', $port_name);
		$port->setAttribute('binding', $binding);

		$soap_address = $this->_dom->createElement('soap:address');
		$soap_address->setAttribute('location', $location);

		$port->appendChild($soap_address);
		$service->appendChild($port);

		$this->_wsdl->appendChild($service);

		return $service;
	}
	
	/**
	 * Add a {@link http://www.w3.org/TR/wsdl#_messages message} element to the WSDL
	 *
	 * @param string $name Name for the {@link http://www.w3.org/TR/wsdl#_messages message}
	 * @param array $parts An array of {@link http://www.w3.org/TR/wsdl#_message parts}
	 *					 The array is constructed like: 'name of part' => 'part xml schema data type'
	 *					 or 'name of part' => array('type' => 'part xml schema type')
	 *					 or 'name of part' => array('element' => 'part xml element name')
	 * @return object The new message's XML_Tree_Node for use in {@link function addDocumentation}
	 */
	public function addMessage($name, $parts){
		$message = $this->_dom->createElement('message');

		$message->setAttribute('name', $name);

		if(sizeof($parts) > 0){
			foreach ($parts as $name => $type){
				$part = $this->_dom->createElement('part');
				$part->setAttribute('name', $name);
				if (is_array($type)) {
					foreach ($type as $key => $value){
						$part->setAttribute($key, $value);
					}
				} else {
					$part->setAttribute('type',$type);
				}
				$message->appendChild($part);
			}
		}

		$this->_wsdl->appendChild($message);

		return $message;
	}
	
	/**
	 * Returns an XSD Type for the given PHP type
	 *
	 * @param string $type PHP Type to get the XSD type for
	 * @return string
	 */
	public function getType($type){
		switch (strtolower($type)) {
			case 'string':
			case 'str':
				return 'xsd:string';
				break;
			case 'int':
			case 'integer':
				return 'xsd:int';
				break;
			case 'float':
			case 'double':
				return 'xsd:float';
				break;
			case 'boolean':
			case 'bool':
				return 'xsd:boolean';
				break;
			case 'array':
				return 'soap-enc:Array';
				break;
			case 'object':
				return 'xsd:struct';
				break;
			case 'mixed':
				return 'xsd:anyType';
				break;
			case 'void':
				return '';
			default:
				// delegate retrieval of complex type to current strategy
				return false;
		}
	}
	
	
	
	/* --------- COMPLEX TYPES ---------- */
	
	private $_operationBodyStyle = array('use'=>'encoded','encodingStyle'=>"http://schemas.xmlsoap.org/soap/encoding/"),
		$_includedTypes=array();
	
	
	public function _getType($paramType){
		$type=$this->getType($paramType);
		if($type===false) $type=$this->_addComplexType($paramType);
		return $type;
	}
	
	
	public function _addFunctionToWsdl($method,$port,$binding,$infos){
		$args=array();
		// RPC style: add each parameter as a typed part
		if($infos['params']!==false) foreach($infos['params'] as $name=>$param){
			$args[$name]=array('type'=>$this->_getType($param['type']));
		}
		$this->addMessage($method.'In',$args);
		
		$isOneWayMessage=$infos['annotations']['Return'][0]==='void';
		if(!$isOneWayMessage){
			$args = array();
			$args['return']=array('type'=>$this->_getType($infos['annotations']['Return'][0]));
			$this->addMessage($method.'Out',$args);
			
			$portOperation=$this->addPortOperation($port,$method,'tns:'.$method.'In','tns:'.$method.'Out');
		}else{
			$portOperation=$this->addPortOperation($port,$method,'tns:'.$method.'In',false);
		}
		
		if(isset($infos['annotations']['Doc'])) $this->addDocumentation($portOperation,$infos['annotations']['Doc'][0]);
		
		// When using the RPC style, make sure the operation style includes a 'namespace' attribute (WS-I Basic Profile 1.1 R2717)
		if(!isset($this->_operationBodyStyle['namespace'])) $this->_operationBodyStyle['namespace']=''.static::$uri;
		
		if($isOneWayMessage == false) $operation=$this->addBindingOperation($binding,$method,$this->_operationBodyStyle,$this->_operationBodyStyle);
		else $operation=$this->addBindingOperation($binding,$method,$this->_operationBodyStyle);
		
		$this->addSoapOperation($operation,static::$uri.'#'.$method);
		//$this->_functions[] = $function->getName();
	}

	/**
	 * Add a {@link http://www.w3.org/TR/wsdl#_types types} data type definition
	 *
	 * @param string $type Name of the class to be specified
	 * @return string XSD Type for the given PHP type
	 */
	public function _addComplexType($type){
		if(in_array($type,$this->_includedTypes)) return "tns:$type";
		if($type=='array') $type='array[]string';
		if(substr($type,0,5)=='array'){
			$typeArray=substr($type,7);
			$nestedCounter=substr_count($typeArray,"[]");
			
			$childTypeName=$this->_getType(str_replace("[]","",$typeArray));
			$complexTypeName=str_repeat("ArrayOf",$nestedCounter+1).ucfirst(substr(strtolower($childTypeName), 4));
			
			$this->addSchemaTypeSection();
			$dom=&$this->_dom;
			$complexType = $dom->createElement('xsd:complexType');
			$complexType->setAttribute('name', $complexTypeName);
			$sequence = $dom->createElement('xsd:sequence');
			
			$element = $dom->createElement('xsd:element');
			$element->setAttribute('name','item');
			$element->setAttribute('type',$childTypeName);
			$element->setAttribute('minOccurs',0);
			$element->setAttribute('maxOccurs','unbounded');
			$sequence->appendChild($element);
			$complexType->appendChild($sequence);
			$wsdl->getSchema()->appendChild($complexType);
			return "tns:$complexTypeName";
		}

		if(!isset($type::$__PROP_DEF)) throw new Exception('Unknown type : '.$type);
		$this->_includedTypes[]=$type;
		
		$this->addSchemaTypeSection();
		$dom=&$this->_dom;
		$complexType = $dom->createElement('xsd:complexType');
		$complexType->setAttribute('name',$type);
		$all=$dom->createElement('xsd:all');
		
		$propDef=$type::$__PROP_DEF;
		foreach($propDef as $name=>$prop){
			$element=$dom->createElement('xsd:element');
			$element->setAttribute('name',$name);
			$element->setAttribute('type',$this->_getType($prop['type']));
			
			if(!isset($prop['annotations']['Required']) || isset($prop['annotations']['NotBindable'])) $element->setAttribute('nillable','true');
			$all->appendChild($element);
		}
		
		$complexType->appendChild($all);
		$wsdl->getSchema()->appendChild($complexType);
		
		return "tns:$type";
	}
}
