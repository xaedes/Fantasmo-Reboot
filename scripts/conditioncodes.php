Fantasmo-Reboot Condition Code Utils v0.1

<?php
    function usage() {
?>
Usage:
php <?php echo basename(__FILE__); ?> [options] 

  --input[=path]              input file path
   -n                         number items when printing
  --print                     Print all condition codes
  --write[=path]              Write condition code look up table rom file
  --write-all                 Writes all above
  --help ; -h                 This help
  
<?php
    }
    
    $opts = getopt("hn",array("input::","print","write::","help"));


    if( count($opts) == 0 ) {
        usage();
    } else {
        
    }
    
    if( isset( $opts["help"] ) || isset( $opts["h"] ) ) {
        usage();
    }
	
	if( !isset( $opts["input"] ) ) {
		$opts["input"] = "condition_codes.csv";
	}
    
    
    $csv = file($opts["input"],FILE_IGNORE_NEW_LINES || FILE_SKIP_EMPTY_LINES);
	unset($csv[0]);
    
    $conditions = array();
    foreach( $csv as $row ) {
		$row = str_getcsv($row,";");
		$conditions[$row[0]] = array();
		
		for( $i=1; $i<=7; $i++ ) {
			$conditions[$row[0]][] = str_repeat("0", 2-strlen($row[$i])).$row[$i];
		}
    }

	
	
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
	
    if( isset( $opts["print"] ) ) {
        $table=array();
		$lookup = array();
		$lookup["00"] = "?";
		$lookup["01"] = "?";
		$lookup["10"] = "0";
		$lookup["11"] = "1";
		foreach($conditions as $name => $v ) {
			$row=array();
			$row[0]=$name;
			foreach($v as $i=>$f) {
				$row[$i+1] = $lookup[$f];
			}
			$table[]=$row;
		}
		
		require_once("table.php");
		print_table( $table );
		
        echo "\n\n";
    }

	
	require_once( "rom.php" );


	
    if( isset( $opts["write"] ) ) {
		echo "Write microinstruction conditions rom file\n";
		
		//input  : condition code
		//output : flag conditions
		
		if( $opts["write"] == FALSE ) 
			$opts["write"] = "cc.rom";
		
		$rom = array();
		$rom["addressBitWidth"] = 8;
		$rom["dataBitWidth"] = 18;
		// print_r($conditions);
		foreach( $conditions as $name => $v ) {
			$m = "";
			foreach( $v as $k ) {
				$m = $k.$m;
			}
			$rom["data"][] = bindec($m);
		}
		// print_r($rom);
		writeRom( $rom, $opts["write"] );
		
    }
    
    
    
    
?>


