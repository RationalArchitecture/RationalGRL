<?
/**
 *  GRLDiagram for RationalGRL Translator.
 *
 *  More information: https://github.com/RationalArchitecture/RationalGRL
 *
 *  @author Marc van Zee 
 */
require("ArgumentEvaluation.php");

class GRLDiagram {
	var $nodes = array();
	var $edges = array();
	var $argEdges = array();

	// we can only obtain the ids of the IEs of implications if we have added them already to the GRL diagram
	// therefore, we first collect all these rules and parse them after we have parsed all IEs
	var $implications = array();

	// similarly for conflicts
	var $conflicts = array();

	// and default inference
	var $default_inferences = array();

	// and practical reasoning
	var $practical_reasonings = array();

	// conflict to resolve if the user would like to resolve them
	var $conflicts_to_resolve = array();

	public function __construct($argMap) {
		$this->parseArgMap($argMap);
	}

	public function getConflicts() {
		return $this->conflicts_to_resolve;
	}

	public function printConflictsAsHTMLDoc() {
		HTMLHeader();
		HTMLConflictForm($this->conflicts_to_resolve);
		HTMLFooter();
	}

	public function evaluateArguments() {
		$evaluation = new ArgumentEvaluation();
		$evaluation->parse();
		$this->setNodesEvaluations($evaluation->getInArguments());
	}

	public function exportAndPrint() {
		global $ID, $EXPORT_URL, $EVALUATE;

		$uid = uniqid();
		$string = "<ul>";

		// export GRL diagram
		$filename1 = $EXPORT_URL . $ID . "-" . $uid . ".grl";
		file_put_contents($filename1, $this->diagramToString());

		$string .= "<br><li><a href=\"$filename1\">Download GRL Diagram</a></li>\n";
		
		// export evaluation
		if ($EVALUATE) {
			$filename2 = $EXPORT_URL . $ID . "-" . $uid . ".csv";
			file_put_contents($filename2, $this->evaluationToString());

			$string .= "<li><a href=\"$filename2\">Download GRL Evaluation</a></li>\n";
		}

		$string .= "</ul><br>";

		HTMLHeader();
		HTMLFinishedText($string);
		HTMLFooter();
	}

	public function diagramToString() {
		return 	"<?xml version='1.0' encoding='ISO-8859-1'?>\n" .
				"<grl-catalog catalog-name=\"URNspec\" description=\"\" author=\"Automatically-generated-Tool\">\n".
				$this->nodesToString() .
				$this->linksToString() .
				"<actor-def></actor-def>\n".
				"<actor-IE-link-def></actor-IE-link-def>\n".
				"</grl-catalog>";
	}

	public function evaluationToString() {
		global $ID;
		$evalName = "RationalGRLEvaluation$ID";
		$string = "\"GRL Strategies for\",\"RationalGRL$ID\"\n\n" .
					"\"Strategy Name\", Author, Description, \"Included Strategies\"\n".
					"\"$evalName\",\"RationalGRL\",\"\",\"\"\n\n";

		$nameStr = "";
		$evalStr = "";

		$i=0;

		foreach ($this->nodes as $node) {
			if ($node->IE == "resource") {
				continue;
			}
			if ($i > 0 && ($i % 7 == 0)) {
				$string .= "\"Strategy Name\"$nameStr\n".
							"\"$evalName\"$evalStr\n\n";
				$nameStr = "";
				$evalStr = "";
			}
			$nameStr .= ",\"$node->name\"";
			$evalStr .= ",$node->quantiativeEvaluation";
			$i++;
		}

		if ($nameStr != "") {
			$string .= "\"Strategy Name\"$nameStr\n".
							"\"$evalName\"$evalStr\n\n";
		}

		return $string;
	}


	/*
	 * We use the following semantics:
	 * - for each argument A in the Grounded Extension: A=IN
	 * - for each argument B not in GE:
	 * 		- if there exists an argument A in GE that attacks B: B=OUT
			- otherwise: B=UNDEC
	 *
	 * IN = +100
	 * UNDEC = 0
	 * OUT = -100
	 */
	private function setNodesEvaluations($nodeIDs) {
		// first
		foreach ($nodeIDs as $nodeID) {
			$node = $this->getNodeFromId(intval($nodeID));
			if ($node != null) {
				$node->quantiativeEvaluation = 100;
			}
		}
	}

	private function parseArgMap($argMap) {
		$this->addNodes($argMap);
		$this->argEdges = $argMap->getEdges();
		$this->addEdges($argMap);
		$this->resolveConflicts();
	}

