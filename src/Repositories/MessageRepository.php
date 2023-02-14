<?php

namespace Wanphp\Plugins\MessageBoard\Repositories;

use Wanphp\Libray\Mysql\Database;
use Wanphp\Plugins\MessageBoard\Domain\MessageInterface;
use Wanphp\Plugins\MessageBoard\Entities\MessageEntity;

class MessageRepository extends \Wanphp\Libray\Mysql\BaseRepository implements MessageInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLE_NAME, MessageEntity::class);
  }
}
