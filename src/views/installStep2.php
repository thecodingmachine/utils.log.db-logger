<?php 
use Mouf\Utils\Common\MoufHelpers\MoufHtmlHelper;

/* @var $this DbLoggerInstallController */ ?>
<h1>Setting up DbLogger</h1>

<p>By clicking the link below, you will create the table for DbLogger (if the table does not already exists).</p>

<form action="install" method="post">
<input type="hidden" id="selfedit" name="selfedit" value="<?php echo plainstring_to_htmlprotected($this->selfedit) ?>" />

<?php 
MoufHtmlHelper::drawInstancesDropDown("Database connection instance", "dbconnection", "DB_ConnectionInterface", false, "dbConnection");
?>

<div>
<label>Table name:</label><input type="text" name="tablename" value="<?php echo plainstring_to_htmlprotected($this->tableName) ?>"></input>
</div>

<div>
<label>Log everything above:</label><select name="level">
	<option value="1">TRACE</option>
	<option value="2">DEBUG</option>
	<option value="3">INFO</option>
	<option value="4">WARN</option>
	<option value="5">ERROR</option>
	<option value="6">FATAL</option>
</select>
</div>

<div>
	<button name="action" type="submit">Install DbLogger</button>
</div>
</form>