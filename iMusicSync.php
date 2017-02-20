#!/usr/bin/php
<?php
require_once 'vendor/autoload.php';
use TimSparrow\Commands\ExportCommand;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// autoload classes
spl_autoload_register(function($name){
	$name = str_replace('\\', '/', $name);
	require_once './'.$name.'.php';
});




$console = new ConsoleKit\Console();
$console->addCommand('ExportCommand');
$console->run();
