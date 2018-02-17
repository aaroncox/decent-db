{#<div class="ui three small secondary statistics">
  <div class="statistic">
    <div class="value">
      {{ account.followers | length }}
    </div>
    <div class="label">
      Followers
    </div>
  </div>
  <div class="statistic">
    <div class="value">
      {{ account.post_count }}
    </div>
    <div class="label">
      Posts
    </div>
  </div>
  <div class="statistic">
    <div class="value">
      {{ account.following | length }}
    </div>
    <div class="label">
      Following
    </div>
  </div>
</div>#}
<h3 class="ui header">
  Recent History
  <div class="sub header">
    Recent operations by @{{ account.account.name }}.
  </div>
</h3>

<table class="ui stackable definition table">
  <thead></thead>
  <tbody>
  {% for item in ops %}
  <tr>
    <td class="three wide">
      <div class="ui small header">
        <div class="sub header">
          <br><a href="/block/<?php echo $item->block ?>"><small style="color: #bbb">Block #<?php echo $item->block ?></small></a>
        </div>
      </div>
    </td>
    <td>
      {% include "_elements/definition_table" with ['data': item] %}
    </td>
  </tr>
  {% else %}
  <tr>
    <td>
      No operations to display.
    </td>
  </tr>
  {% endfor %}
  </tbody>
</table>
