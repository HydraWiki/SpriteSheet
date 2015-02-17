{{ {{TNTN|Extension}}
|name        = SpriteSheet
|license     = {{EL|LGPLv3}}
|status      = stable
|type        = parser function
|author      = Curse Inc. Wiki Platform Team, Alexia E. Smith ([[User:Alexia E. Smith|Alexia E. Smith]])
|version     = 0.9.0
|update      = 2015-02-13
|mediawiki   = 1.23+
|php         = 5.4+
|download    = {{ {{TNTN|GithubDownload}} |CurseStaff|SpriteSheet}}
|hook1       = ParserFirstCallInit
|hook2       = ImagePageShowTOC
|hook3       = ImageOpenShowImageInlineBefore
|hook4       = PageRenderingHash
|hook5       = LoadExtensionSchemaUpdates
|description = Adds a parser functions called <tt>#sprite</tt> and <tt>#slice</tt> to display defined sections of an image without having to use an external editor.
|example     = [http://help.gamepedia.com/Extension:SpriteSheet/Example Gamepedia Help Wiki]
}}

The '''SpriteSheet''' extension allows uploaded images to be divided into sprite sheets or custom slices to be displayed without having to use an external image editor.  The resulting sprites and slices are dynamically generated using CSS.

;Project Homepage: [https://github.com/CurseStaff/SpriteSheet Documentation at Github]
;Source Code: [https://github.com/CurseStaff/SpriteSheet Source Code at Github]
;Bugs: [https://github.com/CurseStaff/SpriteSheet/issues Issue Tracker at Github]
;Licensing: SpriteSheet is released under [http://opensource.org/licenses/lgpl-3.0.html The GNU Lesser General Public License, version 3.0].


==Installation==

{{ {{TNTN|ExtensionInstall}} |download-link=[https://github.com/CurseStaff/SpriteSheet/archive/v0.9.0.zip Download]}}

==Usage==

=== Tags ===

====#sprite - Parser Tag====
* <code><nowiki>{{#sprite:File:Image_Name.png|xPos|yPos}}</nowiki></code>
* With optional thumbnail resize: <code><nowiki>{{#sprite:File:Image_Name.png|xPos|yPos|thumbWidth}}</nowiki></code>

===== Attributes for #sprite Tag =====

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
:The X Coordinate Position of the sprite to select.
|-
| <code>Y Coordinate Position</code>
| '''Required:''' yes
:The X Coordinate Position of the sprite to select.
|-
| <code>Thumb Width</code>
| '''Required:''' no, '''Default:''' <tt>none</tt>
:Size the thumbnail width of the entire image before selecting the sprite.  This is the width of the entire image; not the individual sprite.
|}

===== Examples =====

[[File:SpriteSheetExample1.jpg|thumb|Example #1]]

<pre>{#sprite:File:SpriteSheetExample1.jpg|2|3}}</pre>


====#slice - Parser Tag====
* <code><nowiki>{{#slice:File:Image_Name.png|xPercent|yPercent|widthPercent|heightPercent}}</nowiki></code>
* With optional thumbnail resize: <code><nowiki>{{#slice:File:Image_Name.png|xPercent|yPercent|widthPercent|heightPercent|thumbWidth}}</nowiki></code>

===== Attributes for #slice Tag =====

{| class="wikitable"
|-
! Attribute
! Description
|-
| <code>File</code>
| '''Required:''' yes
:The file page containing the image to use.
|-
| <code>X Coordinate Position, in Percentage</code>
| '''Required:''' yes
:The X Coordinate Position of the sprite to select.
|-
| <code>Y Coordinate Position, in Percentage</code>
| '''Required:''' yes
:The X Coordinate Position of the sprite to select.
|-
| <code>Width, in Percentage</code>
| '''Required:''' yes
:Width in percentage starting from the X coordinate.
|-
| <code>Height, in Percentage</code>
| '''Required:''' yes
:Height in percentage starting from the Y coordinate.
|-
| <code>Thumb Width</code>
| '''Required:''' no, '''Default:''' <tt>none</tt>
:Size the thumbnail width of the entire image before selecting the sprite.  This is the width of the entire image; not the individual sprite.
|}

===== Examples =====

[[File:SpriteSheetExample2.jpg|thumb|Example #2]]

<pre>{#slice:File:SpriteSheetExample1.jpg|35.25|17.03|30.75|29.26}}</pre>