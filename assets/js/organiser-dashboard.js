'use strict';
/**
 * Organiser dashboard: candidate vote bar chart and participation pie chart.
 * Data arrives as escaped JSON in data-chart on #organiser-chart-data.
 */
(function () {
    var dataEl = document.getElementById('organiser-chart-data');
    if (!dataEl) {
        return;
    }

    var data;
    try {
        data = JSON.parse(dataEl.dataset.chart || '{}');
    } catch (error) {
        return;
    }

    var names = data.names || [];
    var votes = data.votes || [];
    var voterReceived = Number(data.received || 0);
    var voterPending = Number(data.pending || 0);

    function drawBar() {
        var el = document.getElementById('organiser-bar-chart');
        if (!el || !window.google || !google.charts || !google.visualization || names.length === 0) {
            return;
        }

        var rows = [['Candidate', 'Votes']];
        for (var i = 0; i < names.length; i++) {
            rows.push([names[i], Number(votes[i]) || 0]);
        }

        var containerWidth = el.parentElement ? el.parentElement.clientWidth : 400;
        var chart = new google.charts.Bar(el);
        chart.draw(google.visualization.arrayToDataTable(rows), google.charts.Bar.convertOptions({
            legend: { position: 'none' },
            bar: { groupWidth: '50%' },
            width: containerWidth,
            height: 280
        }));
    }

    function drawPie() {
        var el = document.getElementById('organiser-pie-chart');
        if (!el || !window.google || !google.visualization) {
            return;
        }

        var containerWidth = el.parentElement ? el.parentElement.clientWidth : 300;
        var chart = new google.visualization.PieChart(el);
        chart.draw(google.visualization.arrayToDataTable([
            ['Participation', 'Voters'],
            ['Ballots Cast', voterReceived],
            ['Pending Voters', voterPending]
        ]), {
            pieHole: 0.45,
            legend: { position: 'bottom' },
            colors: ['#2563eb', '#cbd5e1'],
            chartArea: { width: '90%', height: '75%' },
            width: containerWidth,
            height: 250
        });
    }

    google.charts.load('current', { packages: ['bar', 'corechart'] });
    google.charts.setOnLoadCallback(function () {
        drawBar();
        drawPie();
    });
    window.addEventListener('resize', function () {
        drawBar();
        drawPie();
    });
})();
