Fantasmo-Reboot Microcode Utils v0.1




	TODO
	TODO
	TODO
	TODO
	TODO
	TODO
	TODO
	TODO
	TODO
	TODO
	TODO
	TODO
	TODO








<?php
    function usage() {
?>
Usage:
php <?php echo basename(__FILE__); ?> [options] 

  --input=path                input file path
   -n                         number items when printing
  --print-dsts                Print all destination registers
  --print-srcs                Print all data sources
  --print-ops                 Print all operation mnemnonics
  --print-regs                Print all registers
  --print-cmds                Print unique microcode instructions
  --print-mc                  Print complete microcode
  --write-mit[="mit.rom"]     Write microinstruction look up table rom file
  --mc[="mc.rom"]       Write microcode rom file
  --write-mid[="mid.rom"]     Write microinstruction destinations rom file
  --write-mis[="mis.rom"]     Write microinstruction sources rom file
  --write-mic[="mic.rom"]     Write microinstruction conditions rom file
  --write-all                 Writes all above
  --help ; -h                 This help
  
<?php
    }
	
    $opts = getopt("hn",array("input","print-dsts","print-srcs","print-ops","print-regs","print-cmds","print-mc","write-mit::","write-mc::","write-mid::","write-mis::","write-mic::","write-all","help"));

	if( isset( $opts["write-all"] ) ) {
		$opts["write-mit"] = false;
		$opts["write-mc"] = false;
		$opts["write-mid"] = false;
		$opts["write-mis"] = false;
		$opts["write-mic"] = false;
	}

    if( count($opts) == 0 ) {
        usage();
    } else {
        
    }
    
    if( isset( $opts["help"] ) || isset( $opts["h"] ) ) {
        usage();
    }
	
	if( !isset( $opts["input"] ) ) {
		$opts["input"] = "Detaillierter Ablauf der Befehle.csv";
	}
	
?>