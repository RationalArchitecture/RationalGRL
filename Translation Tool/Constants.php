<?
/**
 *  Constants for RationalGRL Translator.
 *
 *  Contains:
 *  - Enums
 *  - Globals
 *  - Some useful functions
 *
 *  More information: https://github.com/RationalArchitecture/RationalGRL
 *
 *  @author Marc van Zee 
 */

abstract class BasicEnum {
    private static $constCacheArray = NULL;

    private static function getConstants() {
        if (self::$constCacheArray == NULL) {
            self::$constCacheArray = [];
        }
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }
        return self::$constCacheArray[$calledClass];
    }

    public static function isValidName($name, $strict = false) {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    public static function isValidValue($value, $strict = true) {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict);
    }
}

abstract class IE extends BasicEnum {
    const softgoal = 0;
    const goal = 1;
    const evidence = 2;
    const principle = 3;
    const task = 4;
}

abstract class ArgTypes extends BasicEnum {
	const I = 0;
	const CA = 1;
	const RA = 2;
}

abstract class Conflicts extends BasicEnum {
    const IGNORE = 0;
    const ARBITRARY = 1;
    const CHOOSE = 2;
    const RESOLVED = 3;
}

abstract class EvalType extends BasicEnum {
    const FULL = 0;
    const BOTTOM_UP = 1;
}

function getVar($var) {
    return htmlspecialchars($_GET[$var]);
}

function parseGETVars() {
    global $EVIDENCE, $CONFLICT, $EVALUATE, $ID, $DELETE_EDGES;

    $ID = getVar("id");
    $EVIDENCE = getVar("evidence");
    $CONFLICT = getVar("conflicts");
    $EVALUATE = getVar("evaluate");
    
    $i=0;
    while (($var = getVar("removeEdge$i")) != null) {
        $DELETE_EDGES[] = split(",",getVar("removeEdge$i"));
        $i++;
    }
}

function is_valid_url($url) {
    $url_headers = @get_headers($url);
    return ($url_headers[0] != 'HTTP/1.1 404 Not Found');
}

function printAsHTMLDoc($string) {
    echo "<!DOCTYPE html>\n<html>\n<body>\n$string\n</body>\n</html>";
}

function dump($str) {
    echo "<pre>";
    var_dump($str);
    echo "</pre>";
}

$LOCAL = false;
$CONFLICT = Conflicts::IGNORE;
$EVIDENCE = 0;
$EXPORT_URL = "export/";
$EVALUATE = 1;
$ID = null;
$DELETE_EDGES = array();
?>