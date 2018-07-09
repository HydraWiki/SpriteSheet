#Changes
##1.2.0
* Switched to extension registration only.
* Various E_NOTICE fixes and PHP 7.0+ compatibility fixes.
* Fixed CSS and JS not loading for Extension:MobileFrontend.

##1.1.5
* Fixed an incorrect call to makeErrorBox().
* Fixed checking titles for existence that in some cases would fail with a fatal error.
* Fixed central repositories trying to work through a ForeignDBRepo when SpriteSheet only supports ForeignAPIRepo.

##1.1.4
* Fixed MediaWiki 1.26+ compatibility.
 * Removed jQuery dependency in resource loader module
 * Added formatversion=2 to all API calls due to a dumb change in MediaWiki's core API.
* Added extension registration entry point.
* Fixed saving a new named sprite when the sheet did not exist yet in the database.

##1.1.3
* Fixes errors with the sprite sheet changes log that was displaying the edited message template on new sprite sheets.

##1.1.2
* Fixes resize parameter being broken with named sprites and slices.
* Fixes an issue with calculating the size of an object in Javascript.

##1.1.1
* The very first sprite/slice created on a Sheet will not be listed in the show sprite/slice list and requires a page refresh.
* Saving a sprite/slice that has been previously deleted results in it not updating the deleted status.
* The log formatter for sprite names would attempt to use valid, but nonexistent sprite sheets resulting in a fatal error.

##1.1.0
* This version is not backwards compatible with previous versions.
* The SpriteSheet Editor is now hidden by default to reduce the number of curious edits.  Wiki editors are cats.
* Spritesheets no longer automatically saved.  The save button is disabled by default and will be activated when changes are made.  A visual indicator has been added when changes are pending.
* Will now respect page protection on images when editing sprite sheets.
* Parser tags were changed to a "parameter=option" format.  This is to prevent feature creep from destroying the tag format and consistency.  It also allows for easier templating.
* The "thumbnail" parameter has been renamed to "resize".  It now controls the direct size of the sprite output instead of the overall image itself.  This is a more natural thought process when handling the output.
* Better parameter validation and error handling.
* New "link" parameter to have the sprite link to a page or external URL.
* Complete revisioning with the ability to roll back spritesheets, sprites, and slices.
* Spritesheets can now display an overlayed visual difference between revisions.
* Logging has been standardized and improved in relation to the revisioning feature.
* Fixes for CSS selector tagetting.
* Fixed an issue with remote spritesheets that prevented caching correctly.

##1.0.1
* Do not display sprite sheet editor on non-images.

##1.0.0
* Sprite/Slice naming changed to include the file/article name in the tag.  This allows the same name to be used on different sprite sheets and makes it easier to see at a glance what sprite sheet that name belongs to in a parser tag.
* Adjusted the position of the naming pop up and made it closable.
* Dropped the spritesheet_id index from the spritename table.
* Renamed the name index on the spritename table to spritesheet_id_name and made it unique with spritesheet_id and name.
* Naming standards applied to sprite/slice names.
* New list of named sprites/slices and an editing interface for existing ones.
* Now uses the DB key of the Title instead of the article/page ID for reference.  This is to support images stored in remote repositories.
* New #ifsprite and #ifslice parser functions to give alternative output if either is not found.

##0.9.0
* Initial Release for internal QA