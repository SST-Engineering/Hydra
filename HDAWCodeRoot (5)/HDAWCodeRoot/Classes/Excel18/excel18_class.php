<?php

require_once 'PHPExcel/IOFactory.php';


$writePHPExcel = null;
function make_worksheet($p, &$error) {
	global $writePHPExcel;
	$p = $p[1];
	$ws = $writePHPExcel->createSheet();
	$ws->setTitle($p['title']);
	$writePHPExcel->setActiveSheetIndexByName($p['title']);
	return $writePHPExcel->getActiveSheetIndex();
}
function delete_worksheet($p, &$error) {
	global $writePHPExcel;
	$p = $p[1];
	$ws = $writePHPExcel->removeSheetByIndex($p['index']);
	$writePHPExcel->setActiveSheetIndexByName($p['title']);
	return $writePHPExcel->getActiveSheetIndex();
}
function select_worksheet($p, &$error) {
	global $writePHPExcel;
	$p = $p[1];
	$writePHPExcel->setActiveSheetIndexByName($p['title']);
	return $writePHPExcel->getActiveSheetIndex();
}
function open_write_xl($p, &$error) {
	global $writePHPExcel;
	$writePHPExcel = new PHPExcel();
	//
	$p = $p[1];
	$creator = (array_key_exists('creator',$p))?$p['creator']:'';
	$modified_by = (array_key_exists('modifiedBy',$p))?$p['modifiedBy']:'';
	$subject = (array_key_exists('subject',$p))?$p['subject']:'';
	$title = (array_key_exists('title',$p))?$p['title']:'';
	$description = (array_key_exists('description',$p))?$p['description']:'';
	$keywords = (array_key_exists('keywords',$p))?$p['keywords']:'';
	$category = (array_key_exists('category',$p))?$p['category']:'';
	$writePHPExcel->getProperties()->setCreator($creator)
							 ->setLastModifiedBy($modified_by)
							 ->setTitle($title)
							 ->setSubject($subject)
							 ->setDescription($description)
							 ->setKeywords($keywords)
							 ->setCategory($category)
							 ->setCompany("EiB");
	$writePHPExcel->setActiveSheetIndex(0);
	$default_style = array(
		'font' => array(
			'name' => 'Verdana',
			'color' => array('rgb' => '000000'),
			'size' => 8
		),
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
		),
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => 'AAAAAA')
			)
		)
	);
	$writePHPExcel->getDefaultStyle()->applyFromArray($default_style);
	return true;
}
function write_xl_cell($p, &$error) {
	global $writePHPExcel;
	$p = $p[1];
	$cell = (array_key_exists('cell',$p))?$p['cell']:'A1';
	$backcolor = (array_key_exists('backcolor',$p))?$p['backcolor']:null;
	$value = (array_key_exists('value',$p))?$p['value']:'';
	$align = (array_key_exists('align',$p))?$p['align']:false;
	if (!is_null($backcolor)) $writePHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray(
									array('fill' 	=> array(
																'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
																'color'		=> array('argb' => $backcolor)
															)
										 )
									);
	$writePHPExcel->getActiveSheet()->setCellValueExplicit($cell,$value,PHPExcel_Cell_DataType::TYPE_STRING);
	if ($align) $writePHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray(
												   array(
														'alignment' => array(
															'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
															),
														
														'borders' => array(
															'allborders' => array(
																'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
																'color' => array('rgb' => '000000')
																)
															)
														)
													);
	return true;
}
function write_title_cells($p, &$error) {
	global $writePHPExcel;
	$p = $p[1];
	$range = (array_key_exists('range',$p))?$p['range']:'A1:A1';
	$backcolor = (array_key_exists('backcolor',$p))?$p['backcolor']:null;
	$value = (array_key_exists('value',$p))?$p['value']:'';
	$align = (array_key_exists('align',$p))?$p['align']:'';
	if (!is_null($backcolor)) $writePHPExcel->getActiveSheet()->getStyle($range)->applyFromArray(
									array('fill' 	=> array(
																'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
																'color'		=> array('argb' => $backcolor)
															)
										 )
									);
	$cell = (preg_match("/(?P<cell>[\w\d]{2,}):/",$range,$cell_match))?$cell_match['cell']:'A1';
	
	$writePHPExcel->getActiveSheet()->setCellValueExplicit($cell,$value,PHPExcel_Cell_DataType::TYPE_STRING);
	$writePHPExcel->getActiveSheet()->mergeCells($range);
	$writePHPExcel->getActiveSheet()->getStyle($range)->applyFromArray(
												   array(
														'alignment' => array(
															'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
															),
														
														'borders' => array(
															'allborders' => array(
																'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
																'color' => array('rgb' => '000000')
																)
															)
														)
													);
	return true;
}
function write_column_styles($p, &$error) {
	global $writePHPExcel;
	$p = $p[1];
	if (array_key_exists('columns',$p) && !is_null($p['columns'])) {
		foreach ($p['columns'] as $column=>$style) {
			if (array_key_exists('width',$style)) {
				$width = $style['width'];
				$writePHPExcel->getActiveSheet()->getColumnDimensionByColumn($column)->setAutoSize(false);
				$writePHPExcel->getActiveSheet()->getColumnDimensionByColumn($column)->setWidth("{$width}");
			}
		}
	}
	return true;
}
function write_table_xl($p, &$error) {
	global $writePHPExcel;
	$p = $p[1];
	$row = (array_key_exists('row',$p))?$p['row']:1;
	$column = (array_key_exists('column',$p))?$p['column']:0;
	$backcolor = (array_key_exists('backcolor',$p))?$p['backcolor']:null;
	$backcolor_headers = (array_key_exists('backcolor_headers',$p))?$p['backcolor_headers']:null;
	$align = (array_key_exists('align',$p))?$p['align']:false;
	$table = (array_key_exists('table',$p))?$p['table']:null;
	$column_styles = (array_key_exists('column_styles',$p))?$p['column_styles']:array();
	$rotate_headers = (array_key_exists('rotate_headers',$p))?$p['rotate_headers']:90;
	if (is_null($table)) return true;
	if (count($table)==0) return true;
	$on_row = $row;
	$on_column = $column;
	foreach($table[0] as $field=>$value) {
		$writePHPExcel->getActiveSheet()->setCellValueByColumnAndRow($on_column,$on_row, $field);
		$writePHPExcel->getActiveSheet()->getStyleByColumnAndRow($on_column,$on_row)->applyFromArray(
									   array(
											'alignment' => array(
												'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												'rotation' => 90
												)
											)
										);
		$writePHPExcel->getActiveSheet()->getStyleByColumnAndRow($on_column,$on_row)->getAlignment()->setTextRotation($rotate_headers);
		if (!is_null($backcolor_headers)) $writePHPExcel->getActiveSheet()->getStyleByColumnAndRow($on_column,$on_row)->applyFromArray(
									array('fill' 	=> array(
																'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
																'color'		=> array('argb' => $backcolor_headers)
															),
										'alignment' => array(
											'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
											),
										
										'borders' => array(
											'allborders' => array(
												'style' => PHPExcel_Style_Border::BORDER_THIN,
												'color' => array('rgb' => '000000')
												)
											)
										 )
									);
		$on_column++;
	}
	$on_row++;
	$s = "";
	foreach($table as $row) {
		$on_column = $column;
		foreach($row as $field=>$value) {
			if (array_key_exists($on_column, $column_styles)) {
					$ca = PHPExcel_Cell::stringFromColumnIndex($on_column).$on_row;
					$color = array();$color['rgb']=$column_styles[$on_column];
					$writePHPExcel->getActiveSheet()->getStyleByColumnAndRow($on_column,$on_row)->applyFromArray(
								array('font' => array(
									'name' => 'Verdana',
									'color' => $color,
									'size' => 10
									)
								)
							);
					}
			$writePHPExcel->getActiveSheet()->setCellValueByColumnAndRow($on_column,$on_row, $value);
			if (!is_null($backcolor)) $writePHPExcel->getActiveSheet()->getStyleByColumnAndRow($on_column,$on_row)->applyFromArray(
									array('fill' 	=> array(
																'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
																'color'		=> array('argb' => $backcolor)
															),
										'alignment' => array(
											'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
											),
										
										'borders' => array(
											'allborders' => array(
												'style' => PHPExcel_Style_Border::BORDER_THIN,
												'color' => array('rgb' => '000000')
												)
											)
										 )
									);
			$on_column++;
		}
		$on_row++;
	}
	return $s;
}
function write_table_xl_simple($p, &$error) {
	global $writePHPExcel;
	$p = $p[1];
	$table = (array_key_exists('table',$p))?$p['table']:null;
	$row = 1;
	$column = 0;
	$rotate_headers = 90;
	if (is_null($table)) return true;
	if (count($table)==0) return true;
	$on_row = $row;
	$on_column = $column;
		$writePHPExcel->getActiveSheet()->getDefaultStyle()->applyFromArray(
												   array(
														'alignment' => array(
															'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
															),
														
														'borders' => array(
															'allborders' => array(
																'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
																'color' => array('rgb' => '000000')
																)
															)
														)
													);

	foreach($table[0] as $field=>$value) {
		$writePHPExcel->getActiveSheet()->setCellValueByColumnAndRow($on_column,$on_row, $field);
		$writePHPExcel->getActiveSheet()->getStyleByColumnAndRow($on_column,$on_row)->getAlignment()->setTextRotation($rotate_headers);
		$on_column++;
	}
	$on_row++;
	$s = "";
	foreach($table as $row) {
		$on_column = $column;
		foreach($row as $field=>$value) {
			$writePHPExcel->getActiveSheet()->setCellValueByColumnAndRow($on_column,$on_row, $value);
			$on_column++;
		}
		$on_row++;
	}
	return $s;
}
function close_write_xl($p, &$error) {
	global $writePHPExcel;
	$objWriter = PHPExcel_IOFactory::createWriter($writePHPExcel, 'Excel2007');
	$objWriter->save($p[1]);
	return true;
}

