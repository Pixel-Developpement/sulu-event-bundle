<?php

namespace Pixel\EventBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Sulu\Bundle\PersistenceBundle\PersistenceBundleTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EventBundle extends Bundle
{

    use PersistenceBundleTrait;

}