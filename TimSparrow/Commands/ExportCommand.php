<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace TimSparrow\Commands;
use TimSparrow\MusicDB\Artist;
use TimSparrow\MusicDB\Album;
use TimSparrow\MusicDB\Track;


/**
 * Export iPhone music database to filesystem
 * Files are converted to mp3 format using ffmpeg
 * ID3 tags are recreated from database
 * Files are grouped by album, Albums are grouped by artists
 * Audio books and classical works not supported
 *
 * @author TimSparrow
 */
class ExportCommand extends \ConsoleKit\Command{

	private static $pdo = null;	// database handler
	private $albums;
	private static $home;		// user home
	private static $mode=0755;	// directory create mode



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
		// write after this point


		$this->write(" copying...");
		$command = sprintf($this->cmd, $trackFile, $targetFile);
		system($command, $res);
		if($this->useRecode)
		{
			$track->updateTags($targetFile);
		}

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
