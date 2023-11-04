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
					
					if ($subStr == tEnTableK && $primaryIn == tEnTableB && $tPosition == null) { // When we find a table's K value, then we unlock finalValue and change the tPosition.
						$tPosition = $size;
						$tStart = $start;
						$finalValue = false;
						$start += $size;
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
							// First, we check if this is even worth it. It may not be!
							$finalValue = substr($fileString, $position+$tPosition+1, 1);
							if ($finalValue == tEnConst || $finalValue == tEnVar) {
								// Now that it checks out, let's deal with the halves.
								$inProcess = substr($fileString, $position+$tPosition+1, $size-$tPosition-1);
								if ($tStart+$tPosition+1 != $start) { // Oh, there's a chain here!
									$testingVariable = substr($fileString, $start, ($size-$tPosition)-($start-($position+$tPosition)));
									while (true) {
										$toTest = substr($fileString, $start-1, 1);
										$resolutionTest = $this->resolveValue($testingVariable, $toTest);
										if ($resolutionTest == null)
											break;
										$testingVariable = $resolutionTest;
										$start--;
										if ($start-1 == ($position+$tPosition+1)) // If the second to next tag would've been the start tag, then we've reached the end of what we needed to do.
											break;
									}
									$inProcess = substr($fileString, $start-1, 1) . $testingVariable;
								}
								switch (substr($inProcess, 0, 1)) {
									case tEnConst:
										array_push($this->toFill[0], substr($inProcess, 1));
										break;
									case tEnVar:
										array_push($this->toFill[1], substr($inProcess, 1));
										break;
									default:
										break;
							}
							}
						}
						
						$position += $size;
					}
				}
				$position++; // Position incrementer.
			}
		}
		
		// If, for whatever reason, you're looking to clear out old variables that cannot be "resolved", then this function will go through and do so.
		// Please note that unless safe insertion is disabled, you probably won't be able to re-add any complex piping without running refreshRequests().
		public function garbage() {
			
		}
		
		// This function has the sole purpose of purging any duplicates.
		function preen() {
			
		}
		
		// Internal function to try to resolve pipes.
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
			$output = $this->constructFile($this->file, 0);
			return $output;
		}
		
		function constructFile($file, $fileDepth) {
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
			$output = "";
			
			while (true) {
				$substr = substr($fileString, $position, 1);
				
				if ($substr == "")
					break;
				if ($substr == tEnTagQ)
					$position++;
				if ($substr == tEnTagS) { // If we have the start of a tag, move forward one and determine WHICH tag it is.
					$position++;
					$substr = substr($fileString, $position, 1);
					if ($substr == tEnConst || $substr == tEnVar || $substr == tEnFile || $substr == tEnTableB) {
						$isOpen = true;
						$finalValue = false;
						$size = 0;
						$start = $position;
						$tPosition = null;
						$primaryIn = $substr;
					} // If none of these match, it'll prevent the open tag parsing.
				}
				
				while ($isOpen) {
					$size++;
					$substr = substr($fileString, $position+$size, 1);
					
					// Going through tag logic.
					if (($substr == tEnConst || $substr == tEnVar || $substr == tEnFile) && !$finalValue) // We're looking for the variable input chains. 
						$start++;
					else
						$finalValue = true;
					if ($substr == tEnTableK && $primaryIn == tEnTableB && $tPosition == null) { // When we find a table's K value, then we unlock finalValue and change the tPosition.
						$tPosition = $size;
						$tStart = $start;
						$finalValue = false;
						$start = $size;
					}
					if ($substr == tEnTagE) // When we find the end tag, we know it's ended.
						$isOpen = false;
						
					if ($isOpen == false) {
						// First, we try to resolve!
						if ($primaryIn == tEnTableB) {
							$resolved = substr($fileString, $tStart+1, $tPosition-1);
							$resolvedT = substr($fileString, $position+$start+1, $size-$tPosition-2);
							
							// Second half
							if ($start-1 != $tPosition) {
								$resolvedT = substr($fileString, $start+$position+1, $size-$start-1);
								while (true) {
									$toCheck = substr($fileString, $position+$start, 1);
									$resolutionTest = $this->resolveValue($resolvedT, $toCheck);
									if ($resolutionTest == null)
										break;
									$start--;
									$resolvedT = $resolutionTest;
									if ($start-1 == $position+$tPosition)
										break;
								}
							}
							
							// First half
							if ($tStart != $position) {
								$resolved = substr($fileString, $tStart+1, $tPosition-($tStart-$position)-1);
								while (true) {
									$toCheck = substr($fileString, $tStart, 1);
									$resolutionTest = $this->resolveValue($resolved, $toCheck);
									if ($resolutionTest == null)
										break;
									$tStart--;
									$resolved = $resolutionTest;
									if ($tStart == $position)
										break;
								}
							}
						} elseif ($primaryIn == tEnConst || $primaryIn == tEnVar || $primaryIn == tEnFile) {
							$resolved = substr($fileString, $start+1, $size-1);
							if ($start != $position) {
								$resolved = substr($fileString, $start+1, $size-($start-$position)-1);
								while (true) {
									$toCheck = substr($fileString, $start, 1);
									$resolutionTest = $this->resolveValue($resolved, $toCheck);
									if ($resolutionTest == null)
										break;
									$start--;
									$resolved = $resolutionTest;
									if ($start == $position) // If we've reached the end of the tag, we break.
										break;
								}
							}
						}
						
						// And then, we try to process with the resolved.
						$toOutput = substr($fileString, $position-1, $size+2);
						if ($primaryIn == tEnFile) {
							$fileRead = $this->getFile($resolved);
							if ($fileRead != null)
								$toOutput = $this->constructFile($resolved, $fileDepth);
						} elseif ($primaryIn == tEnTableB) {
							$toCheck = substr($fileString, $position+$start, 1);
							if ($toCheck == tEnConst || $toCheck == tEnVar) {
								$toOutput = "";
								$table = null;
								$tableFile = $this->getFile($resolved);
								if ($toCheck == tEnConst && defined($resolved))
									$table = constant($resolved);
								elseif ($toCheck == tEnVar && isset($this->toFill[2][$resolvedT]))
									$table = $this->toFill[2][$resolvedT];
								foreach ($table as $tableData) {
									$toOutput .= $this->constructTable($tableFile, $tableData);
								}
							}
							
						} elseif ($primaryIn == tEnConst || $primaryIn == tEnVar) {
							if ($primaryIn == tEnConst && defined($resolved))
								$toOutput = constant($resolved);
							else 
								if (isset($this->toFill[2][$resolved]))
									$toOutput = $this->toFill[2][$resolved];
						}
						
						$output .= $toOutput;
						$position += $size+1;
					}
				}
				
				$output .= substr($fileString, $position, 1);
				$position++;
			}
			
			return $output;
		}
		
		function constructTable($file, $array) {
			if ($file == null) // Don't bother, the file was empty.
				return;
			
			$position = 0;
			$arrayPos = 0;
			$output = "";
			$isOpen = false;
			while (true) {
				$substr = substr($file, $position, 1);
				
				if ($substr == "")
					break;
				if ($substr == tEnTagQ)
					$position++;
				if ($substr == tEnTagS) {
					$position++;
					$substr = substr($file, $position, 1);
					if ($substr == tEnTableV) {
						$isOpen = true;
						$size = 0;
					}
				}
					
				while ($isOpen) {
					$substr = substr($file, $position+$size, 1);
					
					if ($substr == "")
						$isOpen = false;
					if ($substr == tEnTagE)
						$isOpen = false;
					
					if (!$isOpen) {
						$toProcess = substr($file, $position+1, $size-1);
						if ($size == 1) {
							if (isset($array[$arrayPos])) {
								$output .= $array[$arrayPos];
								$arrayPos++;
							} else
								$output .= "[Table blank!]";
						elseif (isset($array[$toProcess]))
							$output .= $array[$toProcess];
						}
						$position += $size;
						$substr = "";
					}
					$size++;
				}
				
				$output .= $substr;
				$position++;
			}
			
			return $output;
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