<?php

class HDA_EasyXL {
public const ALIGNMENT_ALIGNMENT_GENERAL = "general";
public const ALIGNMENT_ALIGNMENT_CENTER = "center";
public const ALIGNMENT_ALIGNMENT_LEFT = "left";
public const ALIGNMENT_ALIGNMENT_RIGHT = "right";
public const ALIGNMENT_ALIGNMENT_FILL = "fill";
public const ALIGNMENT_ALIGNMENT_JUSTIFY = "justify";
public const ALIGNMENT_ALIGNMENT_CENTER_ACROSS_SELECTION = "center across selection";
public const ALIGNMENT_ALIGNMENT_DISTRIBUTED = "distributed";
public const ALIGNMENT_ALIGNMENT_TOP = "top";
public const ALIGNMENT_ALIGNMENT_MIDDLE = "middle";
public const ALIGNMENT_ALIGNMENT_BOTTOM = "bottom";
public const TEXT_DIRECTION_CONTEXT = 0;
public const TEXT_DIRECTION_LEFT_TO_RIGHT = 1;
public const TEXT_DIRECTION_RIGHT_TO_LEFT = 2;

public const BORDER_BORDER_NONE = 0;
public const BORDER_BORDER_THIN = 1;
public const BORDER_BORDER_MEDIUM = 2;
public const BORDER_BORDER_DASHED = 3;
public const BORDER_BORDER_DOTTED = 4;
public const BORDER_BORDER_THICK = 5;
public const BORDER_BORDER_DOUBLE = 6;
public const BORDER_BORDER_HAIR = 7;
public const BORDER_BORDER_MEDIUM_DASHED = 8;
public const BORDER_BORDER_DASH_DOT = 9;
public const BORDER_BORDER_MEDIUM_DASH_DOT = 10;
public const BORDER_BORDER_DASH_DOT_DOT = 11;
public const BORDER_BORDER_MEDIUM_DASH_DOT_DOT = 12;
public const BORDER_BORDER_SLANTED_DASH_DOT = 13;

public const COLOR_ALICEBLUE = 0xf8f0;
public const COLOR_ANTIQUEWHITE = 0xd7ebfa;
public const COLOR_AQUA = 0xff00;
public const COLOR_AQUAMARINE = 0xd4ff7f;
public const COLOR_AZURE = 0xfffff0;
public const COLOR_BEIGE = 0xdcf5f5;
public const COLOR_BISQUE = 0xc4e4ff;
public const COLOR_BLACK = 0x000000;
public const COLOR_BLANCHEDALMOND = 0xcdebff;
public const COLOR_BLUE = 0xff0000;
public const COLOR_BLUEVIOLET = 0xe22b8a;
public const COLOR_BROWN = 0x2a2aa5;
public const COLOR_BURLYWOOD = 0x87b8de;
public const COLOR_CADETBLUE = 0xa09e5f;
public const COLOR_CHARTREUSE = 0x00ff7f;
public const COLOR_CHOCOLATE = 0x1e69d2;
public const COLOR_CORAL = 0x507fff;
public const COLOR_CORNFLOWERBLUE = 0xed9564;
public const COLOR_CORNSILK = 0xdcf8ff;
public const COLOR_CRIMSON = 0x3c14dc;
public const COLOR_CYAN = 0xffff00;
public const COLOR_DARKBLUE = 0x8b0000;
public const COLOR_DARKCYAN = 0x8b8b00;
public const COLOR_DARKGOLDENROD = 0x0b86b8;
public const COLOR_DARKGRAY = 0xa9a9a9;
public const COLOR_DARKGREEN = 0x006400;
public const COLOR_DARKKHAKI = 0x6bb7bd;
public const COLOR_DARKMAGENTA = 0x8b008b;
public const COLOR_DARKOLIVEGREEN = 0x2f6b55;
public const COLOR_DARKORANGE = 0x008cff;
public const COLOR_DARKORCHID = 0xcc3299;
public const COLOR_DARKRED = 0x00008b;
public const COLOR_DARKSALMON = 0x7a96e9;
public const COLOR_DARKSEAGREEN = 0x8bbc8f;
public const COLOR_DARKSLATEBLUE = 0x8b3d48;
public const COLOR_DARKSLATEGRAY = 0x4f4f2f;
public const COLOR_DARKTURQUOISE = 0xd1ce00;
public const COLOR_DARKVIOLET = 0xd30094;
public const COLOR_DEEPPINK = 0x9314ff;
public const COLOR_DEEPSKYBLUE = 0xffbf00;
public const COLOR_DIMGRAY = 0x696969;
public const COLOR_DODGERBLUE = 0xff901e;
public const COLOR_FIREBRICK = 0x2222b2;
public const COLOR_FLORALWHITE = 0xf0faff;
public const COLOR_FORESTGREEN = 0x228b22;
public const COLOR_FUCHSIA = 0xff00ff;
public const COLOR_GAINSBORO = 0xdcdcdc;
public const COLOR_GHOSTWHITE = 0xfff8f8;
public const COLOR_GOLD = 0x00d7ff;
public const COLOR_GOLDENROD = 0x20a5da;
public const COLOR_GRAY = 0x808080;
public const COLOR_GREEN = 0x008000;
public const COLOR_GREENYELLOW = 0x2fffad;
public const COLOR_HONEYDEW = 0xf0fff0;
public const COLOR_HOTPINK = 0xb469ff;
public const COLOR_INDIANRED = 0x5c5ccd;
public const COLOR_INDIGO = 0x82004b;
public const COLOR_IVORY = 0xf0ffff;
public const COLOR_KHAKI = 0x8ce6f0;
public const COLOR_LAVENDER = 0xfae6e6;
public const COLOR_LAVENDERBLUSH = 0xf5f0ff;
public const COLOR_LAWNGREEN = 0x00fc7c;
public const COLOR_LEMONCHIFFON = 0xcdfaff;
public const COLOR_LIGHTBLUE = 0xe6d8ad;
public const COLOR_LIGHTCORAL = 0x8080f0;
public const COLOR_LIGHTCYAN = 0xffffe0;
public const COLOR_LIGHTGOLDENRODYELLOW = 0xd2fafa;
public const COLOR_LIGHTGREEN = 0x90ee90;
public const COLOR_LIGHTGRAY = 0xd3d3d3;
public const COLOR_LIGHTPINK = 0xc1b6ff;
public const COLOR_LIGHTSALMON = 0x7aa0ff;
public const COLOR_LIGHTSEAGREEN = 0xaab220;
public const COLOR_LIGHTSKYBLUE = 0xface87;
public const COLOR_LIGHTSLATEGRAY = 0x998877;
public const COLOR_LIGHTSTEELBLUE = 0xdec4b0;
public const COLOR_LIGHTYELLOW = 0xe0ffff;
public const COLOR_LIME = 0x00ff00;
public const COLOR_LIMEGREEN = 0x32cd32;
public const COLOR_LINEN = 0xe6f0fa;
public const COLOR_MAGENTA = 0xff00ff;
public const COLOR_MAROON = 0x000080;
public const COLOR_MEDIUMAQUAMARINE = 0xaacd66;
public const COLOR_MEDIUMBLUE = 0xcd0000;
public const COLOR_MEDIUMORCHID = 0xd355ba;
public const COLOR_MEDIUMPURPLE = 0xdb7093;
public const COLOR_MEDIUMSEAGREEN = 0x71b33c;
public const COLOR_MEDIUMSLATEBLUE = 0xee687b;
public const COLOR_MEDIUMSPRINGGREEN = 0x9afa00;
public const COLOR_MEDIUMTURQUOISE = 0xccd148;
public const COLOR_MEDIUMVIOLETRED = 0x8515c7;
public const COLOR_MIDNIGHTBLUE = 0x701919;
public const COLOR_MINTCREAM = 0xfafff5;
public const COLOR_MISTYROSE = 0xe1e4ff;
public const COLOR_MOCCASIN = 0xb5e4ff;
public const COLOR_NAVAJOWHITE = 0xaddeff;
public const COLOR_NAVY = 0x800000;
public const COLOR_OLDLACE = 0xe6f5fd;
public const COLOR_OLIVE = 0x008080;
public const COLOR_OLIVEDRAB = 0x238e6b;
public const COLOR_ORANGE = 0x00a5ff;
public const COLOR_ORANGERED = 0x0045ff;
public const COLOR_ORCHID = 0xd670da;
public const COLOR_PALEGOLDENROD = 0xaae8ee;
public const COLOR_PALEGREEN = 0x98fb98;
public const COLOR_PALETURQUOISE = 0xeeeeaf;
public const COLOR_PALEVIOLETRED = 0x9370db;
public const COLOR_PAPAYAWHIP = 0xd5efff;
public const COLOR_PEACHPUFF = 0xb9daff;
public const COLOR_PERU = 0x3f85cd;
public const COLOR_PINK = 0xcbc0ff;
public const COLOR_PLUM = 0xdda0dd;
public const COLOR_POWDERBLUE = 0xe6e0b0;
public const COLOR_PURPLE = 0x800080;
public const COLOR_RED = 0x0000ff;
public const COLOR_ROSYBROWN = 0x8f8fbc;
public const COLOR_ROYALBLUE = 0xe16941;
public const COLOR_SADDLEBROWN = 0x13458b;
public const COLOR_SALMON = 0x7280fa;
public const COLOR_SANDYBROWN = 0x60a4f4;
public const COLOR_SEAGREEN = 0x578b2e;
public const COLOR_SEASHELL = 0xeef5ff;
public const COLOR_SIENNA = 0x2d52a0;
public const COLOR_SILVER = 0xc0c0c0;
public const COLOR_SKYBLUE = 0xebce87;
public const COLOR_SLATEBLUE = 0xcd5a6a;
public const COLOR_SLATEGRAY = 0x908070;
public const COLOR_SNOW = 0xfafaff;
public const COLOR_SPRINGGREEN = 0x7fff00;
public const COLOR_STEELBLUE = 0xb48246;
public const COLOR_TAN = 0x8cb4d2;
public const COLOR_TEAL = 0x808000;
public const COLOR_THISTLE = 0xd8bfd8;
public const COLOR_TOMATO = 0x4763ff;
public const COLOR_TURQUOISE = 0xd0e040;
public const COLOR_VIOLET = 0xee82ee;
public const COLOR_WHEAT = 0xb3def5;
public const COLOR_WHITE = 0xffffff;
public const COLOR_WHITESMOKE = 0xf5f5f5;
public const COLOR_YELLOW = 0x00ffff;
public const COLOR_YELLOWGREEN = 0x32cd9a;
public const DATATYPE_NUMERIC =  "numeric";
public const DATATYPE_STRING  = "string";
public const DATATYPE_DATE  = "date";
public const DATATYPE_AUTOMATIC ="automatic";
public const DATATYPE_ERROR ="error";
public const DATATYPE_BOOLEAN ="boolean";

public const FORMAT_FORMAT_GENERAL =  "General";
public const FORMAT_FORMAT_INTEGER =  "0";
public const FORMAT_FORMAT_FLOAT_2DECIMALS  = "0.00";
public const FORMAT_FORMAT_INTEGER_PERCENT =  "0%";
public const FORMAT_FORMAT_FLOAT_2DECIMALS_PERCENT =  "0.00%";
public const FORMAT_FORMAT_DATE = "MM/dd/yyyy";
public const FORMAT_FORMAT_DATE_TIME =  "MM/dd/yyyy HH:mm:ss";
public const FORMAT_FORMAT_CURRENCY  = "$0.00";
public const FORMAT_FORMAT_AS_HALVES  = "# ?/2";
public const FORMAT_FORMAT_AS_QUARTERS =  "# ?/4";
public const FORMAT_FORMAT_AS_TENTHS  = "# ?/10";

	
	public function __construct()
	{
	$this->workbook = new COM("EasyXLS.ExcelDocument");

	}
	public $workbook;
	public $onsheet;
	public $is_open;
	public $limit_rows;
	public $limit_columns;
	public $table;
	
