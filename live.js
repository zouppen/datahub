$(function() {

    // Keep recent data in buffer
    var today = [];
    var yesterday = [];
    var devnull = { push: function() {} };

    var data = [
	{
	    data: today,
	    label: "Tänään: ",
	},{
	    data: yesterday,
	    label: "Eilen: ",
	},
    ];

    var plot = $.plot("#placeholder", [], {
	series: {
	    shadowSize: 0,	// Drawing is faster without shadows
	},
	yaxis: {
	},
	xaxis: {
	    mode: "time",
	    timeformat: "%H:%M",
	    show: true,
	},
	crosshair: {
	    mode: "x",
	},
	grid: {
	    hoverable: true,
	    autoHighlight: false,
	},
    });

    function moveOld(from, to, now, point) {
	while (from.length > 0 && from[0][0] <= now - point) {
	    // Turn clock forward to draw over the next day
	    var e = from.shift();
	    e[0] = e[0] + point;
	    to.push(e);
	}	
    }

    function getNewData(args) {
	var start = Date.now();
	// First, get requested data
	$.get("get", args, function(csv) {
	    var lastRow;
	    for (let line of csv.split("\n")) {
		if (line === '') continue; // Skip empty line
		var fields = line.split(",");
		lastRow = fields[0];
		today.push([Number.parseInt(fields[1])*1000, Number.parseFloat(fields[2])]);
	    }
	    
	    // TODO filter old data
	    var now = Date.now();
	    moveOld(today, yesterday, now, 86400000);
	    moveOld(yesterday, devnull, now, 86400000);
	    
	    // Update plot
	    plot.setData(data);
	    plot.setupGrid();
	    plot.draw();

	    // Take care of the legend
	    legends = $("#placeholder .legendLabel");
	    legends.each(function () {
		// fix the widths so they don't jump around
		$(this).css('width', '8em');
	    });
	    considerUpdateLegend();
	    
	    // Ask for more
	    getNewData({r: lastRow});
	}).fail(function() {
	    // Okay, hibernation or network error. Keep a pause of 2 minutes.
	    var minDelay = 1000;
	    var maxDelay = 120000;
	    var delay = Math.min(maxDelay, Math.max(minDelay, start + maxDelay - Date.now()));
	    setTimeout(getNewData, delay, args);
	});
    }

    // Get data newer than two days
    getNewData({t: Math.floor(Date.now()/1000) - 172800});

    // Allow hovering
    var legends;
    var updateLegendTimeout = null;
    var latestPosition = null;

    function updateLegend() {
	updateLegendTimeout = null;

	var pos = latestPosition;
	if (pos === null) return;

	var axes = plot.getAxes();
	if (pos.x < axes.xaxis.min || pos.x > axes.xaxis.max ||
	    pos.y < axes.yaxis.min || pos.y > axes.yaxis.max) {
	    return;
	}

	var i, j, dataset = plot.getData();
	var ts_raw;
	for (i = 0; i < dataset.length; ++i) {

	    var series = dataset[i];

	    // Find the nearest points, x-wise
	    for (j = 0; j < series.data.length; ++j) {
		if (series.data[j][0] > pos.x) {
		    break;
		}
	    }

	    y = series.data[j][1];
	    legends.eq(i).text(series.label.replace(/: .*/, ": " + y.toFixed(1) + " °C"));
	}

	timehover.text("Aika: "+(new Date(pos.x)).toLocaleString());

    }

    function considerUpdateLegend() {
	if (updateLegendTimeout) return;
	updateLegendTimeout = setTimeout(updateLegend, 50);
    }
    
    $("#placeholder").bind("plothover",  function (event, pos, item) {
	latestPosition = pos;
	considerUpdateLegend();
    });


    $("#placeholder").append("<div id='timehover' style='position:absolute;left: 3em; top: 1em; color:#666;font-size:smaller'></div>");
    var timehover = $('#timehover');
});
