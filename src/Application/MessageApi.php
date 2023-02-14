<?php

namespace Wanphp\Plugins\MessageBoard\Application;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Wanphp\Libray\Slim\Setting;
use Wanphp\Libray\Slim\WpUserInterface;
use Wanphp\Plugins\MessageBoard\Domain\ImageInterface;
use Wanphp\Plugins\MessageBoard\Domain\MessageInterface;
use Wanphp\Plugins\MessageBoard\Domain\ReplyInterface;

class MessageApi extends Api
{
  private LoggerInterface $logger;
  private MessageInterface $message;
  private WpUserInterface $user;
  private ImageInterface $image;
  private ReplyInterface $reply;
  private mixed $filepath;
  private string $applicationPath;
  private array $admin;

  /**
   * @param LoggerInterface $logger
   * @param Setting $setting
   * @param MessageInterface $message
   * @param WpUserInterface $user
   * @param ImageInterface $image
   * @param ReplyInterface $reply
   */
  public function __construct(
    LoggerInterface  $logger,
    Setting          $setting,
    MessageInterface $message,
    WpUserInterface  $user,
    ImageInterface   $image,
    ReplyInterface   $reply
  )
  {
    $this->logger = $logger;
    $this->filepath = $setting->get('uploadFilePath');
    $messageBoard = $setting->get('messageBoard');
    $this->applicationPath = $messageBoard['applicationPath'];
    $this->admin = $messageBoard['adminUid'];
    $this->message = $message;
    $this->user = $user;
    $this->image = $image;
    $this->reply = $reply;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    $userid = $this->getUid();
    $uri = $this->request->getUri();

    switch ($this->request->getMethod()) {
      case 'POST':
        $data = $this->request->getParsedBody();
        $msgData = [
          'uid' => $userid,
          'description' => $data['description'],
          'addTime' => time()
        ];

        // 检查重复提交
        $id = $this->message->get('id', ['uid' => $userid, 'description' => $data['description']]);
        if ($id) return $this->respondWithError('请不要重复提交相同留言！');

        $msgId = $this->message->insert($msgData);
        if ($msgId > 0) {
          // 给管理员发送公众号通知
          if ($this->admin) {
            $msgData = array(
              'template_id_short' => 'OPENTM402006312',//	工作任务提醒	政府与公共事业
              'url' => $uri->getScheme() . '://' . $uri->getHost() . $this->applicationPath . 'message/reply/' . $msgId,
              'data' => array(
                'first' => array('value' => "收到新的用户留言：", 'color' => '#173177'),
                'keyword1' => array('value' => $msgData['description'], 'color' => '#173177'),
                'keyword2' => array('value' => date('Y-m-d H:i:s'), 'color' => '#173177'),
                'remark' => array('value' => '点击请进入系统，查看留言详情。', 'color' => '#173177')
              )
            );
            $res = $this->user->sendMessage($this->admin, $msgData);
            $this->logger->info('messageBoardSendMsg', array_merge($this->admin, $res));
          }
          if (!empty($data['images'])) {
            $msgImage = [];
            foreach ($data['images'] as $url) {
              $msgImage[] = ['msgId' => $msgId, 'type' => 0, 'url' => $url];
            }
            $this->image->insert($msgImage);
          }
        }
        return $this->respondWithData(['msgId' => $msgId], 201);
      case 'PATCH':
        $id = $this->resolveArg('id');
        $data = $this->request->getParsedBody();
        if (empty($data)) return $this->respondWithError('无数据');
        $num = $this->message->update($data, ['id' => $id]);
        return $this->respondWithData(['upNum' => $num], 201);
      case 'DELETE';
        $id = $this->resolveArg('id');
        $delNum = $this->message->delete(['id' => $id]);
        if ($delNum > 0) {
          // 留言图片
          $images = $this->image->select('url', ['msgId' => $id, 'type' => 0]);
          if ($images) foreach ($images as $image) if (is_file($this->filepath . $image)) unlink($this->filepath . $image); //删除文件
          // 回复图片
          $replyId = $this->reply->select('id', ['msgId' => $id]);
          if ($replyId) {
            $images = $this->image->select('url', ['msgId' => $replyId, 'type' => 1]);
            if ($images) foreach ($images as $image) if (is_file($this->filepath . $image)) unlink($this->filepath . $image); //删除文件
          }
          $delNum += $this->reply->delete(['msgId' => $id]);
          $delNum += $this->image->delete(['msgId' => $id]);
        }
        return $this->respondWithData(['delNum' => $delNum], 200);
      case 'GET':
        $userid = $this->getUid();
        $where = [];
        $params = $this->request->getQueryParams();

        // 留言分类
        $where['tagId'] = intval($params['tagId'] ?? 0);

        // 检查用户是否为系统管理员
        if (!in_array($userid, $this->admin)) {
          $where['uid'] = $userid;
        }
        if (!empty($params['kw'])) {
          $keyword = trim($params['kw']);
          $where['OR'] = [
            'id[~]' => $keyword,
            'description[~]' => $keyword
          ];
        }
        if (isset($params['status'])) {
          $where['status'] = intval($params['status']);
        }

        $where['LIMIT'] = [$params['start'], $params['length']];
        $where['ORDER'] = ["id" => "DESC"];

        $message = $this->message->select('id,uid,description,status,addTime', $where);
        if (empty($message)) return $this->respondWithData();
        $userId = array_unique(array_column($message, 'uid'));
        // 绑定微信
        $status = [0 => '尚未回复', 1 => '<span class="text-green">已回复</span>'];
        if (in_array($userid, $this->admin)) {
          $users = [];
          foreach ($this->user->getUsers($userId) as $user) {
            $users[$user['id']] = [
              'nickname' => $user['nickname'],
              'headimgurl' => $user['headimgurl'],
              'name' => $user['name'],
              'tel' => $user['tel']
            ];
          }
        }

        foreach ($message as &$msg) {
          $msg['status'] = $status[$msg['status']];
          if (in_array($userid, $this->admin)) $msg['user'] = $users[$msg['uid']] ?? [];
          $msg['images'] = $this->image->select('url', ['msgId' => $msg['id'], 'type' => 0]);
          array_walk($msg['images'], function (&$image) {
            $image = $this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost() . $image;
          });
        }
        return $this->respondWithData($message);
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param array $args
   * @return Response
   * @throws Exception
   */
  public function reply(Request $request, Response $response, array $args): Response
  {
    $this->request = $request;
    $this->response = $response;
    $this->args = $args;

    $userid = $this->getUid();
    $uri = $this->request->getUri();

    switch ($this->request->getMethod()) {
      case 'POST':
        $data = $this->request->getParsedBody();
        $message = $this->message->get('uid,description,addTime', ['id' => $data['msgId']]);
        if (!$message) return $this->respondWithError('此留言已被删除！');

        if (!in_array($userid, $this->admin)) {
          return $this->respondWithError('未知身份，不可回复！');
        }

        $replyData = [
          'uid' => $userid,
          'department' => $data['department'],
          'msgId' => $data['msgId'],
          'description' => $data['description'],
          'ctime' => time()
        ];

        $replyData['id'] = $this->reply->insert($replyData);
        if ($replyData['id'] > 0) {
          // 发送公众号通知通知留言用户
          $msgData = array(
            'template_id_short' => 'OPENTM411665252',//OPENTM411665252	反馈结果通知	IT科技
            'url' => $uri->getScheme() . '://' . $uri->getHost() . $this->applicationPath . 'message/reply/' . $data['msgId'],
            'data' => array(
              'first' => array('value' => "您好，您收到一条短信回复。", 'color' => '#173177'),
              'keyword1' => array('value' => $replyData['department'], 'color' => '#173177'),
              'keyword2' => array('value' => date('Y-m-d H:i:s'), 'color' => '#173177'),
              'keyword3' => array('value' => $replyData['description'], 'color' => '#173177'),
              'remark' => array('value' => '点击请进入系统，查看回复详情。', 'color' => '#173177')
            )
          );
          $this->user->sendMessage([$message['uid']], $msgData);
          // 更新留言最新状态
          $this->message->update(['status' => 1], ['id' => $data['msgId']]);

          if (!empty($data['images'])) {
            $msgImage = [];
            foreach ($data['images'] as $url) {
              $msgImage[] = ['msgId' => $replyData['id'], 'type' => 1, 'url' => $url];
              $replyData['images'][] = $uri->getScheme() . '://' . $uri->getHost() . $url;
            }
            $this->image->insert($msgImage);
          } else {
            $replyData['images'] = [];
          }
        }

        return $this->respondWithData($replyData, 201);
      case 'PATCH':
        $id = $this->resolveArg('id');
        $data = $this->request->getParsedBody();
        if (empty($data)) return $this->respondWithError('无数据');
        $num = $this->reply->update($data, ['id' => $id]);
        return $this->respondWithData(['upNum' => $num], 201);
      case 'DELETE';
        $id = $this->resolveArg('id');
        $delNum = $this->reply->delete(['id' => $id]);
        if ($delNum > 0) {
          // 删除图片
          $images = $this->image->select('url', ['msgId' => $id, 'type' => 1]);
          if ($images) foreach ($images as $image) if (is_file($this->filepath . $image)) unlink($this->filepath . $image); //删除文件
          $delNum += $this->image->delete(['msgId' => $id, 'type' => 1]);
        }
        return $this->respondWithData(['delNum' => $delNum], 200);
      case 'GET':
        $id = intval($this->resolveArg('id'));
        if ($id === 0) return $this->respondWithError('禁止访问', 403);
        // 用户留言
        $message = $this->message->get('id,uid,description,status,addTime', ['id' => $id]);
        if ($message) {
          $message['images'] = $this->image->select('url', ['msgId' => $this->args['id'], 'type' => 0]);
          array_walk($message['images'], function (&$image) {
            $image = $this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost() . $image;
          });
          $user = $this->user->getUser($message['uid']);
          $message['user'] = [
            'nickname' => $user['nickname'],
            'headimgurl' => $user['headimgurl'],
            'name' => $user['name'],
            'tel' => $user['tel']
          ];

          $where = ['msgId' => $id];
          $where['ORDER'] = ["id" => "DESC"];

          $reply = $this->reply->select('id,department,uid,description,ctime', $where);
          foreach ($reply as &$item) {
            $item['images'] = $this->image->select('url', ['msgId' => $item['id'], 'type' => 1]);
            array_walk($item['images'], function (&$image) {
              $image = $this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost() . $image;
            });
          }
          return $this->respondWithData(['message' => $message, 'reply' => $reply]);
        } else {
          return $this->respondWithData();
        }
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }

  public function messageCount(Request $request, Response $response, array $args): Response
  {
    $this->request = $request;
    $this->response = $response;
    $this->args = $args;

    return $this->respondWithData(['count' => $this->message->count('id', ['status' => 0])]);
  }
}
