<?php

# Copyright (c) 2011 John Reese
# Licensed under the MIT license

class ScrumPlugin extends MantisPlugin
{
	const TOKEN_SCRUM_VERSION = 101;
	const TOKEN_SCRUM_CATEGORY = 102;
	
	public function register()
	{
		$this->name = plugin_lang_get("title");
		$this->description = plugin_lang_get("description");

		$this->version = "0.1";
		$this->requires = array(
			"MantisCore" => "1.2.6",
		);
		$this->uses = array(
			"Source" => "0.16",
		);

		$this->author = "John Reese";
		$this->contact = "john@noswap.com";
		$this->url = "http://noswap.com";
	}

	public function config()
	{
		return array(
			#$g_status_enum_string = '10:new,20:feedback,30:acknowledged,40:confirmed,50:assigned,80:resolved,90:closed';
			"board_columns" => array(
				"New" => array(10, 20, 30),
				"Confirmed" => array(40, 50),
				"Resolved" => array(80),
			),

			#$g_severity_enum_string = '10:feature,20:trivial,30:text,40:tweak,50:minor,60:major,70:crash,80:block';
			"board_severity_colors" => array(
				10 => "green",
				20 => "green",
				30 => "green",
				40 => "green",
				50 => "gray",
				60 => "gray",
				70 => "orange",
				80 => "red",
			),

			#$g_resolution_enum_string = '10:open,20:fixed,30:reopened,40:unable to duplicate,50:not fixable,60:duplicate,70:not a bug,80:suspended,90:wont fix';
			"board_resolution_colors" => array(
				10 => "orange",
				20 => "green",
				30 => "red",
				40 => "gray",
				50 => "gray",
				60 => "gray",
				70 => "gray",
				80 => "gray",
				90 => "gray",
			),

			"token_expiry" => 2592000,  # 30 days,
			"sprint_length" => 1209600, # 14 days (14 * 24 * 60 * 60)
			"show_empty_status" => OFF,
		);
	}

	public function hooks()
	{
		return array(
			"EVENT_MENU_MAIN" => "menu",
		);
	}

	public function menu($event)
	{
		$links = array();
		$links[] = '<a href="' . plugin_page("board") . '">' . plugin_lang_get("board") . '</a>';

		return $links;
	}
}
