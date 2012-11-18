<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author franco
 */

namespace FRNK\BackupBundle\BackupTask;

interface BackupTaskInterface {

    /**
     * collects backups
     * @return array of backups
     */
    public function collect();

    /**
     * restores a backup
     * @param array $info
     * @param string $content
     */
    public function restore(array $info, $content);
}

?>