class HDA_XL_GridReader implements PHPExcel_Reader_IReadFilter
{
private $_rows = null;
private $_columns = null;
private $_inSheets = null;

public function __construct($rows=null, $columns=null, $worksheets=null) {
   $this->_rows = $rows;
   $this->_columns = $columns;
   $this->_inSheets = $worksheets;
}

public function readCell($column, $row, $worksheet='') {
   if (!is_null($this->_inSheets) && !$this->matchSheetNameInArray($worksheet, $this->_inSheets)) return false;
   return ((is_null($this->_rows) || ($row >= $this->_rows[0] && $row <= $this->_rows[1])) && 
           (is_null($this->_columns) || in_array($column, $this->_columns)));
}
	public function matchSheetNameInArray($name, $a_names) {
		foreach ($a_names as $a_name) {
			if (strtoupper(trim($name))==strtoupper(trim($a_name))) return true;
		}
		return false;
	}

}

class HDA_XL_Grid {

private $objXlReader = null;
private $objXl = null;
private $_path = null;

public function __construct($path, $method=null, $data_only=true) {
   try {
		switch ($method) {
			case 'CELLS':
			//	$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_nocache;
				$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
				PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
				break;
			default:
				break;
		}
	   
	/*	$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array( 
						'memoryCacheSize' => '128MB'
						);
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
		*/
	//	$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
	//	PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
	//	$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
	//	PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
	//	$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_wincache;
	//	PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
		PHPExcel_Settings::setLibXmlLoaderOptions(null);
		$this->objXlReader = PHPExcel_IOFactory::createReaderForFile($this->_path = $path);
		$this->objXlReader->setReadDataOnly($data_only);
      }
   catch (Exception $e) {
      throw new Exception("Fails in XL init {$path}: ".$e->getMessage());
      }
   }
public static function SetCacheMethod($method) {
	switch ($method) {
		case 'CELLS':
			$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_nocache;
			$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
			PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
			break;
		default:
			break;
	}
}
private $_onlyRows = null;
private $_onlyColumns = null;
private $_onlySheets = null;
private $_filterGrid = null;
public function RestrictLoad($rows=null, $columns=null, $sheets=null) {
   if (!is_null($rows)) $this->_onlyRows = $rows;
   if (!is_null($columns)) $this->_onlyColumns = $columns;
   if (!is_null($sheets)) $this->_onlySheets = $sheets;
   }
private $_objXl = null;
private $_rows = null;
private $_objWorksheet;
public function Worksheet() {
	return $this->_objWorksheet;
}
public function Load() {
   try {
      if (!is_null($this->_onlySheets) && is_array($this->_onlySheets)) {
         $this->objXlReader->setLoadSheetsOnly($this->_onlySheets);
         }
      if (!is_null($this->_onlyRows) || !is_null($this->_onlyColumns)) {
         $this->_filterGrid = new HDA_XL_GridReader($this->_onlyRows, $this->_onlyColumns, $this->_onlySheets);
         $this->objXlReader->setReadFilter($this->_filterGrid);
         }
      $this->_objXl = $this->objXlReader->load($this->_path);

      $this->_objWorksheet = $this->_objXl->setActiveSheetIndex(0);
      }
   catch (Exception $e) {
      $s = print_r($this->_onlySheets, true);
      throw new Exception("Fails in XL Load: (sheets {$s} ) ".$e->getMessage());
      }
   }
public function StartRowIterator() {
	$this->_rows = $this->_objWorksheet->getRowIterator();
	return $this->_rows;
}
public function NextRow() {
	return $this->_rows->next();
}
public function SheetName() {
   try {
      if (is_null($this->_objXl) || !is_object($this->_objXl)) throw new Exception("Fails in XL: no xl open");
	  return $this->_objXl->getTitle();
      }
   catch (Exception $e) {
      throw new Exception("Fails in XL SetSheet: ".$e->getMessage());
      }
   return false;
   }
	
public function SetSheet($s = 0) {
   try {
      if (is_null($this->_objXl) || !is_object($this->_objXl)) throw new Exception("Fails in XL: no xl open");
      $this->_objWorksheet = (is_numeric($s) && is_integer($s))?$this->_objXl->setActiveSheetIndex($s):$this->_objXl->setActiveSheetIndexByName($s);
      return (!is_null($this->_objWorksheet));
      }
   catch (Exception $e) {
      throw new Exception("Fails in XL SetSheet: ".$e->getMessage());
      }
   return false;
   }
public function GetSheets() {
   try {
      if (is_null($this->_objXl) || !is_object($this->_objXl)) throw new Exception("Fails in XL: no xl open");
      $sheets = $this->_objXl->getAllSheets();
      $aa = array();
      foreach($sheets as $sheet) $aa[] = $sheet->getTitle();
      return $aa;
      }
   catch (Exception $e) {
      throw new Exception("Fails in XL: ".$e->getMessage());
      }
   return false;
   }
public function MakeSheet($title) {
   try {
      if (is_null($this->_objXl) || !is_object($this->_objXl)) throw new Exception("Fails in XL: no xl open");
		$ws = $this->_objXl->createSheet();
		$ws->setTitle($title);
		$this->_objXl->setActiveSheetIndexByName($title);
		return $this->_objXl->getActiveSheetIndex();
      }
   catch (Exception $e) {
      throw new Exception("Fails in XL: ".$e->getMessage());
      }
   return false;
   }
public function DeleteSheet($idx) {
   try {
      if (is_null($this->_objXl) || !is_object($this->_objXl)) throw new Exception("Fails in XL: no xl open");
		$ws = $this->_objXl->removeSheetByIndex($idx);
		return true;
      }
   catch (Exception $e) {
      throw new Exception("Fails in XL: ".$e->getMessage());
      }
   return false;
   }
public function AsArray() {
   try {
		$highRow = $this->_objWorksheet->getHighestRow();
	   $highCol = $this->_objWorksheet->getHighestDataColumn();
      if (is_null($this->_objWorksheet) || !is_object($this->_objWorksheet)) throw new Exception("Fails in XL: no worksheet");
      return $this->_objWorksheet->toArray($missing_cell = null, $calc = true, $format = true, $refs = true);
      }
   catch (Exception $e) {
      throw new Exception("Fails in XL (limits row:{$highRow} col:{$highCol}) : ".$e->getMessage());
      }
   }
public function AsNoCalcArray() {
   try {
      if (is_null($this->_objWorksheet) || !is_object($this->_objWorksheet)) throw new Exception("Fails in XL: no worksheet");
      return $this->_objWorksheet->toArray($missing_cell = null, $calc = true, $format = true, $refs = true, $old_values=true);
      }
   catch (Exception $e) {
      throw new Exception("Fails in XL: ".$e->getMessage());
      }
   }
public function WriteTable($table, $start_row) {
	$row = $start_row;
	$column = 0;
	$rotate_headers = 90;
	if (is_null($table)) return true;
	if (count($table)==0) return true;
	$on_row = $row;
	$on_column = $column;
		$this->_objWorksheet->getDefaultStyle()->applyFromArray(
												   array(
														'alignment' => array(
															'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
															),
														
														'borders' => array(
															'allborders' => array(
																'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
																'color' => array('rgb' => '000000')
																)
															)
														)
													);

foreach($table[0] as $field=>$value) {
		$this->_objWorksheet->getStyleByColumnAndRow($on_column,$on_row)->applyFromArray(
												   array(
														'alignment' => array(
															'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
															),
														
														'borders' => array(
															'allborders' => array(
																'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
																'color' => array('rgb' => '000000')
																)
															),
														'fill' => array(
															'type' => PHPExcel_Style_Fill::FILL_SOLID,
															'color'=> array('rgb' => 'F3F37A')
															)
														)
													);
		$this->_objWorksheet->setCellValueByColumnAndRow($on_column,$on_row, $field);
		$this->_objWorksheet->getStyleByColumnAndRow($on_column,$on_row)->getAlignment()->setTextRotation($rotate_headers);
		$on_column++;
	}
	$on_row++;
	foreach($table as $row) {
		$on_column = $column;
		foreach($row as $field=>$value) {
			$this->_objWorksheet->setCellValueByColumnAndRow($on_column,$on_row, $value);
			$on_column++;
		}
		$on_row++;
	}
	return true;
	}

public function ConvertExcelDate($v) {
   return PHPExcel_Shared_Date::ExcelToPHP($v);
   }
public function GetSheetLimits() {
   try {
      if (is_null($this->_objWorksheet) || !is_object($this->_objWorksheet)) throw new Exception("Fails in XL: no worksheet");
	  $a = array();
	  $a['ROWS'] = $this->_objWorksheet->getHighestRow();
	  $a['COLUMNS'] = $this->_objWorksheet->getHighestColumn();
	  return $a;
   }
   catch (Exception $e) {
      throw new Exception("Fails in XL: ".$e->getMessage());
      }

   return false;
}
public function UnFreezePane() {
   try {
      if (is_null($this->_objWorksheet) || !is_object($this->_objWorksheet)) throw new Exception("Fails in XL: no worksheet");
	  $this->_objWorksheet->unfreezePane();
	  return true;
   }
   catch (Exception $e) {
      throw new Exception("Fails in XL: ".$e->getMessage());
      }

   return false;
}
public function ReleaseMemory() {
	try {
		if (!is_null($this->_objWorksheet)) {
			$this->_objWorksheet->reconnectCache();
		}
		return true;
	}
	catch (Exception $e) {
      throw new Exception("Fails in XL Release Mem: ".$e->getMessage());
	}
	return false;
}
public function GetCellValue($loc, $column=null, $do_calc=false) {
   try {
      if (is_null($this->_objWorksheet) || !is_object($this->_objWorksheet)) throw new Exception("Fails in XL: no worksheet");
      $cell = null;
      if (is_null($column)) {
         if ($this->_objWorksheet->cellExists($loc))
            $cell = $this->_objWorksheet->getCell($loc);
		 else return false;
         }
      elseif ($this->_objWorksheet->cellExistsByColumnAndRow($column, $loc))
         $cell = $this->_objWorksheet->getCellByColumnAndRow($column, $loc);
	  else return false;
      if (!is_null($cell)) return ($do_calc)?$cell->getCalculatedValue():$cell->getValue();
      }
   catch (Exception $e) {
      throw new Exception("Fails in XL: ".$e->getMessage());
      }

   return false;
   }
public function GetCell($loc, $column=null, $do_calc=false) {
   try {
      if (is_null($this->_objWorksheet) || !is_object($this->_objWorksheet)) throw new Exception("Fails in XL: no worksheet");
      $cell = null;
      if (is_null($column)) {
         if ($this->_objWorksheet->cellExists($loc))
            $cell = $this->_objWorksheet->getCell($loc);
		 else return false;
         }
      elseif ($this->_objWorksheet->cellExistsByColumnAndRow($column, $loc))
         $cell = $this->_objWorksheet->getCellByColumnAndRow($column, $loc);
	  else return false;
      if (!is_null($cell)) return ($do_calc)?$cell->getCalculatedValue():$cell;
      }
   catch (Exception $e) {
      throw new Exception("Fails in XL: ".$e->getMessage());
      }

   return false;
   }
public function SetCellValue($loc, $value, $column=null) {
   try {
      if (is_null($this->_objWorksheet) || !is_object($this->_objWorksheet)) throw new Exception("Fails in XL: no worksheet");
      $cell = null;
      if (is_null($column)) {
       //  if ($this->_objWorksheet->cellExists($loc))
            $cell = $this->_objWorksheet->getCell($loc);
	//	 else throw new Exception("Fails in XL: no cell to set {$loc} ");
         }
      else //if ($this->_objWorksheet->cellExistsByColumnAndRow($column, $loc))
         $cell = $this->_objWorksheet->getCellByColumnAndRow($column, $loc);
	//  else return false;
      if (!is_null($cell)) return $cell->setValue($value);
      }
   catch (Exception $e) {
      throw new Exception("Fails in XL: ".$e->getMessage());
      }

   return false;
   }
public function SetCell($cell) {
   try {
      if (is_null($this->_objWorksheet) || !is_object($this->_objWorksheet)) throw new Exception("Fails in XL: no worksheet");
		$cell->rebindParent($this->objWorksheet);
		return true;
      }
   catch (Exception $e) {
      throw new Exception("Fails in XL: ".$e->getMessage());
      }

   return false;
   }
public function SetCellStyle($loc, $value, $column=null) {
   try {
      if (is_null($this->_objWorksheet) || !is_object($this->_objWorksheet)) throw new Exception("Fails in XL: no worksheet");
      $cell = null;
      if (is_null($column)) {
       //  if ($this->_objWorksheet->cellExists($loc))
            $cell = $this->_objWorksheet->getCell($loc);
	//	 else throw new Exception("Fails in XL: no cell to set {$loc} ");
         }
      else {//if ($this->_objWorksheet->cellExistsByColumnAndRow($column, $loc))
         $cell = $this->_objWorksheet->getCellByColumnAndRow($column, $loc);
		 if (!is_null($cell)) $loc = $cell->getCoordinate();
	  }
	//  else return false;
      if (!is_null($cell)) {
		  if (is_null($value)) { $value = array(
														'alignment' => array(
															'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
															),
														
														'borders' => array(
															'allborders' => array(
																'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
																'color' => array('rgb' => '0000FF')
																)
															),
														'fill' => array(
															'type' => PHPExcel_Style_Fill::FILL_NONE,
															'color'=> array('rgb' => 'FFFFFF')
															)
														);
		  }
			else {
				$v = array(
														'alignment' => array(
															'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
															),
														
														'borders' => array(
															'allborders' => array(
																'style' => PHPExcel_Style_Border::BORDER_THICK,
																'color' => array('rgb' => '0000FF')
																)
															),
														'fill' => array(
															'type' => PHPExcel_Style_Fill::FILL_NONE,
															'color'=> array('rgb' => 'FFFFFF')
															)
														);
		
				if (array_key_exists('borders', $value)) $v['borders']['allborders']['color']['rgb'] = $value['borders']['color'];
				if (array_key_exists('borders', $value)) switch($value['borders']['style']) {
					case 'NONE': $v['borders']['allborders']['style'] = PHPExcel_Style_Border::BORDER_NONE; break;
					case 'MEDIUM': $v['borders']['allborders']['style'] = PHPExcel_Style_Border::BORDER_MEDIUM; break;
					case 'THICK': $v['borders']['allborders']['style'] = PHPExcel_Style_Border::BORDER_THICK; break;
					case 'THIN': $v['borders']['allborders']['style'] = PHPExcel_Style_Border::BORDER_THIN; break;
					}
				if (array_key_exists('fill', $value)) switch($value['fill']['type']) {
					case 'SOLID':$v['fill']['type'] = PHPExcel_Style_Fill::FILL_SOLID; break;
				}
				if ((array_key_exists('fill', $value)) && ($value['fill']['color'] != null)) $v['fill']['color']['rgb'] = dechex($value['fill']['color']);
				if (array_key_exists('font', $value)) 	{
					$v['font'] = array(
												'name' => 'Verdana',
												'color' => array('rgb'=>$value['font']['color']['rgb']),
												'size' => 10
										);
					if (array_key_exists('italic',$value['font'])) $v['font']['italic'] = true;
				}					
				$value = $v;
														
			}
		$this->_objWorksheet->getStyle($loc)->applyFromArray($value);	
		}
		return true;
      }
   catch (Exception $e) {
      throw new Exception("Fails in XL: ".$e->getMessage());
      }

   return false;
   }
public function SetCellTitle($cell, $range, $title, $color) {
	try {
		if (is_null($this->_objWorksheet) || !is_object($this->_objWorksheet)) throw new Exception("Fails in XL: no worksheet");
		$this->_objWorksheet->setCellValueExplicit($cell,$title,PHPExcel_Cell_DataType::TYPE_STRING);
		$this->_objWorksheet->mergeCells($range);
		$style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				),
			
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK,
					'color' => array('rgb' => $color)
					)
				));
		$this->_objWorksheet->getStyle($range)->applyFromArray($style);
		$style = array('fill' => array('type'=>PHPExcel_Style_Fill::FILL_SOLID,
							'color' => array('rgb' => $color)));
		$this->_objWorksheet->getStyle($range)->applyFromArray($style);
		$style = array('font' => array(
									'name' => 'Verdana',
									'color' => array('rgb'=>0),
									'size' => 10
							));
		$this->_objWorksheet->getStyle($range)->applyFromArray($style);
		}
	catch (Exception $e) {
		throw new Exception("Fails in XL SetTitle: ".$e->getMessage());
		}
	return null;
	}
