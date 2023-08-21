<?php

namespace Sys\Model;

use Sys\Model\BaseModel;
use ReflectionObject;
use ReflectionAttribute;

final class ModelCommit extends BaseModel 
{
    public function __invoke()
    {
        global $container;
        
        if (is_file(CONFIGPATH . 'saverProvider.php')) {
            $saveProvider = require CONFIGPATH . 'saverProvider.php';
        } else {
            $saveProvider = null;
        }

        if (isset($GLOBALS['_changed'])) {
            foreach ($GLOBALS['_changed'] as $entity) {
                $reflection = new ReflectionObject($entity);
                $attribute = $reflection->getAttributes(Saveble::class, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

                if ($attribute) {
                    $class = $attribute->getName();
                } elseif ($saveProvider) {
                    $class = $saveProvider[get_class($entity)];
                } 
                
                $repo = $container->get($class);
                $repo->save($entity);
            }
        }
    }
}
