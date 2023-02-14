<?php

namespace Wanphp\Plugins\MessageBoard\Repositories;

use Wanphp\Libray\Mysql\Database;
use Wanphp\Plugins\MessageBoard\Domain\ImageInterface;
use Wanphp\Plugins\MessageBoard\Entities\ImageEntity;

class ImageRepository extends \Wanphp\Libray\Mysql\BaseRepository implements ImageInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLE_NAME, ImageEntity::class);
  }
}