public function SetSharedStyle($cell, $value) {
	try {
		if (is_null($this->_objWorksheet) || !is_object($this->_objWorksheet)) throw new Exception("Fails in XL: no worksheet");
		$v = array(
												'alignment' => array(
													'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
													),
												
												'borders' => array(
													'allborders' => array(
														'style' => PHPExcel_Style_Border::BORDER_THICK,
														'color' => array('rgb' => '0000FF')
														)
													),
												'fill' => array(
													'type' => PHPExcel_Style_Fill::FILL_NONE,
													'color'=> array('rgb' => 'FFFFFF')
													)
												);

		$v['borders']['allborders']['color']['rgb'] = $value['borders']['color'];
		switch($value['borders']['style']) {
			case 'MEDIUM': $v['borders']['allborders']['style'] = PHPExcel_Style_Border::BORDER_MEDIUM; break;
			case 'THICK': $v['borders']['allborders']['style'] = PHPExcel_Style_Border::BORDER_THICK; break;
			case 'THIN': $v['borders']['allborders']['style'] = PHPExcel_Style_Border::BORDER_THIN; break;
			}
		switch($value['fill']['type']) {
			case 'SOLID':$v['fill']['type'] = PHPExcel_Style_Fill::FILL_SOLID; break;
		}
		if ($value['fill']['color'] != null) $v['fill']['color']['rgb'] = dechex($value['fill']['color']); 
		$this->_objWorksheet->getStyle($cell)->applyFromArray($v);
		$styleobj =  $this->_objWorksheet->getStyle($cell);
		$this->_objWorksheet->setSharedStyle($styleobj, $cell);
		return $styleobj;
		}
	catch (Exception $e) {
		throw new Exception("Fails in XL SetTitle: ".$e->getMessage());
		}
	return null;
	}
