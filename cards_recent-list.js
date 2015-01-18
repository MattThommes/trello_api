var Trello = require("node-trello");
var t = new Trello("APP-KEY", "TOKEN");

var list_id = "54b99153725fa81a31e95b44";
var list_name = "";

// get user info.
/*t.get("/1/members/me", function(err, data) {
  if (err) throw err;
  console.log(data);
});*/

// get list info.
t.get("/1/lists/" + list_id, function(err, data) {
	if (err) throw err;
	list_name = data.name;
});

// get card info.
t.get("/1/lists/" + list_id + "/cards", function(err, data) {
	if (err) throw err;
	console.log("\n'" + list_name + "' cards:\n");
	var counter = 1;
	for (var i in data) {
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