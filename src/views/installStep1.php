<?php /* @var $this DbLoggerInstallController */ ?>
<h1>Setting up DbLogger</h1>

<p>The DbLogger will now be configured. The DbLogger component can detect your database
connection and create the database table that will be used to store the logs automatically.</p>
<p>The DbLogger install procedure will create a "dbLogger" instance.</p>

<form action="configure">
	<input type="hidden" name="selfedit" value="<?php echo $this->selfedit ?>" />
	<button>Configure DbLogger</button>
</form>
<form action="skip">
	<input type="hidden" name="selfedit" value="<?php echo $this->selfedit ?>" />
	<button>Skip</button>
</form>