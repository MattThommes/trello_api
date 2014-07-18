var Trello = require("node-trello");
var t = new Trello("", "");

t.get("/1/lists/529cabd4391c4ddf3f0050ae/cards", function(err, data) {
  if (err) throw err;
	console.log("\n'Recent' cards:\n");
	var counter = 1;
  for (var i in data) {
//console.log(data[i].labels);
		var label_arr = [];
		for (var j in data[i].labels) {
			if (typeof(data[i].labels[j].name) != "undefined") {
				label_arr.push(data[i].labels[j].name);
			}
		}
		var label_str = label_arr.join(", ");
  	var str = counter + ". " + label_str + " [" + data[i].name + "](" + data[i].shortUrl + ")\n";
  	console.log(str);
  	counter++;
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