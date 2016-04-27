<?php
/**
 *	Main file for RationalGRL Translator.
 *
 *  More information: https://github.com/RationalArchitecture/RationalGRL
 *
 *  @author Marc van Zee 
 */

require('Constants.php');
require('GRLDiagram.php');
require('ArgumentMap.php');
require('HTMLCode.php');

function finish($msg) {
	echo "ERROR: " . $msg;
	exit;
}

/** parseAIFdb()->[true,false]
 *  parse an argument diagram using an Argument Web ID (http://www.aifdb.org)
 *
 *  globals:
 *  - LOCAL: for testing, should be false
 *  - CONFLICT: how to resolve conflicts (ARBITRARY, CHOOSE, or IGNORE)
 *  - ID: ID of the argument diagram
 *  - EVALUATE:  whether to export the argument evaluations as well. otherwise only GRL elements
 *
 *  returns:
 *  [true]: parsing successfull
 *  [false]: something went wrong
 */
function parseAIFdb() {
	global $LOCAL, $CONFLICT, $ID, $EVALUATE;

	$url = $LOCAL ? "AIFdb/$ID.json" : "http://www.arg-tech.org/AIFdb/json/".$ID;
	if (!is_valid_url($url)) {
		echo "Id " . $ID . " not found in AIFdb database!<br><br>";
		return false;
	}

	// simply parse the argument diagram as a JSON file
	$string = file_get_contents($url);
	$json = json_decode($string, TRUE);

	// build an argument diagram from the JSON
	$argMap = new ArgumentMap();
	$argMap->parse($json['nodes'], $json['edges']);

	// translate the argument diagram to a GRL diagram
	$grlDiagram = new GRLDiagram($argMap);

	// if we have conflict, and we want to resolve them, let the user resolve them
	if ($CONFLICT == Conflicts::CHOOSE && sizeof($grlDiagram->getConflicts()) > 0) {
		$grlDiagram->printConflictsAsHTMLDoc();
	} 

	// otherwise, export the results that the user wanted
	else {
		if ($EVALUATE) {
			$grlDiagram->evaluateArguments();
		}
		$grlDiagram->exportAndPrint();
	}

	return true;
}

function main() {
	global $ID;

	parseGETVars();

	// if we have an ID, then we are translating
	if ($ID != null) {
		$done = parseAIFdb();
	}

	// if we aren't done, it means something went wrong, so simply show the initial screen again
	// error message is printed already
	if (!$done) {
		HTMLDoc(); 
	}
} 

//-------------------------------------------------------------------------------------------------
// start here
//-------------------------------------------------------------------------------------------------
main();
?>