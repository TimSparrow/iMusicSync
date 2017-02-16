<?php
namespace MusicDB;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Album extends AbstractEntity{


	public function getPathName()
	{
		return $this->album_year . '_' . $this->normalize($this->album);
	}

	/**
	 * Gets artist object for a given album
	 * @return Artist
	 */
	public function getArtist()
	{
		$query = "SELECT * FROM album_artist WHERE album_artist_pid=?";
		$stm = $this->pdo->prepare($query);
		$stm->setFetchMode(PDO::FETCH_CLASS, 'Artist');
		if ($stm->execute(Array($this->album_artist_id)))
		{
			return $stm->fetch();
		}
	}

	public static function getList()
	{
		$pdo = \ImportCommand::getPdo();
		$query = 'SELECT * FROM album ORDER BY sort_album';
		$albums = $pdo->query($query, \PDO::FETCH_CLASS, get_class());
		return $albums;
	}

	public function getTracks()
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
				. "WHERE album_pid = ? "
				. "	AND item_playback.audio_format > 0 "	// skip non-audio files
				. "	AND item.keep_local > 0"				// skip podcasts
				. "ORDER by disc_number,track_number";

		$stm = $this->pdo->prepare($query);
		$stm->setFetchMode(\PDO::FETCH_CLASS, 'Track');
		$stm->execute(Array($this->album_pid));
		return $stm->fetchAll();
	}
}
