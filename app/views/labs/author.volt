{% extends 'layouts/default.volt' %}

{% block header %}

{% endblock %}

{% block content %}

<div class="ui vertical stripe segment">
  <div class="ui middle aligned stackable grid container">
    <h1 class="ui header">
      Author Rewards Leaderboard
      <div class="sub header">
        The top earning authors by date
      </div>
    </h1>
    <div class="row">
      <div class="column">
        <div class="ui top attached menu">
          <a href="/labs/author?date={{ date('Y-m-d', date - 86400)}}" class="item">
            <i class="left arrow icon"></i>
            <span class="mobile hidden">{{ date('Y-m-d', date - 86400)}}</span>
          </a>
          <div class="right menu">
            <?php if($date > time() - 86400): ?>
            <a class="disabled item">
              <span class="mobile hidden">{{ date('Y-m-d', date + 86400)}}</span>
              <i class="right arrow icon"></i>
            </a>
            <?php else: ?>
            <a href="/labs/author?date={{ date('Y-m-d', date + 86400)}}" class="item">
              <span class="mobile hidden">{{ date('Y-m-d', date + 86400)}}</span>
              <i class="right arrow icon"></i>
            </a>
            <?php endif ?>
          </div>
        </div>
        <div class="ui attached basic segment">
          <div class="ui small center aligned header">
            Daily Totals
          </div>
          <div class="ui divided grid">
            <div class="four column row">
              <div class="center aligned column">
                <div class="ui header">
                  {{ totals['steem'] }}
                  <div class="sub header">
                    STEEM
                  </div>
                </div>
              </div>
              <div class="center aligned column">
                <div class="ui header">
                  {{ totals['sbd'] }}
                  <div class="sub header">
                    SBD
                  </div>
                </div>
              </div>
              <div class="center aligned column">
                <div class="ui header">
                  {{ totals['sp'] }}
                  <div class="sub header">
                    Steem Power
                  </div>
                </div>
              </div>
              <div class="center aligned column">
                <div class="ui header">
                  <?php echo $this->largeNumber::format($totals['vest']); ?>
                  <div class="sub header">
                    VEST
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        <div class="ui bottom attached segment">
          <div class="ui header">
            Author Leaderboard for {{ date('Y-m-d', date) }}
            <div class="sub header">
              The accounts earning the highest author rewards by day.
            </div>
          </div>
          <table class="ui table">
            <thead>
              <tr>
                <th class="collapsing">#</th>
                <th>Account</th>
                <th class="collapsing">VESTS/SP</th>
                <th class="collapsing">STEEM</th>
                <th class="collapsing">SBD</th>
                <th class="collapsing">Posts</th>
              </tr>
            </thead>
            <tbody></tbody>
          {% for account in leaderboard %}
            <tr>
              <td>
                #{{ loop.index }}
              </td>
              <td>
                <a href="/@{{ account._id }}">
                  {{ account._id }}
                </a>
              </td>
              <td class="right aligned">
                <div class="ui <?php echo $this->largeNumber::color($account->vest)?> label" data-popup data-content="<?php echo number_format($account->vest, 3, ".", ",") ?> VESTS" data-variation="inverted" data-position="left center">
                  <?php echo $this->largeNumber::format($account->vest); ?>
                </div>
                <br>
                <small>
                  ~<?php echo $this->convert::vest2sp($account->vest, ""); ?> SP*
                </small>
              </td>
              <td>
                {{ account.steem }}
              </td>
              <td>
                {{ account.sbd }}
              </td>
              <td>
                {{ account.count }}
              </td>
            </tr>
          {% else %}
          <tr>
            <td colspan="10">
              <div class="ui message">
                No data for this date
              </div>
            </td>
          </tr>
          {% endfor %}
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

{% endblock %}

{% block scripts %}

{% endblock %}
