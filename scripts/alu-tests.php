<?php
	//This script generates a rom file for testing the ALU
	
	require_once( "rom.php" );
	
	$rom = array();
	// $rom["addressBitWidth"] = bits( count($ops) );
	// $rom["dataBitWidth"] = bits( count($cmds) );

	
	$ops = array("add_us","sub_us", "add_s", 
		"sub_s", "neg", "not", "and", "or", "xor",
		"shl", "shr");
	
	function shl($a,$b,&$r,&$f) {
		$r = ($a << $b) && 0xFFFF;
		
	}
	function shr($a,$b,&$r,&$f) {
		$r = ($a >> $b) && 0xFFFF;
	}
	
	foreach( $ops as $op ) {
		$rom["data"][] = $mit[$op];
	}
	writeRom( $rom, $opts["write-mit"] );

?>