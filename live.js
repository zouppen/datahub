$(function() {

    // Keep recent data in buffer
    var today = [];
    var yesterday = [];
    var devnull = { push: function() {} };

    var data = [
	{
	    data: today,
	    label: "Tänään",
	},{
	    data: yesterday,
	    label: "Eilen",
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
	    timeformat: "%H:%M:%S",
	    show: true,
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
	// First, get requested data
	$.get("https://zouppen.iki.fi/poista/ahma-ng/get.php", args, function(csv) {
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

	    // Ask for more
	    getNewData({r: lastRow});
	});
    }

    // Get data newer than two days
    getNewData({t: Math.floor(Date.now()/1000) - 172800});

    // Add the Flot version string to the footer

    $("#footer").prepend("Powered by Flot " + $.plot.version + " &ndash; ");
});
