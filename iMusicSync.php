#!/usr/bin/php
<?php
require_once 'vendor/autoload.php';
use MusicDB\Album;
use MusicDB\Artist;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

spl_autoload_register(function($name){
	$name = str_replace('\\', '/', $name);
	require_once './'.$name.'.php';
});

class ImportCommand extends \ConsoleKit\Command{
	const iPhoneDir = '~/backups/iphone/'; // change to the dir iPhone is mounted to on production version
	const iTunesDB = 'iTunesControl';
	const dbFile = 'iTunes/MediaLibrary.sqlitedb';
	const targetPath = '~/Music';
	
	private $useLinks = true;
	private $useRecode = false;
	private $recodeParams = "ffmpeg %s ";
	private static $pdo = null;
	private $albums;



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

	public static function getPdo()
	{
		return self::$pdo;
	}

	private function init()
	{
		if(self::$pdo == null)
		{
			$home = getenv('HOME');
			$file = str_replace('~', $home, ($this->getPdoFileName()));
			$schema = 'sqlite:'.$file;
			echo "Using $file as database\n";
			self::$pdo = new \PDO($schema);
		}
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
		$this->init();
		$this->albums = Album::getList();
		foreach($this->albums as $album)
		{
			echo $album->getPathName(). "\n";
			continue;
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
