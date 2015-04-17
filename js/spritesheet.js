mw.spriteSheet = {
	canvas: null,
	values: {},
	oldValues: {},
	selectedSprite: {},
	selectedSlice: {},
	selectedType: null,
	selector: null,
	highlight: {},
	mouseDrag: false,
	sheetSaved: false,
	fetchedSpriteNames: false,
	spriteNames: {},
	namedSpriteEditor: null,
	currentlyEditing: null,
	isRemote: false,
	remoteApiUrl: null,

	/**
	 * Automatically construct/deconstruct the object.
	 *
	 * @return	void
	 */
	toggleEditor: function() {
		if (this.canvas === null) {
			this.initialize();
		} else {
			this.uninitialize();
		}
	},

	/**
	 * Initialize the sprite sheet.
	 *
	 * @return	void
	 */
	initialize: function() {
		if (this.canvas !== null) {
			$('#spritesheet').remove();
		}

		$("#spritesheet_editor").slideDown();

		//Determine if this is a local or remote sprite sheet first.
		var isRemote = parseInt($("input[name='isRemote']").val());
		if (isRemote === 1) {
			this.isRemote = true;
			this.remoteApiUrl = $("input[name='remoteApiUrl']").val();
		}

		var imageWidth = $('#file > a > img').width();
		var imageHeight = $('#file > a > img').height();

		var spritesheet = $("<canvas>").attr('id', 'spritesheet').attr('width', imageWidth).attr('height', imageHeight);

		$(spritesheet).css({
			left: 0,
			position: 'absolute',
			top: 0
		});

		$('#file').css({
			position: 'relative'
		});

		$('#file > a').css({
			display: 'inline-block',
			position: 'relative'
		});

		$(spritesheet).appendTo('#file');

		this.canvas = oCanvas.create({
			canvas: "#spritesheet",
			background: "rgba(0, 0, 0, 0)",
			fps: 60
		});

		this.canvas.bind('mousedown', function() {
			mw.spriteSheet.canvas.bind('mousemove', function() {
				mw.spriteSheet.mouseDrag = true;
			});
			mw.spriteSheet.startSelection();
			mw.spriteSheet.canvas.addChild(mw.spriteSheet.selector);
		});

		this.canvas.bind('mouseup', function() {
			mw.spriteSheet.canvas.unbind('mousemove');
			mw.spriteSheet.stopSelection();
			mw.spriteSheet.mouseDrag = false;
		});

		$('#sprite_columns, #sprite_rows, #sprite_inset').on('change keyup', function(event) {
			if ($(this).attr('readonly')) {
				return;
			}
			if (event.type == 'keyup') {
				if (event.keyCode >= 48 && event.keyCode <= 57) {
					mw.spriteSheet.updateSpriteSheet();
				}
			} else {
				mw.spriteSheet.updateSpriteSheet();
			}
			$('#save_sheet').attr('disabled', false);
			$('#save_sheet').addClass('pulse');
		});

		//Only set these up if this a local sprite sheet.
		if (!this.isRemote) {
			$('#save_sheet').on('click tap', function() {
				mw.spriteSheet.saveSpriteSheet();
			});

			$("input[name='sprite_name']").keyup(function(key) {
				if (key.keyCode == 13) {
					mw.spriteSheet.saveSpriteName();
				}
			});

			$("input[name='update_sprite_name']").keyup(function(key) {
				if (key.keyCode == 13) {
					mw.spriteSheet.updateSpriteName();
				}
			});

			$('#save_named_sprite').on('click tap', function() {
				mw.spriteSheet.saveSpriteName();
			});

			$('#update_named_sprite').on('click tap', function() {
				mw.spriteSheet.updateSpriteName();
			});

			$('#delete_named_sprite').on('click tap', function() {
				mw.spriteSheet.deleteSpriteName();
			});

			$('.named_sprite_popup a.close').on('click', function() {
				$(this).parent().hide();
				if (mw.spriteSheet.currentlyEditing != null) {
					mw.spriteSheet.canvas.removeChild(mw.spriteSheet.highlight[mw.spriteSheet.currentlyEditing].object);
					mw.spriteSheet.highlight[mw.spriteSheet.currentlyEditing].isShown = false;
					mw.spriteSheet.currentlyEditing = null;
				}
			});

			this.namedSpriteEditor = $("#named_sprite_editor").detach();
			$("#file").append(this.namedSpriteEditor);
		}

		$('#save_sheet').attr('disabled', true);

		$('#show_named_sprites').on('click tap', function() {
			mw.spriteSheet.toggleSpriteNameList();
		});

		$("#named_sprites").hide();

		this.updateSpriteSheet();
		this.sheetSaved = true;
	},

	/**
	 * Uninitialize the sprite sheet.
	 *
	 * @return	void
	 */
	uninitialize: function() {
		$('#spritesheet').remove();

		$("#spritesheet_editor").slideUp();

		this.canvas = null;
		this.values = {};
		this.oldValues = {};
		this.selectedSprite = {};
		this.selectedSlice = {};
		this.selectedType = null;
		this.selector = null;
		this.highlight = {};
		this.mouseDrag = false;
		this.sheetSaved = false;
		this.fetchedSpriteNames = false;
		this.spriteNames = {};
		this.namedSpriteEditor = null;
		this.currentlyEditing = null;
		this.isRemote = false;
		this.remoteApiUrl = null;
	},

	/**
	 * Update the canvas object containing the sprite sheet.
	 *
	 * @return	void
	 */
	updateSpriteSheet: function() {
		this.canvas.reset();

		this.parseValues();

		var stroke = "inside 1px rgba(215, 215, 215, 1)";

		if ($("input[name='old_spritesheet_id']").length && !isNaN(this.oldValues.columns) && !isNaN(this.oldValues.rows) && this.oldValues.columns > 0 && this.oldValues.rows > 0) {
			if (!this.oldValues.inset) {
				var inset = 1;
			} else {
				var inset = this.oldValues.inset * 2;
			}

			this.makeGridLines(this.oldValues.columns, this.oldValues.rows, this.oldValues.inset, "inside 1px rgba(237, 102, 102, 1)");

			stroke = "inside 1px rgba(78, 245, 117, 1)";
		}

		if (!isNaN(this.values.columns) && !isNaN(this.values.rows) && this.values.columns > 0 && this.values.rows > 0) {
			//This changing of an inset of 0 to 1 is just for pixel display purposes and has no bearing on positional data.
			if (!this.values.inset) {
				var inset = 1;
			} else {
				var inset = this.values.inset * 2;
			}

			this.makeGridLines(this.values.columns, this.values.rows, inset, stroke);
		}

		this.sheetSaved = false;
	},

	/**
	 * Make grid lines on the canvas.
	 *
	 * @param	integer Columns
	 * @param	integer Rows
	 * @param	integer Inset
	 * @param	string	Stroke
	 * @return	void
	 */
	makeGridLines: function(columns, rows, inset, stroke) {
		var columnWidth = this.canvas.width / columns;
		var rowHeight = this.canvas.height / rows;

		for (var i = 0; i <= columns; i++) {
			var x = i * columnWidth;

			var rectangle = this.canvas.display.rectangle({
				x: x - (Math.floor(inset / 2)),
				y: 0,
				width: inset,
				height: this.canvas.height,
				fill: "rgba(215, 0, 0, 0.5)",
				stroke: stroke
			});
			this.canvas.addChild(rectangle);
		}

		for (var i = 0; i <= rows; i++) {
			var y = i * rowHeight;

			var rectangle = this.canvas.display.rectangle({
				x: 0,
				y: y - (Math.floor(inset / 2)),
				width: this.canvas.width,
				height: inset,
				fill: "rgba(215, 0, 0, 0.5)",
				stroke: stroke
			});
			this.canvas.addChild(rectangle);
		}
	},

	/**
	 * Save the sprite sheet back to the server.
	 *
	 * @return	boolean
	 */
	saveSpriteSheet: function() {
		if (this.isRemote === true) {
			return;
		}

		var api = new mw.Api();

		this.showProgressIndicator();
		$('#save_sheet').attr('disabled', true);
		api.post(
			{
				action: 'spritesheet',
				do: 'saveSpriteSheet',
				format: 'json',
				form: $('#spritesheet_editor form fieldset#spritesheet_form').serialize()
			}
		).done(
			function(result) {
				mw.spriteSheet.hideProgressIndicator();

				if (result.success != true) {
					$('#save_sheet').attr('disabled', false);
					alert(result.message);
					return;
				}

				$('#save_sheet').removeClass('pulse');

				var spriteSheetId = $("input[name='spritesheet_id']").val();

				if (result.spriteSheetId > 0 && spriteSheetId <= 0) {
					$("input[name='spritesheet_id']").val(result.spriteSheetId);
				}

				mw.spriteSheet.sheetSaved = true;
			}
		);
	},

	/**
	 * Save a named sprite/slice back to the server.
	 *
	 * @return	boolean
	 */
	saveSpriteName: function() {
		if (this.isRemote === true) {
			return;
		}

		var api = new mw.Api();

		var spriteName = $('#sprite_name').val();

		if (!this.selectedType) {
			alert(mw.message('please_select_sprite').text());
			return;
		}

		if (!spriteName) {
			alert(mw.message('please_enter_sprite_name').text());
			return;
		}

		this.showProgressIndicator();
		api.post(
			{
				action: 'spritesheet',
				do: 'saveSpriteName',
				format: 'json',
				form: $('#spritesheet_editor form fieldset#spritesheet_form').serialize(),
				type: mw.spriteSheet.selectedType,
				values: (mw.spriteSheet.selectedType == 'slice' ? JSON.stringify(mw.spriteSheet.selectedSlice) : JSON.stringify(mw.spriteSheet.selectedSprite))
			}
		).done(
			function(result) {
				if (result.success != true) {
					alert(result.message);
				} else {
					$('#named_sprite_add').hide();

					//Update the preview.
					mw.spriteSheet.updateSpritePreview(result.data.tag);

					//Remove the selector from the sheet.
					mw.spriteSheet.canvas.removeChild(mw.spriteSheet.selector);

					//Update the list entries.
					mw.spriteSheet.spriteNames[result.data.name] = result.data;
					mw.spriteSheet.updateSpriteNameListEntries();
				}
				mw.spriteSheet.hideProgressIndicator();
			}
		);
	},

	/**
	 * Update the name of a sprite/slice back to the server.
	 *
	 * @return	boolean
	 */
	updateSpriteName: function() {
		if (this.isRemote === true) {
			return;
		}

		var api = new mw.Api();

		var spriteData = this.spriteNames[this.currentlyEditing];

		var oldSpriteName = spriteData.name;
		var newSpriteName = $('#update_sprite_name').val();

		if (!newSpriteName) {
			alert(mw.message('please_enter_sprite_name').text());
			return;
		}

		this.showProgressIndicator();
		api.post(
			{
				action: 'spritesheet',
				do: 'updateSpriteName',
				format: 'json',
				form: $('#spritesheet_editor form fieldset#spritesheet_form').serialize(),
				spritename_id: spriteData.id,
				old_sprite_name: spriteData.name,
				new_sprite_name: newSpriteName
			}
		).done(
			function(result) {
				if (result.success != true) {
					alert(result.message);
				} else {
					$('#named_sprite_editor').hide();

					//No longer editing anything.
					mw.spriteSheet.currentlyEditing = null;

					//Hide the highlight created under the older name first.
					mw.spriteSheet.highlightSpriteName(oldSpriteName, false);

					//Reset name on the sprite data.
					spriteData.name = newSpriteName;

					//Nuke the sprite data from the names list and assign the updated data on to the list.
					delete mw.spriteSheet.spriteNames[oldSpriteName]
					mw.spriteSheet.spriteNames[newSpriteName] = spriteData;

					//Update the visual list.
					$("#named_sprites ul li[data-id='"+spriteData.id+"']").html(newSpriteName).attr('data-name', newSpriteName);

					//Update the preview.
					mw.spriteSheet.updateSpritePreview(result.data.tag);

					//Update the list entries.
					mw.spriteSheet.spriteNames[result.data.name] = result.data;
					mw.spriteSheet.updateSpriteNameListEntries();
				}
				mw.spriteSheet.hideProgressIndicator();
			}
		);
	},

	/**
	 * Delete a SpriteName through the API.
	 *
	 * @return	boolean
	 */
	deleteSpriteName: function() {
		if (this.isRemote === true) {
			return;
		}

		var api = new mw.Api();

		var spriteData = this.spriteNames[this.currentlyEditing];

		this.showProgressIndicator();
		api.post(
			{
				action: 'spritesheet',
				do: 'deleteSpriteName',
				format: 'json',
				form: $('#spritesheet_editor form fieldset#spritesheet_form').serialize(),
				spritename_id: spriteData.id,
				sprite_name: spriteData.name
			}
		).done(
			function(result) {
				if (result.success != true) {
					alert(result.message);
				} else {
					$('#named_sprite_editor').hide();

					//No longer editing anything.
					mw.spriteSheet.currentlyEditing = null;

					//Hide the highlight created under the older name first.
					mw.spriteSheet.highlightSpriteName(spriteData.name, false);

					//Nuke the sprite data from the names list.
					delete mw.spriteSheet.spriteNames[spriteData.name]

					//Update the visual list.
					$("#named_sprites ul li[data-id='"+spriteData.id+"']").remove();

					//Reset the preview to default.
					mw.spriteSheet.updateSpritePreview(mw.message('click_grid_for_preview').escaped());

					//Update the list entries.
					mw.spriteSheet.updateSpriteNameListEntries();
				}
				mw.spriteSheet.hideProgressIndicator();
			}
		);
	},

	/**
	 * Update the sprite preview.
	 *
	 * @return	void
	 */
	updateSpritePreview: function(tag) {
		$('#sprite_preview').html(tag);
	},

	/**
	 * Save a named sprite/slice back to the server.
	 *
	 * @return	boolean
	 */
	getAllSpriteNames: function() {
		var options = {};

		var spriteSheetId = $("input[name='spritesheet_id']").val();
		var title = $("input[name='page_title']").val();

		var parameters = {
			action: 'spritesheet',
			do: 'getAllSpriteNames',
			format: 'json',
			form: $('#spritesheet_editor form fieldset#spritesheet_form').serialize(),
			title: title,
		};

		if (this.isRemote === true) {
			options.ajax = {};
			options.ajax.url = this.remoteApiUrl;
			parameters.origin = mw.config.get('wgServer');
		}
		var api = new mw.Api(options);

		if (!this.fetchedSpriteNames) {
			this.showProgressIndicator();
			api.get(
				parameters,
				{
					async: false
				}
			).done(
				function(result) {
					if (result.success != true) {
						alert(result.message);
					} else {
						mw.spriteSheet.spriteNames = result.data;
						mw.spriteSheet.fetchedSpriteNames = true;
					}
					mw.spriteSheet.hideProgressIndicator();
				}
			);
		}
	},

	/**
	 * Return a boolean indiciating if sprite names have been populated.
	 *
	 * @return	boolean
	 */
	haveAllSpriteNames: function() {
		return this.fetchedSpriteNames && Object.keys(this.spriteNames).length > 0;
	},

	/**
	 * Display a list of sprite names.
	 *
	 * @return	void
	 */
	toggleSpriteNameList: function() {
		if (!this.haveAllSpriteNames() && !$("#named_sprites").is(':visible')) {
			this.getAllSpriteNames();

			this.updateSpriteNameListEntries();
		}

		if (!$("#named_sprites").is(':visible')) {
			$("#named_sprites").slideDown();
			$('button#show_named_sprites').html(mw.message('hide_named_sprites').escaped());
		} else {
			$("#named_sprites").slideUp();
			$('button#show_named_sprites').html(mw.message('show_named_sprites').escaped());
		}
	},

	/**
	 * Update the list entries for sprite names.
	 *
	 * @return	void
	 */
	updateSpriteNameListEntries: function() {
		var list;

		if (!this.haveAllSpriteNames()) {
			$("#named_sprites").html(mw.message('no_results_named_sprites').escaped());
		} else {
			list = $("<ul>");

			$.each(this.spriteNames, function(spriteName, data) {
				if (data.deleted != true) {
					$(list).append(mw.spriteSheet.formatSpriteNameListItem(data));
				}
			});
			$("#named_sprites").html(list);

			$("#named_sprites ul li").on("click", function() {
				mw.spriteSheet.showSpriteNameEditor($(this).attr('data-name'));
			});
			$("#named_sprites ul li").on("mouseenter", function() {
				mw.spriteSheet.highlightSpriteName($(this).attr('data-name'), true);
			});
			$("#named_sprites ul li").on("mouseleave", function() {
				mw.spriteSheet.highlightSpriteName($(this).attr('data-name'), false);
			});
		}
	},

	/**
	 * Format a sprite name list item.
	 *
	 * @return	string	HTML <li> List Item
	 */
	formatSpriteNameListItem: function(data) {
		return $("<li>").attr("data-id", data.id).attr("data-name", data.name).attr("title", mw.message('click_to_edit').escaped()).append(data.name);
	},

	/**
	 * Highlight a named sprite on the canvas.
	 *
	 * @param	string	Sprite Name Key
	 * @param	boolean	Show or Hide the Highlight
	 * @return	void
	 */
	highlightSpriteName: function(spriteName, show) {
		var spriteData = this.spriteNames[spriteName];

		if (show === true && Object.keys(spriteData).length && (!this.highlight.hasOwnProperty(spriteName) || !this.highlight[spriteName].isShown)) {
			switch (spriteData.type) {
				case 'sprite':
					inset = this.values.inset * 2;

					var columnWidth = this.canvas.width / this.values.columns;
					var rowHeight = this.canvas.height / this.values.rows;

					var x = spriteData.values.xPos * columnWidth;
					var y = spriteData.values.yPos * rowHeight;

					this.highlight[spriteName] = {};
					this.highlight[spriteName].object = this.canvas.display.rectangle({
						x: x + (Math.floor(inset / 2)),
						y: y + (Math.floor(inset / 2)),
						width: columnWidth - inset,
						height: rowHeight - inset,
						fill: "rgba(26, 114, 193, 0.6)",
						stroke: "inside 1px rgba(26, 114, 193, 1)"
					});
					this.canvas.addChild(this.highlight[spriteName].object);
					this.highlight[spriteName].isShown = true;
					break;
				case 'slice':
					this.highlight[spriteName] = {};
					this.highlight[spriteName].object = this.canvas.display.rectangle({
						x: this.canvas.width * (spriteData.values.xPercent / 100),
						y: this.canvas.height * (spriteData.values.yPercent / 100),
						origin: {x: "top", y: "left"},
						width: this.canvas.width * (spriteData.values.widthPercent / 100),
						height: this.canvas.height * (spriteData.values.heightPercent / 100),
						fill: "rgba(26, 114, 193, 0.6)",
						stroke: "inside 1px rgba(26, 114, 193, 1)"
					});
					this.canvas.addChild(this.highlight[spriteName].object);
					this.highlight[spriteName].isShown = true;
					break;
			}
		} else if (show === false && this.highlight.hasOwnProperty(spriteName) && this.highlight[spriteName].isShown && spriteName != this.currentlyEditing) {
			this.canvas.removeChild(this.highlight[spriteName].object);
			this.highlight[spriteName].isShown = false;
		}
	},

	/**
	 * Invoke the sprite name editor.
	 *
	 * @param	string	Sprite Name Key
	 * @return	void
	 */
	showSpriteNameEditor: function(spriteName) {
		var spriteData = this.spriteNames[spriteName];
		var oldSpriteName = this.currentlyEditing;

		this.currentlyEditing = spriteName;
		this.highlightSpriteName(oldSpriteName, false);

		this.highlightSpriteName(spriteName, true);

		this.updateSpritePreview(spriteData.tag);

		$("#named_sprite_editor").css('left', this.highlight[spriteName].object.x + (this.highlight[spriteName].object.width / 2) - 35).css('top', this.highlight[spriteName].object.y + this.highlight[spriteName].object.height + 19);
		$("#named_sprite_editor input[name='update_sprite_name']").val(spriteData.name);
		$("#named_sprite_editor").show();
	},

	/**
	 * Parse and prepare values for usage.
	 *
	 * @return	void
	 */
	parseValues: function() {
		var columns = Math.abs(parseInt($('#sprite_columns').val()));
		var rows = Math.abs(parseInt($('#sprite_rows').val()));
		var inset = Math.abs(parseInt($('#sprite_inset').val()));

		this.values = {
			columns: columns,
			rows: rows,
			inset: inset
		};

		if ($("input[name='old_spritesheet_id']").length) {
			var oldColumns = Math.abs(parseInt($('#old_sprite_columns').val()));
			var oldRows = Math.abs(parseInt($('#old_sprite_rows').val()));
			var oldInset = Math.abs(parseInt($('#old_sprite_inset').val()));

			this.oldValues = {
				columns: oldColumns,
				rows: oldRows,
				inset: oldInset
			};
		}
	},

	/**
	 * Returns the values to generate the sprite sheet.
	 *
	 * @return	object	Sprite Sheet Values
	 */
	getValues: function() {
		return this.values;
	},

	/**
	 * Show the progress indicator over top of the canvas.
	 *
	 * @return	void
	 */
	showProgressIndicator: function() {
		if (!this.progressIndicator) {
			this.progressIndicator = this.canvas.display.ellipse({
				x: this.canvas.width / 2,
				y: this.canvas.height / 2,
				radius: 40,
				stroke: "6px linear-gradient(360deg, #000, #fff)"
			});
		}

		this.canvas.addChild(this.progressIndicator);

		this.canvas.setLoop(function () {
			mw.spriteSheet.progressIndicator.rotation = mw.spriteSheet.progressIndicator.rotation + 10;
		}).start();
	},

	/**
	 * Hide the progress indicator over top of the canvas.
	 *
	 * @return	void
	 */
	hideProgressIndicator: function() {
		this.canvas.removeChild(this.progressIndicator);
	},

	/**
	 * Update the parser tag example block.
	 *
	 * @return	void
	 */
	updateSpriteTagExample: function() {
		if (isNaN(this.values.columns) || isNaN(this.values.rows) || this.values.columns < 1 || this.values.rows < 1) {
			return;
		}

		var columnWidth = this.canvas.width / this.values.columns;
		var rowHeight = this.canvas.height / this.values.rows;

		if (this.canvas.touch.canvasFocused) {
			var xPixel = this.canvas.touch.x;
			var yPixel = this.canvas.touch.y;
		} else {
			var xPixel = this.canvas.mouse.x;
			var yPixel = this.canvas.mouse.y;
		}

		var xPos = Math.floor(xPixel / columnWidth);
		var yPos = Math.floor(yPixel / rowHeight);
		var title = $("input[name='page_title']").val();

		this.selectedSprite.xPos = xPos;
		this.selectedSprite.yPos = yPos;

		var example = "{{#sprite:file="+title+"|column="+xPos+"|row="+yPos+"}}";

		//Update the preview.
		this.updateSpritePreview(example);

		//Add a selector for this sprite block.
		this.selector.x = (columnWidth * xPos) + this.values.inset;
		this.selector.y = (rowHeight * yPos) + this.values.inset;
		this.selector.width = columnWidth - (this.values.inset * 2);
		this.selector.height = rowHeight - (this.values.inset * 2);
		this.canvas.redraw();

		$('button#save_named_sprite').html(mw.message('save_named_sprite').escaped());
		this.selectedType = 'sprite';

		$('#named_sprite_add').show();
		$("input[name='sprite_name']").focus().select();
	},

	/**
	 * Update the parser tag example block.
	 *
	 * @return	void
	 */
	updateSliceTagExample: function() {
		if (this.selector.width == 0 || this.selector.height == 0) {
			return;
		}

		if (this.selector.width < 0) {
			var x = this.selector.x + this.selector.width;
			var width = Math.abs(this.selector.width);
		} else {
			var x = this.selector.x;
			var width = this.selector.width;
		}
		var xPercent = ((x / this.canvas.width) * 100).toFixed(2);
		var widthPercent = ((width / this.canvas.width) * 100).toFixed(2);

		if (this.selector.height < 0) {
			var y = this.selector.y + this.selector.height;
			var height = Math.abs(this.selector.height);
		} else {
			var y = this.selector.y;
			var height = this.selector.height;
		}
		var yPercent = ((y / this.canvas.height) * 100).toFixed(2);
		var heightPercent = ((height / this.canvas.height) * 100).toFixed(2);

		var title = $("input[name='page_title']").val();

		this.selectedSlice.xPercent = xPercent;
		this.selectedSlice.yPercent = yPercent;
		this.selectedSlice.widthPercent = widthPercent;
		this.selectedSlice.heightPercent = heightPercent;

		var example = "{{#slice:file="+title+"|x="+xPercent+"|y="+yPercent+"|width="+widthPercent+"|height="+heightPercent+"}}";

		this.updateSpritePreview(example);

		$('button#save_named_sprite').html(mw.message('save_named_slice').escaped());
		this.selectedType = 'slice';

		$('#named_sprite_add').show();
		$("input[name='sprite_name']").focus().select();
	},

	/**
	 * Start selection of a canvas area.
	 *
	 * @return	void
	 */
	startSelection: function () {
		this.setupSelector(this.canvas.mouse.x, this.canvas.mouse.y);

		this.selector.timeline = this.canvas.setLoop(function () {
			var width = mw.spriteSheet.canvas.mouse.x - mw.spriteSheet.selector.x;
			var height = mw.spriteSheet.canvas.mouse.y - mw.spriteSheet.selector.y;

			if (mw.spriteSheet.mouseDrag == true) {
				mw.spriteSheet.selector.width = width;
				mw.spriteSheet.selector.height = height;
				mw.spriteSheet.canvas.redraw();
			}
		}).start();
	},

	/**
	 * Stop selection of a canvas area.
	 *
	 * @return	void
	 */
	stopSelection: function () {
		this.selector.timeline.stop();
		if (this.mouseDrag == true) {
			this.updateSliceTagExample();
		} else {
			this.updateSpriteTagExample();
		}
	},

	/**
	 * Setup the canvas selector.
	 *
	 * @param	integer	X Position
	 * @param	integer	Y Position
	 * @return	void
	 */
	setupSelector: function (x, y) {
		if (mw.spriteSheet.selector !== null) {
			mw.spriteSheet.canvas.removeChild(mw.spriteSheet.selector);
		}
		this.selector = this.canvas.display.rectangle({
			x: x,
			y: y,
			origin: {x: "top", y: "left"},
			width: 0,
			height: 0,
			fill: "rgba(195, 223, 253, 0.5)",
			stroke: "inside 1px rgba(195, 223, 253, 0.5)"
		});
	}
}

$(document).ready(function() {
	if ($("input[name='old_spritesheet_id']").length) {
		mw.spriteSheet.toggleEditor();
	}

	$("#spritesheet_toc").on('click tap', function () {
		mw.spriteSheet.toggleEditor();
	});
});