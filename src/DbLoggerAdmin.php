<?php
// Controller declaration
MoufManager::getMoufManager()->declareComponent('dbloggerinstall', 'Mouf\\Utils\\Log\\Dblogger\\controllers\\DbLoggerInstallController', true);
MoufManager::getMoufManager()->bindComponents('dbloggerinstall', 'template', 'installTemplate');
?>