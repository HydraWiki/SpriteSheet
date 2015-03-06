The **SpriteSheet** extension allows uploaded images to be divided into sprite sheets or custom slices to be displayed without having to use an external image editor.  The resulting sprites and slices are dynamically generated using CSS.

* **Project Homepage:** [Documentation at Github](https://github.com/CurseStaff/SpriteSheet)
* **Mediawiki Extension Page:** [Extension:SpriteSheet](https://www.mediawiki.org/wiki/Extension:SpriteSheet)
* **Source Code:** [Source Code at Github](https://github.com/CurseStaff/SpriteSheet)
* **Bugs:** [Issue Tracker at Github](https://github.com/CurseStaff/SpriteSheet/issues)
* **Licensing:** SpriteSheet is released under [The GNU Lesser General Public License, version 3.0](http://opensource.org/licenses/lgpl-3.0.html).


#Installation

Download and place the file(s) in a directory called EmbedVideo in your extensions/ folder.

Add the following code at the bottom of your LocalSettings.php:

	require_once("$IP/extensions/EmbedVideo/EmbedVideo.php");

Done! Navigate to "Special:Version" on your wiki to verify that the extension is successfully installed.

#Usage

![](documentation/BasicInterface.png)

##Tags

###\#sprite - Parser Tag
Basic Syntax:

	{{#sprite:File:Image_Name.png|xPos|yPos}}

With optional thumbnail resize:

	{{#sprite:File:Image_Name.png|xPos|yPos|thumbWidth}}

####Attributes for #sprite Tag

|       Attribute       | Description                                                                                                                                                                         |
|----------------------:|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| File                  | **Required**: yes<br/>The file page containing the image to use.                                                                                                                    |
| X Coordinate Position | **Required**: yes<br/>The X Coordinate Position of the sprite to select.,Coordinates use zero based numbering.                                                                      |
| Y Coordinate Position | **Required**: yes<br/>The Y Coordinate Position of the sprite to select.,Coordinates use zero based numbering.                                                                      |
| Thumb Width           | **Required**: no, **Default**: null<br/>Size the thumbnail width of the entire image before selecting,the sprite.,This is the width of the entire image; not the individual,sprite. |

####Example

To display the sprite located at column 4, row 2:
<pre>{{#sprite:File:Hanamura-screenshot.jpg|4|2}}</pre>

![](documentation/SpriteUsageExample.png)

###\#slice - Parser Tag
Basic Syntax:

	{{#slice:File:Image_Name.png|xPercent|yPercent|widthPercent|heightPercent}}

With optional thumbnail resize:

	{{#slice:File:Image_Name.png|xPercent|yPercent|widthPercent|heightPercent|thumbWidth}}

####Attributes for #slice Tag

|       Attribute       | Description                                                                                                                                                                          |
|----------------------:|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| File                  | **Required**: yes<br/>The file page containing the image to use.                                                                                                                     |
| X Percentage Position | **Required**: yes<br/>The X Percentage Position of the slice to cut.                                                                                                                 |
| Y Percentage Position | **Required**: yes<br/>The Y Percentage Position of the slice to cut.                                                                                                                 |
| Width, in Percentage | **Required**: yes<br/>Width in percentage starting from the Y position.                                                                                                               |
| Height, in Percentage | **Required**: yes<br/>Height in percentage starting from the Y position.                                                                                                             |
| Thumb Width           | **Required**: no, **Default**: none<br/>Size the thumbnail width of the entire image before selecting the sprite.  This is the width of the entire image; not the individual sprite. |

####Example

<pre>{{#slice:File:Hanamura-screenshot.jpg|28.25|32.97|25.12|23.58}}</pre>

![](documentation/SliceUsageExample.png)

##Naming Sprites/Slices

![](documentation/SpriteNaming.png)

After a sprite or slice has been selected a pop up will open under the tag preview.  This allows a custom name to be set for the selection that can be recalled later.  It uses the same #sprite and #slice parser tags with only the name as the first argument after the file name.  Adding the optional thumb width is still supported.

<pre>{{#sprite:File:Hanamura-screenshot.jpg|Plaque}}</pre>
<pre>{{#sprite:File:Hanamura-screenshot.jpg|Plaque|800}}</pre>
<pre>{{#slice:File:Hanamura-screenshot.jpg|Plaque}}</pre>
<pre>{{#slice:File:Hanamura-screenshot.jpg|Plaque|500}}</pre>