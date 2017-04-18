<h1>Revision 0.9, "Erected Echidna"</h1>

If you do not know how to code in php, it is reccomended you do not experiment with this code. This code will not throw your coins into the abyss, but it could spam your email.
*****************
This revision accounts exchange balances accurately, it has been moderately tested and there still may be some flaws, use at your own risk. It does not work for mintpal yet, I'll do that soon.

Essentially everything is the same as last one when it comes to configuring it, but this time, you get a lot more database entries. You only get emails upon an error!

In the database you will notice 4 New keys. algoSent, This is a record of all your txid's when sending coins to exchanges. algoTrading, this is a pending record for when a trade is submitted, the order id is recorded here. algoTraded, this is successfully traded orders with their exact balance record as well as timestamps and other goodness. The last key is exchBals, this is exactly the same as exchangeBalances, except it is 100% accurate... so I hope...

This script also converts LTC to BTC automatically for all exchanges.

To set this up as a contab and to be ran every 5 minutes for example, you need to type
```
crontab -e
*/5 * * * * php /PATH/TO/master.php
```
