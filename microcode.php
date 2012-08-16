Fantasmo-Reboot Microcode Utils v0.1

<?php
    function usage() {
?>
Usage:
php <?php echo basename(__FILE__); ?> [options] 

   -n                         number items when printing
  --print-dsts                Print all destination registers
  --print-srcs                Print all data sources
  --print-ops                 Print all operation mnemnonics
  --print-regs                Print all registers
  --print-cmds                Print unique microcode instructions
  --print-mc                  Print complete microcode
  --write-mit[="mit.rom"]     Write microinstruction look up table rom file
  --write-mc[="mc.rom"]       Write microcode rom file
  --write-mid[="mid.rom"]     Write microinstruction destinations rom file
  --write-mis[="mis.rom"]     Write microinstruction sources rom file
  --write-mic[="mic.rom"]     Write microinstruction conditions rom file
  --help ; -h                 This help
  
<?php
    }
    
    $opts = getopt("hn",array("print-dsts","print-srcs","print-ops","print-regs","print-cmds","print-mc","write-mit::","write-mc::","write-mid::","write-mis::","write-mic::","help"));


    if( count($opts) == 0 ) {
        usage();
    } else {
        
    }
    
    if( isset( $opts["help"] ) || isset( $opts["h"] ) ) {
        usage();
    }
    
    
    $csv = str_getcsv(str_replace("\n",";", file_get_contents("Detaillierter Ablauf der Befehle.csv")),";");
    // print_r( $csv ); 
    // exit;
    
	function cmd2str( $cmd ) {
		return (isset($cmd["conditional"]) && $cmd["conditional"] ? "C ? " : "").$cmd["dst"]." <- ".$cmd["src"];
	}
	
    $cmds = array();
    $ops = array();
    $dsts = array();
    $srcs = array();
    $regs = array();
	$unique_cmds = array();
    foreach( $csv as $cell ) {
        $cmd = array();
        if( preg_match("#([^\s]+)\s*\?\s*([^\s]+)\s*<-\s*(.+)\s*$#",$cell,$matches) > 0) {
            $cmd = array( "dst" => $matches[2], "src" => $matches[3], "conditional" => $matches[1] );
        } else if( preg_match("#([^\s]+)\s*<-\s*(.+)\s*$#",$cell,$matches) > 0) {
            $cmd = array( "dst" => $matches[1], "src" => $matches[2] );
        } else if( preg_match("#^\s*([A-Z]+)\s*$#",$cell,$matches) > 0 ) {
            $ops[$matches[1]] = true;
			$mit[$matches[1]] = count($cmds);
        }
        if( count($cmd) > 0 ) {
            $cmds[] = $cmd;
            $dsts[$cmd["dst"]] = true;
            $srcs[$cmd["src"]] = true;
            $regs[$cmd["dst"]] = true;
            if( preg_match("#^(\w+)(\[.+)?#",$cmd["src"],$matches) > 0 ) {
                $regs[$matches[1]] = true;
            }
			$unique_cmds[cmd2str($cmd)] = $cmd;			
        }
    }
    $dsts = array_keys($dsts);
    $srcs = array_keys($srcs);
    $ops = array_keys($ops);
    $regs = array_keys($regs);
    sort($dsts);
    sort($srcs);
    // sort($ops);
    sort($regs);
    ksort($unique_cmds);
	
	
	function bits( $n ) {
		return ceil( log($n) / log(2) );;
	}
	function digits( $basis = 10, $n ) {
		return ceil( log(max(1,$n)+1) / log($basis) );;
	}
	
	function dechex_leadingzero( $i, $max ) {
		return str_repeat("0",digits(16,$max)-digits(16,$i)).dechex($i);
	}
	
	function print_items( $items ) {
		global $opts;
		if( isset( $opts["n"] ) ) {
			$n = count($items);
			foreach( array_keys($items) as $i  ) {
				$items[$i] = dechex_leadingzero($i,$n).":".$items[$i];
			}
		}
		echo implode( "\n", $items );
	}
	
    if( isset( $opts["print-dsts"] ) ) {
        $n = count( $dsts );
        $b = bits( $n );
        echo "Destination registers ($n; encodeable in $b bits)\n";
        print_items( $dsts );
        echo "\n\n";
    }
    if( isset( $opts["print-srcs"] ) ) {
        $n = count( $srcs );
        $b = bits( $n );
        echo "Data sources ($n; encodeable in $b bits)\n";
        print_items( $srcs );
        echo "\n\n";
    }
    if( isset( $opts["print-ops"] ) ) {
        $n = count( $ops );
        $b = bits( $n );
        echo "Operation mnemnonics ($n; encodeable in $b bits)\n";
        print_items( $ops );
        echo "\n\n";
    }
    if( isset( $opts["print-regs"] ) ) {
        $n = count( $regs );
        $b = bits( $n );
        echo "Registers ($n; encodeable in $b bits)\n";
        print_items( $regs );
        echo "\n\n";
    }
    if( isset( $opts["print-cmds"] ) ) {
        $n = count( $unique_cmds );
        $b = bits( $n );
        echo "Microcode instructions ($n; encodeable in $b bits)\n";
		print_items( array_keys( $unique_cmds ) );
        echo "\n\n";
    }
	
    if( isset( $opts["print-mc"] ) ) {
        $n = count( $cmds );
        $b = bits( $n );
        echo "Microcode ($n; encodeable in $b bits)\n";
		$items = array();
        foreach( $cmds as $idx => $cmd ) {
			$items[] = cmd2str($cmd);
		}
		print_items( $items );
        echo "\n\n";
    }
	
	function writeRom( $rom, $file ) {
		echo "Writing rom to file $file\n";
		echo "address bit width: ${rom["addressBitWidth"]}\n";
		echo "data bit width: ${rom["dataBitWidth"]}\n";
		$fh = fopen($file,"wb");
		$bytesPerCell = floor( $rom["dataBitWidth"] / 8) + ((($rom["dataBitWidth"] % 8) == 0) ? 0 : 1);
		echo "bytes per cell: $bytesPerCell\n";
		foreach($rom["data"] as $cell) {
			//little endian, cells are padded to full bytes
			for( $i = 0; $i < $bytesPerCell; $i++ ) {
				fwrite( $fh, chr( ($cell >> (8 * $i)) & 0xFF ) );
			}
		}
		fclose($fh);
	}
	

	
    if( isset( $opts["write-mit"] ) ) {
		//Write microinstruction look up table rom file
		
		//input  : opcodes
		//output : offset in microcode
		
		if( $opts["write-mit"] == FALSE ) 
			$opts["write-mit"] = "mit.rom";
		
		$rom = array();
		$rom["addressBitWidth"] = bits( count($ops) );
		$rom["dataBitWidth"] = bits( count($cmds) );

		// print_r( $mit );
		foreach( $ops as $op ) {
			$rom["data"][] = $mit[$op];
		}
		writeRom( $rom, $opts["write-mit"] );
		
    }
    
    if( isset( $opts["write-mc"] ) ) {
		//Write microcode rom file
		
		//input  : offset in microcode
		//output : microcode instruction
		
		if( $opts["write-mc"] == FALSE ) 
			$opts["write-mc"] = "mc.rom";
		
		$cmd_lookup = array_combine( array_keys($unique_cmds), range(0, count($unique_cmds)-1));
		
		$rom = array();
		$rom["addressBitWidth"] = bits( count($cmds) );
		$rom["dataBitWidth"] = bits( count($unique_cmds) );

		foreach( $cmds as $cmd ) {
			$rom["data"][] = $cmd_lookup[ cmd2str( $cmd ) ];
		}
		writeRom( $rom, $opts["write-mc"] );
		
    }
   
    if( isset( $opts["write-mid"] ) ) {
		//Write microinstruction destinations rom file
		
		//input  : microcode instruction
		//output : destination register
		
		if( $opts["write-mid"] == FALSE ) 
			$opts["write-mid"] = "mid.rom";
		
		$dsts_lookup = array_combine( $dsts, range(0, count($dsts)-1));
		
		$rom = array();
		$rom["addressBitWidth"] = bits( count($unique_cmds) );
		$rom["dataBitWidth"] = bits( count($dsts) );

		foreach( $unique_cmds as $cmd ) {
			$rom["data"][] = $dsts_lookup[ $cmd["dst"] ];
		}
		writeRom( $rom, $opts["write-mid"] );
		
    }
     if( isset( $opts["write-mis"] ) ) {
		//Write microinstruction sources rom file
		
		//input  : microcode instruction
		//output : source
		
		if( $opts["write-mis"] == FALSE ) 
			$opts["write-mis"] = "mis.rom";
		
		$srcs_lookup = array_combine( $srcs, range(0, count($srcs)-1));
		
		$rom = array();
		$rom["addressBitWidth"] = bits( count($unique_cmds) );
		$rom["dataBitWidth"] = bits( count($srcs) );

		foreach( $unique_cmds as $cmd ) {
			$rom["data"][] = $srcs_lookup[ $cmd["src"] ];
		}
		writeRom( $rom, $opts["write-mis"] );
		
    }
	
    if( isset( $opts["write-mic"] ) ) {
		//Write microinstruction conditions rom file
		
		//input  : microcode instruction
		//output : conditional
		
		if( $opts["write-mic"] == FALSE ) 
			$opts["write-mic"] = "mic.rom";
		
		$rom = array();
		$rom["addressBitWidth"] = bits( count($unique_cmds) );
		$rom["dataBitWidth"] = 1;

		foreach( $unique_cmds as $cmd ) {
			$rom["data"][] = isset($cmd["conditional"]) && $cmd["conditional"] ? 1 : 0;
		}
		writeRom( $rom, $opts["write-mic"] );
		
    }
    
    
    
    
?>


