<?php
namespace SteemDB\Controllers;

use MongoDB\BSON\Regex;
use MongoDB\BSON\UTCDateTime;

use SteemDB\Models\Account;
use SteemDB\Models\AccountHistory;
use SteemDB\Models\AuthorReward;
use SteemDB\Models\Block30d;
use SteemDB\Models\Comment;
use SteemDB\Models\CurationReward;
use SteemDB\Models\Follow;
use SteemDB\Models\Reblog;
use SteemDB\Models\Vote;
use SteemDB\Models\Statistics;
use SteemDB\Models\Ops;
use SteemDB\Models\Pow;
use SteemDB\Models\Transfer;
use SteemDB\Models\VestingDeposit;
use SteemDB\Models\VestingWithdraw;
use SteemDB\Models\WitnessMiss;
use SteemDB\Models\WitnessHistory;
use SteemDB\Models\WitnessVote;

class AccountController extends ControllerBase
{

  private function getAccount()
  {
    $account = strtolower($this->dispatcher->getParam("account"));
    $cacheKey = 'account-'.$account;
    // Load account from the database
    $this->view->account = Account::findFirst(array(
      array(
        '_id' => $account
      )
    ));
    // Check the cache for this account from the blockchain
    $cached = $this->memcached->get($cacheKey);
    // No cache, let's load
    // if($cached === null) {
    // var_dump($this->view->account); exit;
    // var_dump($this->view->account->account['id']); exit;
      $this->view->live = $this->peerplaysd->getAccount($this->view->account->account['id']);
      // var_dump($this->view->live); exit;
      $this->memcached->save($cacheKey, $this->view->live, 60);
    // } else {
    //   // Use cache
    //   $this->view->live = $cached;
    // }
    return $account;
  }

  public function viewAction()
  {
    $account = $this->getAccount();
    // var_dump($account, $this->view->account); exit;
    $query = [
      '$or' => [
        ['$and' => [
          ['from' => ['$exists' => true]],
          ['from' => $this->view->account->account['id']]
        ]],
        ['$and' => [
          ['to' => ['$exists' => true]],
          ['to' => $this->view->account->account['id']]
        ]],
        ['$and' => [
          ['account' => ['$exists' => true]],
          ['account' => $this->view->account->account['id']]
        ]],
        ['$and' => [
          ['name' => ['$exists' => true]],
          ['name' => $this->view->account->account['name']]
        ]],
      ]
    ];
    $ops = Ops::find([
      $query,
      'sort' => ['_ts' => -1],
      'limit' => 100
    ]);
    $this->view->ops = $ops;
    // $this->view->props = $this->steemd->getProps();
    // try {
      // $this->view->activity = array_reverse($this->peerplaysd->getAccountHistory($account));
    // } catch (Exception $e) {
    //   $this->view->activity = false;
    // }
    // var_dump($this->view->activity); exit;
    // $this->view->chart = true;
    $this->view->pick("account/view");
  }

  public function propsAction()
  {
    $account = $this->getAccount();
    $this->view->history = WitnessHistory::find(array(
      ['owner' => $account],
      'sort' => array('created' => -1),
      'limit' => 100
    ));
    $this->view->pick("account/view");
  }

  public function postsAction()
  {
    $account = $this->getAccount();
    $this->view->comments = Comment::find(array(
      array(
        'author' => $account,
        'depth' => 0,
      ),
      'sort' => array('created' => -1),
      'limit' => 100
    ));
    $this->view->total_payouts = 0;
    $this->view->total_pending = 0;
    foreach($this->view->comments as $comment) {
      if($comment->total_pending_payout_value) {
        $this->view->total_pending += $comment->total_pending_payout_value;
      }
      if($comment->total_payout_value) {
        $this->view->total_payouts += $comment->total_payout_value;
      }
    }
    $this->view->chart = true;
    $this->view->pick("account/view");
  }

  public function votesAction()
  {
    $account = $this->getAccount();
    $this->view->filter = $this->request->get('type');
    switch($this->view->filter) {
      case "incoming":
        $query = array(
          'author' => $account,
        );
        break;
      default:
        $query = array(
          'voter' => $account,
        );
        break;
    }
    $this->view->votes = Vote::find(array(
      $query,
      'sort' => array('_ts' => -1),
      'limit' => 200
    ));
    $this->view->chart = true;
    $this->view->pick("account/view");
  }

  public function repliesAction()
  {
    $account = $this->getAccount();
    $this->view->replies = Comment::find(array(
      array(
        'author' => $account,
        'depth' => ['$gt' => 0],
      ),
      'sort' => array('created' => -1),
      'limit' => 100
    ));
    $this->view->pick("account/view");
  }

  public function followersAction()
  {
    $account = $this->getAccount();
    $this->view->followers = Follow::find([
      ["following" => $account],
      "sort" => ['_ts' => -1]
    ]);
    $this->view->chart = true;
    $this->view->pick("account/view");
  }

