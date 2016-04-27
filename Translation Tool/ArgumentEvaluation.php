<?php
/**
 *  Argument Evaluation for RationalGRL Translator.
 *
 *  More information: https://github.com/RationalArchitecture/RationalGRL
 *
 *  @author Marc van Zee 
 */
class ArgumentEvaluation {
	private $acceptableConclusions = array();

	public function __construct() {

	}

	public function parse() {
		global $ID;

		$url = "http://toast.arg-tech.org/api/aifdb/".$ID;
		if (!is_valid_url($url)) {
			finish("Id " . $ID . " not parsable in TOAST database!<br><br>");
		}

		$string = file_get_contents($url);
		$json = json_decode($string, TRUE);

		$this->acceptableConclusions = $json["acceptableConclusions"][0];
	}

	public function getInArguments() {
		return $this->acceptableConclusions;
	}
}
?>