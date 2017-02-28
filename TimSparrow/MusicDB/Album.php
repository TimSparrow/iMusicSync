<?php
namespace TimSparrow\MusicDB;
use TimSparrow\DB;
use TimSparrow\Config;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Album extends AbstractEntity implements Id3Exportable
{

	const pattern = "%04d_%s";
	/**
	 * Returns
	 * @return String
	 */
	public function getPathName()
	{
		return sprintf(self::pattern, $this->album_year, $this->normalize($this->album));
	}

	/**
	 * Returns db identifier
	 * @return \String
	 */
	public function getId()
	{
		return $this->album_pid;
	}

	public function __toString()
	{
		return $this->album;
	}

	/**
	 * Get all albums in the database
	 * @return \PDOStatement
	 */
	public static function getList()
	{
		$query = "SELECT * FROM album "
				. "WHERE feed_url='' AND keep_local > 0 "		// skip podcasts and ebooks
				. "ORDER BY sort_album";
		if(!$albums = DB::get()->query($query, \PDO::FETCH_CLASS, get_class()))
		{
			throw new \Exception("DB Error: $query fails\n");
		}
		return $albums->fetchAll();
	}

	public function getTracks()
	{
		return Track::getList($this->getId());
	}
	/**
	 * Gets artist object for a given album
	 * @return Artist
	 */
	public function getArtist()
	{
		return Artist::getById($this->album_artist_pid);
	}

	public function getId3Tags($version = 2)
	{
		$tags = Array(
			'Talb' => $this->album,
			'Tdrl' => $this->album_year	//valid since Id3V2.4
		);
		if(isset($this->album_sort))
		{
			$tags['Tso2'] = $this->album_sort;
		}
		return $tags;
	}
}
