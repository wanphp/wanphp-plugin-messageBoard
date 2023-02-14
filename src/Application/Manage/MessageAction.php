<?php

namespace Wanphp\Plugins\MessageBoard\Application\Manage;

use Psr\Http\Message\ResponseInterface as Response;
use Wanphp\Libray\Slim\Action;
use Wanphp\Libray\Slim\WpUserInterface;
use Wanphp\Plugins\messageBoard\Domain\ImageInterface;
use Wanphp\Plugins\messageBoard\Domain\MessageInterface;

/**
 * Class MessageAction
 * @title 留言板
 * @route /admin/messageBoard
 * @package Wanphp\Plugins\messageBoard\Application\Manage
 */
class MessageAction extends Action
{

  private MessageInterface $message;
  private WpUserInterface $user;
  private ImageInterface $image;

  public function __construct(MessageInterface $message, WpUserInterface $user, ImageInterface $image)
  {
    $this->message = $message;
    $this->user = $user;
    $this->image = $image;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    if ($this->request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {
      $where = [];
      $params = $this->request->getQueryParams();
      if (!empty($params['search']['value'])) {
        $keyword = trim($params['search']['value']);
        $where['description[~]'] = $keyword;
      }
      if (isset($params['uid']) && $params['uid'] > 0) {
        $where['uid'] = intval($params['uid']);
      }

      $recordsFiltered = $this->message->count('id', $where);
      $where['LIMIT'] = $this->getLimit();
      $order = $this->getOrder();
      if ($order) $where['ORDER'] = $order;
      else $where['ORDER'] = ['addTime' => 'DESC'];


      $message = $this->message->select('id,uid,description,status,addTime', $where);
      if (empty($message)) return $this->respondWithData();
      $userId = array_unique(array_column($message, 'uid'));
      // 绑定微信
      $status = [0 => '尚未回复', 1 => '<span class="text-green">已回复</span>'];
      if ($userId) {
        $users = [];
        foreach ($this->user->getUsers($userId) as $user) {
          $users[$user['id']] = [
            'nickname' => $user['nickname'],
            'headimgurl' => $user['headimgurl'],
            'name' => $user['name'],
            'tel' => $user['tel']
          ];
        }

        foreach ($message as &$msg) {
          $msg['status'] = $status[$msg['status']];
          $msg['user'] = $users[$msg['uid']] ?? [];
          $msg['images'] = $this->image->select('url', ['msgId' => $msg['id'], 'type' => 0]);
          array_walk($msg['images'], function (&$image) {
            $image = $this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost() . $image;
          });
        }
        unset($msg);
      }

      $data = [
        "draw" => $params['draw'],
        "recordsTotal" => $this->message->count('id'),
        "recordsFiltered" => $recordsFiltered,
        'data' => $message
      ];
      return $this->respondWithData($data);
    } else {
      $data = [
        'title' => '留言板'
      ];

      return $this->respondView('@message-board/message.html', $data);
    }
  }
}
