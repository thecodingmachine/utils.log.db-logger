<?php
namespace Mouf\Utils\Log\Dblogger\controllers;

use Mouf\Html\Template\TemplateInterface;
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\MoufManager;

/**
 * The controller used in the DbLogger install process.
 * 
 * @Component
 * @Logged
 */
class DbLoggerInstallController extends Controller {
	
	public $selfedit;
	
	/**
	 * The active MoufManager to be edited/viewed
	 *
	 * @var MoufManager
	 */
	public $moufManager;
	
	/**
	 * The template used by the main page for mouf.
	 *
	 * @Property
	 * @Compulsory
	 * @var TemplateInterface
	 */
	public $template;
	
	/**
	 * Displays the first install screen.
	 * 
	 * @Action
	 * @Logged
	 * @param string $selfedit If true, the name of the component must be a component from the Mouf framework itself (internal use only) 
	 */
	public function defaultAction($selfedit = "false") {
		$this->selfedit = $selfedit;
		
		if ($selfedit == "true") {
			$this->moufManager = MoufManager::getMoufManager();
		} else {
			$this->moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
				
		$this->template->addContentFile(dirname(__FILE__)."/../views/installStep1.php", $this);
		$this->template->draw();
	}

	/**
	 * Skips the install process.
	 * 
	 * @Action
	 * @Logged
	 * @param string $selfedit If true, the name of the component must be a component from the Mouf framework itself (internal use only)
	 */
	public function skip($selfedit = "false") {
		InstallUtils::continueInstall($selfedit == "true");
	}

	protected $tableName;
	
	/**
	 * Displays the second install screen.
	 * 
	 * @Action
	 * @Logged
	 * @param string $selfedit If true, the name of the component must be a component from the Mouf framework itself (internal use only) 
	 */
	public function configure($selfedit = "false") {
		$this->selfedit = $selfedit;
		
		if ($selfedit == "true") {
			$this->moufManager = MoufManager::getMoufManager();
		} else {
			$this->moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
		
		$this->tableName = "logs";
		
		$this->template->addContentFile(dirname(__FILE__)."/../views/installStep2.php", $this);
		$this->template->draw();
	}
	
	/**
	 * This action generates the DbLogger instance, and creates the table. 
	 * 
	 * @Action
	 * @param string $tablename
	 * @param string $selfedit
	 */
	public function install($dbconnection, $tablename, $level, $selfedit="false") {
		$this->selfedit = $selfedit;
		
		if ($selfedit == "true") {
			$this->moufManager = MoufManager::getMoufManager();
		} else {
			$this->moufManager = MoufManager::getMoufManagerHiddenInstance();
		}
		
		$this->createTable($dbconnection, $tablename, $selfedit);
		
				
		if (!$this->moufManager->instanceExists("dbLogger")) {
			$this->moufManager->declareComponent("dbLogger", "DbLogger");
			$this->moufManager->bindComponent("dbLogger", "dbConnection", $dbconnection);
			$this->moufManager->setParameter("dbLogger", "tableName", $tablename);
			$this->moufManager->setParameter("dbLogger", "level", $level);
		}
		
		$this->moufManager->rewriteMouf();
		
		InstallUtils::continueInstall($selfedit == "true");
	}
	
	protected $errorMsg;
	
	private function displayErrorMsg($msg) {
		$this->errorMsg = $msg;
		$this->template->addContentFile(dirname(__FILE__)."/../views/installError.php", $this);
		$this->template->draw();
	}
	
	protected function createTable($dbconnection, $tablename, $selfedit) {

		$createTableSql = "CREATE TABLE `$tablename` (
			`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`log_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
			`message` VARCHAR( 2000 ) NOT NULL ,
			`trace` TEXT NULL ,
			`log_level` VARCHAR( 8 ) NOT NULL ,
			`server` VARCHAR( 100 ) NULL ,
			`category1` VARCHAR( 30 ) NULL ,
			`category2` VARCHAR( 30 ) NULL ,
			`category3` VARCHAR( 30 ) NULL ,
			`additional_data` TEXT NULL COMMENT 'A JSON encoded object containing additional data',
			`client` VARCHAR( 100 ) NULL,
			`file` VARCHAR( 256 ) NULL,
			`line` INT NULL,
			`class` VARCHAR( 100 ) NULL,
			`function` VARCHAR( 100 ) NULL
			
		) ENGINE = InnoDB ;";
		
		$index1 = "ALTER TABLE `$tablename` ADD FULLTEXT (
			`message`
		)";
		$index2 = "ALTER TABLE `$tablename` ADD INDEX ( `category1` , `category2` , `category3` ) ";
		$index3 = "ALTER TABLE `$tablename` ADD INDEX ( `category2` , `category3` ) ";
		$index4 = "ALTER TABLE `$tablename` ADD INDEX ( `category3` ) ";
		$index5 = "ALTER TABLE `$tablename` ADD INDEX ( `server` ) ";
		$index6 = "ALTER TABLE `$tablename` ADD INDEX ( `client` ) ";
		
		$proxy = DB_ConnectionProxy::getLocalProxy($dbconnection);
		$proxy->exec($createTableSql);
		$proxy->exec($index1);
		$proxy->exec($index2);
		$proxy->exec($index3);
		$proxy->exec($index4);
		$proxy->exec($index5);
		$proxy->exec($index6);
		
	}
	
}