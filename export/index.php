<?php

	require "vendor/autoload.php";
	use MattThommes\Debug;
	use Trello\Client;
	use Trello\Manager;
	$debug = new Debug;

	include "db_connect.php";
	require_once("config.php");

	// to get your auth token.
	echo "<p><a href='https://trello.com/1/connect?key={$trello_key}&name=MyApp&response_type=token' target='_blank'>Get new auth token</a></p>";
	echo "<p><a href='index.php?start'>Start!</a></p>";
	echo "<p><a href='index.php?reset'>Reset everything</a> (this clears out the DB tables!)</p>";

	if (isset($_GET["reset"])) {
		$queries = array(
			"TRUNCATE TABLE trello_board",
			"TRUNCATE TABLE trello_board_list",
			"TRUNCATE TABLE trello_card",
			"TRUNCATE TABLE trello_card_attachment",
			"TRUNCATE TABLE trello_card_checklist_item",
			"TRUNCATE TABLE trello_card_comment",
		);
		foreach ($queries as $q) {
			$db_conn->query($q);
		}
	}

	/*
	 * Check if a certain row exists in the local database.
	 * You can either get a yes/no result (if it exists or not), or have an actual value returned also (if exists).
	 */
	function exists_local($table, $where, $select = null) {
		if (!$select) {
			$select_fields = "COUNT(*) AS c";
		} else {
			$select_fields = "`$return`";
		}
		$query = "SELECT $select_fields FROM `$table` WHERE $where";
		$exists = $GLOBALS["db_conn"]->query($query);
		$exists = $exists->fetch();
		if ($select) {
			return $exists[$select];
		} else {
			return (int)$exists["c"];
		}
	}

	function sql_update_set($fields) {
		$update_set = array();
		while ($i = 0; $i < count($fields); $i++)
			$update_set[] = "$fields[$i] = $values[$i]";
		}
		return $update_set;
	}

	if (isset($_GET["start"])) {

		$client = new Client();
		$client->authenticate($trello_key, $trello_token, Client::AUTH_URL_CLIENT_ID);

		// all of your boards.
		$boards = $client->members()->boards()->all($trello_username);
//$debug->dbg($boards);

		foreach ($boards as $board) {

			// Only process active & private boards for now, AND those in the pre-defined whitelist.
			if (in_array($board["id"], $board_whitelist) && !$board["closed"] && $board["prefs"]["permissionLevel"] == "private") {

				$board_id = (int)exists_local("trello_board", "`board_id` = '$board[id]'", "id");
				$fields = array(
					"board_id",
					"title",
					"description",
					"date_lastactivity",
				);
				$values = array(
					"'$board[id]'",
					"'" . str_replace("'", "\'", $board["name"]) . "'",
					"'" . str_replace("'", "\'", $board["desc"]) . "'",
					"'" . date("Y-m-d H:i:s", strtotime($board["dateLastActivity"])) . "'",
				);
				if (!$board_id) {
					// Board doesn't exist locally yet - enter it.
					$ins = $db_conn->query("INSERT INTO trello_board (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ")");
					$board_id = $ins->insertId();
				} else {
					// Board exists locally - do a SQL update.
					$update_set = sql_update_set($fields);
					$up = $db_conn->query("UPDATE trello_board SET " . implode(", ", $update_set) . " WHERE id = '$board_id'");
				}

				// Get this board's lists.
				$lists = $client->boards()->lists()->all($board["id"]);
				foreach ($lists as $list) {
					$board_list_id = (int)exists_local("trello_board_list", "`board_id` = '$board_id' AND `list_id` = '$list[id]'", "id");
					$fields = array(
						"board_id",
						"list_id",
						"title",
					);
					$values = array(
						"'$board_id'",
						"'$list[id]'",
						"'" . str_replace("'", "\'", $list["name"]) . "'",
					);
					if (!$board_list_id) {
						// List doesn't exist locally yet - enter it.
						$ins = $db_conn->query("INSERT INTO trello_board_list (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ")");
						$board_list_id = $ins->insertId();
					} else {
						// List exists locally - do a SQL update.
						$update_set = sql_update_set($fields);
						$up = $db_conn->query("UPDATE trello_board_list SET " . implode(", ", $update_set) . " WHERE id = '$board_list_id'");
					}
				}

				$params = array(
					"filter" => "all",
					"attachments" => true,
					"checklists" => "all",
					"actions" => "commentCard",
					"limit" => 1,
					//"skip" => 1,
					//"page" => 22, // not working yet
				);
				$cards = $client->boards()->cards()->all($board["id"], $params);
//$debug->dbg($cards);
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
							"due",
							"closed",
						);
						$values = array(
							"'$board_id'",
							"'$card[idList]'",
							"'$card[id]'",
							"'" . str_replace("'", "\'", $card["name"]) . "'",
							"'" . str_replace("'", "\'", $card["desc"]) . "'",
							"'" . date("Y-m-d H:i:s", strtotime($card["dateLastActivity"])) . "'",
							"'" . date("Y-m-d H:i:s", strtotime($card["due"])) . "'",
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
									);
									$comment = strval($action["data"]["text"]);
									$values[] = "'" . mysql_real_escape_string($comment, $db_conn->dbConn) . "'";
									$ins = $db_conn->query("INSERT INTO trello_card_comment (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ")");
									$comment_id = $ins->insertId();
								}
							}
						}
					}
				}

			}

		}

	}

?>