<?php

/**
 * This template renders a search box and action bar for managing surveys view type.
 * It includes filters for survey status, group selection, and a search field.
 * Additionally, it provides options for creating new surveys and survey groups if the user has the appropriate permissions.
 *
 * @var CActiveForm $form The form widget used for submitting search and filter parameters.
 */
?>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.getElementById('surveyDropdown').addEventListener('change', function() {
        const selectedSurveyId = this.value;
        const csrfTokenName = '<?= Yii::app()->request->csrfTokenName ?>';
        const csrfToken = '<?= Yii::app()->request->csrfToken ?>';

        //  URL with surveyid as a query parameter
        const url = '<?= Yii::app()->createUrl('searchBoxWidget/getSurveyResponseTrends') ?>' + '?surveyid=' + selectedSurveyId;

        // Fetch data using GET request
        fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    [csrfTokenName]: csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log(data)
                // Update the chart with the new data
                const labels = data.map(item => item.response_date);
                const responseData = data.map(item => item.response_count);

                responseChart.data.labels = labels;
                responseChart.data.datasets[0].data = responseData;
                responseChart.update();
            })
            .catch(error => {
                console.error('Error fetching survey response trends:', error);
            });
    });

    const ctx = document.getElementById('responseChart').getContext('2d');
    const responseChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            datasets: [{
                label: 'Responses',
                data: [1200, 1900, 3000, 3400, 2200, 1900, 2800],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>
<style>
    .survey-icon {
        border-radius: 50%;
        background-color: rgba(221, 225, 230, 1);
        width: fit-content;
        padding: 10px 12px
    }

    .card {
        border-radius: 12px;
    }

    .quick-actions .btn {
        width: 100%;
        margin-bottom: 10px;
    }

    .active-surveys,
    .recent-activity {
        max-height: 400px;
        overflow-y: auto;
    }

    .chart-container {
        height: 250px;
    }

    .nav-tabs {
        border-bottom: 1px solid #ddd;
    }

    .nav-tabs .nav-link {
        color: #555;

        font-weight: bold;
    }

    .nav-tabs .nav-link.active {
        border-bottom: 3px solid #122867;

        border-top: none;
        background: none;

    }
</style>

