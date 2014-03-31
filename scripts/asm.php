Fantasmo-Reboot Assembler v0.1

<?php
    function usage() {
?>
Usage:
php <?php echo basename(__FILE__); ?> [options] 

   -i "path"
  --input="path"              input file path
   -o "path"				  
  --output="path"             output file path  
  --help ; -h                 This help
  
<?php
    }
    
    $opts = getopt("o:i:",array("input:","output:","help"));
	// print_r( $opts );
	if( isset( $opts["o"] ) && !isset( $opts["output"] )) {
		$opts["output"] = $opts["o"];
	}
	if( isset( $opts["i"] ) && !isset( $opts["input"] )) {
		$opts["input"] = $opts["i"];
	}

    if( (count($opts) == 0) 
		|| (!isset($opts["input"])) ) {
        usage();
		exit;
    }
	
	if( !isset($opts["output"]) ) {
		$opts["output"] = "a.out";
	}
    
    if( isset( $opts["help"] ) || isset( $opts["h"] ) ) {
        usage();
    }
	
	function asm_lvl_0( $input_file, $output_file ) {
		$file = file( $input_file,FILE_IGNORE_NEW_LINES || FILE_SKIP_EMPTY_LINES);

		$ops = array();
		$ops["nop"]		= array( "opcode" => "00", "params" => array() );
		$ops["ext"]		= array( "opcode" => "01", "params" => array() );
		$ops["reset"]	= array( "opcode" => "02", "params" => array() );
		$ops["ldc"]		= array( "opcode" => "03", "params" => array("register","constant") );
		$ops["ld"]		= array( "opcode" => "04", "params" => array("register","address") );
		$ops["st"]		= array( "opcode" => "05", "params" => array("register","address") );
		$ops["ild"]		= array( "opcode" => "06", "params" => array("register","register") );
		$ops["ist"]		= array( "opcode" => "07", "params" => array("register","register") );
		$ops["mov"]		= array( "opcode" => "08", "params" => array("register","register") );
		$ops["in"]		= array( "opcode" => "09", "params" => array("register","device") );
		$ops["out"]		= array( "opcode" => "0a", "params" => array("register","device") );
		$ops["iin"]		= array( "opcode" => "0b", "params" => array("register","register") );
		$ops["iout"]	= array( "opcode" => "0c", "params" => array("register","register") );
		$ops["jmp"]		= array( "opcode" => "0d", "params" => array("condition","address") );
		$ops["ijmp"]	= array( "opcode" => "0e", "params" => array("condition","register") );
		$ops["addu"]	= array( "opcode" => "1000", "params" => array("register","register") );
		$ops["subu"]	= array( "opcode" => "1001", "params" => array("register","register") );
		$ops["adds"]	= array( "opcode" => "1002", "params" => array("register","register") );
		$ops["subs"]	= array( "opcode" => "1003", "params" => array("register","register") );
		$ops["neg"]		= array( "opcode" => "0f04", "params" => array("register") );
		$ops["not"]		= array( "opcode" => "0f05", "params" => array("register") );
		$ops["and"]		= array( "opcode" => "1006", "params" => array("register","register") );
		$ops["or"]		= array( "opcode" => "1007", "params" => array("register","register") );
		$ops["xor"]		= array( "opcode" => "1008", "params" => array("register","register") );
		$ops["shl"]		= array( "opcode" => "1009", "params" => array("register","register") );
		$ops["shr"]		= array( "opcode" => "1010", "params" => array("register","register") );
		$ops["cmpu"]	= array( "opcode" => "1101", "params" => array("register","register") );
		$ops["cmps"]	= array( "opcode" => "1103", "params" => array("register","register") );
		$ops["flg"]		= array( "opcode" => "12", "params" => array("register") );
		$ops["ldpc"]	= array( "opcode" => "13", "params" => array("register") );
		
		$param_regex = array();
		$param_regex["register"]="r[a-fA-F0-9]+";
		$param_regex["constant"]="0x[a-fA-F0-9]+";
		$param_regex["address"]="0x[a-fA-F0-9]+";
		$param_regex["device"]="0x[a-fA-F0-9]+";
		$param_regex["condition"]="\w+";
		
		$param_size["register"] = 2;
		$param_size["constant"] = 4;
		$param_size["address"] = 4;
		$param_size["device"] = 4;
		$param_size["condition"] = 2;
		
		$conditions = array("T","LT","LEQ","GT","GEQ","EQ","NEQ");
		$conditions_lookup = array_combine( $conditions, array_map("dechex",range(0,count( $conditions )-1)) );
		
		$hex_code = "";
		foreach( $file as $ln => $line ) {
			$ln++;
			
			
			if( preg_match("#^(\w+)(.*)$#",$line,$matches) > 0 ) {
				$this_hex_code = "";
				$op = $matches[1];
				$rest = $matches[2];
				if( !isset($ops[strtolower($op)]) ) {
					echo "Unknown operation '$op' in line $ln: \n";
					echo "$ln : $line\n";
					return;
				}
				$op_info = $ops[strtolower($op)];
				
				$this_hex_code .= $op_info["opcode"];
				
				
				$regex = array();
				foreach( $op_info["params"] as $param ) {
					$regex[] = "(".$param_regex[$param].")";
				}
				$regex = "#^".implode("\s*,\s*",$regex)."$#";
				
				
				if( preg_match($regex,trim($rest), $matches) == 0 ) {
					// echo "$regex\n";
					echo "Parameters not as expected in line $ln: \n";
					echo "$ln : $line\n";
					echo "Expected: $op ".implode(",",$op_info["params"])."\n";
					return;
				} 
				
				
				foreach( $op_info["params"] as $i => $param ) {
					$i++;
					if( $param == "condition" ) {
						if( !isset($conditions_lookup[$matches[$i]]) ) {
							echo "Unknown condition $matches[$i] in line $ln: \n";
							echo "$ln : $line\n";
							echo "Available Conditions: ".implode(", ",$conditions)."\n";
							return;
						}
						$matches[$i] = $conditions_lookup[$matches[$i]];
					} else {
						$matches[$i] = preg_replace("#r|0x#","",$matches[$i]);
					}
					
					$p = str_repeat("0",$param_size[$param]-strlen($matches[$i])).$matches[$i];
					
					for( $i = strlen($p)-2; $i >= 0 ; $i-=2 )
						$this_hex_code .= substr($p,$i,2);
				}
				
				$this_hex_code = str_repeat("0",strlen($this_hex_code) % 4).$this_hex_code;
				
				echo "$this_hex_code\n";
				$hex_code .= $this_hex_code;
			}
		}
		require_once("rom.php");
		$rom = array();
		$rom["addressBitWidth"] = 8;
		$rom["dataBitWidth"] = 16;
		
		for( $i = 0; $i < strlen($hex_code); $i+=4 ) {
			$rom["data"][] = hexdec( substr($hex_code,$i+2,2).substr($hex_code,$i,2)  );
		}
		writeRom( $rom, $output_file );
		echo "$hex_code\n";
	}
	asm_lvl_0( $opts["input"], $opts["output"] );
?>