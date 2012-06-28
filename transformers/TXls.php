<?php
class TXls extends STransformer{
	public static function init(){
		include_once CLIBS.'PHPExcel.php';
	}
	public static function getContentType(){
		return 'application/vnd.ms-excel';
	}
	
	protected $objPHPExcel,$row=2;
	public function __construct($component){
		parent::__construct($component);
		PHPExcel_Autoloader::Register();//Should NOT be THERE, a PHP 5.3.10 bug ?
		PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp,array('memoryCacheSize'=>'512MB'));
		
		$this->objPHPExcel = new PHPExcel();
		$this->objPHPExcel->getProperties()->setTitle($component->title);
		$this->objPHPExcel->setActiveSheetIndex(0);
	}
	
	public function titles($fields){
		$col=0;
		foreach($fields as &$field){
			$this->objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col,1,$field['title']);
			$this->objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col++)->setAutoSize(true);
		}
	}
	
	public function row($row,$fields){
		$col=0;
		foreach($fields as $i=>$field){
			$value=self::getValueFromModel($row,$field,$i);
			$value=$this->getDisplayableValue($field,$value,$row);
			if($field['type']==='string'){
				$this->objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow($col,$this->row,$value,PHPExcel_Cell_DataType::TYPE_STRING);
				$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$this->row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			}else $this->objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col,$this->row,$value);//
			$col++;
		}
		$this->row++;
	}
	
	public function toFile($fileName){
		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
		$objWriter->save($fileName);
	}
	
	public function display(){
		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	/*$objDrawing = new PHPExcel_Worksheet_Drawing();
		$objDrawing->setName('Logo');
		$objDrawing->setDescription('Logo');
		$objDrawing->setPath('./images/officelogo.jpg');
		$objDrawing->setHeight(36);
		
		$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
		
		
		$objDrawing->setCoordinates('B15');
		 * 
		 * 
		 * 
		 * 
		 * // currency format, &euro; with < 0 being in red color
$currencyFormat = '#,#0.## \€;[Red]-#,#0.## \€';
// number format, with thousands seperator and two decimal points.
$numberFormat = '#,#0.##;[Red]-#,#0.##';*/
}
TXls::init();
