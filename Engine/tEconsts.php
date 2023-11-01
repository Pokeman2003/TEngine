<?php
	// Settings!
	const tEnMaxFiles = 480;	// Maximum number of files.
	const tEnMaxDepth = 8;		// How many recursions until we stop going deeper.
	// Symbols. Modify at your leisure, but note that this will break all templates not designed for them.
	const tEnTag = "\\[]";	// The tags to denote values. The first one is an escape, the second and third create the tag.
	const tEnConst = "!";	// Constant tag
	const tEnVar = "@";		// Variable tag
	const tEnFile = "#";	// File tag
	const tEnTable = "*:^";	// Table tags (first is a file tag, second denotes a variable binding, third is replaced by the values)	
	
	// Please don't mess with these, these AREN'T settings.
	const tEnMajor = 1;
	const tEnMinor = 0;
	const tEnPatch = 0;
	const tEnCodename = "Exemplar";
?>