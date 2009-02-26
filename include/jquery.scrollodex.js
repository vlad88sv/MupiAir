/* -------------------------------------------------- *
 * Scrollodex 1.0
 * Updated: 9/15/08
 * -------------------------------------------------- *
 * Author: Aaron Kuzemchak
 * URL: http://aaronkuzemchak.com/
 * Copyright: 2008 Aaron Kuzemchak
 * License: MIT License
 * -------------------------------------------------- *
 * Requires jQuery 1.2.6+, jQuery UI Slider control
** -------------------------------------------------- */


// $ aliasing
(function($) {

	// setup the plugin
	$.fn.scrollodex = function(settings) {
	
		// default settings
		var settings = $.extend({
			maxRows: 5, // maximum rows to show
			colWidths: null, // optional array of column widths
			altClass: null, // class to assign alternate rows for striping
			matchHeight: false // makes all rows equal height
		}, settings);
		
		// for each object requested
		return this.each(function() {
		
			// setup any variables
			var $table = $(this);
			var totalRows = $("tbody tr", $table).length;
			var rowHeight = 0;
			
			// if altClass is specified, stripe the table
			if(settings.altClass) {
			
				$("tbody tr:odd", $table).addClass(settings.altClass);
			}
			
			// if colWidths specified, add them to table headers
			if(settings.colWidths) {
			
				$("thead th", $table).each(function(i) {
				
					$(this).attr("width", settings.colWidths[i]);
				});
			}
			
			// if matchHeight is true, loop through all rows and get the tallest height
			// then set all rows to that height
			if(settings.matchHeight) {
			
				$("tbody tr", $table).each(function() {
				
					if($(this).height() > rowHeight) {
					
						rowHeight = $(this).height();
					}
				});
				$("tbody tr td", $table).attr("height", rowHeight);
			}
			
			// now that the cosmetic stuff is done, let's make sure the table is long enough
			// if the table has less rows than maxRows, exit the script
			if(totalRows <= settings.maxRows) { return false; }
			
			// ok, now to the fun stuff...
			// hide table rows
			$("tbody tr:gt(" + (settings.maxRows - 1) + ")", $table).hide();
			
			// add the slider right after the table
			var $slider = $('<div class="scrollBar">\
								<div class="scrollPrev">\
									<a href="#"></a>\
								</div>\
								<div class="scrollArea">\
									<div class="scrollHandle"></div>\
								</div>\
								<div class="scrollNext">\
									<a href="#"></a>\
								</div>\
							</div>');
			$table.after($slider);
			
			// let's make the slider come alive
			$(".scrollArea", $slider).slider({
				handle: ".scrollHandle",
				min: 0,
				max: (totalRows - settings.maxRows),
				startValue: 0,
				slide: function(e, ui) {
				
					scrollTheTable(ui.value, settings.maxRows, $table);
				}
			});
			
			// let's make the buttons work with the scroller
			// previous button
			$(".scrollPrev a", $slider).click(function() {
			
				$(".scrollArea", $slider).slider("moveTo", "-=1");
				return false;
			});
			// next button
			$(".scrollNext a", $slider).click(function() {
			
				$(".scrollArea", $slider).slider("moveTo", "+=1");
				return false;
			});
		});
	}
	
	// scrolls the table
	function scrollTheTable(scrollValue, maxRows, $theTable) {
	
		// hide rows outside of range, show rows in range
		$("tbody tr", $theTable).filter(":lt(" + scrollValue + ")").hide().end()
			.filter(":gt(" + (scrollValue + maxRows - 1) + ")").hide().end()
			.slice(scrollValue, (scrollValue + maxRows)).show();
	}
})(jQuery);