<?php

namespace Wanphp\Plugins\MessageBoard\Domain;

interface MessageInterface extends \Wanphp\Libray\Mysql\BaseInterface
{
  const TABLE_NAME = "message_board";
}
