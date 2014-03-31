<?php

	function print_table( $table, $padding = 1 ) {
		$col_widths = array();
		foreach( $table as $row ) {
			foreach( $row as $i=>$cell ) {
				if( !isset($col_widths[$i]) )
					$col_widths[$i] = 0;
				$col_widths[$i] = max($col_widths[$i],strlen($cell));
			}
		}
		
		foreach( $table as $row ) {
			foreach( $row as $i=>$cell ) {
				$row[$i] = str_repeat(" ",$col_widths[$i]-strlen($cell)).$cell;
			}
			$row = implode(str_repeat(" ",$padding),$row);
			echo "$row\n";
		}
	}
?>