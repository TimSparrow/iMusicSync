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

namespace TimSparrow\MusicDB;
use TimSparrow\Config;

/**
 * Track-> album artwork
 *
 * @author timofey
 */
class Artwork extends AbstractEntity
{
	private static $artworkPath = null;
	public function __construct()
	{
		if(null==self::$artworkPath)
		{
			self::$artworkPath = Config::get('iPhoneDir').  Config::get('iTunesDb') . Config::get('artworkPath');
		}
	}

	public static function getForTrack($item_pid)
	{
		$query = "SELECT "
				. " artwork.artwork_token AS token, "
				. " artwork.relative_path AS path,"
				. " artwork.artwork_type AS type, "
				. " artwork.artwork_source_type AS source_type,"
				. " artwork_token"
				. "FROM artwork_token JOIN artwork ON artwork.token = artwork_token.artwork_token "
				. "WHERE artwork_type=1 AND artwork_token.entity_id = ?";
		$stm = DB::get()->prepare($query);
		$stm->setFetchMode(\PDO::FETCH_CLASS, get_class());
		if($stm->execute(Array($item_pid)))
		{
			return $stm->fetch();
		}
		else {
			throw new Exception('Artwork not found in iTunes database');
		}
	}

	public function getId()
	{
		return $this->token;
	}

	public function __toString()
	{
		return sprintf('Artwork[%s]', $this->getId());
	}

	public function getOriginal()
	{
		return self::$artworkPath . 'Originals/' . $this->path;
	}

	public function getScaled($size)
	{
		$path = self::$artworkPath . 'Cached/' .$size . 'x' . $size . '/'. $this->path;
		if(file_exists($path))
		{
			return $path;
		}
		else {
			throw new Exception(sprintf("Cannot find artwork for size %s", $size));
		}
	}
}
