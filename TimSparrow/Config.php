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

namespace TimSparrow;
use TimSparrow\Exceptions\ConfigException;

/**
 * Container of configuration for iMusicSync utility
 *
 * @author TimSparrow
 */
class Config
{
	/**
	 * Config file name
	 */
	const configFile = './iMusicSync.ini';
	const version = '0.1.0';
	/**
	 * default values
	 * overriden by config.ini values
	 * or (in future) by command line overrides
	 * @var Array
	 */
	private $defaults = Array(
		'iPhoneDir'		=> '~/backups/iphone/',				// change to the dir iPhone is mounted to on production version
		'iTunesDB'		=> 'iTunes_Control',
		'dbFile'		=> 'iTunes/MediaLibrary.sqlitedb',
		'musicLibPath'	=> '/Music',
		'exportTargetPath'	=> '~/Music',

		'useLinks'		=> false,	// use hard links (debug only, ignored if useRecode=true), deprecated
		'useRecode'		=> true,	// recode files to mp3
		'cmdRecode'		=> "ffmpeg -y -loglevel error -hide_banner -i %1s %2s",	// recode command
		'cmdCopy'		=> "cp %1s %2s",		// copy command
		'cmdLink'		=> "ln %1s %2s",			// link command @deprecated
		'overwrite'		=> 'newer',					// overwrite files that exist [newer] | all | none
		'exportDirMode' => 0755,	// directory create mode on export

		'id3format'		=> 2.4		// version of id3 format to implement
	);
	private static $instance=null;
	private $config=null;
	private static $home=null;

	/**
	 * private constructor for singleton tpl
	 */
	private function __construct()
	{
		if(file_exists(self::configFile))
		{
			$this->loadConfig(self::configFile);
		}
		else {
			trigger_error(sprintf("Config file %s not found, using defaults", self::configFile), E_USER_WARNING);
		}
	}

	/**
	 * Singleton instance
	 * @return Config
	 */
	public static function getInstance()
	{
		if(null===self::$instance)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Returns config option. If not found, returns a default value
	 * If no default given, returns null and issues a warning
	 * @param String $name
	 * @return Mixed
	 */
	public function __get($name)
	{
		if(is_array($this->config) && array_key_exists($name, $this->config))
		{
			return $this->config[$name];
		}
		elseif(array_key_exists($name, $this->defaults))
		{
			return $this->defaults[$name];
		}
		else
		{
			trigger_error("Undefined config property $name", E_USER_WARNING);
			return null;
		}
	}

	public static function getSoftware()
	{
		return sprintf("iMusicSync v%s", self::version);
	}

	/**
	 * @see self::getInstance->$name
	 * @param String $name
	 * @return Mixed
	 */
	public static function get($name)
	{
		return self::getInstance()->$name;
	}

	/**
	 * Loads an .ini-style config file into $this->config
	 * @see parse_ini_file
	 * @param String $file
	 */
	protected function loadConfig($file)
	{
		$this->config = parse_ini_file($file, false);
	}

	public static function getFullPath($path)
	{
		if(null===self::$home)
		{
			self::$home = getenv('HOME');
		}
		return str_replace('~', self::$home, $path);;
	}


	public static function init($args, $options)
	{
		$this->getInstance()->setOptions($args, $options);
	}

	protected function setOptions($args, $options)
	{
		unset($args);
		foreach($options as $key => $value)
		{
			if(array_key_exists($key, $this->config))
			{
				trigger_error(sprintf("CLI: Using %s ==> %s", $key, $value), E_USER_NOTICE);
				$this->config[$key] = $value;
			}
			else {
				trigger_error(sprintf("CLI: Ignoring invalid option %s", $key), E_USER_NOTICE);
			}
		}
	}

	/**
	 * Returns array of keys for
	 * @return Array
	 */
	public static function getKeys()
	{
		return array_seys($this->getInstance()->defaults);
	}
}
