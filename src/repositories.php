<?php
declare(strict_types=1);

use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
  $containerBuilder->addDefinitions([
    \Wanphp\Plugins\messageBoard\Domain\MessageInterface::class => \DI\autowire(\Wanphp\Plugins\messageBoard\Repositories\MessageRepository::class),
    \Wanphp\Plugins\messageBoard\Domain\ReplyInterface::class => \DI\autowire(\Wanphp\Plugins\messageBoard\Repositories\ReplyRepository::class),
    \Wanphp\Plugins\messageBoard\Domain\ImageInterface::class => \DI\autowire(\Wanphp\Plugins\messageBoard\Repositories\ImageRepository::class)
  ]);
};