    public function __destruct() {
		if ($this->workbook != null) {
			$this->workbook->Dispose();
			$this->workbook = null;
		}

	}
	public function open($file_to_read) {
		$path = pathinfo($file_to_read);
		try {
			switch (strtolower($path['extension'])) {
				case 'xlsb': $this->is_open = $this->workbook->easy_LoadXLSBFile($file_to_read); break;
				case 'xlsx': $this->is_open = $this->workbook->easy_LoadXLSXFile($file_to_read); break;
				}
			return $this->is_open;
		}
		catch (Exception $r) {
			throw new Exception($this->workbook->easy_getError());
		}
		return false;
	}
	public function load($sheet=null) {
		try {
			if ($this->is_open){
				if (!is_null($sheet)) {
					$this->onsheet =  $this->workbook->easy_getSheet($sheet);
					$this->table = $this->onsheet->easy_getExcelTable();
					$this->limit_columns = $this->table->ColumnCount();
					$this->limit_rows = $this->table->RowCount()+1;
					$calc_error = $this->workbook->easy_getSheet($sheet)->easy_computeFormulas($this->workbook, true);
					if (strlen($calc_error)>0) throw new Exception($calc_error);
				}
				return true;
			}
			return false;
		}
		catch (Exception $r) {
			throw new Exception($this->workbook->easy_getError());
		}
	}
	public function asArray() {
		return null;
	}
public function WriteTable($table, $start_row) {
	try {
		$row = $start_row;
		$column = 0;
		$rotate_headers = 90;
		if (is_null($table)) return true;
		if (count($table)==0) return true;
		$this->table->setRowCount(count($table)+1);
		$on_row = $row;
		$on_column = $column;
		$in_table = $this->table->RowCount();
		foreach ($table[0] as $field=>$value) {
			$cell = $this->table->easy_getCell($on_row, $on_column); $cell->setValue($field);
			$cell->setForeground((int)HDA_EasyXL::COLOR_YELLOW);
			$cell->setBackground((int)HDA_EasyXL::COLOR_BLUE);
			$cell->setBorderColors((int)HDA_EasyXL::COLOR_WHITE,(int)HDA_EasyXL::COLOR_WHITE,(int)HDA_EasyXL::COLOR_WHITE,(int)HDA_EasyXL::COLOR_WHITE);
			$cell->setBorderStyles((int)HDA_EasyXL::BORDER_BORDER_MEDIUM,(int)HDA_EasyXL::BORDER_BORDER_MEDIUM,(int)HDA_EasyXL::BORDER_BORDER_MEDIUM,(int)HDA_EasyXL::BORDER_BORDER_MEDIUM);
			$this->table->easy_setCellAt($cell, $row, $column);
			$on_column += 1;
		}
		$on_row++;
		foreach($table as $rn=>$row) {
			$on_column = $column;
			foreach($row as $field=>$value) {
				$this->table->easy_getCell($on_row,$on_column)->setValue($value);
				$on_column++;
			}
			$on_row++;
		}
	}
	catch(Exception $e) {
			throw new Exception("Write Table {$e} ".$this->workbook->easy_getError());;
		}
	return true;
	}
	public function saveAs($file_path, $extension) {
		switch ($extension) {
			case 'xlsb':
				return $this->workbook->easy_WriteXLSBFile($file_path.".xlsb");
			case 'xlsx':
			default:
				return $this->workbook->easy_WriteXLSXFile($file_path.".xlsx");
		}
	return null;
	}
	
