<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace TimSparrow\MusicDB;
use TimSparrow\DB;

/**
 * Container for an Artist entity
 *
 * @author timofey
 */
class Artist extends AbstractEntity implements Id3Exportable, MusicLibExportable
{
	public function getPathName()
	{
		return $this->normalize($this->album_artist);
	}

	public function getId()
	{
		return $this->album_artist_pid;
	}

	public function __toString()
	{
		return $this->album_artist;
	}

	/**
	 * Gets artist object for a given album
	 * @return Artist
	 */
	public static function getById($id)
	{
		$query = "SELECT * FROM album_artist WHERE album_artist_pid=?";
		$stm = DB::get()->prepare($query);
		$stm->setFetchMode(\PDO::FETCH_CLASS, get_class());
		if ($stm->execute(Array($id)))
		{
			return $stm->fetch();
		}
	}

	public function getId3Tags($version = 2)
	{
		return Array(
			'Tpe1' => $this->album_artist,
			'Tso2' => $this->sort_album_artist
		);
	}
}
