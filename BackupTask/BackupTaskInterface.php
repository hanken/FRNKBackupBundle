<?php 
 /*
 * @Copyright Copyright(c) 2012, franco fallica <franco.fallica@gmail.com>
 *
 * This file is part of the FRNKBackupBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
