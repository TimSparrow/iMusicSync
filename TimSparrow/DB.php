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

/**
 * Database singleton
 *
 * @author TimSparrow
 */
class DB
{
	private static $db = null;

	/**
	 * Singleton private constructor
	 */
	private function __construct()
	{
		
	}

	/**
	 * Returns path to iPhone database file
	 * @return type
	 */
	private static function getPdoFileName()
	{
		return Config::getFullPath(Config::get('iPhoneDir')). Config::get('iTunesDB') . '/' . Config::get('dbFile');
	}

	public static function init()
	{
		if(null === self::$db)
		{
			$file = self::getPdoFileName();
			$schema = 'sqlite:'.$file;
			trigger_error("Using $file as database", E_USER_NOTICE);
			self::$db = new \PDO($schema);
			self::$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		}
		return self::$db;
	}

	public static function get()
	{
		return self::$db;
	}
}
