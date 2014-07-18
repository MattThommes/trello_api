var Trello = require("node-trello");
var t = new Trello("", "");

// Cards for "Recent" list under Development board.
// Example:
/*

{
	id: '533b2b6806818efc17c516c0',
	checkItemStates: [],
	closed: false,
	dateLastActivity: '2014-07-16T15:15:07.539Z',
	desc: '[Email thread](https://mail.google.com/mail/u/1/#inbox/1451ec7e78dd89cf)\n\nProduct/s ordered\nDate of product/s ordered\nAverage order total \n**Date of first purchase.**\n**Date of last visit.**\nNumber of time they have visited site.\n**Number of lifetime orders.**\n**Location of customer Country and State**\nRMA Request/s\nConversion rate. Visits against purchases.\nAbandon cart products\nAbandon cart Date/s\nAbandon cart Total/s\nProducts on wish list',
	descData: { emoji: {} },
	idBoard: '520d05d3ed7e17c446002468',
	idList: '529cabd4391c4ddf3f0050ae',
	idMembersVoted: [],
	idShort: 289,
	idAttachmentCover: '',
	manualCoverAttachment: true,
	name: 'See if you can add these fields to be imported from BigCommerce (also check if Zapier does it already)',
	pos: 851968,
	shortLink: 'Yx7lC8x4',
	badges: 
	 { votes: 0,
		 viewingMemberVoted: false,
		 subscribed: false,
		 fogbugz: '',
		 checkItems: 0,
		 checkItemsChecked: 0,
		 comments: 7,
		 attachments: 1,
		 description: true,
		 due: null },
	due: null,
	idChecklists: [],
	idMembers: [],
	labels: [ [Object] ],
	shortUrl: 'https://trello.com/c/Yx7lC8x4',
	subscribed: false,
	url: 'https://trello.com/c/Yx7lC8x4/289-see-if-you-can-add-these-fields-to-be-imported-from-bigcommerce-also-check-if-zapier-does-it-already'
}

*/

t.get("/1/lists/529cabd4391c4ddf3f0050ae/cards", function(err, data) {
  if (err) throw err;
	console.log("\n'Recent' cards:\n");
  for (var i in data) {
//console.log(data[i].labels);
		var label_arr = [];
		for (var j in data[i].labels) {
			if (typeof(data[i].labels[j].name) != "undefined") {
				label_arr.push(data[i].labels[j].name);
			}
		}
		var label_str = label_arr.join(", ");
  	var str = "* " + label_str + " [" + data[i].name + "](" + data[i].shortUrl + ")\n";
  	console.log(str);
  }
});

/*
t.get("/1/members/me", function(err, data) {
  if (err) throw err;
  console.log(data);
});

// URL arguments are passed in as an object.
t.get("/1/members/me", { cards: "open" }, function(err, data) {
  if (err) throw err;
  console.log(data);
});
*/