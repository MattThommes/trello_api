var Trello = require("node-trello");
var t = new Trello("", "");

var list_id = "539cabd4301c4ddw3n0050ce";
var list_name = "";

// get list info.
t.get("/1/lists/" + list_id, function(err, data) {
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