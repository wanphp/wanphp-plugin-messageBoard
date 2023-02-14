<?php

namespace Wanphp\Plugins\messageBoard\Entities;
use Wanphp\Libray\Mysql\EntityTrait;

/**
 * @OA\Schema(
 *   title="留言图片",
 *   description="留言图片",
 *   required={"msgId","url"}
 * )
 */
class ImageEntity implements \JsonSerializable
{

  use EntityTrait;

  /**
   * @DBType({"key": "PRI","type":"int(11) NOT NULL AUTO_INCREMENT"})
   * @OA\Property(format="int64", description="图片ID")
   * @var integer|null
   */
  private ?int $id;
  /**
   * @DBType({"key": "MUL","type":"int(11) NOT NULL DEFAULT 0"})
   * @OA\Property(description="留言ID")
   * @var integer
   */
  private int $msgId;
  /**
   * @DBType({"type":"char(1) NOT NULL DEFAULT '0'"})
   * @OA\Property(description="图片类型,0为留言，1为回复")
   * @var integer
   */
  private int $type;
  /**
   * @DBType({"type":"varchar(50) NOT NULL DEFAULT ''"})
   * @OA\Property(description="图片地址")
   * @var string
   */
  private string $url;
}
