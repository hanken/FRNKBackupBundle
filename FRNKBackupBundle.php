<?php

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
