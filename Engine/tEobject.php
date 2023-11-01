<?php
	class tEngine {
		public function __construct($file) {
			$this->file = $file;
			$this->path = "";
			
			$pathTemp = explode("/", $file);
			for ($i = 0; $i < sizeof($pathTemp)-1; $i++)
				$this->path = $this->path . $pathTemp[$i] . "/";
		
			if (!file_exists($file))
				return null;
			$this->refreshVariables();
		}
		
		function getFile($file) {
			if (file_exists($this->path . $file))
				return file_get_contents($this->path . $file);
			if (file_exists($file))
				return file_get_contents($file);
			if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . $file))
				return 0;
			return null;
		}
		
		function refreshVariables() {
			$this->toFill = array(array(), array(), array());
			$this->varFile($this->file, 0);
		}
		
		function varFile($file, $fileDepth) {
			if ($fileDepth >= tEnMaxFiles)
				return;
			$fileDepth++;
			$fileString = $this->getFile($file);
			if ($fileString == null)
				return;
			$position = 0;
			$isOpen = false;
			while (true) {
				$tString = substr($fileString, $position, 1);
				//echo $tString;
				if ($tString == "")
					break;
				if ($tString == tEnTagQ)
					$position++;
				if ($tString == tEnTagS) {
					$position++;
					$type = substr($fileString, $position, 1);
					if ($type == tEnConst || $type == tEnVar || $type == tEnFile ||$type == tEnTableB)
						$isOpen = true;
				}
				$position++;
				while ($isOpen) {
					$tString = substr($fileString, $position, 1);
					
					
					if ($tString == "")
						break;
					if ($type == tEnTableB && $tString == tEnTableF)
						$size = $tEnTableK;
					if ($tString == tEnTagE) {
						if ($type == tEnTableB) {
							
						}
						else {
							$size = $position-1;
							if ($type == tEnConst)
								$this->addValue($this->toFill[0], substr($fileString, $position-$size, $position-$size));
						}
						$isOpen = false;
					}
					$position++;
				}
			}
		}
		
		function addValue($array, $value) {
			echo $value;
		}
		
		public function construct() {
			$output = "";
			
			return $output;
		}
		
		function constructFile($file) {
			
		}
		
		public function setVariable($index, $value) {
			$toComplete = false;
			foreach ($this->toFill[1] as $testing) {
				if ($testing == $index) {
					$toComplete = true;
					break;
				}
			}
			echo $toComplete;
			if ($toComplete == false)
				return false;
			
			$this->toFill[1][$index] = $value;
			return true;
		}
		
		public function returnVars() {
			return $this->toFill[1];
		}
		public function returnConsts() {
			return $this->toFill[0];
		}
		public function returnSets() {
			return $this->toFill[2];
		}
	}
?>