private function intToRgb($color) {
	$rgb = array(3);
	$rgb[0] = ( $color >> 16 ) & 0xFF;
    $rgb[1] =  ( $color >> 8 ) & 0xFF; 
    $rgb[2] = $color & 0xFF; 
	file_put_contents("tmp\\colors.txt", "{$color} rgb: ".print_r($rgb, true));
	return $rgb;
}
public function UseSharedStyle($styleobj, $loc) {
   try {
      if (is_null($this->_objWorksheet) || !is_object($this->_objWorksheet)) throw new Exception("Fails in XL: no worksheet");
	  return $this->_objWorksheet->duplicateStyle($styleobj, $loc);
   }
	catch (Exception $e) {
		throw new Exception("Fails in XL UseStyle: ".$e->getMessage());
		}
	return null;
}
public function GetCellType($loc, $column=null) {
   try {
      if (is_null($this->_objWorksheet) || !is_object($this->_objWorksheet)) throw new Exception("Fails in XL: no worksheet");
      $cell = null;
      if (is_null($column)) {
         if ($this->_objWorksheet->cellExists($loc))
            $cell = $this->_objWorksheet->getCell($loc);
         }
      elseif ($this->_objWorksheet->cellExistsByColumnAndRow($column, $loc))
         $cell = $this->_objWorksheet->getCellByColumnAndRow($column, $loc);
      if (!is_null($cell)) return $cell->getDataType();
      }
   catch (Exception $e) {
      throw new Exception("Fails in XL: ".$e->getMessage());
      }

   return null;
   }
