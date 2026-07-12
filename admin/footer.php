<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById("myChart"), {

    type: 'line',

    data: {

        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],

        datasets: [{

            label: 'Penjualan',

            data: [20, 32, 25, 40, 36, 55],

            fill: false

        }]

    }

});
</script>

</body>

</html>