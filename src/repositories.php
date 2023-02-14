<?php
declare(strict_types=1);

use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
  $containerBuilder->addDefinitions([
    \Wanphp\Plugins\MessageBoard\Domain\MessageInterface::class => \DI\autowire(\Wanphp\Plugins\MessageBoard\Repositories\MessageRepository::class),
    \Wanphp\Plugins\MessageBoard\Domain\ReplyInterface::class => \DI\autowire(\Wanphp\Plugins\MessageBoard\Repositories\ReplyRepository::class),
    \Wanphp\Plugins\MessageBoard\Domain\ImageInterface::class => \DI\autowire(\Wanphp\Plugins\MessageBoard\Repositories\ImageRepository::class)
  ]);
};
