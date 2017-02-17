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
	const iTunesDB = 'iTunes_Control';
	const dbFile = 'iTunes/MediaLibrary.sqlitedb';
	const sourcePath = '/Music';
	const targetPath = '~/Music';
	
	private $useLinks = true;	// use hard links (debug only)
	private $useRecode = false;	// recode files to mp3 (not implemented)
	private $cmdRecode = "ffmpeg %1s %2s";	// recode command
	private $cmdCopy = "cp %1s %2s";		// copy command
	private $cmdLink = "ln %1s %2s";		// link command
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

			$this->initCmd();
		}
	}

	private function initCmd()
	{
		if($this->useRecode)
		{
			$this->cmd = $this->cmdRecode;
			$this->writeln("Recode mode selected");
		}
		elseif($this->useLinks)
		{
			$this->cmd = $this->cmdLink;
			$this->writeln("Hard link mode selected");
		}
		else
		{
			$this->cmd = $this->cmdCopy;
			$this->writeln("Copy mode selected");
		}
	}

	private function getSourcePath()
	{
		return $this->getFullPath(self::iPhoneDir);
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
		$trackFile = $this->getSourcePath().$track->getMediaFile();
		$targetFile = $path.'/'.$track->getPathName();

		if(is_file($targetFile))
		{
			if(filemtime($targetFile) >= filemtime($trackFile))
			{
				$this->writeln('target exists, skipping');
				return true;
			}
			else {
				$this->write('target is old, overwriting');
			}
		}
		$this->write(" copying...");
		$command = sprintf($this->cmd, $trackFile, $targetFile);
		system($command, $res);
		$this->writeln("done");
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
				$this->write($artist . ' // '. $album);
				$tracks = $album->getTracks();
				$path = $this->createPathForTracks(Array($artist->getPathName(), $album->getPathName()));
				$this->writeln(sprintf("::Tracks:%d", sizeof($tracks)));
				foreach($tracks as $track)
				{
					$this->write("\t".$track." --> ");
					$this->saveTrack($track, $path);
				}
				exit;
			}
		}
		catch (\PDOException $x)
		{
			$this->writerr("Database exception: ".$x->getMessage());
			$this->writerr(print_r($x->errorInfo, true));
			$this->writeerr($x->getTraceAsString());
			exit;
		}
	}
}


$console = new ConsoleKit\Console();
$console->addCommand('ImportCommand');
$console->run();
