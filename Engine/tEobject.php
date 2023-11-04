<?php
	class tEngine {
		// Constructor
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
		
		// Tries to acquire the file's contents. If not, it returns null.
		function getFile($file) {
			if (file_exists($this->path . $file))
				return file_get_contents($this->path . $file);
			if (file_exists($file))
				return file_get_contents($file);
			if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . $file))
				return 0;
			return null;
		}
		// This is only used once. It dumps all variables, and then attempts to process this file.
		function refreshVariables() {
			$this->toFill = array(array(), array(), array());
			$this->varFile($this->file, 0);
		}
		
		// This is the public version of the above to functions. It dumps the 2 index tables, and then calls upon the variable processor.
		public function refreshRequests() {
			$toFill = array(array(), array());
			array_push($toFill, $this->toFill[2]);
			$this->toFill = $toFill;
			$this->varFile($this->file, 0);
			$this->preen();
		}
		
		// The variable processor. Complex. Way too complex.
		function varFile($file, $fileDepth) {
			// Some basic testing. Test if the file depth is too much to process, if the file even exists...
			if ($fileDepth >= tEnMaxFiles)
				return;
			$fileDepth++;
			$fileString = $this->getFile($file);
			if ($fileString == null)
				return;
			
			// Some basic setup.
			$position = 0;
			$isOpen = false;
			
			// The actual processor.
			while (true) {
				$subStr = substr($fileString, $position, 1); // The current character to process.
				
				// Some basic checks.
				if ($subStr == "") // End of the file.
					break;
				if ($subStr == tEnTagQ) // Skip a character if the break key as been called upon.
					$position++;
				
				if ($subStr == tEnTagS) { // If we have the start of a tag, move forward one and determine WHICH tag it is.
					$position++;
					$subStr = substr($fileString, $position, 1);
					if ($subStr == tEnConst || $subStr == tEnVar || $subStr == tEnFile || $subStr == tEnTableB) {
						$isOpen = true;
						$finalValue = false;
						$size = 0;
						$start = $position;
						$tPosition = null;
						$primaryIn = $subStr;
					} // If none of these match, it'll prevent the open tag parsing.
				}
				
				// If we have found an open tag, we now parse it.
				while ($isOpen) {
					$size++;
					$subStr = substr($fileString, $position+$size, 1);
					
					if ($subStr == "") // If we've found the end of file, then yeah... just close this.
						$isOpen = false;
					if (($subStr == tEnConst || $subStr == tEnVar || $subStr == tEnFile) && !$finalValue) // We're looking for the variable input chains. 
						$start++;
					else
						$finalValue = true;
					
					if ($subStr == tEnTableK && $primaryIn == tEnTableB) { // When we find a table's K value, then we unlock finalValue and change the tPosition.
						$tPosition = $size;
						$tStart = $start;
						$finalValue = false;
						$start = $size;
					}
					
					if ($subStr == tEnTagE) // When we find the end tag, we know it's ended.
						$isOpen = false;
					
					if ($isOpen == false && $size != 0) {
						if ($primaryIn != tEnTableB) { // Regular variables.
							$inProcess = substr($fileString, $start, $size);
							if ($start != $position) { // Okay, so basically, we start trying to resolve that chain.
								$testingVariable = substr($fileString, $start+1, $size-($start-$position+1));
								while (true) {
									$toTest = substr($fileString, $start, 1);
									$resolutionTest = $this->resolveValue($testingVariable, $toTest);
									if ($resolutionTest == null) // If we've encountered an unsolvable test, abandon the plan.
										break;
									$testingVariable = $resolutionTest;
									$start--;
									if (substr($fileString, $start-1, 1) == tEnTagS) // If the second to next tag would've been the start tag, then we've reached the end of what we needed to do.
										break;
								}
								$inProcess = substr($fileString, $start, 1) . $testingVariable; // Now we replace the intended variable with THIS~!
							}
							// Now we get to the good part.
							switch (substr($inProcess, 0, 1)) {
								
								case tEnConst:
									array_push($this->toFill[0], substr($inProcess, 1));
									break;
								case tEnVar:
									array_push($this->toFill[1], substr($inProcess, 1));
									break;
								case tEnFile:
									$this->varFile(substr($inProcess, 1), $fileDepth);
									break;
								default:
									break;
							}
							
						} else { // Tables are treated through special logic due to their crazy two-part complexity. Gotta love it!
							// The first half of this is resolving the table filename.
							$inProcess = substr($fileString, $tStart, $tPosition-1);
							if ($tStart != $position) { // Okay, so basically, we start trying to resolve that half of the chain.
								echo "A";
							}
						}
						
						$position += $size;
						echo "<br>" . $inProcess . "<br>";
					}
				}
				$position++; // Position incrementer.
			}
		}
		
		function addValue($array, $value) {
			echo $value;
		}
		// If, for whatever reason, you're looking to clear out old variables that cannot be "resolved", then this function will go through and do so.
		// Please note that unless safe insertion is disabled, you probably won't be able to readd what you want to.
		public function garbage() {
			
		}
		
		// This function has the sole purpose of purging any duplicates.
		function preen() {
			
		}
		
		// 
		function resolveValue($checkFor, $valueType) {
			switch ($valueType) {
				case tEnConst:
					if (defined($checkFor))
						return constant($checkFor);
					return null;
					
				case tEnVar:
					if (isset($this->toFill[2][$checkFor]))
						return $this->toFill[2][$checkFor];
					return null;
				
				case tEnFile: // This one might cause the most chaos if used very poorly, but I'm going to include it for posterity's sake.
					$fileLoad = $this->getFile($checkFor);
					if ($fileLoad != null)
						return $fileLoad;
					return null;
				
				default:
					return null;
			}
		}
		
		public function construct() {
			$output = "";
			
			return $output;
		}
		
		function constructFile($file, $fileDepth) {
			
		}
		
		// When you want to add a value, this is the function to call upon. Index is the variable name, and value is what you want to kill.
		public function setVariable($index, $value) {
			$toComplete = false;
			if (tEnSafeInsert) { // Safe insertion. Ensures this value is requested first.
				foreach ($this->toFill[1] as $testing) {
					if ($testing == $index) {
						$toComplete = true;
						break;
					}
				}
				if ($toComplete == false)
					return false;
			}
			
			$this->toFill[2][$index] = $value;
			return true;
		}
		
		// Returns requested variables.
		public function returnVars() {
			return $this->toFill[1];
		}
		// Returns requested constants.
		public function returnConsts() {
			return $this->toFill[0];
		}
		// Returns variables that have already been set.
		public function returnSets() {
			return $this->toFill[2];
		}
	}
?>