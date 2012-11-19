<?php 
 /*
 * @Copyright Copyright(c) 2012, franco fallica <franco.fallica@gmail.com>
 *
 * This file is part of the FRNKBackupBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */



namespace FRNK\BackupBundle\BackupBackend;

use FRNK\BackupBundle\BackupBackend\BackupBackendInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Finder\Finder;

use \DateTime;

class FilesystemBackupBackend implements BackupBackendInterface {
    const DATE_FORMAT = 'YmdHis';
    protected $path;

    /**
     * Constructor for FilesystemBackupBackend
     * @param type $path
     */
    function __construct($path) {

        if ($this->isWritable($path)) {
            $this->path = $path;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function store($namespace, $name, $data) {

        $fs = new Filesystem();
        $namespacePath = $this->path . DIRECTORY_SEPARATOR . $namespace;
        if (!$fs->exists($namespacePath)) {
            try {
                $fs->mkdir($namespacePath, 0755);
            } catch (IOException $ex) {
                throw new IOException(sprintf('Could not create namespace path %s', $namespacePath), $ex->getCode(), $ex);
            }
        }
        $date = new DateTime('now');
        $filename = sprintf('%s%s%s-%s', $namespacePath, DIRECTORY_SEPARATOR, $date->format(FilesystemBackupBackend::DATE_FORMAT), $name);
        file_put_contents($filename, $data);
    }

    public function listBackups($namespace = null) {
        if ($namespace) {
            $path = $this->path . DIRECTORY_SEPARATOR . $namespace;
        } else {
            $path = $this->path;
        }
        $backups = array();
        $finder = new Finder();
        $finder->files()->in($path);
        foreach ($finder as $file){
            $pathInfo = pathinfo($file);
            $backups [] = $pathInfo['dirname'] .'/'.$pathInfo['filename'].'.'.$pathInfo['extension'];
        }
         return $backups;
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveBackup($backupName){
        
        if (false !== $file = $this->findBackup($backupName)){
            return file_get_contents($file);
        }
        return false;
    }
    
    
    public function getBackupInfo($backupName){
        if (false !== $file = $this->findBackup($backupName)){
            return $this->buildInfoArray($file);
        }
        return false;
    }
    
    private function buildInfoArray($file){
        $pathInfo = pathinfo($file);
        $namespace = substr($pathInfo['dirname'],strpos($pathInfo['dirname'],$this->path)+strlen($this->path));
        $name = explode( "-",$pathInfo['filename']);
        $backupName = $name[1];
        $date = DateTime::createFromFormat(FilesystemBackupBackend::DATE_FORMAT, $name[0]);
       
        return array('namespace' => $namespace, 'name' => $backupName, 'date' => $date);
    }
    
    /** 
     * finds a backup based on its file name
     * 
     * @param string $backupName
     * @return Finder file if found, false otherwise
     */
    private function findBackup($backupName){
        $finder = new Finder();
        $finder->files()->in($this->path)->name(basename($backupName));
        foreach ($finder as $file){
            return $file; 
        }
        return false;
    }
    /**
     * checks if a path dir is writeable
     * @param type $path
     * @return boolean
     * @throws \InvalidArgumentException
     */
    private function isWritable($path) {
        $fs = new Filesystem();

        if (!$fs->isAbsolutePath($path)) {
            throw new \InvalidArgumentException(sprintf('FilesystemBackup path (%s) must be absolute.', $path));
        }

        if (!$fs->exists($path)) {
            try {
                $fs->mkdir($path, 0755);
            } catch (IOException $ex) {
                throw new \InvalidArgumentException(sprintf('Tried to create FilesystemBackup path (%s), but failed. Please create it manualy or make the parent folder wirteable.', $path));
            }
        }

        try {
            $fs->touch($path . "/test");
            $fs->remove($path . "/test");
        } catch (IOException $ex) {
            throw new \InvalidArgumentException(sprintf('FilesystemBackup path (%s) must be writeable.', $path));
        }

        return true;
    }

}

?>
