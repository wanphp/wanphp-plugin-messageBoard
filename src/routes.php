<?php
declare(strict_types=1);

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Psr\Http\Server\MiddlewareInterface as Middleware;

return function (App $app, Middleware $PermissionMiddleware, Middleware $OAuthServerMiddleware) {
  $app->group('/api/plugin', function (Group $group) {
    // 留言
    $group->map(['GET', 'POST', 'PATCH', 'DELETE'], '/message[/{id:[0-9]+}]', \Wanphp\Plugins\MessageBoard\Application\MessageApi::class);
    // 留言总数
    $group->get('/messageCount[/{id:[0-9]+}]', \Wanphp\Plugins\MessageBoard\Application\MessageApi::class . ':messageCount');
    // 回复
    $group->map(['GET', 'POST', 'PATCH', 'DELETE'], '/message/reply[/{id:[0-9]+}]', \Wanphp\Plugins\MessageBoard\Application\MessageApi::class . ':reply');
  })->addMiddleware($OAuthServerMiddleware);

  $app->group('/admin', function (Group $group) {
    // 留言板
    $group->get('/messageBoard[/{id:[0-9]+}]', \Wanphp\Plugins\MessageBoard\Application\Manage\MessageAction::class);
  })->addMiddleware($PermissionMiddleware);
};


