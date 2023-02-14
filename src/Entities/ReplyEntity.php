<?php

namespace Wanphp\Plugins\MessageBoard\Entities;

use Wanphp\Libray\Mysql\EntityTrait;

/**
 * @OA\Schema(
 *   title="留言回复记录",
 *   description="留言回复记录",
 *   required={"qesId,description"}
 * )
 */
class ReplyEntity implements \JsonSerializable
{

  use EntityTrait;

  /**
   * @DBType({"key": "PRI","type":"int(11) NOT NULL AUTO_INCREMENT"})
   * @OA\Property(format="int64", description="留言回复ID")
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
   * @DBType({"key": "MUL","type":"varchar(50) NOT NULL DEFAULT ''"})
   * @OA\Property(description="回复部门")
   * @var integer
   */
  private int $department;
  /**
   * @DBType({"key": "MUL","type":"int(11) NOT NULL DEFAULT 0"})
   * @OA\Property(description="回复用户ID")
   * @var integer
   */
  private int $uid;
  /**
   * @DBType({"type":"varchar(1000) NOT NULL DEFAULT ''"})
   * @OA\Property(description="回复内容")
   * @var string
   */
  private string $description;
  /**
   *
   * @DBType({"key": "MUL","type":"char(10) NOT NULL DEFAULT ''"})
   * @OA\Property(description="回复时间")
   * @var integer
   */
  private int $ctime;
}
