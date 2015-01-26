mw.spriteSheet = {
	canvas: null,
	values: {},


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

		$('#file > a').css({
			display: 'inline-block',
			position: 'relative'
		});

		$(spritesheet).appendTo('#file > a');

		this.canvas = oCanvas.create({ canvas: "#spritesheet", background: "rgba(0, 0, 0, 0)" });

		$('#sprite_columns').on('change keyup', function() {
			mw.spriteSheet.updateSpriteSheet();
		});

		$('#sprite_rows').on('change keyup', function() {
			mw.spriteSheet.updateSpriteSheet();
		});

		$('#sprite_inset').on('change keyup', function() {
			mw.spriteSheet.updateSpriteSheet();
		});
	},

	/**
	 * Update the canvas object containing the sprite sheet.
	 *
	 * @return	void
	 */
	updateSpriteSheet: function() {
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
	}
}

$(document).ready(function() {
	mw.spriteSheet.initialize();
});