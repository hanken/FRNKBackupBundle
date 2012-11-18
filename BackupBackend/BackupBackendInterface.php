<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author franco
 */

namespace FRNK\BackupBundle\BackupBackend;


interface BackupBackendInterface {
   
    /**
     * stores a backup instance into the backend
     * @param string $namespace
     * @param string $name
     * @param string $data
     */
    public function store($namespace, $name, $data);

    /**
     * lists all backups in this backend.
     */
    public function listBackups($namespace = null);
    
    /**
     * returns a single backup from the backend as string. 
     * @param type $backupName
     * @return string the backup's content as string. 
     */
    public function retrieveBackup($backupName);
    
    /**
     * returns an array of infos about the backup.
     * @param type $backupName
     * @return array of infos. 
     */
    public function getBackupInfo($backupName);
    
}

?>
