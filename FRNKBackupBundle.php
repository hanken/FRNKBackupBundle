<?php 
 /*
 * @Copyright Copyright(c) 2012, franco fallica <franco.fallica@gmail.com>
 *
 * This file is part of the FRNKBackupBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */



namespace FRNK\BackupBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use FRNK\BackupBundle\DependencyInjection\Compiler\BackupTaskPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FRNKBackupBundle extends Bundle
{
    
    /**
    * @see Symfony\Component\HttpKernel\Bundle\Bundle::build()
    */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
         $container->addCompilerPass(new BackupTaskPass());
    }
    
}
