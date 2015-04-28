The **SpriteSheet** extension allows uploaded images to be divided into sprite sheets or custom slices to be displayed without having to use an external image editor.  The resulting sprites and slices are dynamically generated using CSS.

* **Project Homepage:** [Documentation at Github](https://github.com/CurseStaff/SpriteSheet)
* **Mediawiki Extension Page:** [Extension:SpriteSheet](https://www.mediawiki.org/wiki/Extension:SpriteSheet)
* **Source Code:** [Source Code at Github](https://github.com/CurseStaff/SpriteSheet)
* **Bugs:** [Issue Tracker at Github](https://github.com/CurseStaff/SpriteSheet/issues)
* **Licensing:** SpriteSheet is released under [The GNU Lesser General Public License, version 3.0](http://opensource.org/licenses/lgpl-3.0.html).


#Installation

Download and place the file(s) in a directory called SpriteSheet in your extensions/ folder.

Add the following code at the bottom of your LocalSettings.php:

	require_once("$IP/extensions/SpriteSheet/SpriteSheet.php");

Done! Navigate to "Special:Version" on your wiki to verify that the extension is successfully installed.

#Usage

![](documentation/BasicInterface.png)

##Tags

###\#sprite - Parser Tag
The #sprite tag format accepts X and Y coordinate positions to select a section of the image in a traditional column and row format.

Basic Syntax:

	{{#sprite:file=Example.png|column=0|row=0}}

It can also be spaced across lines for readability:

	{{#sprite:
	file=Example.png
	|column=0
	|row=0
	}}

With optional resize and link:

	{{#sprite:
	file=Example.png
	|column=0
	|row=0
	|resize=300
	|link=ExampleArticle
	}}

####Parameters for #sprite Tag

| Parameter | Description                                                                                                                                      |
|----------:|--------------------------------------------------------------------------------------------------------------------------------------------------|
| file      | **Required**: yes<br/>The file page containing the image to use.                                                                                 |
| column    | **Required**: yes<br/>The X Coordinate Position of the sprite to select.  Coordinates use zero based numbering.                                  |
| row       | **Required**: yes<br/>The Y Coordinate Position of the sprite to select.  Coordinates use zero based numbering.                                  |
| resize    | **Required**: no, **Default**: null<br/>Display size in pixel width of the sprite.  Note: Will not resize larger than 100% of the original size. |
| link      | **Required**: no, **Default**: null<br/>Page name or external URL to have the sprite link to.                                                    |

####Example

To display the sprite located at column 4, row 2:
<pre>{{#sprite:
file=File:Hanamura-screenshot.jpg
|column=4
|row=2
}}</pre>

![](documentation/SpriteUsageExample.png)


###\#ifsprite - Parser Tag
The #ifsprite tag is used to display a named sprite if it exists.  If the named sprite does not actually exist yet it will instead return the given wiki text.

Basic Syntax:

	{{#ifsprite:
	file=File:Example.png
	|name=TestSprite
	|wikitext={{SpriteNotFound}}
	}}

####Parameters for #ifsprite Tag

| Parameter | Description                                                                                                                                               |
|----------:|-----------------------------------------------------------------------------------------------------------------------------------------------------------|
| file      | **Required**: yes<br/>The file page containing the image to use.                                                                                          |
| name      | **Required**: yes<br/>The named sprite to load.                                                                                                           |
| resize    | **Required**: no, **Default**: null<br/>Display size in pixel width of the sprite.  Note: Will not resize larger than 100% of the original size.          |
| wikitext  | **Required**: yes, **Default**: null<br/>The wiki text to parse and display if the named sprite is not found.  Can be left blank to not display anything. |

####Example

<pre>{{#ifsprite:
file=File:Hanamura-screenshot.jpg
|name=Plaque
|wikitext=[http://www.example.com/ Use This Example]
}}</pre>


###\#slice - Parser Tag
The #slice tag takes X and Y positioning along with width and height sizing to select a section of the image.  All four parameters take units in pixels(px) or percentages(%), but they all must use the same unit.

Basic Syntax:

	{{#slice:file=File:Example.png|x=0|y=0|width=10|height=10}}

It can also be spaced across lines for readability:

	{{#slice:
	file=File:Example.png
	|x=0
	|y=0
	|width=10
	|height=10
	}}

With optional resize and link:

	{{#slice:
	file=Example.png
	|x=0
	|y=0
	|width=10
	|height=10
	|resize=300
	|link=ExampleArticle
	}}

####Parameters for #slice Tag

| Parameter | Description                                                                                                                                      |
|----------:|--------------------------------------------------------------------------------------------------------------------------------------------------|
| file      | **Required**: yes<br/>The file page containing the image to use.                                                                                 |
| x         | **Required**: yes<br/>The X position, in pixels or percentage, of the slice to cut.                                                              |
| y         | **Required**: yes<br/>The Y position, in pixels or percentage, of the slice to cut.                                                              |
| width     | **Required**: yes<br/>Width in in pixels or percentage starting from the Y position.                                                             |
| height    | **Required**: yes<br/>Height in in pixels or percentage starting from the Y position.                                                            |
| resize    | **Required**: no, **Default**: null<br/>Display size in pixel width of the sprite.  Note: Will not resize larger than 100% of the original size. |
| link      | **Required**: no, **Default**: null<br/>Page name or external URL to have the sprite link to.                                                    |

####Example

<pre>{{#slice:
file=File:Hanamura-screenshot.jpg
|x=28.25
|y=32.97
|width=25.12
|height=23.58
}}</pre>

![](documentation/SliceUsageExample.png)


###\#ifslice - Parser Tag
The #ifslice tag is used to display a named slice if it exists.  If the named slice does not actually exist yet it will instead return the given wiki text.

Basic Syntax:

	{{#ifslice:
	file=File:Image_Name.png
	|name=SliceTest
	|wikitext={{SpriteNotFound}}
	}}

####Attributes for #ifslice Tag

| Parameter | Description                                                                                                                                              |
|----------:|----------------------------------------------------------------------------------------------------------------------------------------------------------|
| file      | **Required**: yes<br/>The file page containing the image to use.                                                                                         |
| name      | **Required**: yes<br/>The named slice to load.                                                                                                           |
| resize    | **Required**: no, **Default**: null<br/>Display size in pixel width of the slice.  Note: Will not resize larger than 100% of the original size.          |
| wikitext  | **Required**: yes, **Default**: null<br/>The wiki text to parse and display if the named slice is not found.  Can be left blank to not display anything. |

####Example

<pre>{{#ifslice:
file=File:Hanamura-screenshot.jpg
|name=Plaque
|wikitext=[http://www.example.com/ Use This Example]
}}</pre>

##Naming Sprites/Slices

![](documentation/SpriteNaming.png)

After a sprite or slice has been selected a pop up will open under the tag preview.  This allows a custom name to be set for the selection that can be recalled later.  It uses the same #sprite and #slice parser tags with only the name as the first argument after the file name.  Adding the optional thumb width is still supported.

<pre>{{#sprite:File:Hanamura-screenshot.jpg|Plaque}}</pre>
<pre>{{#sprite:File:Hanamura-screenshot.jpg|Plaque|800}}</pre>
<pre>{{#slice:File:Hanamura-screenshot.jpg|Plaque}}</pre>
<pre>{{#slice:File:Hanamura-screenshot.jpg|Plaque|500}}</pre>