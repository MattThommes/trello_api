# Trello API tests
==

Playing around with the Trello API.

### `cards_recent-list.js`

I wanted a quick way to output all cards in a particular Trello list. The Trello interface is great, but once you have a lot of cards in a particular list, it's hard to visualize them all.

This script uses Node.js to fetch all cards from a certain Trello board & list. So once you have the list ID, you can use that to generate all cards in that list.

Example output:

	'LIST NAME' cards:

	1. Label Name [Card Name here](https://trello.com/c/xxxxxx)

	2. Another Label Name [Card 2 Name here](https://trello.com/c/xxxxxx)

You can then copy/paste this output into a Markdown editor to view the HTML.