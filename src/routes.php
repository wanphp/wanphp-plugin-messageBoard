<?php
declare(strict_types=1);

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Psr\Http\Server\MiddlewareInterface as Middleware;

return function (App $app, Middleware $PermissionMiddleware, Middleware $OAuthServerMiddleware) {
  $app->group('/api/plugin', function (Group $group) {
    // 留言
    $group->map(['GET', 'POST', 'PATCH', 'DELETE'], '/message[/{id:[0-9]+}]', \App\Application\Api\Civilization\MessageApi::class);
    // 留言总数
    $group->get('/messageCount[/{id:[0-9]+}]', \App\Application\Api\Civilization\MessageApi::class . ':messageCount');
    // 回复
    $group->map(['GET', 'POST', 'PATCH', 'DELETE'], '/message/reply[/{id:[0-9]+}]', \App\Application\Api\Civilization\MessageApi::class . ':reply');
  })->addMiddleware($OAuthServerMiddleware);

  $app->group('/admin', function (Group $group) {
    // 留言板
    $group->get('/messageBoard[/{id:[0-9]+}]', \Wanphp\Plugins\messageBoard\Application\Manage\MessageAction::class);
  })->addMiddleware($PermissionMiddleware);
};


