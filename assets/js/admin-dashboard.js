'use strict';
/**
 * Administrator dashboard: system user base bar chart.
 * Chart data arrives as escaped JSON in data-chart on #admin-bar-chart.
 */
(function () {
    var el = document.getElementById('admin-bar-chart');
    if (!el) {
        return;
    }

    var rows;
    try {
        rows = JSON.parse(el.dataset.chart || '[]');
    } catch (error) {
        return;
    }

    function drawChart() {
        if (!window.google || !google.charts || !google.visualization) {
            return;
        }

        var containerWidth = el.parentElement ? el.parentElement.clientWidth : 300;
        var chart = new google.charts.Bar(el);
        chart.draw(google.visualization.arrayToDataTable(rows), google.charts.Bar.convertOptions({
            legend: { position: 'none' },
            bar: { groupWidth: '55%' },
            width: containerWidth,
            height: 250
        }));
    }

    google.charts.load('current', { packages: ['bar'] });
    google.charts.setOnLoadCallback(drawChart);
    window.addEventListener('resize', drawChart);
})();
