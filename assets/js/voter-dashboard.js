'use strict';
/**
 * Voter dashboard: synchronized countdown tiles and, when the election has
 * concluded, the final vote distribution bar chart.
 * Data arrives as escaped JSON on #voter-dashboard-data.
 */
(function () {
    var dataEl = document.getElementById('voter-dashboard-data');
    if (!dataEl) {
        return;
    }

    var countdown;
    try {
        countdown = JSON.parse(dataEl.dataset.countdown || '{}');
    } catch (error) {
        countdown = {};
    }

    var chart = null;
    if (dataEl.dataset.chart) {
        try {
            chart = JSON.parse(dataEl.dataset.chart);
        } catch (error) {
            chart = null;
        }
    }

    var startTarget = Number(countdown.start || 0);
    var endTarget = Number(countdown.end || 0);
    var clientOffset = Number(countdown.serverTime || Date.now()) - Date.now();

    function renderTiles(containerId, targetMillis) {
        var el = document.getElementById(containerId);
        if (!el || targetMillis <= 0) {
            return;
        }

        var now = Date.now() + clientOffset;
        var diff = Math.max(0, Math.floor((targetMillis - now) / 1000));

        var days = Math.floor(diff / 86400);
        diff %= 86400;
        var hours = Math.floor(diff / 3600);
        diff %= 3600;
        var minutes = Math.floor(diff / 60);
        var seconds = Math.floor(diff % 60);

        function pad(value) {
            return value < 10 ? '0' + value : '' + value;
        }

        el.innerHTML =
            '<div class="countdown-tile"><span class="val">' + days + '</span><span class="lbl">Days</span></div>' +
            '<div class="countdown-tile"><span class="val">' + pad(hours) + '</span><span class="lbl">Hours</span></div>' +
            '<div class="countdown-tile"><span class="val">' + pad(minutes) + '</span><span class="lbl">Minutes</span></div>' +
            '<div class="countdown-tile"><span class="val">' + pad(seconds) + '</span><span class="lbl">Seconds</span></div>';
    }

    function updateTimers() {
        renderTiles('vote_start_countdown', startTarget);
        renderTiles('vote_end_countdown', endTarget);
        renderTiles('poll_close_countdown', endTarget);
    }

    updateTimers();
    window.setInterval(updateTimers, 1000);

    if (!chart || !chart.names || !chart.votes) {
        return;
    }

    function drawBar() {
        var el = document.getElementById('voter-bar-chart');
        if (!el || !window.google || !google.charts || !google.visualization || chart.names.length === 0) {
            return;
        }

        var rows = [['Candidate', 'Votes']];
        for (var i = 0; i < chart.names.length; i++) {
            rows.push([chart.names[i], Number(chart.votes[i]) || 0]);
        }

        var containerWidth = el.parentElement ? el.parentElement.clientWidth : 400;
        var bar = new google.charts.Bar(el);
        bar.draw(google.visualization.arrayToDataTable(rows), google.charts.Bar.convertOptions({
            legend: { position: 'none' },
            bar: { groupWidth: '50%' },
            width: containerWidth,
            height: 280
        }));
    }

    google.charts.load('current', { packages: ['bar'] });
    google.charts.setOnLoadCallback(drawBar);
    window.addEventListener('resize', drawBar);
})();