	private function addNodes($argMap) {
		foreach ($argMap->getNodes() as $node) {
			$id = $node->id;
			$text = $node->text;
			$argType = $node->type;

			switch($argType) {
				case "I": $this->parseI($node); break;
				case "CA": $this->parseCA($node); break;
				case "RA": $this->parseRA($node); break;
				default: finish("Invalid argument type: $argType in argument $id ($text)");
			}
		}
		
	}

	private function addEdges($argMap) {
		// add all contribution links
		$this->addContributionLinks();
		$this->addConflictLinks();
	}

	private function resolveConflicts() {
		global $CONFLICT, $DELETE_EDGES;

		switch ($CONFLICT) {
			case Conflicts::IGNORE:
				// remove all conflicts
				foreach ($this->edges as $key => $edge) {
					if ($edge->quantitativeContribution < 0) {
						unset($this->edges[$key]);
					}
				}
				break;
			case Conflicts::ARBITRARY:
				// for each bi-directional conflict, remove one of the two arbitrary
				$this->processConflicts(true);
				break;
			case Conflicts::CHOOSE:
				$this->processConflicts();
				break;
			case Conflicts::RESOLVED:
				// remove chosen edges
				foreach ($DELETE_EDGES as $arr) {
					$id1 = $arr[0];
					$id2 = $arr[1];

					// remove edge from $id1 to $id2
					foreach ($this->edges as $key => $value) {
    					if ($value->srcid == $id1 && $value->destid == $id2) {
    						unset($this->edges[$key]);
    					}
    				}
				}
		}
	}

	private function processConflicts($delete=false) {
		foreach ($this->edges as $key1 => $value1) {
			if ($value1->quantitativeContribution < 0) {
				$srcid1 = $value1->srcid;
				$destid1 = $value1->destid;

				foreach ($this->edges as $key2 => $value2) {
					if ($key2<=$key1) continue;

					if ($value2->quantitativeContribution < 0) {
						$srcid2 = $value2->srcid;
						$destid2 = $value2->destid;

						if ($srcid1 == $destid2 && $destid1 == $srcid2) {
							if ($delete) {
								unset($this->edges[$key1]);
							} else {
								$this->conflicts_to_resolve[] = array($this->getNodeFromId($srcid1), $this->getNodeFromId($srcid2));
							}
						}
					}
				}
			}
		}
	}

	private function addContributionLinks() {
		$not = "[NOT]";
		$impl = "->";

		foreach ($this->implications as $node) {
			//echo "<pre>"; var_dump($node); echo "</pre>";
			$text = $node->text;
			$pos = strpos($text, $impl);
			$lhs = $this->parseModality(trim(substr($text, 0, $pos)))[1];
			$rhs = trim(substr($text, $pos+strlen($impl)));

			if (substr($rhs, 0, strlen($not) ) === $not) {
				// consequent is negated, so add a negative contribution link
				$rhs = $this->parseModality(strip(substr($rhs, strlen($not))))[1];
				$this->addContribution($this->getNodeId($lhs),$this->getNodeId($rhs), false);
			} else {
				$rhs = $this->parseModality($rhs)[1];
				// add positive contribution
				$this->addContribution($this->getNodeId($lhs),$this->getNodeId($rhs), true);
			}
		}
	}

	private function addContribution($fromId, $toId, $positive) {
		$this->edges[] = new GRLLink($fromId, $toId, $positive);
	}

	private function addConflictLinks() {
		global $EVIDENCE;

		// - For each conflict link between any two nodes of type [TASK], [GOAL], [PRINCIPLE]  in the argument, 
		// 	 include a negative link between the corresponding tasks, goals and principles in the GRL diagram.
		// - For conflict and rule links between a node [EVIDENCE] a  and a node b of either [TASK], [GOAL], 
		//   [PRINCIPLE] type, include a belief link between a and b.

		foreach ($this->conflicts as $node) {
			// in order to find out which nodes are conflicting, we have to use the argument edges
			$src = $this->getSrcNode($node->id);
			$dest = $this->getDestNode($node->id);

			// only represent evidence if the user has enabled it
			if ($src->IE != "evidence" || $EVIDENCE) {
				$this->addContribution($src->id, $dest->id, false);
			}
		}
	}

	private function getSrcNode($id) {
		foreach ($this->argEdges as $edge) {
			if ($edge->toID == $id) {
				return $this->getNodeFromId($edge->fromID);
			}
		}
		finish("Couldn't find source id of node $id");
	}

