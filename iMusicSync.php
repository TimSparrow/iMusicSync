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

// autoload classes
spl_autoload_register(function($name){
	$name = str_replace('\\', '/', $name);
	require_once './'.$name.'.php';
});

class ImportCommand extends \ConsoleKit\Command{
	const iPhoneDir = '~/backups/iphone/'; // change to the dir iPhone is mounted to on production version
	const iTunesDB = 'iTunesControl';
	const dbFile = 'iTunes/MediaLibrary.sqlitedb';
	const targetPath = '~/Music';
	
	private $useLinks = true;	// use hard links (debug only)
	private $useRecode = false;	// recode files to mp3 (not implemented)
	private $recodeParams = "ffmpeg %1s %2s";	// recode command
	private static $pdo = null;	// database handler
	private $albums;
	private static $home;		// user home
	private static $mode=0755;	// directory create mode


	private static function getPdoFileName()
	{
		return self::getFullPath(self::iPhoneDir). self::iTunesDB . '/' . self::dbFile;
	}

	/**
	 * Constructs and returns a path to save tracks for a given album
	 * @param array $pathComponents - array like artist, album, etc.
	 * @return string - path
	 */
	private function createPathForTracks(Array $pathComponents)
	{
		$exportPath = self::getFullPath(self::targetPath) . '/' . implode('/', $pathComponents);
		if(!is_dir($exportPath))
		{
			if(!mkdir($exportPath, self::$mode, true))
			{
				throw new Exception("Failed to create directory $exportPath");
			}
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
			$file = $this->getPdoFileName();
			$schema = 'sqlite:'.$file;
			echo "Using $file as database\n";
			self::$pdo = new \PDO($schema);
			self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
	}

	private static function getFullPath($path)
	{
		if(null===self::$home)
		{
			self::$home = getenv('HOME');
		}
		return str_replace('~', self::$home, $path);;
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

	/**
	 * Required method by \ConsoleKit\Command
	 * @param array $args
	 * @param array $options
	 */
	public function execute(array $args, array $options = array())
	{
		$this->init();
		try
		{
			$this->albums = Album::getList();
			echo sprintf("Got %d albums\n", sizeof($this->albums));
			foreach($this->albums as $album)
			{

				$artist = $album->getArtist();
				echo $artist . ' // '. $album;

				$trackData = $album->getTracks();

				$path = $this->createPathForTracks(Array($artist->getPathName(), $album->getPathName()));
				echo "path: $path\n";
				foreach($trackData as $track)
				{
					echo "\t".$track." --> ".$track->getPathName()."\n";
					continue;
					$this->saveTrack($track, $path);
				}
				exit;

			}
		}
		catch (\PDOException $x)
		{
			echo "Database exception: ".$x->getMessage()."\n";
			print_r($x->errorInfo);
			echo $x->getTraceAsString();
			exit;
		}
	}
}


$console = new ConsoleKit\Console();
$console->addCommand('ImportCommand');
$console->run();
