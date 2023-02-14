<?php

namespace Wanphp\Plugins\messageBoard\Repositories;

use Wanphp\Libray\Mysql\Database;
use Wanphp\Plugins\messageBoard\Domain\ReplyInterface;
use Wanphp\Plugins\messageBoard\Entities\ReplyEntity;

class ReplyRepository extends \Wanphp\Libray\Mysql\BaseRepository implements ReplyInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLE_NAME, ReplyEntity::class);
  }

}
