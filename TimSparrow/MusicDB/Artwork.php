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

	const OTHER				= 0x00;
	const ICON_32			= 0x01;
	const ICON_OTHER			= 0x02;
	const COVER_FRONT		= 0x03;
	const COVER_BACK		= 0x04;
	const LEAFLET_PAGE		= 0x05;
	const MEDIA				= 0x06;
	const LEAD_ARTIST		= 0x07;
	const ARTIST			= 0x08;
	const CONDUCTOR			= 0x09;
	const ORCHESTRA			= 0x0A;
	const COMPOSER			= 0x0B;
	const LYRICIST			= 0x0C;
	const RECORDING_LOCATION= 0x0D;
	const DURING_RECORDING	= 0x0E;
	const DURING_PERFORMANCE= 0x0F;
	const SCREEN_CAPTURE		= 0x10;
	const FISH				= 0x11;
	const ILLUSTRATION		= 0x12;
	const LOGO_ARTIST		= 0x13;
	const LOGO_PUBLISHER		= 0x14;

	private static $artworkPath = null;
	private $picType=false;
	public function __construct()
	{
		if(null==self::$artworkPath)
		{
			self::$artworkPath = Config::get('iPhoneDir').  Config::get('iTunesDb') . Config::get('artworkPath');
		}
	}

	/**
	 * Returns an artwork stored in database for a given entity
	 * @param String $item_pid - iTunes item id
	 * @return Artwork
	 * @throws Exception if not found
	 */
	public static function getForEntity($item_pid)
	{
		$query = "SELECT "
				. " artwork.artwork_token AS token, "
				. " artwork.relative_path AS path,"
				. " artwork.artwork_type AS type, "
				. " artwork.artwork_source_type AS source_type,"
				. " artwork_token"
				. "FROM artwork_token JOIN artwork ON artwork.token = artwork_token.artwork_token "
				. "WHERE artwork_token.entity_id = ?";
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

	/**
	 * Returns id of the current object
	 * @return type
	 */
	public function getId()
	{
		return $this->token;
	}

	public function __toString()
	{
		return sprintf('Artwork[%s]', $this->getId());
	}

	/**
	 * Returns path to the file containing original artwork image
	 * @return String
	 */
	public function getOriginal()
	{
		return self::$artworkPath . 'Originals/' . $this->path;
	}

	/**
	 * Returns a scaled version of the image if available
	 * @param int $size of the scaled down image
	 * @return string path to the file
	 * @throws Exception if does not exist
	 */
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

	/**
	 * Returns image data for the original image file
	 * @return String - binary image data
	 */
	public function getData()
	{
		return file_get_contents($this->getOriginal());
	}

	/**
	 * Detects and returns the mime type of the image
	 * @return string
	 */
	public function getMimeType()
	{
		return 'image/';
	}
}
