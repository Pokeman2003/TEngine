<?php
	include "tEngine.php";
	$tableInput = array(array(tEnMajor, tEnMinor, tEnPatch, tEnCodename), 
	array(tEnMaxFiles, tEnMaxDepth, tEnTagQ, tEnTagS), 
	array(tEnTagE, tEnTableB, tEnTableV, tEnTableK), 
	array(tEnVar, tEnFile, tEnSafeInsert, "Left blank."));
	
	const likeThis = "<b>like this</b>";
	const thisLike = "likeThis";
	
	$likeThis = "<i>like this</i>";
	$templateFile = new tEngine("test/test.html");
	
	$templateFile->setVariable("tableInput", $tableInput);
	$templateFile->setVariable("likeThis", $likeThis);
	
	
	/* var_dump($templateFile->returnConsts());
	echo "<br>";
	var_dump($templateFile->returnVars());
	echo "<br>";
	var_dump($templateFile->returnSets());
	echo "<hr>"; */
	
	echo $templateFile->construct();
?>