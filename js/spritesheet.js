mw.spriteSheet = {
	canvas: null,
	values: {},
	selector: null,
	mouseDrag: false,

	/**
	 * Initialize the sprite sheet.
	 *
	 * @return	void
	 */
	initialize: function() {
		if (this.canvas === null) {
			$('#spritesheet').remove();
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
		});

		this.canvas.bind('mouseup', function() {
			mw.spriteSheet.canvas.unbind('mousemove');
			mw.spriteSheet.stopSelection();
			mw.spriteSheet.mouseDrag = false;
		});

		$('#sprite_columns').on('change keyup', function() {
			mw.spriteSheet.update();
		}).change();

		$('#sprite_rows').on('change keyup', function() {
			mw.spriteSheet.update();
		}).change();

		$('#sprite_inset').on('change keyup', function() {
			mw.spriteSheet.update();
		}).change();

		$('#sprite_save').on('click tap', function() {
			mw.spriteSheet.save();
		});
	},

	/**
	 * Update the canvas object containing the sprite sheet.
	 *
	 * @return	void
	 */
	update: function() {
		this.canvas.reset();

		this.parseValues();

		if (!this.values.inset) {
			inset = 1;
		} else {
			inset = this.values.inset * 2;
		}

		if (isNaN(this.values.columns) || isNaN(this.values.rows) || this.values.columns < 1 || this.values.rows < 1) {
			return;
		}

		var columnWidth = this.canvas.width / this.values.columns;
		var rowHeight = this.canvas.height / this.values.rows;

		for (var i = 0; i <= this.values.columns; i++) {
			var x = i * columnWidth;

			var rectangle = this.canvas.display.rectangle({
				x: x - (Math.floor(inset / 2)),
				y: 0,
				width: inset,
				height: this.canvas.height,
				fill: "rgba(215, 0, 0, 0.5)",
				stroke: "inside 1px rgba(215, 215, 215, 1)"
			});
			this.canvas.addChild(rectangle);
		}

		for (var i = 0; i <= this.values.rows; i++) {
			var y = i * rowHeight;

			var rectangle = this.canvas.display.rectangle({
				x: 0,
				y: y - (Math.floor(inset / 2)),
				width: this.canvas.width,
				height: inset,
				fill: "rgba(215, 0, 0, 0.5)",
				stroke: "inside 1px rgba(215, 215, 215, 1)"
			});
			this.canvas.addChild(rectangle);
		}
	},

	/**
	 * Save the sprite sheet back to the server.
	 *
	 * @return	boolean
	 */
	save: function() {
		var api = new mw.Api();

		mw.spriteSheet.showProgressIndicator();
		api.post(
			{
				action: 'spritesheet',
				do: 'save',
				format: 'json',
				form: $('form#spritesheet_editor').serialize()
			}
		).done(
			function(result) {
				if (result.success != true) {
					alert(result.message);
				}
				mw.spriteSheet.hideProgressIndicator();
			}
		);
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

		var example = "{{#sprite:"+title+"|"+xPos+"|"+yPos+"}}";

		$('#sprite_preview').html(example);
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

		var example = "{{#slice:"+title+"|"+xPercent+"|"+yPercent+"|"+widthPercent+"|"+heightPercent+"}}";

		$('#sprite_preview').html(example);
	},

	/**
	 * Start selection of a canvas area.
	 *
	 * @return	void
	 */
	startSelection: function () {
		if (this.selector !== null) {
			this.canvas.removeChild(this.selector);
		}
		this.selector = this.canvas.display.rectangle({
			x: this.canvas.mouse.x,
			y: this.canvas.mouse.y,
			origin: {x: "top", y: "left"},
			width: 0,
			height: 0,
			fill: "rgba(195, 223,253, 0.5)",
			stroke: "inside 1px rgba(195, 223,253, 0.5)"
		});
		this.canvas.addChild(this.selector);

		this.selector.timeline = this.canvas.setLoop(function () {
			var width = mw.spriteSheet.canvas.mouse.x - mw.spriteSheet.selector.x;
			var height = mw.spriteSheet.canvas.mouse.y - mw.spriteSheet.selector.y;

			mw.spriteSheet.selector.width = width;
			mw.spriteSheet.selector.height = height;
			mw.spriteSheet.canvas.redraw();
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
	}
}

$(document).ready(function() {
	mw.spriteSheet.initialize();
});