  public function followersWhalesAction()
  {
    $account = $this->getAccount();
    $this->view->followers = Account::find([
      ['name' => ['$in' => $this->view->account->followers]],
      'sort' => ['vesting_shares' => -1],
    ]);
    $this->view->pick("account/view");
  }

  public function followingAction()
  {
    $account = $this->getAccount();
    $this->view->followers = Follow::find([
      ["follower" => $account],
      "sort" => ['_ts' => -1]
    ]);
    $this->view->pick("account/view");
  }

  public function witnessAction()
  {
    $account = $this->getAccount();
    $this->view->received = Account::agg([
      ['$match' => [
        'votes.id' => $this->view->account->miner_id
      ]],
    ])->toArray();
    $this->view->cast = Account::agg([
      ['$match' => [
        'miner_id' => ['$in' => array_column($this->view->account->votes, 'id')]
      ]],
    ])->toArray();
    $this->view->chart = true;
    $this->view->pick("account/view");
  }

  public function blocksAction()
  {
    $account = $this->getAccount();
    $this->view->mining = Block30d::find(array(
      array(
        'witness' => $account,
      ),
      'sort' => array('_ts' => -1),
      'limit' => 100
    ));
    $this->view->chart = true;
    $this->view->pick("account/view");
  }

  public function missedAction()
  {
    $account = $this->getAccount();
    $this->view->mining = WitnessMiss::find(array(
      array(
        'witness' => $account,
      ),
      'sort' => array('date' => -1),
      'limit' => 100
    ));
    $this->view->pick("account/view");
  }

  public function reblogsAction()
  {
    $account = $this->getAccount();
    $page = $this->view->page = (int) $this->request->get('page') ?: 1;
    $this->view->reblogs = Reblog::find(array(
      array(
        'account' => $account,
      ),
      'sort' => array('_ts' => -1),
      'limit' => 100,
      'skip' => 100 * ($page - 1)
    ));
    $this->view->pick("account/view");
  }

  public function rebloggedAction()
  {
    $account = $this->getAccount();
    $page = $this->view->page = (int) $this->request->get('page') ?: 1;
    $this->view->reblogs = Reblog::find(array(
      array(
        'author' => $account,
      ),
      'sort' => array('_ts' => -1),
      'limit' => 100,
      'skip' => 100 * ($page - 1)
    ));
    $this->view->pick("account/view");
  }

  public function proxiedAction()
  {
    $account = $this->getAccount();
    $this->view->proxied = Account::find(array(
      array('proxy' => $account)
    ));
    $this->view->pick("account/view");
  }

  public function curationAction()
  {
    $account = $this->getAccount();
    $this->view->curation = CurationReward::agg(array(
      ['$match' => [
        'curator' => $account,
        ]],
      ['$group' => [
        '_id' => [
          'doy' => ['$dayOfYear' => '$_ts'],
          'year' => ['$year' => '$_ts'],
          'month' => ['$month' => '$_ts'],
          'week' => ['$week' => '$_ts'],
          'day' => ['$dayOfMonth' => '$_ts']
        ],
        '_ts' => ['$first' => '$_ts'],
        'reward' => ['$sum' => '$reward'],
        'votes' => ['$sum' => 1],
        ]],
      ['$sort' => [
        '_ts' => -1,
        ]],
    ));
    $this->view->stats = CurationReward::agg([
      [
        '$match' => [
          'curator' => $account,
          '_ts' => [
            '$gte' => new UTCDateTime(strtotime("-30 days") * 1000),
          ],
        ]
      ],
      [
        '$group' => [
          '_id' => '$curator',
          'day' => ['$sum' => ['$cond' => [
            [
              '$gte' => [
                '$_ts',
                new UTCDateTime(strtotime("-1 days") * 1000)
              ]
            ],
            '$reward',
            0
          ]]],
          'week' => ['$sum' => ['$cond' => [
            [
              '$gte' => [
                '$_ts',
                new UTCDateTime(strtotime("-7 days") * 1000)
              ]
            ],
            '$reward',
            0
          ]]],
          'month' => ['$sum' => ['$cond' => [
            [
              '$gte' => [
                '$_ts',
                new UTCDateTime(strtotime("-30 days") * 1000)
              ]
            ],
            '$reward',
            0
          ]]],
        ]
      ]
    ])->toArray();
    $this->view->chart = true;
    $this->view->pick("account/view");
  }

  public function curationDateAction() {
    $account = $this->getAccount();
    $this->view->date = $this->dispatcher->getParam("date");
    $this->view->curation = CurationReward::find(array(
      array(
        'curator' => $account,
        '_ts' => [
          '$gte' => new UTCDateTime(strtotime($this->view->date) * 1000),
          '$lte' => new UTCDateTime((strtotime($this->view->date) + 86400) * 1000),
        ]
      ),
      'sort' => array('_ts' => -1),
      'skip' => $limit * ($page - 1),
      'limit' => $limit,
    ));
    $this->view->pick("account/view");
  }

