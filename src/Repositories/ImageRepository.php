<?php

namespace Wanphp\Plugins\messageBoard\Repositories;

use Wanphp\Libray\Mysql\Database;
use Wanphp\Plugins\messageBoard\Domain\ImageInterface;
use Wanphp\Plugins\messageBoard\Entities\ImageEntity;

class ImageRepository extends \Wanphp\Libray\Mysql\BaseRepository implements ImageInterface
{
  public function __construct(Database $database)
  {
    parent::__construct($database, self::TABLE_NAME, ImageEntity::class);
  }
}
