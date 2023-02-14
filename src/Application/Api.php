<?php

namespace Wanphp\Plugins\messageBoard\Application;

use Wanphp\Libray\Slim\Action;

/**
 * @OA\Info(
 *     description="留言板插件，插件不能单独运行",
 *     version="1.0.0",
 *     title="留言板"
 * )
 * @OA\Tag(
 *     name="Massage Board",
 *     description="留言板"
 * )
 * @OA\Tag(
 *     name="Massage Bank",
 *     description="后端管理"
 * )
 */

/**
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT",
 * )
 * @OA\Schema(
 *   title="出错提示",
 *   schema="Error",
 *   type="object"
 * )
 * @OA\Schema(
 *   title="成功提示",
 *   schema="Success",
 *   type="object"
 * )
 */
abstract class Api extends Action
{
}
