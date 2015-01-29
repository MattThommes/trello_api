<?php

	require "vendor/autoload.php";
	use MattThommes\Debug;
	use Trello\Client;
	use Trello\Manager;
	$debug = new Debug;

	include "db_connect.php";
	require_once("config.php");

	// to get your auth token.
	echo "<p><a href='https://trello.com/1/connect?key={$trello_key}&name=MyApp&response_type=token'>Get new token</a></p>";
		
	$client = new Client();
	$client->authenticate($trello_key, $trello_token, Client::AUTH_URL_CLIENT_ID);

	// all of your boards.
	//$boards = $client->members()->boards()->all($trello_username);
//$debug->dbg($boards);

	// set a specific board ID here (in case you just want to process one).
	$board_id = $board_medev;
	$params = array(
		"filter" => "all",
		"attachments" => true,
		"checklists" => "all",
		"actions" => "commentCard",
		"limit" => 1,
		"page" => 22, // not working yet
	);
	$cards = $client->boards()->cards()->all($board_id, $params);
$debug->dbg($cards);
	foreach ($cards as $card) {
//$debug->dbg($card);
		$query = "SELECT COUNT(*) AS count FROM trello_card WHERE board_id = '$board_id' AND card_id = '$card[id]'";
		$exists = $db_conn->query($query);
		$exists = $exists->fetch();
		if (!(int)$exists["count"]) {
			$fields = array(
				"board_id",
				"list_id",
				"card_id",
				"title",
				"description",
				"date_lastactivity",
				"closed",
			);
			$values = array(
				"'$board_id'",
				"'$card[idList]'",
				"'$card[id]'",
				"'" . str_replace("'", "\'", $card["name"]) . "'",
				"'" . str_replace("'", "\'", $card["desc"]) . "'",
				"'" . date("Y-m-d H:i:s", strtotime($card["dateLastActivity"])) . "'",
				((int)$card["closed"]) ? 1 : 0,
			);
			$ins = $db_conn->query("INSERT INTO trello_card (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ")");
			$card_id = $ins->insertId();
			if ($card["checklists"]) {
				foreach ($card["checklists"] as $checklist) {
					foreach ($checklist["checkItems"] as $item) {
						$query = "SELECT COUNT(*) AS count FROM trello_card_checklist_item WHERE card_id = '$card_id' AND checklist_id = '$checklist[id]' AND item_id = '$item[id]'";
						$exists = $db_conn->query($query);
						$exists = $exists->fetch();
						if (!(int)$exists["count"]) {
							$fields = array(
								"card_id",
								"checklist_id",
								"checklist_title",
								"item_id",
								"state",
								"title",
							);
							$values = array(
								$card_id,
								"'$checklist[id]'",
								"'" . str_replace("'", "\'", $checklist["name"]) . "'",
								"'$item[id]'",
								"'$item[state]'",
								"'" . str_replace("'", "\'", $item["name"]) . "'",
							);
							$ins = $db_conn->query("INSERT INTO trello_card_checklist_item (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ")");
							$checklist_item_id = $ins->insertId();
						}
					}
				}
			}
			if ($card["attachments"]) {
				foreach ($card["attachments"] as $attachment) {
					$query = "SELECT COUNT(*) AS count FROM trello_card_attachment WHERE card_id = '$card_id' AND attachment_id = '$attachment[id]'";
					$exists = $db_conn->query($query);
					$exists = $exists->fetch();
					if (!(int)$exists["count"]) {
						$fields = array(
							"card_id",
							"attachment_id",
							"date_create",
							"title",
							"url",
						);
						$values = array(
							$card_id,
							"'$attachment[id]'",
							"'" . date("Y-m-d H:i:s", strtotime($attachment["date"])) . "'",
							"'" . str_replace("'", "\'", $attachment["name"]) . "'",
							"'$attachment[url]'",
						);
					}
				}
			}
			if ($card["actions"]) {
				// comments.
				foreach ($card["actions"] as $action) {
					$query = "SELECT COUNT(*) AS count FROM trello_card_comment WHERE card_id = '$card_id' AND action_id = '$action[id]'";
					$exists = $db_conn->query($query);
					$exists = $exists->fetch();
					if (!(int)$exists["count"]) {
						$fields = array(
							"card_id",
							"action_id",
							"date_create",
							"comment",
						);
						$values = array(
							$card_id,
							"'$action[id]'",
							"'" . date("Y-m-d H:i:s", strtotime($action["date"])) . "'",
							"'" . str_replace("'", "\'", $action["data"]["text"]) . "'",
						);
						$ins = $db_conn->query("INSERT INTO trello_card_comment (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ")");
						$comment_id = $ins->insertId();
					}
				}
			}
		}
break; // just do one for now.
	}

?>