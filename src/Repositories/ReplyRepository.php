<?php

namespace Wanphp\Plugins\MessageBoard\Repositories;

use Wanphp\Libray\Mysql\Database;
use Wanphp\Plugins\MessageBoard\Domain\ReplyInterface;
use Wanphp\Plugins\MessageBoard\Entities\ReplyEntity;

class ReplyRepository extends \Wanphp\Libray\Mysql\BaseRepository implements ReplyInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLE_NAME, ReplyEntity::class);
  }

}
