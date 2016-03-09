<?php
class UUID{
	public static function create() {

		$s = "123";
		$hexDigits = "0123456789ABCDEF";

		for ( $i = 0; $i < 32; $i++) {
			$s[$i] = $hexDigits[rand(0,15)];
		}
		$s[12] = "4";
		$s[16] = $hexDigits[ ($s[16] & 0x3) | 0x8];

		return $s;
	}
}
?>