<?php

namespace Wanphp\Plugins\messageBoard\Entities;

use Wanphp\Libray\Mysql\EntityTrait;

/**
 * @OA\Schema(
 *   title="留言板",
 *   description="留言板数据结构",
 *   required={"description"}
 * )
 */
class MessageEntity implements \JsonSerializable
{

  use EntityTrait;

  /**
   * @DBType({"key": "PRI","type":"int(11) NOT NULL AUTO_INCREMENT"})
   * @OA\Property(format="int64", description="留言ID")
   * @var integer|null
   */
  private ?int $id;
  /**
   * @DBType({"type":"varchar(1000) NOT NULL DEFAULT ''"})
   * @OA\Property(description="描述")
   * @var string
   */
  private string $description;
  /**
   * @DBType({"key": "MUL","type":"int(11) NOT NULL DEFAULT '0'"})
   * @OA\Property(description="留言用户ID")
   * @var integer
   */
  private int $uid;
  /**
   * @DBType({"key": "MUL","type":"smallint(6) NULL DEFAULT '0'"})
   * @OA\Property(description="留言分类")
   * @var integer
   */
  private int $tagId;
  /**
   * @DBType({"type":"char(1) NOT NULL DEFAULT '0'"})
   * @OA\Property(description="留言最新状态，0未回复，1已回复")
   * @var integer
   */
  private int $status;
  /**
   *
   * @DBType({"type":"char(10) NOT NULL DEFAULT ''"})
   * @OA\Property(description="提交时间")
   * @var integer
   */
  private int $addTime;
}
