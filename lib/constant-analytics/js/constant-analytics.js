/**
 * ccStats.js
 * 
 * Javascript for Constant Contact's Constant Analytics WordPress plugin. 
 * Originally developed by Crowd Favorite for MailChimp; 
 * Adapted by Katz Web Services, Inc. in accordance with the GPL v2 License.
 */
;(function($) {
	window.ccStats = {};

	if (!Function.prototype._cfBind) {
		Function.prototype._cfBind = function(obj) {
			var f = this;
			return (function() {
				return f.apply(obj, arguments);
			});
		};
	}

	if (!Array.prototype.indexOf) {
		Array.prototype.indexOf = function(elt /*, from*/) {
			var len = this.length;
			var from = Number(arguments[1]) || 0;
			from = (from < 0) ? Math.ceil(from) : Math.floor(from);
			if (from < 0)
				from += len;

			for (; from < len; from++) {
				if (from in this && this[from] === elt) return from;
			}
			return -1;
		};
	}

	if (!String.prototype.commaize && !Number.prototype.commaize) {
		String.prototype.commaize = Number.prototype.commaize = function() {
			nStr = this + '';
			x = nStr.split('.');
			x1 = x[0];
			x2 = x.length > 1 ? '.' + x[1] : '';
			var rgx = /(\d+)(\d{3})/;
			while (rgx.test(x1)) {
				x1 = x1.replace(rgx, '$1' + ',' + '$2');
			}
			return x1 + x2;
		};
	}

	ccStats.displayDates = {
		start: null,
		end: null,
		lastStart: null,
		lastEnd: null
	};
	
	ccStats.renderers = {
		vml: {
			setFillColor: function(element, color) {
				if($().prop) {
					$(element).prop('fillcolor', color);
				} else {
					$(element).attr('fillcolor', color);
				}
			},
			setStrokeColor: function(element, color) {
				$(element).get(0).stroked = true;
				$(element).get(0).strokecolor = color;
			},
			setStrokeWeight: function(element, weight) {
				$(element).get(0).stroked = true;
				$(element).get(0).strokeweight = weight;
			},
			setRadius: function(element, radius) {
				$(element).css({ width: (radius * 2), height: (radius * 2) });
			},
			setY: function(element, value) {
				$(element).css('top', value);
			},
			getDimensions: function(element) {
				return { width: parseFloat($(element).css('width')), height: parseFloat($(element).css('height')) };
			},
			getOffset: function(element) {
				return { top: parseFloat($(element).css('top')), left: parseFloat($(element).css('left')) };
			},
			getCenterOffset: function(circle) {
				var o = ccStats.gfx.getOffset(circle);
				var d = ccStats.gfx.getDimensions(circle);
				return { top: o.top + (d.height / 2), left: o.left + (d.width / 2) };
			},
			setAttribute: function(element, name, value) {
				$(element).get(0)[name] = value;
			},
			getAttribute: function(element, name) {
				return $(element).get(0)[name];
			},

			circleName: 'oval',
			renderer: 'vml'
		},
		svg: {
			setFillColor: function(element, color) {
				if($().prop) {
					$(element).prop('fill', color);
				} else {
					$(element).attr('fill', color);
				}
			},
			setStrokeColor: function(element, color) {
				if($().prop) {
					$(element).prop('stroke', color);
				} else {
					$(element).attr('stroke', color);
				}
			},
			setStrokeWeight: function(element, weight) {
				if($().prop) {
					$(element).prop('stroke-width', weight);
				} else {
					$(element).attr('stroke-width', weight);
				}
			},
			setRadius: function(element, radius) {
				if($().prop) {
					$(element).prop('r').baseVal.value = radius;
				} else {
					$(element).attr('r').baseVal.value = radius;
				}
			},
			setY: function(element, value) {
				if($().prop) {
					$(element).prop('cy').baseVal.value = value;
				} else {
					$(element).attr('cy').baseVal.value = value;
				}
			},
			getDimensions: function(element) {
				return { width: parseFloat($(element).get(0).getAttribute('width')), height: parseFloat($(element).get(0).getAttribute('height')) };
			},
			getOffset: function(element) {
				return { top: parseFloat($(element).get(0).getAttribute('y')), left: parseFloat($(element).get(0).getAttribute('x')) };
			},
			getCenterOffset: function(circle) {
				return { top: parseFloat(ccStats.gfx.getAttribute(circle, 'cy')), left: parseFloat(ccStats.gfx.getAttribute(circle, 'cx'))  };
			},
			setAttribute: function(element, name, value) {
				$(element).get(0).setAttribute(name, value);
			},
			getAttribute: function(element, name) {
				return $(element).get(0).getAttribute(name);
			},
			circleName: 'circle',
			renderer: 'svg'
		}
	};
	
	// Will be one of the renderer objects above once we determine which 
	// engine we're working with.
	ccStats.gfx = null;
	
	ccStats.easeOut = function (frame, start, delta, nFrames) {
		return -delta * (frame /= nFrames) * (frame - 2) + start;
	};
	
	ccStats.animateLineChart = function(startCoords, endCoords, frame, nFrames) {
		var chartLine = null;
		var chartFill = null;
		
		if (ccStats.gfx.renderer == 'svg') {
			var chartLine = $('path[stroke=#92BCD0]');
			var chartFill = $('path[fill=#92BCD0]');
		}
		else {
			var nShapes = $('shape').size();
			var chartLine = $('shape:eq(' + (nShapes - 1) + ') path');
			var chartFill = $('shape:eq(' + (nShapes - 2) + ') path');
		}

		var interpValues = $.map(startCoords, function(coord, i) {
			return { 
				x: coord.x, 
				y: ccStats.easeOut(frame, startCoords[i].y, endCoords[i].y - startCoords[i].y, nFrames)
			};
		});
		ccStats.lineChart.jqCircles.each(function(index) {
			ccStats.gfx.setY(this, (ccStats.gfx.renderer == 'svg' ? 
				interpValues[index].y : 
				Math.round(interpValues[index].y - (ccStats.gfx.getDimensions(this).height / 2)))
			);
		});
		
		if (ccStats.gfx.renderer == 'svg') {
			var d = 'M' + interpValues[0].x + ',' + interpValues[0].y;
			$.each(interpValues, function(i, coord) {
				d += 'L' + coord.x + ',' + coord.y;
			});

			ccStats.gfx.setAttribute(chartLine, 'd', d);
			d += 'L' + interpValues[interpValues.length - 1].x + ',' + (ccStats.lineChart.height + ccStats.lineChart.offsetY);
			d += 'L' + interpValues[0].x + ',' + (ccStats.lineChart.height + ccStats.lineChart.offsetY);
			ccStats.gfx.setAttribute(chartFill, 'd', d);
		}
		else {
			// vml freaks out if you give it floats
			var d = 'm ' + interpValues[0].x + ',' + Math.round(interpValues[0].y);
			d += ' l ';
			$.each(interpValues, function(i, coord) {
				d += coord.x + ',' + Math.round(coord.y) + ' ';
			});

			ccStats.gfx.setAttribute(chartLine, 'v', d + ' e');
			d += interpValues[interpValues.length - 1].x + ',' + (ccStats.lineChart.height + ccStats.lineChart.offsetY) + ' ';
			d += interpValues[0].x + ',' + (ccStats.lineChart.height + ccStats.lineChart.offsetY);
			ccStats.gfx.setAttribute(chartFill, 'v', d + ' x');
		}
	};
	
	ccStats.handleDashboardReady = function() {
		var jqCCSTATS = $(ccStats);
		jqCCSTATS.bind('fetchingGAVisits', function() { ccStats.setHeaderStatus($('#ccStats-box-site-traffic'), 'loading', 'Fetching Google Analytics Visits'); });
		jqCCSTATS.bind('gaVisitsFetched', function() { ccStats.setHeaderStatus($('#ccStats-box-site-traffic'), 'normal'); });
		jqCCSTATS.bind('gaVisitsFetchFailed', function(event, error) { ccStats.addHeaderError($('#ccStats-box-site-traffic'), 'Could not fetch visit data: ' + error); });
		jqCCSTATS.bind('gaVisitsFetched', ccStats.updateVisitsChart._cfBind(ccStats));

		jqCCSTATS.bind('mediumFilterChanged', ccStats.updateVisitStats._cfBind(ccStats));

		// geo map
		jqCCSTATS.bind('fetchingGAGeo', function() { ccStats.setHeaderStatus($('#ccStats-box-traffic-by-region'), 'loading'); });
		jqCCSTATS.bind('gaGeoFetched', function() { ccStats.setHeaderStatus($('#ccStats-box-traffic-by-region'), 'normal'); });
		jqCCSTATS.bind('gaGeoFetchFailed', function(event, error) { ccStats.addHeaderError($('#ccStats-box-traffic-by-region'), 'Could not fetch geo data: ' + error); });
		jqCCSTATS.bind('gaGeoFetched', ccStats.updateMap._cfBind(ccStats));
		
		// pie chart
		jqCCSTATS.bind('fetchingGAReferralMedia', function() { ccStats.setHeaderStatus($('#ccStats-box-referring-traffic-overview'), 'loading'); });
		jqCCSTATS.bind('gaReferralMediaFetched', function() { ccStats.setHeaderStatus($('#ccStats-box-referring-traffic-overview'), 'normal'); });
		jqCCSTATS.bind('gaReferralMediaFetchFailed', function(event, error) { ccStats.addHeaderError($('#ccStats-box-referring-traffic-overview'), 'Could not fetch referral data: ' + error); });
		jqCCSTATS.bind('gaReferralMediaFetched', ccStats.updateReferralMediumChart._cfBind(ccStats));
	
		// wp posts
		jqCCSTATS.bind('fetchingWPPosts', function() { ccStats.setHeaderStatus($('#ccStats-box-site-traffic'), 'loading', 'Fetching Blog Post Dates'); });
		jqCCSTATS.bind('wpPostsFetched', function() { ccStats.setHeaderStatus($('#ccStats-box-site-traffic'), 'normal'); });
		jqCCSTATS.bind('wpPostsFetched', function() { $('#ccStats-linechart-legend li.blog-post').show('fast'); });
		jqCCSTATS.bind('wpPostsFetchFailed', function(event, error) { ccStats.addHeaderError($('#ccStats-box-site-traffic'), 'Could not fetch WordPress post data: ' + error); });
		jqCCSTATS.bind('wpPostsFetched', ccStats.updateVisitsChartWithPosts._cfBind(ccStats));

		// cc campaigns
		jqCCSTATS.bind('fetchingMCCampaigns', function() { ccStats.setHeaderStatus($('#ccStats-box-site-traffic'), 'loading', 'Fetching Constant Contact Campaigns'); });
		jqCCSTATS.bind('mcCampaignsFetched', function() { ccStats.setHeaderStatus($('#ccStats-box-site-traffic'), 'normal'); });
		jqCCSTATS.bind('mcCampaignsFetched', function() { $('#ccStats-linechart-legend li.campaign').show('fast'); });
		jqCCSTATS.bind('mcCampaignsFetchFailed', function(event, error) { ccStats.addHeaderError($('#ccStats-box-site-traffic'), 'Could not fetch Constant Contact campaign data: ' + error); });
		jqCCSTATS.bind('mcCampaignsFetched', ccStats.updateVisitsChartWithCampaigns._cfBind(ccStats));
			

		// top referrals table
		jqCCSTATS.bind('fetchingGATopReferrals', function() { ccStats.setHeaderStatus($('#ccStats-box-top-referrers'), 'loading'); });
		jqCCSTATS.bind('gaTopReferralsFetched', function() { ccStats.setHeaderStatus($('#ccStats-box-top-referrers'), 'normal'); });
		jqCCSTATS.bind('gaTopReferralsFetchFailed', function(event, error) { ccStats.addHeaderError($('#ccStats-box-top-referrers'), 'Could not fetch referral data: ' + error); });
		jqCCSTATS.bind('gaTopReferralsFetched', ccStats.updateTopReferrersChart._cfBind(ccStats));
		
		jqCCSTATS.bind('fetchingGAKeywords', function() { ccStats.setHeaderStatus($('#ccStats-box-top-referrers'), 'loading'); });
		jqCCSTATS.bind('gaKeywordsFetched', function() { ccStats.setHeaderStatus($('#ccStats-box-top-referrers'), 'normal'); });
		jqCCSTATS.bind('gaKeywordsFetchFailed', function(event, sourceName, error) { ccStats.addHeaderError($('#ccStats-box-top-referrers'), 'Could not fetch keyword data: ' + error); });
		jqCCSTATS.bind('gaKeywordsFetched', ccStats.handleKeywordsFetched._cfBind(ccStats));
		
		jqCCSTATS.bind('fetchingGAEmailReferrals', function() { ccStats.setHeaderStatus($('#ccStats-box-top-referrers'), 'loading'); });
		jqCCSTATS.bind('gaEmailReferralsFetched', function() { ccStats.setHeaderStatus($('#ccStats-box-top-referrers'), 'normal'); });
		jqCCSTATS.bind('gaEmailReferralsFetchFailed', function(event, sourceName, error) { ccStats.addHeaderError($('#ccStats-box-top-referrers'), 'Could not fetch email referral data: ' + error); });
		jqCCSTATS.bind('gaEmailReferralsFetched', ccStats.handleEmailReferralsFetched._cfBind(ccStats));

		
		jqCCSTATS.bind('fetchingGAReferralPaths', function() { ccStats.setHeaderStatus($('#ccStats-box-top-referrers'), 'loading'); });
		jqCCSTATS.bind('gaReferralPathsFetched', function() { ccStats.setHeaderStatus($('#ccStats-box-top-referrers'), 'normal'); });
		jqCCSTATS.bind('gaReferralPathsFetchFailed', function(event, sourceName, error) { ccStats.addHeaderError($('#ccStats-box-top-referrers'), 'Could not fetch referral paths: ' + error); });
		jqCCSTATS.bind('gaReferralPathsFetched', ccStats.handleReferralPathsFetched._cfBind(ccStats));
		
		// top content table
		jqCCSTATS.bind('fetchingGATopContent', function() { ccStats.setHeaderStatus($('#ccStats-top-content'), 'loading'); });
		jqCCSTATS.bind('gaTopContentFetched', function() { ccStats.setHeaderStatus($('#ccStats-top-content'), 'normal'); });
		jqCCSTATS.bind('gaTopContentFetchFailed', function(event, error) { ccStats.addHeaderError($('#ccStats-top-content'), 'Could not fetch top content data: ' + error); });
		jqCCSTATS.bind('gaTopContentFetched', ccStats.updateTopContentChart._cfBind(ccStats));

		
		
		// display date change triggers other stuff
		jqCCSTATS.bind('displayDatesChanged', function(event, start, end) {
			ccStats.clearTableStack(jQuery('#ccStats-top-referrers'));
			ccStats.clearTableStack(jQuery('#ccStats-top-content'));
			ccStats.fetchGAVisits();
			ccStats.fetchGAGeo();
			ccStats.fetchGATopReferrals();
			ccStats.fetchGAReferralMedia();
			ccStats.fetchGATopContent();
		});
		
		var selectDateRange = function(start, end) {
			if (start.equals(ccStats.displayDates.start) && end.equals(ccStats.displayDates.end)) {
				return;
			}
			
			var jqdp = $('#ccStats-datepicker-calendars');
			jqdp.dpmmClearSelected();
			
			var s = new Date(start.valueOf());
			var d = new Date(end.valueOf());
			var nDays = 0;
			while (d.isAfter(start) || d.equals(start)) {				// this is a bit pricey
				jqdp.dpmmSetSelected(d.toString('dd/MM/yyyy'));
				d.addDays(-1);
				nDays++;
			}
			
			ccStats.displayDates.start = start;
			ccStats.displayDates.end = end;

			var startInputVal = new Date($('#ccStats-current-start-date input').val().replace(/-/g, '/'));
			var endInputVal = new Date($('#ccStats-current-end-date input').val().replace(/-/g, '/'));

			if (isNaN(startInputVal.valueOf()) || !(startInputVal.equals(start))) {
				$('#ccStats-datepicker-start-date').val(start.toString('yyyy-MM-dd'));
			}
			if (isNaN(endInputVal.valueOf()) || !(endInputVal.equals(end))) {
				$('#ccStats-datepicker-end-date').val(end.toString('yyyy-MM-dd'));
			}

			$('#ccStats-current-start-date span').html(start.toString('MMM dd, yyyy'));
			$('#ccStats-current-end-date span').html(end.toString('MMM dd, yyyy'));

			$('#ccStats-current-start-date input').val(start.toString('MMM dd, yyyy'));
			$('#ccStats-current-end-date input').val(end.toString('MMM dd, yyyy'));

			$('#ccStats-current-date-range-desc').html(nDays + ' day' + (nDays > 1 ? 's' : '') + ' selected');
			
			if(nDays > 307) {
				if($('#ccStats-date-range-exceeded').length == 0) {
					$('#ccStats-box-site-traffic').before('<div class="ccStats-box" style="display:none;" id="ccStats-date-range-exceeded"><div class="error"><p>The date range selected is larger than can be displayed in the chart (up to 307 days). <span class="dismiss" title="Dismiss this message">X</span></p></div></div>');
					$('#ccStats-date-range-exceeded').slideDown();
				}
			} else {
				$('#ccStats-date-range-exceeded').slideUp('fast').remove();
			}
		};

		var toggleDatePopup = function() {
			if ($('#ccStats-datepicker-popup').hasClass('open')) {
				$('#ccStats-datepicker-pane').slideUp();
				$('#ccStats-datepicker-popup').removeClass('open');

				$('#ccStats-current-date-range input').hide();
				$('#ccStats-current-date-range span').show();
				
				if (!ccStats.displayDates.lastStart || !ccStats.displayDates.lastEnd || 
					!ccStats.displayDates.lastStart.equals(ccStats.displayDates.start) || 
					!ccStats.displayDates.lastEnd.equals(ccStats.displayDates.end)
				) {
					ccStats.displayDates.lastStart = ccStats.displayDates.start;
					ccStats.displayDates.lastEnd = ccStats.displayDates.end;
					$(ccStats).trigger('displayDatesChanged', [ccStats.displayDates.start, ccStats.displayDates.end]);
				}
			}
			else {
				$('#ccStats-datepicker-popup').addClass('open');
				$('#ccStats-datepicker-pane').slideDown();

				$('#ccStats-current-date-range input').show();
				$('#ccStats-current-date-range span').hide();
			}
		};

		var buildDatepicker = function() {
			if (!$('#ccStats-datepicker-popup').data('datepicker')) {
				var dpmm = $('#ccStats-datepicker-calendars').datePickerMultiMonth({
					numMonths: 2,
					inline: true,
					selectMultiple: true,
					startDate: '01/01/2000',
					endDate: Date.today().toString('dd/MM/yyyy'),
					renderCallback: function(jqCell, date, m, y) {
						var element = jqCell.get(0);
						element.onselectstart = function() {
							return false;
						};
						element.unselectable = "on";
						element.style.MozUserSelect = "none";

						jqCell.click(function(event) {
							if (event.shiftKey) {
								if (date.isBefore(ccStats.displayDates.start)) {
									selectDateRange(date, ccStats.displayDates.end);
								}
								else if (date.isAfter(ccStats.displayDates.start)) {
									selectDateRange(ccStats.displayDates.start, date);
								}
								else if (date.between(ccStats.displayDates.start, ccStats.displayDates.end)) {
									if (date.valueOf() - ccStats.displayDates.start.valueOf() > ccStats.displayDates.end.valueOf() - date.valueOf()) {
										selectDateRange(date, ccStats.displayDates.end);
									}
									else {
										selectDateRange(ccStats.displayDates.start, date);
									}
								}
							}
							else {
								if (ccStats.displayDates.start.equals(ccStats.displayDates.end)) {
									selectDateRange(ccStats.displayDates.start, date);
								}
								else {
									selectDateRange(date, date);
								}
							}
						});
					}
				});
				$('#ccStats-datepicker-calendars').data('datepicker', dpmm);
			}
		};
		
		$('#ccStats-date-range-exceeded .dismiss').live('click', function() {
			$(this).parents('#ccStats-date-range-exceeded').slideUp(function() { $(this).remove(); });
		});
		
		$('#ccStats-datepicker-popup')
			.hover(function(){ $('#ccStats-datepicker-popup').addClass('hover'); }, function() { $('#ccStats-datepicker-popup').removeClass('hover'); })
			.click(function(event) {
				if (event.target.nodeName.toLowerCase() == 'input') {
					return true;
				}
				toggleDatePopup();
			});
		$('#ccStats-current-start-date input, #ccStats-current-end-date input').bind('blur', function() {
			clearTimeout($(this).data('idleTimer'));
			$(this).data('idleTimer', setTimeout((function() {
				var d = new Date($(this).val().replace(/-/g, '/'));
				if (isNaN(d.valueOf())) {
					$(this).addClass('invalid');
				}
				else {
					$(this).removeClass('invalid');
				}
				if ($('input.invalid', $(this).parent()).size() == 0) {
					selectDateRange(
						new Date($('#ccStats-current-start-date input').val().replace(/-/g, '/')), 
						new Date($('#ccStats-current-end-date input').val().replace(/-/g, '/'))
					);
				}
			})._cfBind(this), 500));
		});
		$('#ccStats-apply-date-range').click(function() {
			toggleDatePopup();
		});

		buildDatepicker();
		selectDateRange(Date.today().addMonths(-1), Date.today().addDays(-1));

		// kick it off
		jqCCSTATS.trigger('displayDatesChanged', [ccStats.displayDates.start, ccStats.displayDates.end]);
		
	};
		
	ccStats.updateVisitStats = function(event, medium) {
		
		$('#ccStats-stat-visits').html(ccStats.visitStats.mediumSummaries[medium].totalVisits.commaize());
		$('#ccStats-stat-pageviews').html(ccStats.visitStats.mediumSummaries[medium].totalPageviews.commaize());
		$('#ccStats-stat-pages-per-visit').html(ccStats.visitStats.mediumSummaries[medium].totalPagesPerVisit.commaize());
		$('#ccStats-stat-bounce-rate').html(ccStats.visitStats.mediumSummaries[medium].bounceRate);
		$('#ccStats-stat-time-on-site').html(ccStats.visitStats.mediumSummaries[medium].avgTimeOnSite);
		$('#ccStats-stat-new-visits').html(ccStats.visitStats.mediumSummaries[medium].percentNewVisits);
		$('.ccStats-stats-list').slideDown();

		$.each({
			'ccStats-stat-visits-spark': ccStats.visitStats.mediumSummaries[medium].visits,
			'ccStats-stat-pageviews-spark': ccStats.visitStats.mediumSummaries[medium].pageviews,
			'ccStats-stat-pages-per-visit-spark': ccStats.visitStats.mediumSummaries[medium].pagesPerVisit,
			'ccStats-stat-bounce-rate-spark': ccStats.visitStats.mediumSummaries[medium].bounceRates,
			'ccStats-stat-time-on-site-spark': ccStats.visitStats.mediumSummaries[medium].timeOnSite,
			'ccStats-stat-new-visits-spark': ccStats.visitStats.mediumSummaries[medium].newVisits
		}, function(id, filteredData) {
			var table = new google.visualization.DataTable();
			var spark = new google.visualization.ImageSparkLine($('#' + id).empty().get(0));
			table.addColumn('number', id);
			table.addRows(filteredData.length);
			$.each(filteredData, function(i, value) {
				table.setValue(i, 0, value);
			});
			spark.draw(table, { 
				width: 70, 
				height: 30, 
				showAxisLines: false, 
				showValueLabels: false, 
				labelPosition: 'none'
			});
		});
	};
		
	ccStats.updateMap = function(event, data) {
		var table = new google.visualization.DataTable();
		table.addColumn('string', 'Country');
		table.addColumn('number', 'Visits');
		var rows = [];
		$.each(data, function(i, datum) {
			rows.push({ country: datum.dimensions.country, visits: datum.metrics.visits });
		});
		table.addRows(rows.length);
		$.each(rows, function(i, row) {
			table.setValue(i, 0, row.country);
			table.setValue(i, 1, parseInt(row.visits, 10));
		});
		var geomap = new google.visualization.GeoMap($('#ccStats-geo-map').empty().get(0));
		geomap.draw(table, { dataMode: 'regions', width:'600px', colors: [0xC1D8EC, 0xD98E26] });
	};
		
	ccStats.updateReferralMediumChart = function(event, data) {
		var table = new google.visualization.DataTable();
		table.addColumn('string', 'Medium');
		table.addColumn('number', 'Visits');
		var rows = [];
		var media = {};
		var nMedia = 0;
		var totalVisits = 0;

		$.each(data, function(i, row) {
			
			//var medium = 'other';
			var medium = row.dimensions.medium;
			switch (row.dimensions.medium) {
				case '(none)':
				case '(not set)':
					medium = 'direct traffic';
				break;
				case 'referral':
					medium = 'referring traffic';
				break;
				case 'email':
					medium = 'email campaigns';
				break;
				case 'organic':
					medium = 'search engines';
				break;
			}
			
			if (media[medium]) {
				media[medium] += row.metrics.visits;
			}
			else {
				media[medium] = row.metrics.visits;
				nMedia++;
			}
			totalVisits += row.metrics.visits;
		});
		
		table.addRows(nMedia);
		
		var i = 0;
		var legendText = [];
		var colors = ['#5D83AD', '#91AFD1', '#BDD2EF', '#1b4065', '#777777', '#333333'];
		
		$.each(media, function(medium, visits) {
			table.setValue(i, 0, medium);
			table.setValue(i, 1, visits);
			legendText.push('\
				<li>\
					<div class="ccStats-color-swatch" style="background:' + colors[i] + '"></div><strong>' + medium + '</strong>\
					<div>' + visits.commaize() + ' (' + ((100 * visits / totalVisits).toPrecision(2)) + '%)</div>\
				</li>\
			');
			i++;
		});
		var d = $('#ccStats-box-referring-traffic-overview').width() * 0.45;
		$('#ccStats-referring-traffic-chart').width(d);//.css('padding-top', Math.min(d * .3, 40)); // Keep the pie chart in view
		var piechart = new google.visualization.PieChart($('#ccStats-referring-traffic-chart').empty().get(0));
		piechart.draw(table, { 
			is3D:true, 
			legend: 'none', 
			width: d, 
			height: d,
			legendFontSize:14,
			legendTextColor:'#777777',
			colors: colors 
		});
		$('#ccStats-referring-traffic-overview-legend').html('\
			<ul>\
				' + legendText.join("\n") + '\
			</ul>\
		').css('left', d + (d * .2) + 'px');
	};
		
	ccStats.updateTopReferrersChart = function(event, data) {
		var dataTable = new google.visualization.DataTable();
		dataTable.addColumn('string', 'Source/Medium');
		dataTable.addColumn('number', 'Visits');
		dataTable.addColumn('number', 'Pages/Visit');
		dataTable.addColumn('string', 'Avg. Time on Site');
		var rows = [];
		var media = {};
		var nRows = 0;
		//console.dir(data);
		// our code on the server runs a separate request to google for each medium
		$.each(data, function(medium, referrals) {
			if (!referrals || typeof referrals != 'object' || !('length' in referrals)) {
				return true;
			}
			$.each(referrals, function(i, row) {
				var key = row.dimensions.source + ' / ' + row.dimensions.medium;

				if (media[key]) {
					media[key].visits += row.metrics.visits;
					media[key].pagesPerVisit += row.metrics.visits ? (row.metrics.pageviews / row.metrics.visits) : 0;
					media[key].timeOnSite += row.metrics.timeOnSite;
				}
				else {
					media[key] = {};
					if (row.dimensions.medium == 'organic' || row.dimensions.medium == 'cpc') {
						media[key].markup = '<a title="Show Top Keywords from ' + row.dimensions.source.replace(/\"/, '\'') + '" href="javascript:ccStats.fetchGAKeywords(\'' + row.dimensions.source + '\');">' + row.dimensions.source + ' / ' + row.dimensions.medium + '</a>';
					}
					else if (row.dimensions.medium == 'referral') {
						media[key].markup = '<a title="Show Top Referring Paths from ' + row.dimensions.source.replace(/\"/, '\'') + '" href="javascript:ccStats.fetchGAReferralPaths(\'' + row.dimensions.source + '\');">' + row.dimensions.source + ' / ' + row.dimensions.medium + '</a>';
					}
					/*
else if (row.dimensions.medium == 'email') {
						media[key].markup = '<a title="Show Top Email Campaigns" href="javascript:ccStats.fetchGAEmailReferrals(\'' + row.dimensions.source + '\');">' + row.dimensions.source + ' / ' + row.dimensions.medium + '</a>';
					}
*/
					else {
						media[key].markup = '<a title="Show Top Referring Paths from ' + row.dimensions.source.replace(/\"/, '\'') + '" href="javascript:ccStats.fetchGAReferralPaths(\'' + row.dimensions.source + '\');">' + row.dimensions.source.replace(/\"/, '\'') + ' / ' + row.dimensions.medium + '</a>';
					}
					media[key].visits = row.metrics.visits;
					media[key].pagesPerVisit = row.metrics.visits ? (row.metrics.pageviews / row.metrics.visits) : 0;
					media[key].timeOnSite = row.metrics.timeOnSite;
					nRows++;
				}
			});
		});

		$.each(media, function(id, row) { row.timeOnSite = ccStats.secToDuration(row.visits ? Math.round((row.timeOnSite) / row.visits) : 0); });

		dataTable.addRows(nRows);
		var i = 0;
		$.each(media, function(medium, row) {
			dataTable.setValue(i, 0, row.markup);
			dataTable.setValue(i, 1, row.visits);
			dataTable.setValue(i, 2, parseFloat(row.pagesPerVisit.toPrecision(3)));
			dataTable.setValue(i, 3, row.timeOnSite);
			i++;
		});
		ccStats.pushTable(jQuery('#ccStats-top-referrers'), dataTable, 'Top Referrers');
	};
		
	ccStats.handleKeywordsFetched = function(event, data, sourceName) {
		var keywordData = new google.visualization.DataTable();
		keywordData.addColumn('string', 'Keyword');
		keywordData.addColumn('number', 'Pageviews');
		keywordData.addColumn('number', 'Unique Pageviews');
		keywordData.addColumn('string', 'Avg. Time on Page');
		
		keywordData.addRows(data.length);
		
		$.each(data, function(i, row) {
			keywordData.setValue(i, 0, row.dimensions.keyword);
			keywordData.setValue(i, 1, row.metrics.pageviews);
			keywordData.setValue(i, 2, row.metrics.uniquePageviews);
			
			keywordData.setValue(i, 3, 
				ccStats.secToDuration(Math.round(row.metrics.timeOnPage / (row.metrics.pageviews - row.metrics.exits)))
			);
		});
		
		ccStats.pushTable(jQuery('#ccStats-top-referrers'), keywordData, 'Keywords from ' + sourceName);
	};
	
	ccStats.handleReferralPathsFetched = function(event, data, sourceName) {
		var keywordData = new google.visualization.DataTable();
		keywordData.addColumn('string', 'Referring Page');
		keywordData.addColumn('number', 'Pageviews');
		keywordData.addColumn('number', 'Unique Pageviews');
		keywordData.addColumn('string', 'Avg. Time on Page');
		
		keywordData.addRows(data.length);
		
		$.each(data, function(i, row) {
			keywordData.setValue(i, 0, '<a class="ccStats-outgoing" href="http://' + row.dimensions.source + row.dimensions.referralPath + '">' + row.dimensions.referralPath + '</a>');
			keywordData.setValue(i, 1, row.metrics.pageviews);
			keywordData.setValue(i, 2, row.metrics.uniquePageviews);
			keywordData.setValue(i, 3, 
				ccStats.secToDuration((row.metrics.pageviews - row.metrics.exits) > 0 ? Math.round(row.metrics.timeOnPage / (row.metrics.pageviews - row.metrics.exits)) : 0)
			);
		});
		
		ccStats.pushTable(jQuery('#ccStats-top-referrers'), keywordData, 'Referring Paths from ' + sourceName);
	};
	ccStats.handleEmailReferralsFetched = function(event, data, sourceName) {
		var keywordData = new google.visualization.DataTable();
		keywordData.addColumn('string', 'Campaign');
		keywordData.addColumn('number', 'Pageviews');
		keywordData.addColumn('number', 'Unique Pageviews');
		keywordData.addColumn('string', 'Avg. Time on Page');
		
		keywordData.addRows(data.length);
		
		$.each(data, function(i, row) {
			keywordData.setValue(i, 0, row.dimensions.campaign);
			keywordData.setValue(i, 1, row.metrics.pageviews);
			keywordData.setValue(i, 2, row.metrics.uniquePageviews);
			keywordData.setValue(i, 3, 
				ccStats.secToDuration((row.metrics.pageviews - row.metrics.exits) > 0 ? Math.round(row.metrics.timeOnPage / (row.metrics.pageviews - row.metrics.exits)) : 0)
			);
		});
		
		ccStats.pushTable(jQuery('#ccStats-top-referrers'), keywordData, 'Email Campaigns');
	};
	
	ccStats.updateTopContentChart = function(event, data) {
		var contentData = new google.visualization.DataTable();
		contentData.addColumn('string', 'Page');
		contentData.addColumn('number', 'Pageviews');
		contentData.addColumn('number', 'Unique Pageviews');
		contentData.addColumn('string', 'Avg. Time on Page');
		
		contentData.addRows(data.length);
		
		var secs = 0;
		$.each(data, function(i, row) {
			contentData.setValue(i, 0, '<a class="ccStats-outgoing" href="' + row.dimensions.pagePath + '">' + row.dimensions.pagePath + '</a>');
			contentData.setValue(i, 1, row.metrics.pageviews);
			contentData.setValue(i, 2, row.metrics.uniquePageviews);
			if (row.metrics.pageviews - row.metrics.exits > 0) {
				secs = Math.round(row.metrics.timeOnPage / (row.metrics.pageviews - row.metrics.exits));
			}
			else {
				secs = 0;
			}
			contentData.setValue(i, 3, ccStats.secToDuration(secs));
		});

		ccStats.pushTable(jQuery('#ccStats-top-content'), contentData, 'Top Content');
	};
				
	ccStats.setMediumFilter = function(mediumFilter, tab) {
		ccStats.hideTooltip();
		ccStats.lineChart.drawFrame = $($('#ccStats-all-traffic-graph rect')[1]);
		ccStats.lineChart.height = ccStats.gfx.getDimensions(ccStats.lineChart.drawFrame).height;
		ccStats.lineChart.offsetY = ccStats.gfx.getOffset(ccStats.lineChart.drawFrame).top;
		
		var endCoords = [];
		var startCoords = [];
		var valueToY = function(value) {
			return ccStats.lineChart.height + ccStats.lineChart.offsetY - (
				(value - ccStats.lineChart.bottomLineValue) / (ccStats.lineChart.topLineValue - ccStats.lineChart.bottomLineValue) * ccStats.lineChart.height
			);
		};
		
		ccStats.lineChart.jqCircles.each(function(index) {
			var offset = ccStats.gfx.getCenterOffset(this);
			startCoords.push({ 
				x: offset.left, 
				y: offset.top 
			});
			endCoords.push({ 
				x: offset.left,
				y: valueToY(ccStats.lineChart.visitData.getProperty(index, 1, 'dayStats').getVisits(mediumFilter) || 0)
			});
		});
		
		tab = tab ? tab : $('#ccStats-' + mediumFilter.replace(/[^\w]/g, '-') +'-traffic-tab');
		if (tab && $(tab).size()) {
			$(tab).addClass('ccStats-selected').siblings().removeClass('ccStats-selected');
		}
		else {
			$('#ccStats-box-site-traffic ul.ccStats-tabs li:last').addClass('ccStats-selected').siblings().removeClass('ccStats-selected');
		}

		var frame = 0;
		var nFrames = 20;
		setTimeout(function() {
			if (frame <= nFrames) {
				setTimeout(arguments.callee, 25);
				ccStats.animateLineChart(startCoords, endCoords, frame++, nFrames);
			}
		}, 25);

		ccStats.visitStats.mediumFilter = mediumFilter;
		
		$(ccStats).trigger('mediumFilterChanged', [mediumFilter]);
	};
		
		
	ccStats.updateVisitsChart = function(event, data) {
		var visitData = new google.visualization.DataTable();
		visitData.addColumn('date', 'Date');
		visitData.addColumn('number', 'Visits');

		if (!data || data.length == 0) {
			visitData.addRows(1);
			visitData.setCell(0, 0, 'No Visit Data');
		} 
		else {
			visitData.addRows(ccStats.visitStats.nDays);

			i = 0;
			$.each(ccStats.visitStats.days, function(dateKey, day) {
				visitData.setCell(i, 0, day.date);
				visitData.setCell(i, 1, day.getVisits('all traffic'));
				visitData.setProperty(i, 1, 'dayStats', day);
				i++;
			});
		}

		$('#ccStats-box-site-traffic ul.ccStats-tabs').empty();
		$.each(ccStats.visitStats.mediumSummaries, function(name, summary) {
		//	if (['all traffic', 'cpc', 'email', 'organic', 'referral', '(none)'].indexOf(name) >= 0) {
				if(name == '(none)') { displayname = 'direct'; } else { displayname = name; }
				var tab = $('<li id="ccStats-' + name.replace(/[^\w]/g, '-') +'-traffic-tab">' + displayname + '</li>').click(function() {
					ccStats.setMediumFilter(name, this);
				});
				$('#ccStats-box-site-traffic ul.ccStats-tabs').prepend(tab);
		//	}
		});
		
		var tabWidth = 0;			// IE can't figure this bit out on its own; mostly harmless in other browsers.
		$('#ccStats-box-site-traffic ul.ccStats-tabs li').each(function() {
			var jq = $(this);
			tabWidth += jq.width() + 
				parseInt(jq.css('margin-left'), 10) + 
				parseInt(jq.css('margin-right'), 10) +
				parseInt(jq.css('padding-left'), 10) + 
				parseInt(jq.css('padding-right'), 10);
		});
		$('#ccStats-box-site-traffic ul.ccStats-tabs').width(tabWidth + 2);

		var dateFormatter = new google.visualization.DateFormat({formatType: 'medium'});
		dateFormatter.format(visitData, 0);
		
		ccStats.lineChart.visitData = visitData;

		$('#ccStats-all-traffic-graph').css('opacity', .1);
		var chart = new google.visualization.AreaChart($('#ccStats-all-traffic-graph').empty().get(0));
		
		google.visualization.events.addListener(chart, 'ready', function() {
			setTimeout(function() {
				
				var iframe = $('#ccStats-all-traffic-graph iframe').get(0);
				var iframeDoc = iframe.contentWindow || iframe.contentDocument;
				if (iframeDoc.document) {
					iframeDoc = iframeDoc.document;
				}
				
				ccStats.lineChart.iframeDoc = iframeDoc;

				var svg = $('svg', iframeDoc);
				var vml = $($('group', iframeDoc)[0]);
				ccStats.gfx = svg.size() ? ccStats.renderers.svg : ccStats.renderers.vml;
				var graphics = svg.size() ? svg.clone() : $(vml.html());
				$('#ccStats-all-traffic-graph').empty().append(graphics);

				ccStats.gfx.setStrokeColor($('#ccStats-all-traffic-graph rect:first'), '#ffffff');	// ie wub
				
				if (ccStats.gfx.renderer == 'svg') {
					
					var yAxisValues = $('text').map(function() {
						if (this.getAttribute('transform') !== null) {
							return null;
						}
						else {
							return parseFloat(this.childNodes[0].textContent.replace(/,/g, ''));
						}
					});
					ccStats.lineChart.topLineValue = yAxisValues[yAxisValues.length - 1];
					ccStats.lineChart.bottomLineValue = yAxisValues[0];
					
					// copy a background "all traffic" fill area
					var chartFill = $('path[fill=#92BCD0]');
					ccStats.gfx.setFillColor(chartFill.clone().insertBefore(chartFill), '#cfcfcf');
				}
				else {
					
					var yAxisValues = $('textpath').map(function() {
						if (this.string.indexOf(' ') > -1) {
							return null;
						}
						else {
							return parseFloat(this.string.replace(/,/g, ''));
						}
					});
					ccStats.lineChart.topLineValue = yAxisValues[yAxisValues.length - 1];
					ccStats.lineChart.bottomLineValue = yAxisValues[0];

					// @todo: get the background fill working in IE
					//var chartFill = $('shape:eq(' + ($('shape').size() - 2) + ')');
					//var clonedFill = chartFill.clone().insertBefore(chartFill);
					//$('fill', clonedFill).attr('color', '#cfcfcf').attr('id', 'blah');
				}
				

				var circles = ccStats.lineChart.jqCircles = $(ccStats.gfx.circleName, graphics);
				circles.each(function(index) {
					
					if($().prop) {
						$(this).prop('onclick', null);
					} else {
						$(this).attr('onclick', null);
					}

					$(this).hover(function() {
						ccStats.gfx.setStrokeWeight($(this), $(this).data('strokeWeight') + 1);
					}, function() {
						ccStats.gfx.setStrokeWeight($(this), $(this).data('strokeWeight'));
					});

					ccStats.gfx.setFillColor($(this), '#ffffff');
					ccStats.gfx.setStrokeColor($(this), '#92BCD0');
					ccStats.gfx.setStrokeWeight($(this).data('strokeWeight', 1), 1);
					ccStats.gfx.setRadius($(this).data('radius', 3), 3);

					$(this).click(function(event) {

						ccStats.hideTooltip();
						ccStats.gfx.setRadius($(this), $(this).data('radius') + 3);

						var extra = '';
						var postProp = ccStats.lineChart.visitData.getProperty(index, 0, 'post');
						if (postProp) {
							extra += '\
								<div class="ccStats-post-point-link">\
									<a href="' + postProp.guid + '"><strong>Post</strong>: ' + postProp.post_title.substring(0, 12) + '&hellip;</a>\
								</div>\
							';
						}
						var campaignProp = ccStats.lineChart.visitData.getProperty(index, 0, 'campaignSent');
						if (campaignProp) {
							extra += '\
								<div class="ccStats-campaign-point-link">\
									<a href="' + campaignProp.archive_url + '"><strong>Campaign</strong>: ' + campaignProp.title.substring(0, 12) + '&hellip;</a>\
								</div>\
							';
						}
						var visits = '';
						if (ccStats.visitStats.mediumFilter !== 'all traffic') {
							visits = (visitData.getProperty(index, 1, 'dayStats').getVisits(ccStats.visitStats.mediumFilter).commaize() || '0') + ' <span style="color:#aaaaaa">(out of ' + visitData.getValue(index, 1).commaize() + ')</span>';
						}
						else {
							visits = visitData.getValue(index, 1).commaize();
						}
						var content = '\
							<strong>' + visitData.getValue(index, 0).toString('dddd, MMM dd yyyy') + '</strong><br/>\
							' + extra + '\
							' + '<strong>Visits</strong>: ' + visits + '\
						';
						
						ccStats.renderTooltip(event, $('#ccStats-all-traffic-graph'), content);

						return false;
					});
				});

				$('#ccStats-all-traffic-graph').show();

				// now fetch posts and campaigns for overlay
				ccStats.fetchWPPosts();
//				if (ccStats.mcAPIKey.length) {
					ccStats.fetchMCCampaigns();
//				}
				$('#ccStats-all-traffic-graph').animate({ opacity: 1 }, function() {
					ccStats.setMediumFilter(ccStats.visitStats.mediumFilter);
				});
			}, 500);

		});
		chart.draw(visitData, {
			width: '100%', 
			height: 300, 
			legend: 'none', 
			title: '',
			backgroundColor: { stroke: null, strokeSize: 0, fill:'#ffffff' },
			borderColor: '#92BCD0',
			colors: ['#92BCD0'],
			axisFontSize: 11,
			enableTooltip: false,
			min: 0
		});

	};
		
	ccStats.updateVisitsChartWithPosts = function(event, data) {
		$.each(data, function(i, post) {
			if (post.post_date) {
				var postDate = new Date(post.post_date.substring(0, 'yyyy-mm-dd'.length).replace(/-/g, '/'));
				var nRows = ccStats.lineChart.visitData.getNumberOfRows();
				for (var i = 0; i < nRows; i++) {
					var dataDate = ccStats.lineChart.visitData.getValue(i, 0);
					if (postDate.equals(dataDate)) {
						var circle = ccStats.lineChart.jqCircles[i];
						$(circle).data('strokeWeight', 3);
						ccStats.gfx.setStrokeWeight(circle, 3);
						if (ccStats.lineChart.visitData.getProperty(i, 0, 'campaignSent')) {
							ccStats.gfx.setFillColor(circle, '#00576F');
							ccStats.gfx.setStrokeColor(circle, '#D98E26');
						}
						else {
							ccStats.gfx.setFillColor(circle, '#D98E26');
							ccStats.gfx.setStrokeColor(circle, '#D98E26');
						}
						ccStats.gfx.setRadius($(circle).data('radius', 5), 5);
						ccStats.lineChart.visitData.setProperty(i, 0, 'post', post);
					}
				}
			}
		});
	};
		
	ccStats.updateVisitsChartWithCampaigns = function(event, data) {
		$.each(data, function(i, campaign) {
			if (campaign.send_time) {
				var sendDate = new Date(campaign.send_time.substring(0, 'yyyy-mm-dd'.length).replace(/-/g, '/'));
				var nRows = ccStats.lineChart.visitData.getNumberOfRows();
				for (var i = 0; i < nRows; i++) {
					var dataDate = ccStats.lineChart.visitData.getValue(i, 0);
					if (sendDate.equals(dataDate)) {
						var circle = ccStats.lineChart.jqCircles[i];
						$(circle).data('strokeWeight', 3);
						ccStats.gfx.setStrokeWeight(circle, 3);
						if (ccStats.lineChart.visitData.getProperty(i, 0, 'post')) {
							ccStats.gfx.setFillColor(circle, '#00576F');
							ccStats.gfx.setStrokeColor(circle, '#D98E26');
						}
						else {
							ccStats.gfx.setFillColor(circle, '#00576F');
							ccStats.gfx.setStrokeColor(circle, '#00576F');
						}
						ccStats.gfx.setRadius($(circle).data('radius', 5), 5);
						ccStats.lineChart.visitData.setProperty(i, 0, 'campaignSent', campaign);
					}
				}
			}
		});
	};
		
	ccStats.hideTooltip = function() {
		$('.ccStats-tooltip-container').remove();
		if (ccStats.lineChart.jqCircles) {
			ccStats.lineChart.jqCircles.each(function() { ccStats.gfx.setRadius($(this), $(this).data('radius')); });
		}
	};
		
	ccStats.renderTooltip = function(event, container, content) {
		var containerOffset = container.offset();
		var className = '';
		var left = 0;
		var top = 0;
		var lr = '';
		var ul = '';

		if (event.pageX + 200 - containerOffset.left > jQuery(container).width()) {
			lr = 'right';
			left = event.pageX - containerOffset.left - 180;
		}
		else {
			lr = 'left';
			left = event.pageX - containerOffset.left - 5;
		}
		
		if (event.pageY - containerOffset.top < 100) {
			ul = 'upper';
			top = event.pageY - containerOffset.top - 40;
		}
		else {
			ul = 'lower';
			top = event.pageY - containerOffset.top - 60;
		}
		
		var markup = '';
		if (ul == 'upper') {
			markup = '\
				<div class="ccStats-tooltip-upper-' + lr + '-point"></div>\
				<div class="ccStats-tooltip-upper-' + lr + '-top"></div>\
			';
		}
		else {
			markup = '\
				<div class="ccStats-tooltip-lower-star-top"></div>\
			';
		}
		markup += '\
			<div class="ccStats-tooltip-body">' + content + '</div>\
		';
		
		if (ul == 'upper') {
			markup += '<div class="ccStats-tooltip-upper-star-bottom"></div>';
		}
		else {
			markup += '\
				<div class="ccStats-tooltip-lower-' + lr + '-bottom"></div>\
				<div class="ccStats-tooltip-lower-' + lr + '-point"></div>\
			';
		}
		
		var fade = (('support' in $) && $.support.opacity) || !($.browser.msie);
		if (fade) {
			var t = $('<div class="ccStats-tooltip-container">' + markup + '</div>').css({ left: left, top: top, opacity: 0.1 });
			$(container).prepend(t);
			t.click(function(event) {
				ccStats.hideTooltip();
			}).animate({
				top: (ul == 'upper' ? '+=40' : '-=' + (t.height() - 60)),
				opacity: 1
			}, 300);
		}
		else {
			// ie no likey 
			var t = $('<div class="ccStats-tooltip-container">' + markup + '</div>').css({ left: left, top: top });
			$(container).prepend(t);
			t.click(function(event) {
				ccStats.hideTooltip();
			}).animate({
				top: (ul == 'upper' ? '+=40' : '-=' + (t.height() - 70))
			}, 0);
		}
	};
		
	ccStats.pushTable = function(container, data, title) {
		var table = new ccStats.Table(data, title);
		container = $(container);
		var stack = container.data('tableStack') || container.data('tableStack', []).data('tableStack');
		stack.push(table);
		table.draw(container);
		ccStats.setHeaderTitle(container.parents('.ccStats-box'), table.title);
		container.parents('.ccStats-box').find('.ccStats-breadcrumbs').html(ccStats.getTableBreadcrumbs(container));
	};
	
	ccStats.popTable = function(container) {
		var stack = container.data('tableStack');
		stack.pop();
		container.empty();
		if (stack.length) {
			var table = stack[stack.length - 1];
			table.draw(container);
			ccStats.setHeaderTitle(container.parents('.ccStats-box'), table.title);
			container.parents('.ccStats-box').find('.ccStats-breadcrumbs').html(ccStats.getTableBreadcrumbs(container));
		}
	};
	
	ccStats.clearTableStack = function(container) {
		container.data('tableStack', []);
		ccStats.setHeaderTitle(container.parents('.ccStats-box'), '');
		container.parents('.ccStats-box').find('.ccStats-breadcrumbs').html('');
	};
	
	ccStats.getTableBreadcrumbs = function(container) {
		var stack = container.data('tableStack');
		if (stack.length < 2) {
			return '';
		}
		var links = [];
		$.each(stack, function(i, table) {
			if (i < stack.length - 1) {
				links.push('<a href="#" onclick="ccStats.popTable(jQuery(this).parents(\'.ccStats-box\').find(\'.ccStats-table-container\')); return false">' + table.title + '</a>');
			}
			else {
				links.push(table.title);
			}
		});
		return links.join(' &raquo; ');
	};

	ccStats.Table = function(data, title) {
		this.data = data;
		this.title = title;
		this.jqContainer = null;
		this.tableViz = null;
		this.sortedColumn = 2;
		this.page = 0;
	};
	
	ccStats.Table.prototype.draw = function(container) {
		
		this.jqContainer = $(container);
		
		this.tableViz = new google.visualization.Table(this.jqContainer.empty().get(0));
		
		google.visualization.events.addListener(this.tableViz, 'ready', this.handleRedraw._cfBind(this));
		google.visualization.events.addListener(this.tableViz, 'sort', (function(info) {
			this.sortedColumn = info.column + 1;
			this.handleRedraw();
		})._cfBind(this));
		google.visualization.events.addListener(this.tableViz, 'page', (function(info) {
			this.page = info.page;
			this.handleRedraw();
		})._cfBind(this));

		this.tableViz.draw(this.data, { 
			width: '100%', 
			page: 'enable', 
			showRowNumber: true, 
			sortAscending: false,
			sortColumn: 1,
			allowHtml: true,
			startPage: this.page,
			cssClassNames: {
				headerRow: 'ccStats-table-header',
				tableRow: 'ccStats-table-row',
				selectedTableRow: 'ccStats-table-row-selected',
				hoverTableRow: 'ccStats-table-row-hover',
				oddTableRow: 'ccStats-table-row-odd'
			}
		});
	};
	
	ccStats.Table.prototype.handleRedraw = function() {
		jQuery('.ccStats-table-header td', this.jqContainer).each(function(i) {
		    jQuery(this).width(['2%', '47%', '15%', '15%', '20%'][i]);
		});
		jQuery('button', this.jqContainer).addClass('button');
		jQuery('td', this.jqContainer).removeClass('sorted');
		var sortedColumn = this.sortedColumn;
		jQuery('.ccStats-table-header, .ccStats-table-row, .ccStats-table-row-odd', this.jqContainer).each(function() {
			jQuery('td:eq(' + (sortedColumn) + ')', this).addClass('sorted');
		});
	};


	ccStats.lineChart = {
		iframeDoc: null,
		jqCircles: null,
		visitData: null,
		
		drawFrame: null,
		height: null,
		offsetY: null,
		topLineValue: 0,
		bottomLineValue: 0
	};
	
	ccStats.maxFetchAttempts = 10;
	ccStats.fetchFailed = function(f, args, failureEventName, failureEventArgs) {
		f.attempts = ('attempts' in f) ? f.attempts + 1 : 1;
		if (f.attempts < ccStats.maxFetchAttempts) {
			f.apply(ccStats, (args || []));
		}
		else {
			$(ccStats).trigger(failureEventName, failureEventArgs);
			f.attempts = 0;
		}
		
	};
	ccStats.fetchSucceeded = function(f, eventName, eventArgs) {
		f.attempts = 0;
		$(ccStats).trigger(eventName, eventArgs);
	};
	
	ccStats.setHeaderTitle = function(box, title) {
		$('.ccStats-box-header h3', box).html(title);
	};
	
	// note that box needs to be position:relative or absolute.
	ccStats.addHeaderError = function(box, error) {
		$('.ccStats-box-status', box).removeClass().addClass('ccStats-box-status ccStats-error').click(function(event) {
			ccStats.renderTooltip(event, box, error);
		});
	};
	
	ccStats.setHeaderStatus = function(box, status, title) {
		$('.ccStats-box-status', box).unbind().removeClass().addClass('ccStats-box-status ccStats-' + status);
		if(!title || title === '' || title === 'undefined' || typeof(title) !== 'string') { title = ''; }
		
		if(title !== '') {
			$('.ccStats-box-status-text', box).text(title).fadeIn('fast');
		} else {
			$('.ccStats-box-status-text', box).fadeOut('fast', function() { $(this).text(title); });
		}
	};

	ccStats.secToDuration = function(sec) {
		var min = Math.floor(sec / 60);
		var hours = (min < 60 ? 0 : Math.floor(min / 60));
		min -= hours * 60;
		var sec = sec - (hours * 3600) - (min * 60);
		hours = hours < 10 ? '0' + hours : hours + '';
		min = min < 10 ? '0' + min : min + '';
		sec = sec < 10 ? '0' + sec : sec + '';
		return hours + ':' + min + ':' + sec;
	};

	ccStats.visitStats = {
		days: {},
		mediumSummaries: {},
		nDays: 0,
		mediumFilter: 'all traffic'
	};

	ccStats.MediumSummary = function(medium) {
		this.medium = medium;
		
		this.totalVisits = 0;
		this.totalBounceRate = 0;
		this.totalNewVisits = 0;
		this.totalPageviews = 0;
		this.totalTimeOnSite = 0;
	
		this.avgTimeOnSite = '';
		this.bounceRate = '';
		this.percentNewVisits = '';
		this.totalPagesPerVisit = '';
	
		// for sparklines
		this.visits = [];
		this.bounceRates = [];
		//this.bounceRates = [];
		this.newVisits = [];
		this.pageviews = [];
		this.timeOnSite = [];
		this.pagesPerVisit = [];
	};

	// a day's stats 
	/**
	 * {
	 * 		metric1: {
	 * 			'all traffic': value,
	 * 			medium1: value,
	 * 			medium2: value
	 * 			...
	 * 		},
	 * 		metric2: {
	 * 			'all traffic': value,
	 * 			...
	 * 		}
	 * }
	 */
	ccStats.DayStats = function(date) {
		this.date = date;
		// create getters for each of our metrics. each method takes a medium as argument, so, ex:
		// 		var visits = day.getVisits('cpc');
		//		var views = day.getPageviews('cpc');
		// etc ...
		$.each(['visits', 'bounces', 'newVisits', 'pageviews', 'timeOnSite', 'pagesPerVisit', 'entrances'], (function(i, metric) {
			this[metric] = { 'all traffic': 0 };
			this['get' + metric.substring(0, 1).toUpperCase() + metric.substr(1)] = (function(medium) {
				return this[metric][medium];
			})._cfBind(this);
		})._cfBind(this));
	};
	

	ccStats.fetchGAVisits = function() {
		$(ccStats).trigger('fetchingGAVisits');
		$.get('', {
			ccStats_action: 'get_ga_data',
			start_date: ccStats.displayDates.start.toString('yyyy-MM-dd'),
			end_date: ccStats.displayDates.end.toString('yyyy-MM-dd'),
			data_type: 'visits'
		}, function(result, status) {
			if (result.success) {
				ccStats.visitStats.days = {};
				ccStats.visitStats.mediumSummaries = {
					'all traffic': new ccStats.MediumSummary('all traffic')
				};
				ccStats.visitStats.nDays = 0;
				
				var data = result.data;
				var medium = '';
				var summary = null;
				for (var i = 0; i < data.length; i++) {
					var dateKey = data[i].dimensions.date;
					medium = data[i].dimensions.medium;

					// if this is the first time we've seen this date
					if (!(dateKey in ccStats.visitStats.days)) {
						ccStats.visitStats.days[dateKey] = new ccStats.DayStats(new Date(
							parseInt(dateKey.substr(0, 4), 10), 
							parseInt(dateKey.substr(4, 2), 10) - 1, 
							parseInt(dateKey.substr(6, 2), 10)
						));
						ccStats.visitStats.nDays++;
					}
					
					var dayStats = ccStats.visitStats.days[dateKey];
					
					$.each(data[i].metrics, function(metricKey, value) {
						dayStats[metricKey][medium] = value;
						dayStats[metricKey]['all traffic'] = (('all traffic' in dayStats[metricKey]) ? dayStats[metricKey]['all traffic'] + value : value);
					});
					
					
					if (!(medium in ccStats.visitStats.mediumSummaries)) {
						ccStats.visitStats.mediumSummaries[medium] = new ccStats.MediumSummary(medium);
					}
					
					summary = ccStats.visitStats.mediumSummaries[medium];

					summary.visits.push(data[i].metrics.visits);
					summary.totalVisits += data[i].metrics.visits;
					summary.bounceRates.push(data[i].metrics.entrances > 0 ? data[i].metrics.bounces / data[i].metrics.entrances : 0);
					summary.totalBounceRate += (data[i].metrics.entrances > 0 ? data[i].metrics.bounces / data[i].metrics.entrances : 0);
					summary.newVisits.push(data[i].metrics.visits > 0 ? data[i].metrics.newVisits / data[i].metrics.visits : 0);
					summary.totalNewVisits += data[i].metrics.newVisits;
					summary.pageviews.push(data[i].metrics.pageviews);
					summary.totalPageviews += data[i].metrics.pageviews;
					summary.timeOnSite.push(data[i].metrics.visits > 0 ? data[i].metrics.timeOnSite / data[i].metrics.visits : 0);
					summary.totalTimeOnSite += data[i].metrics.timeOnSite;
					summary.pagesPerVisit.push(data[i].metrics.visits > 0 ? data[i].metrics.pageviews / data[i].metrics.visits : 0);
					
				}
				
				$.each(ccStats.visitStats.mediumSummaries, function(medium, summary) {
					summary.avgTimeOnSite = ccStats.secToDuration(Math.round(summary.totalTimeOnSite / summary.totalVisits));
					summary.bounceRate = (summary.totalBounceRate / ccStats.visitStats.nDays * 100).toPrecision(4) + '%';
					summary.percentNewVisits = ((summary.totalNewVisits / summary.totalVisits) * 100).toPrecision(4) + '%';
					summary.totalPagesPerVisit = (summary.totalPageviews / summary.totalVisits).toPrecision(3);
				});
				
				// compose summary for all traffic
				medium = 'all traffic';
				summary = ccStats.visitStats.mediumSummaries[medium];

				$.each(ccStats.visitStats.days, function(dateKey, day) {
					summary.visits.push(day.getVisits(medium));
					summary.totalVisits += day.visits[medium];
					summary.bounceRates.push(day.entrances[medium] > 0 ? day.bounces[medium] / day.entrances[medium] : 0);
					summary.totalBounceRate += (day.entrances[medium] > 0 ? day.bounces[medium] / day.entrances[medium] : 0);
					summary.newVisits.push(day.visits[medium] > 0 ? day.newVisits[medium] / day.visits[medium] : 0);
					summary.totalNewVisits += day.newVisits[medium];
					summary.pageviews.push(day.pageviews[medium]);
					summary.totalPageviews += day.pageviews[medium];
					summary.timeOnSite.push(day.visits[medium] > 0 ? day.timeOnSite[medium] / day.visits[medium] : 0);
					summary.totalTimeOnSite += day.timeOnSite[medium];
					summary.pagesPerVisit.push(day.visits[medium] > 0 ? day.pageviews[medium] / day.visits[medium] : 0);
				});
				
				summary.avgTimeOnSite = ccStats.secToDuration(Math.round(summary.totalTimeOnSite / summary.totalVisits));
				summary.bounceRate = (summary.totalBounceRate / ccStats.visitStats.nDays * 100).toPrecision(4) + '%';
				summary.percentNewVisits = ((summary.totalNewVisits / summary.totalVisits) * 100).toPrecision(4) + '%';
				summary.totalPagesPerVisit = (summary.totalPageviews / summary.totalVisits).toPrecision(3);

				ccStats.fetchSucceeded(ccStats.fetchGAVisits, 'gaVisitsFetched', [result.data]);
			}
			else {
				ccStats.fetchFailed(ccStats.fetchGAVisits, undefined, 'gaVisitsFetchFailed', [result.error]);
			}
		}, 'json');
	};
	
	ccStats.fetchGATopContent = function() {
		$(ccStats).trigger('fetchingGATopContent');
		$.get('', {
			ccStats_action: 'get_ga_data',
			start_date: ccStats.displayDates.start.toString('yyyy-MM-dd'),
			end_date: ccStats.displayDates.end.toString('yyyy-MM-dd'),
			data_type: 'top_content'
		}, function(result, status) {
			if (result.success) {
				//console.log(result);
				ccStats.fetchSucceeded(ccStats.fetchGATopContent, 'gaTopContentFetched', [result.data]);
			}
			else {
				ccStats.fetchFailed(ccStats.fetchGATopContent, undefined, 'gaTopContentFetchFailed', [result.error]);
			}
		}, 'json');


	};
	
	ccStats.fetchGAGeo = function() {
		$(ccStats).trigger('fetchingGAGeo');
		$.get('', {
			ccStats_action: 'get_ga_data',
			start_date: ccStats.displayDates.start.toString('yyyy-MM-dd'),
			end_date: ccStats.displayDates.end.toString('yyyy-MM-dd'),
			data_type: 'geo'
		}, function(result, status) {
			if (result.success) {
				ccStats.fetchSucceeded(ccStats.fetchGAGeo, 'gaGeoFetched', [result.data]);
			}
			else {
				ccStats.fetchFailed(ccStats.fetchGAGeo, undefined, 'gaGeoFetchFailed', [result.error]);
			}
		}, 'json');
	};
	
	ccStats.fetchGATopReferrals = function() {
		$(ccStats).trigger('fetchingGATopReferrals');
		$.get('', {
			ccStats_action: 'get_ga_data',
			start_date: ccStats.displayDates.start.toString('yyyy-MM-dd'),
			end_date: ccStats.displayDates.end.toString('yyyy-MM-dd'),
			data_type: 'top_referrals'
		}, function(result, status) {
			if (result.success) {
				ccStats.fetchSucceeded(ccStats.fetchGATopReferrals, 'gaTopReferralsFetched', [result.data]);
			}
			else {
				ccStats.fetchFailed(ccStats.fetchGATopReferrals, undefined, 'gaTopReferralsFetchFailed', [result.error]);
			}
		}, 'json');
	};
	
	ccStats.fetchGAReferralMedia = function() {
		$(ccStats).trigger('fetchingGAReferralMedia');
		$.get('', {
			ccStats_action: 'get_ga_data',
			start_date: ccStats.displayDates.start.toString('yyyy-MM-dd'),
			end_date: ccStats.displayDates.end.toString('yyyy-MM-dd'),
			data_type: 'referral_media'
		}, function(result, status) {
			if (result.success) {
				ccStats.fetchSucceeded(ccStats.fetchGAReferralMedia, 'gaReferralMediaFetched', [result.data]);
			}
			else {
				ccStats.fetchFailed(ccStats.fetchGAReferralMedia, undefined, 'gaReferralMediaFetchFailed', [result.error]);
			}
		}, 'json');
	};
	
	ccStats.fetchWPPosts = function() {
		$(ccStats).trigger('fetchingWPPosts');
		$.get('', {
			ccStats_action: 'get_wp_posts',
			start_date: ccStats.displayDates.start.toString('yyyy-MM-dd'),
			end_date: ccStats.displayDates.end.toString('yyyy-MM-dd')
		}, function(result, status) {
			if (result.success) {
				ccStats.fetchSucceeded(ccStats.fetchWPPosts, 'wpPostsFetched', [result.data]);
			}
			else {
				ccStats.fetchFailed(ccStats.fetchWPPosts, undefined, 'wpPostsFetchFailed', [result.error]);
			}
		}, 'json');
	};
	
	ccStats.fetchMCCampaigns = function() {
		$(ccStats).trigger('fetchingMCCampaigns');
		$.get('', {
			ccStats_action: 'get_cc_data',
			data_type: 'campaigns',
			start_date: ccStats.displayDates.start.toString('yyyy-MM-dd HH:mm:ss'),
			end_date: ccStats.displayDates.end.toString('yyyy-MM-dd HH:mm:ss')
		}, function(result, status) {
			//console.log(result.data);
			if (result.success) {
				//console.log(result.data);
				ccStats.fetchSucceeded(ccStats.fetchMCCampaigns, 'mcCampaignsFetched', [result.data]);
			}
			else {
				//console.log('fetch failed');
				ccStats.fetchFailed(ccStats.fetchMCCampaigns, undefined, 'mcCampaignsFetchFailed', [result.error]);
			}
		}, 'json');
	};
	
	ccStats.fetchGAKeywords = function(sourceName) {
		$(ccStats).trigger('fetchingGAKeywords');
		$.get('', {
			ccStats_action: 'get_ga_data',
			data_type: 'keywords',
			source_name: sourceName,
			start_date: ccStats.displayDates.start.toString('yyyy-MM-dd'),
			end_date: ccStats.displayDates.end.toString('yyyy-MM-dd')
		}, function(result, status) {
			if (result.success) {
				ccStats.fetchSucceeded(ccStats.fetchGAKeywords, 'gaKeywordsFetched', [result.data, sourceName]);
			}
			else {
				ccStats.fetchFailed(ccStats.fetchGAKeywords, [sourceName], 'gaKeywordsFetchFailed', [sourceName, result.error]);
			}
		}, 'json');
	};

	ccStats.fetchGAReferralPaths = function(sourceName) {
		$(ccStats).trigger('fetchingGAReferralPaths');
		$.get('', {
			ccStats_action: 'get_ga_data',
			data_type: 'referral_paths',
			source_name: sourceName,
			start_date: ccStats.displayDates.start.toString('yyyy-MM-dd'),
			end_date: ccStats.displayDates.end.toString('yyyy-MM-dd')
		}, function(result, status) {
			if (result.success) {
				ccStats.fetchSucceeded(ccStats.fetchGAReferralPaths, 'gaReferralPathsFetched', [result.data, sourceName]);
			}
			else {
				ccStats.fetchFailed(ccStats.fetchGAReferralPaths, [sourceName], 'gaReferralPathsFetchFailed', [sourceName, result.error]);
			}
		}, 'json');
	};

	ccStats.fetchGAEmailReferrals = function(sourceName) {
		$(ccStats).trigger('fetchingGAEmailReferrals');
		$.get('', {
			ccStats_action: 'get_ga_data',
			data_type: 'email_referrals',
			source_name: sourceName,
			start_date: ccStats.displayDates.start.toString('yyyy-MM-dd'),
			end_date: ccStats.displayDates.end.toString('yyyy-MM-dd')
		}, function(result, status) {
			if (result.success) {
				ccStats.fetchSucceeded(ccStats.fetchGAReferralPaths, 'gaEmailReferralsFetched', [result.data, sourceName]);
			}
			else {
				ccStats.fetchFailed(ccStats.fetchGAReferralPaths, [sourceName], 'gaEmailReferralsFetchFailed', [sourceName, result.error]);
			}
		}, 'json');
	};

	$(document).ready(function() {
		if (pagenow == 'dashboard_page_constant-analytics') {
			(ccStats.handleDashboardReady._cfBind(ccStats))();
		}

	});


})(jQuery);