<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MusicDB;

/**
 * Description of Track
 *
 * @author timofey
 */
class Track extends AbstractEntity
{
	const pattern = "%d_%02d_%s";
	
	public function getPathName()
	{
		return sprintf(self::pattern,  $this->disc_number, $this->track_number, $this->title);
	}

	public function getId()
	{
		return $this->pid;
	}

	public function __toString()
	{
		return $this->title;
	}

	public function getFile()
	{
		return $this->path . '/'.$this->filename;
	}

	public static function getList($album_pid)
	{
		$query = "SELECT "
			. "	item.item_pid AS pid, "
			. "	item.media_type AS media_type,"
			. "	item.item_artist_pid AS item_artist_pid,"
			. "	item.album_pid AS album_pid,"
			. "	item.disc_number AS disc_number,"
			. "	item.track_number AS track_number,"
			. "	item.genre_id AS genre_id,"
			. "	genre.genre AS genre,"
			. "	item_extra.title AS title,"
			. "	item_extra.location AS filename,"
			. "	base_location.path AS path,"
			. "	item_playback.bit_rate AS bitrate"
			. "FROM item JOIN item_extra ON item.item_pid = item_extra.item_pid"
			. "	JOIN genre ON item.genre_id=genre.genre_id"
			. "	JOIN item_playback ON item.item_pid= item_playback.item_pid"
			. "	JOIN base_location ON base_location.base_location_id=item.base_location_id"
			. "WHERE album_pid = ? "					// current album only
			. "	AND item_playback.audio_format > 0 "	// skip non-audio files
			. "	AND item.keep_local > 0"				// skip podcasts
			. "ORDER by disc_number,track_number";

		$stm = $this->pdo->prepare($query);
		$stm->setFetchMode(\PDO::FETCH_CLASS, get_class());
		$stm->execute(Array($album_pid));
		return $stm->fetchAll();
	}
}