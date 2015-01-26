mw.spriteSheet = {
	canvas: null,

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

		$('#sprite_columns').on('change keyup', function () {
			mw.spriteSheet.updateSpriteSheet();
		});

		$('#sprite_rows').on('change keyup', function () {
			mw.spriteSheet.updateSpriteSheet();
		});

		$('#sprite_inset').on('change keyup', function () {
			mw.spriteSheet.updateSpriteSheet();
		});
	},

	updateSpriteSheet: function() {
		this.canvas.reset();

		var columns = Math.abs($('#sprite_columns').val());
		var rows = Math.abs($('#sprite_rows').val());
		var inset = Math.abs($('#sprite_inset').val());

		if (!inset) {
			inset = 1;
		} else {
			inset = inset * 2;
		}

		if (columns == 0 || rows == 0) {
			return;
		}

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
				stroke: "inside 1px rgba(215, 215, 215, 1)"
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
				stroke: "inside 1px rgba(215, 215, 215, 1)"
			});
			this.canvas.addChild(rectangle);
		}
	},
}

$(document).ready(function() {
	mw.spriteSheet.initialize();
});