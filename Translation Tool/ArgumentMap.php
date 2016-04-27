<?
/**
 *  Argument Diagram for RationalGRL Translator.
 *
 *  More information: https://github.com/RationalArchitecture/RationalGRL
 *
 *  @author Marc van Zee 
 */
class ArgumentMap {
	private $nodes = array(),
				$edges = array();

	public function addNode($node) {
		$this->nodes[] = new ArgNode($node);
	}

	public function addEdge($edge) {
		// we only allow edges between existing nodes
		$fromID = $edge["fromID"];
		$toID = $edge["toID"];

		$foundFrom = false;
		$foundTo = false;

		foreach ($this->nodes as $node) {
			if ($foundFrom && $foundTo) {
				break;
			}
			if ($node->id == $fromID) {
				$foundFrom = true;
			} else if ($node->id == $toID) {

				$foundTo = true;
			}
		}
		if (!$foundFrom || !$foundTo) {
			echo 'Cannot add edge $edge["edgeID"] ('.$fromID.", ".$toID.") (non-existing nodes)!";
			exit(-1);
		}
		$this->edges[] = new ArgEdge($edge);
	}

	public function parse($nodes, $edges) {
		foreach ($nodes as $node) {
			$this->addNode($node);
		}

		foreach ($edges as $edge) {
			$this->addEdge($edge);
		}
	}

	public function getNodes() {
		return $this->nodes;
	}

	public function getEdges() {
		return $this->edges;
	}
}

class ArgNode
{
	var $id;
	var $text;
	var $type;

	function __construct($node) {
		$this->id = $node["nodeID"];
		$this->text = $node["text"];
		$this->type = $node["type"];
	}
}

class ArgEdge
{
	var $id;
	var $fromID;
	var $toID;

	function __construct($edge) {
		$this->id = $edge["edgeID"];
		$this->fromID = $edge["fromID"];
		$this->toID = $edge["toID"];
	}
}
?>