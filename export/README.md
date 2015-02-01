# Exporting Trello data

This script will handle requesting and parsing your Trello data, and saving it to a MySQL database.

First create these two files:

* `config.php`
* `db_connect.php`

`config.php` should at least have this content (with real values filled in, of course):

	<?php

		$trello_key = "YOUR_APP_KEY";
		$trello_token = "YOUR_TOKEN";
		$trello_username = "YOUR_USERNAME";

	?>

`db_connect.php` should have this content (with real values filled in, of course):

	<?php

		use MattThommes\Backend\Mysql;

		$mysql_host = "";
		$mysql_user = "";
		$mysql_pass = "";
		$mysql_db   = "";
		$db_conn = new Mysql($mysql_host, $mysql_user, $mysql_pass, $mysql_db);

	?>

Here are the database tables used:

	CREATE TABLE `trello_board` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`board_id` varchar(40) NOT NULL DEFAULT '',
		`title` varchar(254) NOT NULL DEFAULT '',
		`description` text,
		`date_lastactivity` datetime NOT NULL,
		PRIMARY KEY (`id`)
	);

	CREATE TABLE `trello_card` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`board_id` varchar(40) NOT NULL DEFAULT '',
		`list_id` varchar(40) NOT NULL DEFAULT '',
		`card_id` varchar(40) NOT NULL DEFAULT '',
		`title` varchar(254) NOT NULL DEFAULT '',
		`description` text,
		`date_lastactivity` datetime NOT NULL,
		`closed` tinyint(1) unsigned NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
	);

	CREATE TABLE `trello_card_attachment` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`card_id` int(11) NOT NULL,
		`attachment_id` varchar(40) NOT NULL DEFAULT '',
		`date_create` datetime NOT NULL,
		`title` varchar(200) NOT NULL DEFAULT '',
		`url` varchar(254) NOT NULL DEFAULT '',
		PRIMARY KEY (`id`)
	);

	CREATE TABLE `trello_card_checklist_item` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`card_id` int(11) NOT NULL,
		`checklist_id` varchar(40) NOT NULL DEFAULT '',
		`checklist_title` varchar(200) NOT NULL DEFAULT '',
		`item_id` varchar(40) NOT NULL DEFAULT '',
		`state` tinyint(1) unsigned NOT NULL DEFAULT '0',
		`title` text NOT NULL,
		PRIMARY KEY (`id`)
	);

	CREATE TABLE `trello_card_comment` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`card_id` int(11) NOT NULL,
		`action_id` varchar(40) NOT NULL DEFAULT '',
		`date_create` datetime NOT NULL,
		`comment` text NOT NULL,
		PRIMARY KEY (`id`)
	);