	private function getDestNode($id) {
		foreach ($this->argEdges as $edge) {
			if ($edge->fromID == $id) {
				return $this->getNodeFromId($edge->toID);
			}
		}
		finish("Couldn't find destination id of node $id");
	}

	// find the id of a node from the name
	private function getNodeId($name) {
		foreach ($this->nodes as $node) {
			if (strtolower($node->name) == strtolower($name)) {
				return $node->id;
			}
		}
		finish("Node id not found ($name)");
	}

	private function getNodeFromId($id) {
		foreach ($this->nodes as $node) {
			if ($node->id == $id) {
				return $node;
			}
		}
		//finish("Couldn't get node from id $id");
		return null;
	}

	private function parseI($node) {
		$text = trim($node->text);

		// first check if we have an implication
		$impl = "->";
		$not = "[NOT]";

		if (($pos = strpos($text, $impl)) == false) {
			// no implication, so we can try to translate it to an IE directly
			$this->parseIE($node);			
		} else {
			// we have an implication. store in $implications and process lastly
			$this->implications[] = $node;
		}			
	}

	// input:	[IE] text
	// returns:	array(strtolower(IE), text)
	private function parseModality($text) {
		$regexp = "/^\[([a-z]*)\]([a-z_\-0-9]*)/i";
		$retval = preg_match(($regexp), $text, $regs);

		if ($retval == true ) {
			$regs[1] = strtolower(trim($regs[1]));

			if (IE::isValidName($regs[1])) {
				$str = trim(substr($text,strlen($regs[0])));
				return array($regs[1], $str);
			}
		}
		return null;
	}

	private function parseIE($node) {
		global $EVIDENCE;

		$text = $node->text;
		if (($arr = $this->parseModality($text)) != null) {
			// always add nodes, also evidence, because we need them when adding the conflict links
			// otherwise we cannot find back the ids
			$this->nodes[] = new GRLNode($node->id, $arr[0], $arr[1]);
			return;
	   	}

	   	finish("Intentional Element invalid ($text)");
	}

	private function parseCA($node) {
		if ($node->text == "Default Conflict") {
			$this->conflicts[] = $node;
		} else {
			finish("Argument $node->id of type CA has incorrect description ($node->text)");
		}
	}

	private function parseRA($node) {
		if ($node->text == "Practical Reasoning") {
			$practical_reasonings = $node;
		} else if ($node->text == "Default Inference") {
			$default_inferences = $node;
		} else {
			finish("Argument $node->id of type RA has incorrect description ($node->text)");
		}
	}

	private function nodesToString() {
		global $EVIDENCE;

		$str = "<element-def>\n";

		foreach ($this->nodes as $node) {
			$id = $node->id;
			$name = $node->name;
			$type = ucfirst($node->IE);
			if ($type == "Resource") {
				if (!$EVIDENCE) { continue; }
				$type = "Ressource"; // error in GRL
			}
			$str .= "<intentional-element id=\"$id\" name=\"$name\" description=\"\" type=\"$type\" decompositiontype=\"And\"/>\n";
		}

		return $str . "</element-def>\n";
	}

	private function linksToString() {
		$str = "<link-def>\n";

		foreach ($this->edges as $edge) {
			$name = $edge->name;
			$srcid = $edge->srcid;
			$destid = $edge->destid;
			$contrtype = $edge->contributiontype;
			$quantcontr = $edge->quantitativeContribution;

			if ($this->getNodeFromId($srcid)->IE == "resource" && !$EVIDENCE) { continue; }

			$str .= "<contribution name=\"Contribution $name\" description=\"\" ". 
						"srcid=\"$srcid\" destid=\"$destid\" contributiontype=\"$contrtype\" quantitativeContribution=\"$quantcontr\" correlation=\"false\"/>\n";
		}

		return $str . "</link-def>\n";
	}
}

class GRLNode {
	var $id, $IE, $name, $quantiativeEvaluation = -100;

	function __construct($id, $IE, $name) {
		$this->id = $id;
		$this->IE = $IE == "principle" ? "softgoal" : ($IE == "evidence" ? "resource" : $IE);
		$this->name = $name;
	}
}

class GRLLink {
	var $name, $srcid, $destid, 
			$contributiontype, $quantitativeContribution;

	public function __construct($srcid, $destid, $positive) {
		$this->srcid = $srcid;
		$this->destid = $destid;
		$this->contributiontype = $positive ? "Help" : "Hurt";
		$this->quantitativeContribution = $positive ? 25 : -25;
	}
}
?>