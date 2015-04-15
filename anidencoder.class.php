<?php
/**
 *
 * Alpha-numeric unique id encoder.
 *
 * This class creates unique alpha-numeric keys out of unique integer numbers, looking like:
 * OjLf5, evPn0, wfQzm, RlSn0, QfDH4, 2zLf5, Iz1ZxPs, nmKl21b, bj8Xiqm, etc.
 * 
 * 
 * METHODS:
 * static enc($uint, $padTo = 5, $schema = null) : string - wrapper for (new + encode())
 * static dec($string, $schema = null) : uint or FALSE    - wrapper for (new + decode())
 * encode($uint, $padTo = 5) : string
 * decode($string) : uint or FALSE
 * 
 * 
 * USAGE:
 * 
 *  
 * $uniqueNumber = 12345; // your unique numeric id
 * 
 * $uniqueKey = ANIdEncoder::enc($uniqueNumber); // return pv5L3 (with default schema)
 * 
 * OR
 * 
 * $e = new ANIdEncoder;
 * $uniqueKey = $e->encode($uniqueNumber); // return pv5L3 (with default schema)
 * $uniqueKeyPaddedTo4 = $e->encode($uniqueNumber, 4); // return pv5L (with default schema)
 * $uniqueKeyNotPadded = $e->encode($uniqueNumber, 0); // return pv5 (with default schema)
 * 
 * 
 * get numeric value:
 * 
 *  
 * $uniqueKey = 'pv5L3'; // your previously encoded key
 * 
 * $value = ANIdEncoder::dec($uniqueNumber);
 * 
 * 
 * Licensed under GPL v. 2. 
 * PROVIDED AS IS. NO WARRANTY. USE ON YOUR OWN RISK.
 * 
 * Copyright (C) 2015. https://github.com/acat22/anidencoder
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */
class ANIdEncoder {
	// offset set for pseudo-random
	protected $_offsetChars = array('0','1','2','3','4','5','6','7','8','9',
	'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
	'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
	
	/** 
	 * This is the default scheme.
	 * You can change it up to your own tastes by passing via constructor.
	 * 
	 * WARNING:
	 * Always use the same scheme for one stack of values, if you change it, 
	 * the uniqueness fails and you won't be able to retrieve values.
	 */
	protected $_schema = array(
	'W1q3ewRQ2E4rT6tU5y7Ou8i9op0YIPnb',
	'nrmzdxsgtcjkqflvbahw',
	'H8YD5LJ3A7S2RPWQKN1TBU0G4M6F9CVZ',
	'nAXzFdxsBgHcZKjSDkMCfVGlNvmJbLah',
	'1ax5z2mr7wsv3bcg8qn9l4i0dfp6tjky'
	);
	
	// wrapper
	public static function enc($int, $padTo = 5, $schema = null) {
		return (new ANIdEncoder($schema))->encode($int, $padTo);
	}
	
	// wrapper
	public static function dec($key, $schema = null) {
		return (new ANIdEncoder($schema))->decode($key);
	}
	
	/**
	 * @constructor.
	 *
	 * Pass your own schema if you want.
	 *
	 * @param array $schema
	 */
	public function __construct($schema = null) {
		// custom schema
		if ($schema) $this->_schema = $schema;
		
		// pre-generate stuff for better performance
		$this->_flipBase = array_flip($this->_offsetChars);
		
		$this->_charsets = array();
		$this->_flipCharsets = array();
		$this->_rowLengths = array();
		$this->_numRows = count($this->_schema);
		for ($i = 0; $i < $this->_numRows; $i++) {
			$a = $this->_schema[$i];
			
			$s = str_split($a);
			$len = count($s);
			
			$aa = array_merge($s, $s); // double it for better performance in encoding
			$this->_charsets[] = $aa;
			$this->_flipCharsets[] = array_flip($s);
			$this->_rowLengths[] = $len;
		}
	}
	
	/**
	 * Encode the value.
	 * 
	 * Pass the unique UINT numeric value.
	 * Pass the number to which you want the string to be padded. 
	 * Default minimum string length is 5 characters.
	 *
	 * @param uint $int
	 * @param uint $padTo
	 * @return string
	 */
	public function encode($int, $padTo = 5) {
	
		if (!is_numeric($int)) $int = intval($int);
		
		$mod = $int;
		$key = '';
		$char = '';
		
		$row = 0;
		
		// improving performance
		$l = $this->_numRows;
		$charsets = $this->_charsets;
		$lengths = $this->_rowLengths;
		$flipBase = $this->_flipBase;
		$keyLen = 1;
		
		$offsetBase = 0;
		while (true) {
			
			// current row
			$curLen = $lengths[$row];
			
			$pos = $mod;
			// is there still more to be encoded?
			if ($mod >= $curLen) {
				// in the next iteration we will use the leftover
				$mod = floor($mod / $curLen);
				// get the remain value
				$pos -= $mod * $curLen;
			} else {
				if ($padTo && $keyLen < $padTo) {
					// we need to add zeros
					$mod = 0;
				} else {
					// end
					// get char with applied pseudo-random shifting offset
					// finish
					return $key.($charsets[$row][$pos + ($offsetBase % $curLen)]);
				}
			}
			
			// get char with applied pseudo-random shifting offset
			$char = $charsets[$row][$pos + ($offsetBase % $curLen)];
			$key .= $char;
			
			// next row in cycle
			$row++;
			if ($row == $l) $row = 0;
			
			$offsetBase += $flipBase[$char];
			
			$keyLen++;
		}
	}
	
	/**
	 * Decode the value from the key.
	 * 
	 * Pass the key you've encoded before.
	 *
	 * @param string $key
	 * @return uint
	 */
	public function decode($key) {
	
		$key = strval($key);
		
		$int = 0;
		
		$str = str_split($key);
		$strPos = count($str) - 1;
		
		// improving performance
		$l = $this->_numRows;
		$flipCharsets = $this->_flipCharsets;
		$lengths = $this->_rowLengths;
		$flipBase = $this->_flipBase;
		
		// generate offsets table for better performance
		$offsetBase = 0;
		$offsetBases = array($offsetBase);
		for ($i = 0; $i < $strPos; $i++) {
			$offsetBase += $flipBase[$str[$i]];
			$offsetBases[] = $offsetBase;
		}
		
		$row = $strPos % $l;
		$l--;
		
		for ($i = $strPos; $i > -1; $i--) {
			$char = $str[$i];
			
			// current row
			$curLen = $lengths[$row];
			
			$val = $flipCharsets[$row][$char];
			if ($val === NULL) return FALSE; // wrong key
			
			// remove pseudo-random shifting offset
			$val -= $offsetBases[$i] % $curLen;
			if ($val < 0) $val += $curLen;
			
			// power
			$int = ($int * $curLen) + $val;
			
			// next row
			$row--;
			if ($row < 0) $row = $l;
		}
		
		return $int;
	}
}
 