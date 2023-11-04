<?php
	include "tEngine.php";
	$templateFile = new tEngine("test/test.html");
	$tableInput = array(array(tEnMajor, tEnMinor, tEnPatch, tEnCodename), 
	
	array(tEnMaxFiles, tEnMaxDepth, tEnTagQ, tEnTagS), 
	array(tEnTagE, tEnTableB, tEnTableV, tEnTableK), 
	array(tEnVar, tEnFile, tEnSafeInsert, $templateFile->setVariable("safeInsertTest", "Safe insert is definitely disabled!")));
	
	const likeThis = "<b>like this</b>";
	const thisLike = "likeThis";
	
	$likeThis = "<i>like this</i>";
	
	$templateFile->setVariable("tableInput", $tableInput);
	$templateFile->setVariable("likeThis", $likeThis);
	
	echo $templateFile->construct();
	
	
	echo "<hr>";
	var_dump($templateFile->returnConsts());
	echo "<br>";
	var_dump($templateFile->returnVars());
	echo "<br>";
	var_dump($templateFile->returnSets());
?>