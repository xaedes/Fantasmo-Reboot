<?php
	
	function writeRom( $rom, $file, $verbose = true ) {
		if( $verbose ) {
			echo "Writing rom to file $file\n";
			echo "address bit width: ${rom["addressBitWidth"]}\n";
			echo "data bit width: ${rom["dataBitWidth"]}\n";
		}
		$fh = fopen($file,"wb");
		$bytesPerCell = floor( $rom["dataBitWidth"] / 8) + ((($rom["dataBitWidth"] % 8) == 0) ? 0 : 1);
		if($verbose) echo "bytes per cell: $bytesPerCell\n";
		foreach($rom["data"] as $cell) {
			//little endian, cells are padded to full bytes
			for( $i = 0; $i < $bytesPerCell; $i++ ) {
				fwrite( $fh, chr( ($cell >> (8 * $i)) & 0xFF ) );
			}
		}
		fclose($fh);
		if($verbose) echo "\n";
	}
?>