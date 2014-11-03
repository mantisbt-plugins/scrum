# Scrum Board plugin for MantisBT

Copyright (c) 2011 - 2012  John Reese - http://noswap.com  
Copyright (c) 2012 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net

Released under the [MIT license](http://opensource.org/licenses/MIT)


## Description

Adds a Scrum board based on Status, Category, and Target Version
to MantisBT.


## Requirements

The plugin requires [MantisBT](http://www.mantisbt.org/) version 1.2.6 or higher.

If the [Source Integration plugin](https://github.com/mantisbt-plugins/source-integration)
(version 0.16 or higher) is installed, the cards will display the number of
changesets attached to each issue.


## Installation

1. Download or clone a copy of the [plugin's code](https://github.com/mantisbt-plugins/scrum).
2. Copy the plugin (the `Scrum/` directory) into your Mantis
   installation's `plugins/` directory.
3. While logged into your Mantis installation as an administrator, go to
   *Manage -> Manage Plugins*.
4. In the *Available Plugins* list, you'll find the *Scrum* plugin;
   click the **Install** link.


## Usage

A new *Scrum Board* item is added to MantisBT's Main menu.

It will display issues for the currently selected Project as Scrum Cards,
which can be filtered by *Target Version* and *Category*, with the cards
distributed in columns based on their Status.

### Configuration

At this time, the plugin does not yet include a configuration page;
this will be added in a future version of the plugin.

To change the Scrum Board's layout, you need to manually edit the
config() method in `Scrum.php`, or set the parameters using the Manage
Configuration page:

  - *board_columns* specifies which Status goes into which column;
    the array's key corresponds to the Board Column, and the value is
    an array of Status codes as defined in *status_enum_string*.
  - *board_severity_colors* and *board_resolution_colors* respectively
    define the colors to use for display of each severity and resolution code.
  - *token_expiry* determines how long the tokens used to store the filter
    preferences are kept (in seconds)
  - *sprint_length* specifies the duration of a sprint (in seconds)
  - *show_empty_status* when set to ON, the Status name will be displayed in
    the Scrum Board's columns even if there are no cards with this Status
    (defaults to OFF).


## Support

Problems or questions dealing with use and installation should be
directed to the [#mantisbt](irc://freenode.net/mantisbt) IRC channel
on Freenode.

The latest source code can found on
[Github](https://github.com/mantisbt-plugins/scrum).

We encourage you to submit Bug reports and enhancements requests on the
[Github issues tracker](https://github.com/mantisbt-plugins/scrum/issues).
If you would like to propose a patch, do not hesitate to submit a new
[Pull Request](https://github.com/mantisbt-plugins/scrum/compare/).