public function SetCellType($loc, $column=null) {
   try {
      if (is_null($this->_objWorksheet) || !is_object($this->_objWorksheet)) throw new Exception("Fails in XL: no worksheet");
      $cell = null;
      if (is_null($column)) {
         if ($this->_objWorksheet->cellExists($loc))
            $cell = $this->_objWorksheet->getCell($loc);
         }
      elseif ($this->_objWorksheet->cellExistsByColumnAndRow($column, $loc))
         $cell = $this->_objWorksheet->getCellByColumnAndRow($column, $loc);
      if (!is_null($cell)) return $cell->setDataType();
      }
   catch (Exception $e) {
      throw new Exception("Fails in XL: ".$e->getMessage());
      }

   return null;
   }
public function SetColumnStyle($columnstyles) {
	try {
		foreach ($columnstyles as $column=>$style) {
			if (array_key_exists('width',$style)) {
				$width = $style['width'];
				 $this->_objWorksheet->getColumnDimension($column)->setAutoSize(false);
				 $this->_objWorksheet->getColumnDimension($column)->setWidth("{$width}");
			}
		}
	}
	catch (Exception $e) {
		throw new Exception("Fails in Xl ".$e->getMessage());
		}
	return true;
	}

public function SaveAs($filename) {
   try {
		$objWriter = PHPExcel_IOFactory::createWriter($this->_objXl, 'Excel2007');
		$objWriter->save($filename);
		
		$this->_objWorksheet = null;
		$this->objXlReader = null;
		if (!is_null($this->_objXl)) {
		  $this->_objXl->disconnectWorksheets();
		  $this->_objXl->garbageCollect();
		}	
		PHPExcel_CachedObjectStorageFactory::finalize();
		return true;
		}
   catch (Exception $e) {
      throw new Exception("Fails in XL SaveAs: ".$e->getMessage());
      }
   return false;
   }

public function FlushAs($filename) {
	try {
		$objWriter = PHPExcel_IOFactory::createWriter($this->_objXl, 'Excel2007');
		$objWriter->save($filename);
		$objWriter = null;
	  if (!is_null($this->_objXl)) {
		  $this->_objXl->disconnectWorksheets();
		  $this->_objXl->garbageCollect();
	  }
		PHPExcel_CachedObjectStorageFactory::finalize();
		return true;
	}
	catch (Exception $e) {
      throw new Exception("Fails in XL Flush: ".$e->getMessage());
	}
	return false;
}
   
public function Close() {
   try {
      $this->_objWorksheet = null;
      $this->objXlReader = null;
	  if (!is_null($this->_objXl)) {
		  $this->_objXl->disconnectWorksheets();
		  $this->_objXl->garbageCollect();
	  }
//      $this->_objXl = null;
		PHPExcel_CachedObjectStorageFactory::finalize();
      return true;
      }
   catch (Exception $e) {
      throw new Exception("Fails in XL: ".$e->getMessage());
      }
   return false;
   }
}


?>