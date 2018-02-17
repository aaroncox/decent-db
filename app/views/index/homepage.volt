{% extends 'layouts/homepage.volt' %}

{% block header %}

{% endblock %}

{% block content %}
<style>
.block-animation {
  background-color:red;
  animation: loadin 1s forwards;
  background-color:rgba(105, 205, 100, 1);
}
@keyframes loadin {
    from {background-color:rgba(105, 205, 100, 1);}
    to {background-color:rgba(105, 205, 100, 0);}
}
</style>

<div class="ui body container">
  <h1 class="ui header">
    decent-db.com
    <div class="sub header">
      Block explorer and database for the Decent blockchain.
    </div>
    <div class="ui hidden divider "></div>
  </h1>
  <div class="ui stackable grid">
    {#
    <div class="row">
      <div class="sixteen wide column">
        <!-- TradingView Widget BEGIN -->
        <script type="text/javascript" src="https://d33t3vvu2t2yu5.cloudfront.net/tv.js"></script>
        <script type="text/javascript">
        new TradingView.widget({
          "autosize": true,
          "symbol": "POLONIEX:STEEMBTC",
          "interval": "120",
          "timezone": "Etc/UTC",
          "theme": "White",
          "style": "1",
          "locale": "en",
          "toolbar_bg": "#f1f3f6",
          "enable_publishing": false,
          "hide_top_toolbar": true,
          "allow_symbol_change": true,
          "hideideas": true
        });
        </script>
        <!-- TradingView Widget END -->
      </div>
    </div>
    #}
    <div class="row">
      <div class="ten wide column">
        <div class="ui small dividing header">
          <a class="ui tiny blue basic button" href="/blocks" style="float:right">
            View more blocks
          </a>
          Recent Blockchain Activity
          <div class="sub header">
            Displaying most recent irreversible blocks.
          </div>
        </div>
        <div class="ui grid">
          <div class="two column row">
            <div class="column">
              <span class="ui horizontal blue basic label" data-props="head_block_number">
                {{  props['head_block_number'] }}
              </span>
              Current Height
            </div>
            <div class="column">
              <span class="ui horizontal orange basic label" data-props="reversible_blocks">
                {{ props['head_block_number'] - props['last_irreversible_block_num'] }}
              </span>
              Reversable blocks awaiting concensus
            </div>
          </div>
        </div>
        <table class="ui small table" id="blockchain-activity">
          <thead>
            <tr>
              <th class="collapsing">Height</th>
              <th class="six wide">Time</th>
              <th>Miner</th>
              <th class="collapsing">Transactions</th>
              <th class="collapsing">Operations</th>
            </tr>
          </thead>
          <tbody>
            {% for current in blocks %}
            <tr>
              <td>
                <a href="/block/{{ current._id }}">
                  {{ current._id }}
                </a>
              </td>
              <td>
                {{ current._ts.toDateTime().format("Y-m-d H:i:s") }} UTC
              </td>
              <td>
                <a href="/@{{ current.witness_name }}">
                  {{ current.witness_name }}
                </a>
              </td>
              <td>
                {{ current.transactions | length }}
              </td>
              <td>
                <?php
                $count = 0;
                foreach(array_column($current->transactions, 'operations') as $ops) {
                  $count += count($ops);
                } ?>
                {{ count }}
              </td>
            </tr>
            {% endfor %}
          </tbody>
        </table>
      </div>
      <div class="six wide centered column">
        <div class="ui small dividing header">
          Metrics
          <div class="sub header">
            Parameters, global properties and statistics
          </div>
        </div>
        {#
        <div class="ui horizontal stacked segments">
          <div class="ui center aligned segment">
            <div class="ui tiny statistic">
              <div class="value" data-props="steem_per_mvests">
                {{ props['steem_per_mvests'] }}
              </div>
              <div class="label">
                STEEM per MVest
              </div>
            </div>
          </div>
          <div class="ui center aligned segment">
            <div class="ui tiny statistic">
              <div class="value">
                <span data-state-feed="base">
                  <i class="notched circle loading icon"></i>
                </span>
              </div>
              <div class="label">
                per
                <span data-state-feed="quote">
                  <i class="notched circle loading icon"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
        <div class="ui divider"></div>
        <div class="ui small header">
          Network Performance
        </div>
        <table class="ui small definition table" id="state">
          <tbody>
            <tr>
              <td class="eight wide">Transactions per second (24h)</td>
              <td>
                {{ tx_per_sec }} tx/sec
              </td>
            </tr>
            <tr>
              <td>Transactions over 24h</td>
              <td>
                {{ tx }} transactions
              </td>
            </tr>
          </tbody>
        </table>
        <div class="ui small header">
          Consensus State
        </div>
        <table class="ui small definition table" id="state">
          <tbody>
            <tr>
              <td class="eight wide">Account Creation Fee</td>
              <td>
                <span data-state-witness-median="account_creation_fee">
                  <i class="notched circle loading icon"></i>
                </span>
              </td>
            </tr>
            <tr>
              <td>Maximum Block Size</td>
              <td>
                <span data-state-witness-median="maximum_block_size">
                  <i class="notched circle loading icon"></i>
                </span>
              </td>
            </tr>
            <tr>
              <td>SBD Interest Rate</td>
              <td>
                <span data-state-witness-median="sbd_interest_rate">
                  <i class="notched circle loading icon"></i>
                </span>
              </td>
            </tr>
          </tbody>
        </table>
        #}
        <div class="ui small header">
          Global Properties
        </div>
        <table class="ui small definition table" id="global_props">
          <tbody>
            {% for key, value in props %}
              {% if key not in ['id', 'steem_per_mvests', 'head_block_id', 'recent_slots_filled', 'head_block_number'] %}
                <tr>
                  <td class="eight wide">{{ key }}</td>
                  <td data-props="{{ key }}">{{ value }}</td>
                </tr>
              {% endif %}
            {% endfor %}
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>


{% endblock %}

{% block scripts %}
{% endblock %}
