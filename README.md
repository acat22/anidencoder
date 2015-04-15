Alpha-numeric unique id encoder
===============================

This class creates unique alpha-numeric keys out of unique integer numbers, looking like:  
OjLf5, evPn0, wfQzm, RlSn0, QfDH4, 2zLf5, rjWBb, ThAZw, 6c1Cf, tqGA2, 4xLf5,  
YwZVrIm, Iz1ZxPs, Pj7gn3n, nmKl21b, bj8Xiqm, etc.  

It doesn't create words (well, low probability) and the uniqueness is guaranteed.  
It doesn't create stuff like 'aaaaa' or '11111' or 'allow' or 'busy' too.    

METHODS:  
static enc($uint, $padTo = 5, $schema = null) : string - *wrapper for (new + encode())*  
static dec($string, $schema = null) : uint or FALSE    - *wrapper for (new + decode())*  
encode($uint, $padTo = 5) : string  
decode($string) : uint or FALSE  


USAGE:
-----

	$uniqueNumber = 12345; // your unique numeric id

	$uniqueKey = ANIdEncoder::enc($uniqueNumber); // default padding is 5 characters

OR
--

	$e = new ANIdEncoder;
	$uniqueKey = $e->encode($uniqueNumber);             // default padding is 5 characters
	$uniqueKeyPaddedTo4 = $e->encode($uniqueNumber, 4); // padding up to 4 characters
	$uniqueKeyNotPadded = $e->encode($uniqueNumber, 0); // no padding


get numeric value:
------------------

	$uniqueKey = 'pv5L3'; // your previously encoded key

	$value = ANIdEncoder::dec($uniqueNumber);

OR
--

	$e = new ANIdEncoder;
	$value = $e->encode($uniqueKey); // return 12345


Using custom scheme:
--------------------

	$schema = array(
		'1234567890qwertyuiop', 
		'asdfghjklzxcvbnm', 
		'asdfghjkl1234567890'
	); // your custom schema
	
	$e = new ANIdEncoder($schema);

or pass the scheme into the static methods:
------------------------------------------------

	$uniqueKey = ANIdEncoder::enc($uniqueNumber, 5, $schema);



**IMPORTANT:**  
DO NOT CHANGE THE SCHEMA after you've started to enrypt and to store ids with the schema you use.  
Otherwise you won't be able to retrive correct numeric values via decode() and uniqueness fails. 



DESCRIPTION:  
The bigger the number, the more characters will be in the final string. By default, 
it pads up the string to 5 characters if it's shorter.  
The rates and encounters of characters and the similarity of the generated keys to real 
existing english words depend entirely on your scheme.  
The default scheme makes it unlikely for the keys similar to real existing english 
words to be generated. 

ALGORITHM:  
The algorithm uses an array called "scheme", it consists from "rows" - strings of characters.   
They may be uneven and contain different characters sets.   
In each row each character shall be used only once.  
The algorithm divides the number by the length of the current row and encodes the remain with  
it. Then repeats with the leftover, using the next row in cycle, until the whole number is  
encrypted. To provide "pseudo-random", in each step the current row is shifted depending on  
the current state.  

SCHEME IMPROVING:  
You can make your own scheme. Say, if you make a scheme that doesn't contain 'e'  
character in one row and doesn't contain 'n' character in the next row, the combination  
of 'en' can never happen within those two lines. You can check if a word may be generated  
by looking at your scheme.  
Say, the scheme is (for short example) :  
[  
   'abcdeh',  
   'rda890',  
   'MNopTs'  
]  
the following scheme can happen to make keys (if without padding) such as:   
haT, baN, cap, has, caN, c8sad, h9pad  
if you remove/replace 'a' character from the second row, it will never happen.  

You can change the scheme and use your own.  
You may want to make the scheme more neat, lessen numbers of uppercase characters or add  
more numbers.  



Licensed under GPL v. 2. 
PROVIDED AS IS. NO WARRANTY. USE ON YOUR OWN RISK.

Copyright (C) 2015

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