	public function sheet_limits() {
		if ($this->is_open) {
			return array('ROWS'=>$this->limit_rows, 'COLUMNS'=>$this->limit_columns);
		}
		else return null;
	}
	
	public function validate() {
		try {
			file_put_contents("tmp/validate.txt",print_r(get_class_methods($this->table),true));
		//$this->validate();
		}
		catch (Exception $e) {
			throw new Exception("Validate {$e}  ".$this->workbook->easy_getError());
			}
	}
	
	public function getCell($pos) {
		try {
			$cell =  $this->table->easy_getCellAt($pos);
			if ($cell==null) return null;
			if ($cell->containsFormula()) {
				$nvalue = $cell->getFormulaResultValue();
			//	$cell->setValue($nvalue);
			}
			else $nvalue = $cell->getValue();
			$value = $cell->getValue();
			$type = $cell->getDataType();
			$fvalue = $cell->getFormattedValue();
			
			//file_put_contents("tmp/dump.txt",file_get_contents("tmp/dump.txt")." cell {$pos} {$value} {$nvalue} {$fvalue} {$type}\n\r");
			return $nvalue;

		}
		catch (Exception $e) {
			return null;
			}
	}
	public function getCellDetails($row,$column) {
		try {
			$cell =  $this->table->easy_getCellAt($row,$column);
			if ($cell==null) return null;
			if ($cell->containsFormula()) {
				$nvalue = $cell->getFormulaResultValue();
			//	$cell->setValue($nvalue);
			}
			else $nvalue = $cell->getValue();
			$rvalue = $cell->getFormulaResultValue();
			$value = $cell->getValue();
			$type = $cell->getDataType();
			$fvalue = $cell->getFormattedValue();
			$a = array();
			$a['value'] = $value;
			$a['type'] = $type;
			$a['fvalue'] = $fvalue;
			$a['nvalue'] = $nvalue;
			$a['rvalue'] = $rvalue;
			
			return $a;

		}
		catch (Exception $e) {
			file_put_contents("tmp/dump.txt","Cell Details {$e}");
			return false;
			}
	}
	public function getCellAt($row,$column) {
		try {
			$cell =  $this->table->easy_getCellAt($row,$column);
			if ($cell==null) return null;
			if ($cell->containsFormula()) {
				$nvalue = $cell->getFormulaResultValue();
				//$cell->setValue($nvalue);
			} 
			else {
				$type = $cell->getDataType();
				switch ($type) {
					case "date":
					case "numeric":
						$nvalue = $cell->getValue(); break;
					default: $nvalue = $cell->getFormattedValue();
				}
			}
			return $nvalue;
		}
		catch (Exception $e) {
			return null;
			}
	}
	public function getFormat($row, $column) {
		try {
			$cell =  $this->table->easy_getCellAt($row,$column);
			if ($cell==null) return null;
			return $cell->getFormat();
		}
		catch (Exception $e) {
			return null;
			}
	}
	public function setFormat($row, $column, $format) {
		try {
			$cell =  $this->table->easy_getCellAt($row,$column);
			if ($cell==null) return null;
			$format = 'General';
			$e = $cell->setFormat($format);
			$f = $cell->getFormattedValue();
			return $f;
		}
		catch (Exception $e) {
			throw new Exception("SetFormat {$e} {$row} {$column} {$format} ".$this->workbook->easy_getError());
			}
	}
	public function setColumnFormat($column, $format) {
		try {
			$column =  $this->table->easy_getColumnAt($column);
			if ($column==null) return null;
			return $column->setFormat($format);
		}
		catch (Exception $e) {
			throw new Exception("SetColumnFormat {$e} {$column} {$format} ".$this->workbook->easy_getError());
			}
	}
	public function getCellType($row, $column) {
		try {
			$cell =  $this->table->easy_getCellAt($row,$column);
			if ($cell==null) return null;
			return $cell->getDataType();
		}
		catch (Exception $e) {
			return null;
			}
	}
	public function setCellType($row, $column, $type) {
		try {
			$cell =  $this->table->easy_getCell($row,$column);
			if ($cell==null) return null;
			$cell->setDataType($type); //(HDA_EasyXL::DATATYPE_DATE);
			$ntype = $cell->getDataType();
			return $ntype;
		}
		catch (Exception $e) {
			return null;
			}
	}
	public function makeCell() {
		return  new COM("EasyXLS.ExcelCell");
	}
	public function setCellAt($row, $column, $value, $html=false) {
		try {
			$cell = $this->table->easy_getCell($row, $column);
			//$cell->setDataType(HDA_EasyXL::DATATYPE_STRING);
			if ($html) $cell->setHTMLValue($value);
			else {
				$cell->setValue($value);
				$this->table->easy_setCellAt($cell, $row, $column);
			}
			//file_put_contents("tmp/dump.txt",file_get_contents("tmp/dump.txt")." cell {$row} {$column} {$value} ");
			return true;
		}
		catch (Exception $e) {
			throw new Exception("Set Cell At {$row} {$column} {$value} {$e} ".$this->workbook->easy_getError());;
			}
	}
	public function setTitleCells($row, $column, $from_row, $from_col, $to_row, $to_col, $title, $color) {
		try {
			$this->table->easy_mergeCells($from_row,$from_col,$to_row,$to_col);
			$cell =  $this->table->easy_getCellAt($row, $column);
			if ($cell==null) $cell =  $this->makeCell();
			$cell->setValue($title);
			if ($color != null) $cell->setBackground(hexdec($color));
			$cell->setHorizontalAlignment("center");
			$this->table->easy_setCellAt($cell, $row, $column);
		}
		catch (Exception $e) {
			throw new Exception("Set Title Cells ".$e);;
			}
	}
	public function setColumnWidth($column, $width) {
		try {
		//	$i = $this->table->getColumnWidth();
		//	$this->table->setColumnWidth_2($column-1, -1);
			$c = $this->table->easy_getColumnAt($column-1);
			$c->setWrap(false);
			$c->setWidth($width);
		}
		catch (Exception $e) {
			throw new Exception("Set Column Width ".$e);;
			}
	}
	public function setCellColor($row, $column, $fore_color, $back_color) {
		try {
			$cell =  $this->table->easy_getCellAt($row,$column);
			if (is_null($cell)) $cell =  $this->makeCell();
			if (!is_null($fore_color)) $cell->setForeground(hexdec($fore_color));
			if (!is_null($back_color)) $cell->setBackground(hexdec($back_color));
			$this->table->easy_setCellAt($cell, $row, $column);
			$a = array();
			$a['fore'] = $cell->getForeground();
			$a['back'] = $cell->getBackground();
			return $a;
		}
		catch (Exception $e) {
			throw new Exception("Set Cell Color ".$e);;
			}
	}
	public function setStyle($row, $column, $fore_color=null, $back_color=null, $border=null){
		$xlsStyleHeader = new COM("EasyXLS.ExcelStyle");
		$xlsStyleHeader->setFont("Verdana");
		$xlsStyleHeader->setFontSize(10);
		$xlsStyleHeader->setItalic(false);
		$xlsStyleHeader->setBold(false);
		$xlsStyleHeader->setForeground((int)$fore_color);
		$xlsStyleHeader->setBackground((int)$back_color);
		$xlsStyleHeader->setBorderColors ((int)HDA_EasyXL::COLOR_GRAY, (int)HDA_EasyXL::COLOR_GRAY, (int)HDA_EasyXL::COLOR_GRAY, (int)HDA_EasyXL::COLOR_GRAY);
		$xlsStyleHeader->setBorderStyles (HDA_EasyXL::BORDER_BORDER_MEDIUM, HDA_EasyXL::BORDER_BORDER_MEDIUM, 
											HDA_EasyXL::BORDER_BORDER_MEDIUM, HDA_EasyXL::BORDER_BORDER_MEDIUM);
		$xlsStyleHeader->setHorizontalAlignment(HDA_EasyXL::ALIGNMENT_ALIGNMENT_CENTER);
		$xlsStyleHeader->setVerticalAlignment(HDA_EasyXL::ALIGNMENT_ALIGNMENT_BOTTOM);
		$xlsStyleHeader->setWrap(false);
		//$xlsStyleHeader->setDataType($DATATYPE_STRING);
		$this->table->easy_getCell($row,$column)->setStyle($xlsStyleHeader);

	}
	public function makeSheet($sheetName) {
		try {
			return $this->workbook->easy_addWorksheet_2($sheetName);
			}
		catch (Exception $r) {
			throw new Exception("Make Sheet error ".$r);;
			//throw new Exception($this->workbook->easy_getError());;
		}
	}
	public function removeSheet($sheetName) {
		try {
			$this->workbook->easy_removeSheet($sheetName);
			}
		catch (Exception $r) {
			throw new Exception("Remove Sheet error ".$r);;
			//throw new Exception($this->workbook->easy_getError());;
		}
	}
	public function getSheets() {
		try {
			$sheets = array();
			$sheet_n = 1;
			$finding_sheets = true;
			while ($finding_sheets) {
				try {
					$sheet = $this->workbook->easy_getSheetAt($sheet_n);
					if ($sheet != null) $sheets[] = $sheet->getSheetName(); 
				}
				catch (Exception $e) { $finding_sheets = false; }
				$sheet_n += 1;
			}
			
			return $sheets;
			}
		catch (Exception $r) {
			throw new Exception("Get Sheets error ".$r);;
			//throw new Exception($this->workbook->easy_getError());;
		}
	}
	public function removeRows($r1, $nRows) {
		try {
			return $this->table->easy_removeRowRange($r1, $nRows);
			}
		catch (Exception $r) {
			throw new Exception("Remove Row Range error ".$r);;
		}
	}
	public function close() {
		if ($this->table != null) {
			$this->table->Dispose();
			$this->table = null;
		}
		if ($this->workbook != null) {
			$this->workbook->Dispose();
			$this->workbook = null;
		}
	}
	public function debug() {
		return print_r($this,true);
	}
	public function last_error() {
		return $this->workbook->easy_getError();
	}
}

?>