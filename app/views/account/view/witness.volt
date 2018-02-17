<h3 class="ui header">
  Miner Voting
  <div class="sub header">
    Snapshot of blockchain information cached <?php echo $this->timeAgo::mongo($account->scanned); ?>
  </div>
</h3>
<div class="ui top attached tabular menu">
  {#<a class="active item" data-tab="history">History</a>#}
  <a class="active item" data-tab="received">Votes Received</a>
  <a class="item" data-tab="cast">Votes Cast</a>
</div>
<div class="ui bottom attached padded segment">
  <div class="ui active tab" data-tab="received">
    <div class="ui large header">
      Votes for {{ account.account['name'] }}
      <div class="sub header">
        {{ received | length }} total accounts
      </div>
    </div>
    <div class="ui divided relaxed list">
      {% for voter in received %}
      <div class="item">
        <a href="/@{{ voter['_id'] }}">
          {{ voter['_id'] }}
        </a>
      </div>
      {% else %}
      <div class="item">
        <p>This account has not received any miner votes.</p>
      </div>
      {% endfor %}
    </div>
  </div>
  <div class="ui tab" data-tab="cast">
    <div class="ui large header">
      {{ account.account['name'] }} votes for
      <div class="sub header">
        Witnesses that {{ account.account['name'] }} is voting for {{ cast | length }}.
      </div>
    </div>
    <div class="ui divided relaxed list">
      {% for vote in cast %}
      <div class="item">
        <a href="/@{{ vote['_id'] }}">
          {{ vote['_id'] }}
        </a>
      </div>
      {% else %}
      <div class="item">
        <div class="ui warning message">
          <p>This account has not voted for anyone as a miner.</p>
        </div>
      </div>
      {% endfor %}
    </div>
  </div>
</div>
