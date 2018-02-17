{% extends 'layouts/default.volt' %}

{% block content %}
<div class="ui vertical stripe segment">
  <div class="ui top aligned stackable grid container">
    <div class="row">
      <div class="sixteen wide column">
        <div class="ui huge header">
          Miners
          <div class="sub header">
            DPOS elected miners and relevant historical information.
          </div>
        </div>
        <div style="overflow-x:auto;">
          <div class="ui top attached tabular menu">
            <a class="active item" href="/witnesses">Miners</a>
            <!--
            <a class="item" href="/witness/history">History</a>
            <a class="item" href="/witness/misses">Misses</a>
            -->
          </div>
          <div class="ui bottom attached segment">
            <div class="ui active tab">
              <table class="ui small unstackable table">
                <thead>
                  <tr>
                    <th class="right aligned">Rank</th>
                    <th>Miner</th>
                    <th>Votes</th>
                    <th class="center aligned">
                      Misses
                    </th>
                    <th>Last Block</th>
                    <!--
                    <th>Price Feed</th>
                    <th>
                      Reg Fee<br>
                      APR<br>
                      Block Size
                    </th>
                    <th>Version</th>
                    <th>VESTS</th>
                    -->
                  </tr>
                </thead>
                <tbody>
                  {% for witness in witnesses %}
                    <tr class="{{ witness.row_status }}">
                      <td class="right aligned collapsing">
                        <strong>{{ loop.index }}</strong>
                      </td>
                      <td>
                        <div class="ui header">
                          <a href="/@{{ witness._id }}">
                            {{ witness._id }}
                          </a>
                          <div class="sub header">
                            <a href="{{ witness.url }}">
                              witness url
                            </a>
                          </div>
                        </div>
                      </td>
                      <td class="collapsing">
                        <div class="ui header">
                          <?php echo $this->largeNumber::format($witness->total_votes); ?>
                        </div>
                      </td>
                      <td class="center aligned">
                        <a href="/@{{ witness._id }}/missed" class="ui small header">
                          {% if witness.invalid_signing_key %}
                          <i class="warning sign icon" data-popup data-title="Witness Disabled" data-content="This witness does not have a signing key either at the owners request or because too many blocks have been missed."></i>
                          {% endif %}
                          <div class="content">
                            {% if witness.total_missed > 0 %}
                              {{ witness.total_missed }}
                            {% else %}
                              0
                            {% endif %}
                          </div>
                        </a>
                      </td>
                      <td>
                        {{ witness.last_confirmed_block_num }}
                      </td>
                      <!--
                      <td>
                        <div class="ui header">
                          {% if witness.sbd_exchange_rate.base === "0.000 STEEM" or witness.last_sbd_exchange_update_late %}<i class="warning sign icon" data-popup data-title="Outdated Price Feed" data-content="This witness has not submitted a price feed update in over a week."></i>{% endif %}
                          <div class="content">
                            {{ witness.sbd_exchange_rate.base }}
                            {% if witness.sbd_exchange_rate.quote != "1.000 STEEM" %}
                            (<?php echo round((1 - 1/explode(" ", $witness->sbd_exchange_rate['quote'])[0]) * 100, 1) ?>%)
                            {% endif %}
                            <div class="sub header">
                              {{ witness.sbd_exchange_rate.quote }}<br>
                              {% if "" ~ witness.last_sbd_exchange_update > 0 %}
                                <?php echo $this->timeAgo::mongo($witness->last_sbd_exchange_update); ?>
                              {% else %}
                                Never
                              {% endif %}
                            </div>
                          </div>
                        </div>
                      </td>
                      <td>
                        {{ witness.props.account_creation_fee }}
                        <br>
                        {{ witness.props.sbd_interest_rate / 100 }}<small>%</small> APR
                        <br>
                        {{ witness.props.maximum_block_size }}
                      </td>
                      <td>
                        {{ witness.running_version }}
                      </td>
                      <td>
                        {{ partial("_elements/vesting_shares", ['current': witness.account[0]]) }}
                      </td>
                      -->
                    </tr>
                  {% endfor %}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
{% endblock %}
