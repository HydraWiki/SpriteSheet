#Changes
##1.0
* Sprite/Slice naming changed to include the file/article name in the tag.  This allows the same name to be used on different sprite sheets and makes it easier to see at a glance what sprite sheet that name belongs to in a parser tag.
* Adjusted the position of the naming pop up and made it closable.
* Dropped the spritesheet_id index from the spritename table.
* Renamed the name index on the spritename table to spritesheet_id_name and made it unique with spritesheet_id and name.
* Naming standards applied to sprite/slice names.
* New list of named sprites/slices and an editing interface for existing ones.
* Now uses the DB key of the Title instead of the article/page ID for reference.  This is to support images stored in remote repositories.
* New #ifsprite and #ifslice parser functions to give alternative output if either is not found.

##0.9
* Initial Release for internal QA
