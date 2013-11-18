<?php

namespace Fuga\Component\Excel;

class ExcelParser extends ExcelFileParser {
	/*** преобразует из xls-юникода в win1251 */
	function uc2html($str) {
		$ret = '';
		for ($i = 0; $i < strlen($str) / 2; $i++) {
			$charcode = ord($str[$i * 2]) + 256 * ord($str[$i * 2 + 1]);
			if ($charcode == 1105) {
				$ret.= 'ё';
			} else {
				if ($charcode > 175) {
					$char = chr($charcode - 848);
					$ret .= $char;
				} else {
					$ret .= chr($charcode);
				}
			}
		}
		return $ret;
	}
	/*** возвращает нормализованные данные из xls-клетке */
	function process_cell(&$cell) {
		if (!is_array($cell)) {
			return false; // empty cell
		}
		$result = array();
		switch ($cell['type']) {
			// string
			case 0:
			$ind = $cell['data'];
			if( $this->sst['unicode'][$ind] ) {
				$s = $this->uc2html($this->sst['data'][$ind]);
			} else {
				$s = $this->sst['data'][$ind];
			}
			$result['type'] = 'string';
			$result['data'] = $s;
			break;
			// integer number
			case 1:
			$result['type'] = 'int';
			$result['data'] = (int)($cell['data']);
			break;
			// float number
			case 2:
			$result['type'] = 'float';
			$result['data'] = (float)($cell['data']);
			break;
			// date
			case 3:
			$result['type'] = 'date'; //      print "dt_date>";
			$result['timestamp'] = $this->xls2tstamp($cell['data']);
			$result['datetime'] = date('Y-m-d H:i:s', $result['timestamp']);
			$result['data'] = fdate($result['datetime'], 'd.m.Y');
			break;
			default:
			$result['type'] = 'unknown';
			$result['data'] = $cell['data'];
			break;
		}
		return $result;
	}
	/*** import */
	function import($fileName) {
		$ret = false;
		if (!$this->ParseFromFile($fileName)) {
			$ws = $this->worksheet['data'][0];
			if (is_array($ws) && isset($ws['max_row']) && isset($ws['max_col']) ) {
				$ret = array();
				for ($i = 0; $i<= $ws['max_row']; $i++) {
					$ret[$i] = array();
					for ($j = 0; $j<= $ws['max_col']; $j++) {
						if (isset($ws['cell'][$i][$j])) {
							$cell = $this->process_cell($ws['cell'][$i][$j]);
							$ret[$i][$j] = $cell['data'];
						}
					}
				}
				$ret['max_row'] = $ws['max_row'];
				$ret['max_col'] = $ws['max_col'];
			}
		}
		return $ret;
	}
}
