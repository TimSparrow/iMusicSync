<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace TimSparrow\MusicDB;
use TimSparrow\DB;
/**
 * Description of Track
 *
 * @author TimSparrow
 */
class Track extends AbstractEntity implements Id3Exportable
{
	const pattern = "%d_%02d_%s";

	private $_album=null;
	private $_artist=null;
	private $_time=null;
	
	public function getPathName()
	{
		return sprintf(self::pattern,  $this->disc_number, $this->track_number, $this->normalize($this->title));
	}

	public function getId()
	{
		return $this->pid;
	}

	public function __toString()
	{
		return $this->title;
	}

	public function getMediaFile()
	{
		return $this->path . '/'.$this->filename;
	}

	public function getFileExtension()
	{
		$dpos = strrpos($this->filename, '.');
		return substr($this->filename, $dpos + 1);
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
			. " item_extra.disc_count AS disc_count,"
			. " item_extra.track_count AS track_count,"
			. " item_extra.total_time_ms AS track_time,"
			. " item_extra.file_size AS file_size,"
			. " item_exita.comment AS comment,"
			. "	base_location.path AS path,"
			. "	item_playback.bit_rate AS bitrate,"
			. " item.playback.duration AS duration "
			. "FROM item JOIN item_extra ON item.item_pid = item_extra.item_pid "
			. "	JOIN genre ON item.genre_id=genre.genre_id "
			. "	JOIN item_playback ON item.item_pid= item_playback.item_pid"
			. "	JOIN base_location ON base_location.base_location_id=item.base_location_id "
			. "WHERE album_pid = ? "					// current album only
			. "	AND item_playback.audio_format > 0 "	// skip non-audio files
			. "	AND item.keep_local > 0 "				// skip podcasts
			. "ORDER by disc_number,track_number";

		$stm = DB::get()->prepare($query);
		if(!$stm)
		{
			throw new \Exception("Failed to prepare $query");
		}
		$stm->setFetchMode(\PDO::FETCH_CLASS, get_class());
		$stm->execute(Array($album_pid));
		return $stm->fetchAll();
	}

	

	public function getId3Tags($version=2)
	{
		return Array(
			'Tit2'	=> $this->title,
			'Tlen'	=> $this->getId3Len(),
			'Trck'	=> $this->track_number,
			'Tpos'	=> $this->disc_number,
			'Tcon'	=> $this->genre,
			'Comm'	=> $this->comment
		);
	}

	/**
	 * Track duration in format required by ID3 for TIME tag
	 * @deprecated since version Id3v2.4 (frame TIME)
	 * @return \String
	 */
	private function getId3Time(){
		return $this->getTrackTime('Hi');
	}

	/**
	 * Track duration in format required by ID3 for TLEN tag
	 * @return String
	 */
	private function getId3Len(){
		return $this->getTrackTime('H:i:s.u');
	}

	/**
	 * Decodes track time from database and stores it internally
	 * as a DateTime object
	 * @param String $format - used to convert DateTime object
	 * @see DateTime::format
	 * @return String
	 */
	protected function getTrackTime($format=null)
	{
		if(null===$this->_time)
		{
			$this->_time = \DateTime::createFromFormat('U.u', $this->track_time / 1000);
		}
		return $this->_time->format($format);
	}

}
