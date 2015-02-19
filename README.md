The '''SpriteSheet''' extension allows uploaded images to be divided into sprite sheets or custom slices to be displayed without having to use an external image editor.  The resulting sprites and slices are dynamically generated using CSS.

;Project Homepage: [https://github.com/CurseStaff/SpriteSheet Documentation at Github]
;Mediawiki Extension Page: [https://www.mediawiki.org/wiki/Extension:SpriteSheet Extension:SpriteSheet]
;Source Code: [https://github.com/CurseStaff/SpriteSheet Source Code at Github]
;Bugs: [https://github.com/CurseStaff/SpriteSheet/issues Issue Tracker at Github]
;Licensing: SpriteSheet is released under [http://opensource.org/licenses/lgpl-3.0.html The GNU Lesser General Public License, version 3.0].


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

{| class="wikitable"
|-
! Attribute
! Description
|-
| <code>File</code>
| '''Required:''' yes
:The file page containing the image to use.
|-
| <code>X Coordinate Position</code>
| '''Required:''' yes
:The X Coordinate Position of the sprite to select.  Coordinates use zero based numbering.
|-
| <code>Y Coordinate Position</code>
| '''Required:''' yes
:The Y Coordinate Position of the sprite to select.  Coordinates use zero based numbering.
|-
| <code>Thumb Width</code>
| '''Required:''' no, '''Default:''' <tt>none</tt>
:Size the thumbnail width of the entire image before selecting the sprite.  This is the width of the entire image; not the individual sprite.
|}

|       Attribute       | Description                                                                                                                                                             |
|:---------------------:|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| File                  | **Required**: yes
	The file page containing the image to use.                                                                                                                |
| X Coordinate Position | **Required**: yes The X Coordinate Position of the sprite to select.,Coordinates use zero based numbering.                                                                  |
| Y Coordinate Position | **Required**: yes The Y Coordinate Position of the sprite to select.,Coordinates use zero based numbering.                                                                  |
| Thumb Width           | **Required**: no, **Default**: none Size the thumbnail width of the entire image before selecting,the sprite.,This is the width of the entire image; not the individual,sprite. |

####Example

To display the sprite located at column 2, row 3:
<pre>{#sprite:File:SpriteSheetExample1.jpg|2|3}}</pre>


###\#slice - Parser Tag
Basic Syntax:

	{{#slice:File:Image_Name.png|xPercent|yPercent|widthPercent|heightPercent}}

With optional thumbnail resize:

	{{#slice:File:Image_Name.png|xPercent|yPercent|widthPercent|heightPercent|thumbWidth}}

####Attributes for #slice Tag

{| class="wikitable"
|-
! Attribute
! Description
|-
| <code>File</code>
| '''Required:''' yes
:The file page containing the image to use.
|-
| <code>X Percentage Position</code>
| '''Required:''' yes
:The X Percentage Position of the slice to cut.
|-
| <code>Y Percentage Position</code>
| '''Required:''' yes
:The Y Percentage Position of the slice to cut.
|-
| <code>Width, in Percentage</code>
| '''Required:''' yes
:Width in percentage starting from the X position.
|-
| <code>Height, in Percentage</code>
| '''Required:''' yes
:Height in percentage starting from the Y position.
|-
| <code>Thumb Width</code>
| '''Required:''' no, '''Default:''' <tt>none</tt>
:Size the thumbnail width of the entire image before selecting the sprite.  This is the width of the entire image; not the individual sprite.
|}

####Example

<pre>{#slice:File:SpriteSheetExample1.jpg|35.25|17.03|30.75|29.26}}</pre>