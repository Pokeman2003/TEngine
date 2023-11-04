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
		public function refreshRequests() {
			$toFill = array(array(), array());
			array_push($toFill, $this->toFill[2]);
			$this->toFill = $toFill;
			$this->varFile($this->file, 0);
			$this->preen();
		}
		
		function varFile($file, $fileDepth) {
			echo $file . "<br>";
			if ($fileDepth >= tEnMaxFiles)
				return;
			$fileDepth++;
			$fileString = $this->getFile($file);
			if ($fileString == null)
				return;
			$position = 0;
			$isOpen = false;
			while (true) {
				$subStr = substr($fileString, $position, 1);
				
				if ($subStr == "")
					break;
				if ($subStr == tEnTagQ)
					$position++;
				if ($subStr == tEnTagS) {
					$position++;
					$subStr = substr($fileString, $position, 1);
					if ($subStr == tEnConst || $subStr == tEnVar || $subStr == tEnFile || $subStr == tEnTableB) {
						$isOpen = true;
						$finalValue = false;
						$size = 0;
						$start = $position;
					}
				}
				
				// If we have found an open tag, we now parse it.
				while ($isOpen) {
					$position++;
					$subStr = substr($fileString, $position, 1);
					
					if ($subStr == "") {
						$isOpen = false;
					}
					if (($subStr == tEnConst || $subStr == tEnVar) && !$finalValue)
						$start++;
					else
						$finalValue = true;
					
					if ($subStr == tEnTableK)
						$tSize = $position;
					
					if ($subStr == tEnTagE) {
						$size = $position-$start;
						$isOpen = false;
					}
					
					if ($isOpen == false && $size != 0) {
						echo "<br>" . substr($fileString, $start, $size) . "<br>";
					}
				}
				$position++;
			}
		}
		
		function addValue($array, $value) {
			echo $value;
		}
		function preen() {
			
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