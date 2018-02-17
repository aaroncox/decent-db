<?php
namespace SteemDB\Controllers;

use SteemDB\Models\Block30d;

class IndexController extends ControllerBase
{
  public function indexAction()
  {
    return $this->response->redirect('/');
  }
  public function liveAction()
  {

  }

  public function homepageAction() {

    $this->view->props = $props = $this->peerplaysd->getProps();
    $this->view->blocks = Block30d::find([
      [],
      "skip" => $limit * ($page - 1),
      "sort" => ['_id' => -1],
      "limit" => 100
    ]);

    // var_dump($this->view->props); exit;
    // $this->view->totals = $totals = $this->util->distribution($props);
    // $count = 0;
    // foreach(array_column($current->transactions, 'operations') as $ops) {
    //   $count += count($ops);
    // }
    // $pipeline = [
    //   [
    //     '$sort' => ['_id' => -1]
    //   ],
    //   [
    //     '$limit' => 28800 * 1
    //   ],
    //   [
    //     '$unwind' => '$transactions'
    //   ],
    //   [
    //     '$group' => [
    //       '_id' => '24h',
    //       'tx' => ['$sum' => ['$size' => '$transactions.operations']]
    //     ]
    //   ]
    //   // [
    //   //   '$unwind' => '$transactions'
    //   // ],
    //   // [
    //   //   '$unwind' => '$transactions.operations'
    //   // ],
    //   // [
    //   //   '$project' => [
    //   //     '_ts' => '$_ts',
    //   //     'operation' => ['$arrayElemAt' => ['$transactions.operations', 0]],
    //   //     'data' => ['$arrayElemAt' => ['$transactions.operations', 1]],
    //   //   ]
    //   // ],
    //   // [
    //   //   '$group' => [
    //   //     '_id' => '$_id',
    //   //     'ts'  => ['$first' => '$_ts'],
    //   //     'opCount' => ['$sum' => 1],
    //   //     'opTypes' => ['$push' => '$operation'],
    //   //     'ops' => ['$push' => '$data'],
    //   //   ]
    //   // ],
    //   // [
    //   //   '$sort' => ['_id' => -1]
    //   // ]
    // ];
    // $options = [
    //   'allowDiskUse' => true,
    //   'cursor' => [
    //     'batchSize' => 0
    //   ]
    // ];
    // $results = Block30d::agg($pipeline, $options)->toArray();
    // $tx = 0;
    // foreach($results as $result) {
    //   $tx += $result['tx'];
    // }
    // // echo "<pre>"; var_dump($tx); print_r($results); exit;
    // $this->view->tx = $results[0]['tx'];
    // $this->view->tx_per_sec = round($tx / 86400, 3);


    // $this->view->blocks = ;
    // var_dump($this->view->blocks); exit;
  }

  public function show404Action() {

  }
}
