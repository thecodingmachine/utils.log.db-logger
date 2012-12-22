<?php
namespace Mouf\Utils\Log\Dblogger;

use Mouf\Utils\Log\LogInterface;
use Mouf\Database\DBConnection\ConnectionInterface;
use \Exception;
use Mouf\Mvc\Splash\Utils\ExceptionUtils;
/**
 * A logger class that writes messages into the database.
 * <p>The logger accepts a number of parameters in the "additional_parameters" parameter:</p>
 * <ul>
 * 	<li>category1</li>
 * 	<li>category2</li>
 * 	<li>category3</li>
 * 	<li>server (the server name the error comes from, defaults to the current server name)</li>
 * 	<li>client (the client IP that triggered the error, defaults to the detected client IP)</li>
 *  <li>data (additional data to be stored, as a json string)</li>
 *  <li>trace (will be appended to the trace, if provided)</li>
 * </ul>
 * 
 * @Component
 */
class DbLogger implements LogInterface {
	
	/**
	 * The service used to store data in the database.
	 * 
	 * @Property
	 * @Compulsory
	 * @var ConnectionInterface
	 */
	public $dbConnection;
	
	/**
	 * The name of the table storing the logs.
	 * 
	 * @Property
	 * @Compulsory
	 * @var string
	 */
	public $tableName;
	
	public static $TRACE = 1;
	public static $DEBUG = 2;
	public static $INFO = 3;
	public static $WARN = 4;
	public static $ERROR = 5;
	public static $FATAL = 6;
	
	/**
	 * The minimum level that will be tracked by this logger.
	 * Any log with a level below this level will not be logger.
	 *
	 * @Property
	 * @Compulsory 
	 * @OneOf "1","2","3","4","5","6"
	 * @OneOfText "TRACE","DEBUG","INFO","WARN","ERROR","FATAL"
	 * @var int
	 */
	public $level;
	
	public function trace($string, Exception $e=null, array $additional_parameters=array()) {
		if($this->level<=self::$TRACE) {
			$this->logMessage("TRACE", $string, $e, $additional_parameters);
		}
	}
	public function debug($string, Exception $e=null, array $additional_parameters=array()) {
		if($this->level<=self::$DEBUG) {
			$this->logMessage("DEBUG", $string, $e, $additional_parameters);
		}
	}
	public function info($string, Exception $e=null, array $additional_parameters=array()) {
		if($this->level<=self::$INFO) {
			$this->logMessage("INFO", $string, $e, $additional_parameters);
		}
	}
	public function warn($string, Exception $e=null, array $additional_parameters=array()) {
		if($this->level<=self::$WARN) {
			$this->logMessage("WARN", $string, $e, $additional_parameters);
		}
	}
	public function error($string, Exception $e=null, array $additional_parameters=array()) {
		if($this->level<=self::$ERROR) {
			$this->logMessage("ERROR", $string, $e, $additional_parameters);
		}
	}
	public function fatal($string, Exception $e=null, array $additional_parameters=array()) {
		if($this->level<=self::$FATAL) {
			$this->logMessage("FATAL", $string, $e, $additional_parameters);
		}
	}

	private function logMessage($level, $string, $e=null, array $additional_parameters=array()) {
		$trace = debug_backtrace();
		$file = $trace[1]['file'];
		$line = $trace[1]['line'];
		$class = null;
		$function = null;
		if (isset($trace[2])) {
			if (isset($trace[2]['class'])) {
				$class = $trace[2]['class'];
			}
			if (isset($trace[2]['function'])) {
				$function = $trace[2]['function'];
			}
		}
		
		$traceStr = "";
		if ($e == null) {
			if (!$string instanceof Exception) {
				$msg = $string;
			} else {
				$msg = $string->getMessage();
				$traceStr = ExceptionUtils::getTextForException($string);
			}
		} else {
			$msg = $string;
			$traceStr = ExceptionUtils::getTextForException($e);
		}

		if (isset($additional_parameters['server'])) {
			$server = $additional_parameters['server'];
		} else {
			if (isset($_SERVER['SERVER_NAME']))  {
				$server = $_SERVER['SERVER_NAME'];
			} else {
				$server = null;
			}
		}
		
		if (isset($additional_parameters['client'])) {
			$client = $additional_parameters['client'];
		} else {
			if (isset($_SERVER['REMOTE_ADDR']))  {
				$client = $_SERVER['REMOTE_ADDR'];
			} else {
				$client = null;
			}
		}
		
		if (isset($additional_parameters['trace'])) {
			$traceStr .= $additional_parameters['trace'];
		}
		
		$category1 = "";
		if (isset($additional_parameters['category1'])) {
			$category1 = $additional_parameters['category1'];
		}
		
		$category2 = "";
		if (isset($additional_parameters['category2'])) {
			$category2 = $additional_parameters['category2'];
		}
		
		$category3 = "";
		if (isset($additional_parameters['category3'])) {
			$category3 = $additional_parameters['category3'];
		}
		
		$additional_data = null;
		if (isset($additional_parameters['data'])) {
			$additional_data = $additional_parameters['data'];
		}
		

		// In case we use triggers (using the LogStats), we must ensure the trigger is performed in a transaction.
		// Therefore, we start a transaction (even if this seems completely useless for a MyISAM table).
		$this->dbConnection->beginTransaction();
		
		$sql = "INSERT INTO ".$this->dbConnection->escapeDBItem($this->tableName)." (message, trace, log_level, server, category1, category2, category3, additional_data, client, file, line, class, function)
			VALUES (".$this->dbConnection->quoteSmart($msg).", ".
					$this->dbConnection->quoteSmart($traceStr).", ".
					$this->dbConnection->quoteSmart($level).", ".
					$this->dbConnection->quoteSmart($server).", ".
					$this->dbConnection->quoteSmart($category1).", ".
					$this->dbConnection->quoteSmart($category2).", ".
					$this->dbConnection->quoteSmart($category3).", ".
					$this->dbConnection->quoteSmart($additional_data).", ".
					$this->dbConnection->quoteSmart($client).", ".
					$this->dbConnection->quoteSmart($file).", ".
					$this->dbConnection->quoteSmart($line).", ".
					$this->dbConnection->quoteSmart($class).", ".
					$this->dbConnection->quoteSmart($function).")";
		
		$this->dbConnection->exec($sql);
		$this->dbConnection->commit();
	}
}

?>