  public function authoringAction()
  {
    $account = $this->getAccount();
    $this->view->filter = $filter = $this->request->get('filter', 'string') ?: null;
    // Define our default match against rewards
    $match = [
      'author' => $account,
    ];
    switch($filter) {
      case "comments":
        $match['permlink'] = ['$regex' => new Regex('^re-', 'i')];
        break;
      case "posts":
        $match['permlink'] = ['$regex' => new Regex('^(?!re-)', 'i')];
        break;
    }
    $this->view->authoring = AuthorReward::agg(array(
      ['$match' => $match],
      ['$group' => [
        '_id' => [
          'doy' => ['$dayOfYear' => '$_ts'],
          'year' => ['$year' => '$_ts'],
          'month' => ['$month' => '$_ts'],
          'week' => ['$week' => '$_ts'],
          'day' => ['$dayOfMonth' => '$_ts']
        ],
        '_ts' => ['$first' => '$_ts'],
        'sbd_payout' => ['$sum' => '$sbd_payout'],
        'steem_payout' => ['$sum' => '$steem_payout'],
        'vesting_payout' => ['$sum' => '$vesting_payout'],
        'posts' => ['$sum' => 1],
      ]],
      ['$sort' => [
        '_ts' => -1,
      ]],
    ));
    $this->view->stats = AuthorReward::agg([
      [
        '$match' => [
          'author' => $account,
          '_ts' => [
            '$gte' => new UTCDateTime(strtotime("-30 days") * 1000),
          ],
        ]
      ],
      [
        '$group' => [
          '_id' => '$author',
          'day' => ['$sum' => ['$cond' => [
            [
              '$gte' => [
                '$_ts',
                new UTCDateTime(strtotime("-1 days") * 1000)
              ]
            ],
            '$vesting_payout',
            0
          ]]],
          'week' => ['$sum' => ['$cond' => [
            [
              '$gte' => [
                '$_ts',
                new UTCDateTime(strtotime("-7 days") * 1000)
              ]
            ],
            '$vesting_payout',
            0
          ]]],
          'month' => ['$sum' => ['$cond' => [
            [
              '$gte' => [
                '$_ts',
                new UTCDateTime(strtotime("-30 days") * 1000)
              ]
            ],
            '$vesting_payout',
            0
          ]]],
        ]
      ]
    ])->toArray();
    $this->view->chart = true;
    $this->view->pick("account/view");
  }


  public function authoringDateAction()
  {
    $account = $this->getAccount();
    $this->view->page = $page = (int) $this->request->get("page") ?: 1;
    $this->view->date = $this->dispatcher->getParam("date");
    $limit = 50;
    $this->view->filter = $filter = $this->request->get('filter', 'string') ?: null;
    // Define our default match against rewards
    $match = [
      'author' => $account,
      '_ts' => [
        '$gte' => new UTCDateTime(strtotime($this->view->date) * 1000),
        '$lte' => new UTCDateTime((strtotime($this->view->date) + 86400) * 1000),
      ]
    ];
    switch($filter) {
      case "comments":
        $match['permlink'] = ['$regex' => new Regex('^re-', 'i')];
        break;
      case "posts":
        $match['permlink'] = ['$regex' => new Regex('^(?!re-)', 'i')];
        break;
    }
    $this->view->authoring = AuthorReward::find(array(
      $match,
      'sort' => array('_ts' => -1),
      'skip' => $limit * ($page - 1),
      'limit' => $limit,
    ));
    $this->view->pages = ceil(AuthorReward::count(array(
      array('author' => $account)
    )) / $limit);
    $this->view->pick("account/view");
  }

  public function powerupAction()
  {
    $account = $this->getAccount();
    $this->view->powerup = VestingDeposit::find(array(
      array('to' => $account),
      'sort' => array('_ts' => -1)
    ));
    $this->view->chart = true;
    $this->view->pick("account/view");
  }

  public function powerdownAction()
  {
    $account = $this->getAccount();
    $this->view->powerdown = VestingWithdraw::find(array(
      array('from_account' => $account),
      'sort' => array('_ts' => -1)
    ));
    $this->view->chart = true;
    $this->view->pick("account/view");
  }

  public function transfersAction()
  {
    $account = $this->getAccount();
    $this->view->page = $page = (int) $this->request->get("page") ?: 1;
    $limit = 50;
    $this->view->transfers = Transfer::find(array(
      array(
        '$or' => array(
          array('from' => $account),
          array('to' => $account),
        )
      ),
      'sort' => array('_ts' => -1),
      'skip' => $limit * ($page - 1),
      'limit' => $limit,
    ));
    $this->view->pages = ceil(Transfer::count(array(
      array(
        '$or' => array(
          array('from' => $account),
          array('to' => $account),
        )
      ),
    )) / $limit);
    $this->view->chart = true;
    $this->view->pick("account/view");
  }

  public function dataAction()
  {
    $account = $this->getAccount();
    $this->view->pick("account/view");
  }
}
