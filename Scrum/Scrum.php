<?php

# Copyright (c) 2011 John Reese
# Licensed under the MIT license

class ScrumPlugin extends MantisPlugin
{
	public function register()
	{
		$this->name = plugin_lang_get("title");
		$this->description = plugin_lang_get("description");

		$this->version = "0.1";
		$this->requires = array(
			"MantisCore" => "1.2.6",
		);

		$this->author = "John Reese";
		$this->contact = "john@noswap.com";
		$this->url = "http://noswap.com";
	}
}
