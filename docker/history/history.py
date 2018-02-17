from datetime import datetime, timedelta
from decent import Decent
from pymongo import MongoClient
from pprint import pprint
import collections
import time
import sys
import os

from apscheduler.schedulers.background import BackgroundScheduler

dct = Decent(node=os.environ['steemnode'])
rpc = dct.rpc

mongo = MongoClient("mongodb://mongo")
db = mongo.decentdb

# mvest_per_account = {}

# def load_accounts():
#     pprint("[DCT] - Loading mvest per account")
#     for account in db.account.find():
#         if "name" in account.keys():
#             mvest_per_account.update({account['name']: account['vesting_shares']})

def update_props_history():
    pprint("[DCT] - Update Global Properties")

    props = rpc.get_dynamic_global_properties()
    for key in ['recently_missed_count', 'miner_budget_from_fees']:
        pprint(key)
        props[key] = float(props[key])
    for key in ['time', 'next_maintenance_time', 'last_budget_time']:
        pprint(key)
        props[key] = datetime.strptime(props[key], "%Y-%m-%dT%H:%M:%S")
    db.props_history.insert(props)

def update_history():

    # Load all accounts
    users = rpc.lookup_accounts(-1, 1000)
    more = True
    while more:
        newUsers = rpc.lookup_accounts(users[-1][0], 1000)
        if len(newUsers) < 1000:
            more = False
        users = users + newUsers

    # Set dates
    now = datetime.now().date()
    today = datetime.combine(now, datetime.min.time())

    pprint("[DCT] - Update History (" + str(len(users)) + " accounts)")
    # Snapshot User Count
    db.statistics.update({
      'key': 'users',
      'date': today,
    }, {
      'key': 'users',
      'date': today,
      'value': len(users)
    }, upsert=True)

    assets = {}
    listassets = rpc.list_assets('', 100)
    for asset in listassets:
        assets.update({
            asset['symbol']: asset['id']
        })
    assetids = list(assets.values())


    # Update history on accounts
    for user in users:

        accountname, accountid = user
        # if accountname != 'williamhill1934':
        #     continue

        # Load State
        state = rpc.get_full_accounts([accountid], False)[0][1]
        doc = state.copy()
        # Get Account Data
        account = collections.OrderedDict(sorted(doc['account'].items()))
        doc['scanned'] = datetime.now()
        miner = rpc.lookup_miner_accounts(accountname, 1)
        if len(miner) > 0 and miner[0][0] == accountname:
            doc.update({'miner_id': miner[0][1]})
        doc.update({
            'account': account
        })
        total = 0
        for balance in doc['balances']:
            for symbol in assets:
                if 'asset_type' in balance and assets[symbol] == balance['asset_type']:
                    total = total + int(balance['balance'])
                    balance.update({
                        'symbol': symbol,
                        'balance': int(balance['balance'])
                    })
        for balance in doc['vesting_balances']:
            total = total + int(balance['balance']['amount'])
            balance.update({
                'balance': {
                    'amount': int(balance['balance']['amount']),
                    'asset_id': balance['balance']['asset_id']
                }
            })
        doc['account'].update({'total_balance': total})
        # if accountname == 'williamhill1934':
        #     pprint(doc)
        #     sys.stdout.flush()
        for vote in doc['votes']:
            if 'witness_account' in vote:
                search = vote['witness_account']
                result = [e for e in users if e[1] == search]
                if len(result):
                    vote.update({
                        'name': result[0][0]
                    })
        # Save current doc of account
        db.account.update({'_id': accountname}, doc, upsert=True)
    #     # Create our Snapshot dict
    #     wanted_keys = ['name', 'proxy_witness', 'activity_shares', 'average_bandwidth', 'average_market_bandwidth', 'savings_balance', 'balance', 'comment_count', 'curation_rewards', 'lifetime_bandwidth', 'lifetime_vote_count', 'next_vesting_withdrawal', 'reputation', 'post_bandwidth', 'post_count', 'posting_rewards', 'sbd_balance', 'savings_sbd_balance', 'sbd_last_interest_payment', 'sbd_seconds', 'sbd_seconds_last_update', 'to_withdraw', 'vesting_balance', 'vesting_shares', 'vesting_withdraw_rate', 'voting_power', 'withdraw_routes', 'withdrawn', 'witnesses_voted_for']
    #     snapshot = dict((k, account[k]) for k in wanted_keys if k in account)
    #     snapshot.update({
    #       'account': user,
    #       'date': today,
    #       'followers': len(account['followers']),
    #       'following': len(account['following']),
    #     })
    #     # Save Snapshot in Database
    #     db.account_history.update({
    #       'account': user,
    #       'date': today
    #     }, snapshot, upsert=True)

def update_supply():
    pprint("updating supply")
    now = datetime.now().date()
    today = datetime.combine(now, datetime.min.time())
    assets = dct.rpc.get_asset("1.3.0")
    objects = dct.rpc.get_objects(['2.3.0'])
    # pprint(assets)
    tokens = int(objects[0]['current_supply'])
    total = int(assets['options']['max_supply'])
    vests = total - tokens

    supply = {
        '_id': today,
        'dct': tokens,
        'vests': vests,
        'total': total
    }
    db.supply_history.update({'_id': today}, supply, upsert=True)

if __name__ == '__main__':
    # Load all account data into memory
    # load_accounts()
    # Start job immediately
    update_supply()
    update_props_history()
    update_history()
    # Schedule it to run every 6 hours
    scheduler = BackgroundScheduler()
    scheduler.add_job(update_supply, 'interval', minutes=240, id='update_supply')
    scheduler.add_job(update_history, 'interval', minutes=30, id='update_history')
    scheduler.add_job(update_props_history, 'interval', minutes=60, id='update_props_history')
    scheduler.start()
    # Loop
    try:
        while True:
            time.sleep(2)
    except (KeyboardInterrupt, SystemExit):
        scheduler.shutdown()
