<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *  The FolderBackupTask zips a list of folder into one zip file each. 
 *
 * @author franco
 */

namespace FRNK\BackupBundle\BackupTask;

use FRNK\BackupBundle\BackupTask\BackupTaskInterface;
use \ZipArchive;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

class FolderBackupTask implements BackupTaskInterface {

    private $paths;
    protected $includeHiddenFiles;
    protected $includeVCS;
    protected $tmp;

    /**
     * constructor for the FolderBackupTask
     * @param array $paths paths to backup
     * @param string $tmp a tmp dir (used for archives)
     * @param boolean $inculdeHiddenFiles if hidden files should be included. 
     * @param boolean $includeVCS if .git or .svn folders should be included. 
     */
    function __construct(array $paths = array(), $tmp = '/tmp', $inculdeHiddenFiles = false, $includeVCS = true) {
        $this->paths = $paths;
        $this->tmp = $tmp;
        $this->includeHiddenFiles = $inculdeHiddenFiles;
        $this->includeVCS = $includeVCS;

        $this->isWritable($tmp);
    }

    /**
     * {@inheritDoc}
     */
    public function restore(array $info, $content, $force = false) {
        if (!isset($info['name'])) {
            return false;
        }

        $name = $info['name'];
        $dir = $this->reverseFileName($name);

        $fs = new Filesystem();
        if ($fs->exists($dir) && !$force) {
            throw new \RuntimeException("Restore path still exists. Will not Overwrite it. Use --force to overwrite files");
        }

        return $this->extractZipContent($content, $dir);
    }

    /**
     * {@inheritDoc}
     */
    public function collect() {
        $backups = array();
        foreach ($this->paths as $path) {
            $zipContent = $this->zipFolder($path);
            $name = $this->createFileName($path);
            $backups[] = array('name' => $name, 'data' => $zipContent);
        }
        return $backups;
    }

    protected function reverseFileName($name) {
        $dir = '/' . preg_replace('/_/', '/', $name) . '/';
        return $dir;
    }

    /**
     * builds up the proper filename to be used. 
     * @param string $path
     * @return string
     */
    protected function createFileName($path) {
        if (substr($path, 0, 1) === "/") {
            $path = substr($path, 1);
        }
        return preg_replace('/\//', '_', $path) . '.zip';
    }

    /**
     * creates a string containing the contents of a zipped folder. 
     * @param string $path
     * @return string
     */
    protected function zipFolder($path) {
        $tmpFile = $this->tmp . DIRECTORY_SEPARATOR . 'backup.zip';

        $zip = new ZipArchive();
        $zip->open($tmpFile, ZipArchive::CREATE);

        $fs = new Filesystem();

        if (!$fs->exists($path)) {
            $zip->addEmptyDir(($path));
        } else {
            $finder = new Finder();
            $finder->files()
                    ->in($path)
                    ->ignoreDotFiles(!$this->includeHiddenFiles)
                    ->ignoreVCS(!$this->includeVCS);

            foreach ($finder as $file) {
                $localname = $file;
                if (substr($localname, 0, 1) === "/") {
                    $localname = substr($file, 1);
                }
                $zip->addFile($file, $localname);
            }
        }
        
        $zip->close();

        $content = file_get_contents($tmpFile);
        $fs->remove($tmpFile);

        return $content;
    }

    protected function extractZipContent($content, $dir) {
        $tmpFile = $this->tmp . DIRECTORY_SEPARATOR . 'backup.zip';
        if (false === file_put_contents($tmpFile, $content)) {
            throw new \RuntimeException('Could not create temporary zip file.');
        }
        $fs = new Filesystem();

        $zip = new ZipArchive();
        if ($zip->open($tmpFile)) {
            $result =  $zip->extractTo('/');
        }else{
            $result = false;
        }
        $zip->close();
        $fs->remove($tmpFile);
        return $result;
    }

    /**
     * checks if dir is writeable. 
     * @param string $path
     * @return boolean
     * @throws \InvalidArgumentException
     */
    private function isWritable($path) {
        $fs = new Filesystem();

        if (!$fs->isAbsolutePath($path)) {
            throw new \InvalidArgumentException(sprintf('tmp_dir (%s) must be absolute.', $path));
        }

        if (!$fs->exists($path)) {
            try {
                $fs->mkdir($path, 0755);
            } catch (IOException $ex) {
                throw new \InvalidArgumentException(sprintf('Tried to create tmp_dir (%s), but failed. Please create it manualy or make the parent folder wirteable.', $path));
            }
        }

        try {
            $fs->touch($path . "/test");
            $fs->remove($path . "/test");
        } catch (IOException $ex) {
            throw new \InvalidArgumentException(sprintf('tmp_dir path (%s) must be writeable.', $path));
        }

        return true;
    }

}

?>
