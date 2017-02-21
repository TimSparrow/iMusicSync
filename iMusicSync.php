#!/usr/bin/php
<?php

/*
 * Copyright (C) 2017 TimSparrow
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

// autoload composer dependencies
require_once 'vendor/autoload.php';
use TimSparrow\Commands\ExportCommand;
use TimSparrow\Commands\ImportCommand;

// autoload our classes
spl_autoload_register(function($name){
	$name = str_replace('\\', '/', $name);
	require_once './'.$name.'.php';
});




$console = new ConsoleKit\Console();
$console->setVerboseException();
try {
	$console->addCommand('TimSparrow\Commands\ExportCommand');
	$console->addCommand('ImportCommand');
	$console->run();
}
catch (\Exception $x)
{
	$this->writeException($x);
}
