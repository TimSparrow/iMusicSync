#!/usr/bin/php
<?php
use MusicDB\Album;
use MusicDB\Artist;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ImportCommand extends \ConsoleKit\Command{
	const iPhoneDir = '~/backups/iphone/'; // change to the dir iPhone is mounted to on production version
	const iTunesDB = 'iTunesControl';
	const dbFile = 'iTunes/MediaLibrary.sqlitedb';
	const targetPath = '~/Music';
	
	private $useLinks = true;
	private $useRecode = false;
	private $recodeParams = "ffmpeg %s ";
	private $pdo = null;
	private $albums;

	/**
	 * Retrieve all albums from database 
	 */
	private function getAlbumList()
	{
		$query = 'SELECT * FROM album ORDER BY sort_album';
		$this->albums = $this->pdo->query($query, \PDO::FETCH_CLASS, 'Album');
		print_r($this->albums);
		exit;
	}

	/**
	 * Get artist for an album
	 * @param Album $album - album object from db
	 * @return Artist - object representation of an artist
	 */


	private static function getPdoFileName()
	{
		return self::iPhoneDir. self::iTunesDB . '/' . self::dbFile;
	}

	/**
	 * Constructs and returns a path to save tracks for a given album
	 * @param array $pathComponents - array like artist, album, etc.
	 * @return string - path
	 */
	private function createPathForTracks(Array $pathComponents)
	{
		$exportPath = self::targetPath . '/'. implode('/', array_map(function($path){
			return preg_replace('/\s+/', '-', $path);
		}, $pathComponents));
		if(!is_dir($exportPath))
		{
			mkdir($exportPath);
		}
		return $exportPath;
	}

	private function getPdo()
	{
		if($this->pdo == null)
		{
			$this->pdo = new \PDO('sqlite:'.$this->getPdoFileName());
		}
		return $this->pdo;
	}

	private function saveTrack($track, $path)
	{
		$trackFile = $this->getTrackFile($track);
		if($this->useLinks)
		{
			$command = "ln $trackFile, $targetFile";
			exec();
		}
		else
		{

		}
	}

	public function execute(array $args, array $options = array())
	{
		$this->getAlbumList();
		foreach($this->albums as $album)
		{
			$artist = $album->getArtist();
			$trackData = $this->getTrackData($album);
			$path = $this->createPathForTracks(Array($artist->album_artist, $album->album_year . '_' . $album->album));
			foreach($trackData as $track)
			{
				$this->saveTrack($track, $path);
			}

		}
	}
}


$console = new ConsoleKit\Console();
$console->addCommand('ImportCommand');
$console->run();
