(function ($) {
  "use strict";

  let performanceChart, complexityChart, memoryChart, responseSizeChart;

  $(document).ready(function () {
    initDashboard();

    // Event listeners
    $("#refresh-logs").on("click", loadQueryLogs);
    $("#clear-logs").on("click", clearAllLogs);

    // Modal functionality
    $(".close").on("click", function () {
      $("#query-detail-modal").hide();
    });

    $(window).on("click", function (e) {
      if (e.target.id === "query-detail-modal") {
        $("#query-detail-modal").hide();
      }
    });

    // Auto-refresh every 30 seconds
    setInterval(function () {
      loadStats();
      loadQueryLogs();
    }, 30000);
  });

  function initDashboard() {
    loadStats();
    loadQueryLogs();
    loadCharts();
  }

  function loadStats() {
    $.ajax({
      url: wpgraphqlMonitor.ajax_url,
      type: "POST",
      data: {
        action: "get_query_stats",
        nonce: wpgraphqlMonitor.nonce,
      },
      success: function (response) {
        if (response.success) {
          const data = response.data;
          $("#total-queries").text(data.total_queries);
          $("#avg-response-time").text(data.avg_execution_time + " ms");
          $("#error-rate").text(data.error_rate + "%");
          $("#slowest-query").text(data.max_execution_time + " ms");
        }
      },
      error: function () {
        showNotice("Error loading statistics", "error");
      },
    });
  }

  function loadQueryLogs(page = 1) {
    $.ajax({
      url: wpgraphqlMonitor.ajax_url,
      type: "POST",
      data: {
        action: "get_query_logs",
        nonce: wpgraphqlMonitor.nonce,
        page: page,
      },
      success: function (response) {
        if (response.success) {
          populateLogsTable(response.data.logs);
          updateCharts(response.data.logs);
        }
      },
      error: function () {
        showNotice("Error loading query logs", "error");
      },
    });
  }

  function populateLogsTable(logs) {
    const tbody = $("#query-logs-table tbody");
    tbody.empty();

    if (!logs || logs.length === 0) {
      tbody.append('<tr><td colspan="7">No query logs found</td></tr>');
      return;
    }

    logs.forEach(function (log) {
      const row = $("<tr>");

      // Format timestamp
      const timestamp = new Date(log.timestamp).toLocaleString();

      // Format execution time
      const executionTime = (parseFloat(log.execution_time) * 1000).toFixed(2) + " ms";

      // Format memory usage
      const memoryUsage = formatBytes(parseInt(log.memory_usage));

      // Error indicator
      const errorIndicator =
        log.error_count > 0
          ? `<span class="error-indicator">${log.error_count} errors</span>`
          : '<span class="success-indicator">No errors</span>';

      row.html(`
                <td>${timestamp}</td>
                <td>${log.operation_name || "Anonymous"}</td>
                <td class="${log.execution_time > 1 ? "slow-query" : ""}">${executionTime}</td>
                <td>${memoryUsage}</td>
                <td>${log.query_complexity}</td>
                <td>${errorIndicator}</td>
                <td>
                    <button class="button button-small view-details" data-id="${log.id}">View Details</button>
                </td>
            `);

      tbody.append(row);
    });

    // Add click handler for view details buttons
    $(".view-details").on("click", function () {
      const logId = $(this).data("id");
      const logData = logs.find((l) => l.id == logId);
      showQueryDetails(logData);
    });
  }

  function showQueryDetails(log) {
    let errorsHtml = "";
    if (log.errors && log.errors !== "null" && log.errors !== "[]") {
      try {
        const errors = JSON.parse(log.errors);
        errorsHtml =
          '<div class="error-details"><h4>Errors:</h4><pre>' + JSON.stringify(errors, null, 2) + "</pre></div>";
      } catch (e) {
        errorsHtml = '<div class="error-details"><h4>Errors:</h4><pre>' + log.errors + "</pre></div>";
      }
    }

    let variablesHtml = "";
    if (log.query_variables && log.query_variables !== "null" && log.query_variables !== "[]") {
      try {
        const variables = JSON.parse(log.query_variables);
        variablesHtml =
          '<div class="variables-section"><h4>Variables:</h4><pre>' +
          JSON.stringify(variables, null, 2) +
          "</pre></div>";
      } catch (e) {
        variablesHtml =
          '<div class="variables-section"><h4>Variables:</h4><pre>' + log.query_variables + "</pre></div>";
      }
    }

    const content = `
            <div class="query-details">
                <div class="detail-section">
                    <h4>Performance Metrics</h4>
                    <table class="detail-table">
                        <tr><td>Execution Time:</td><td>${(parseFloat(log.execution_time) * 1000).toFixed(
                          2
                        )} ms</td></tr>
                        <tr><td>Memory Usage:</td><td>${formatBytes(parseInt(log.memory_usage))}</td></tr>
                        <tr><td>Query Size:</td><td>${formatBytes(parseInt(log.query_size))}</td></tr>
                        <tr><td>Response Size:</td><td>${formatBytes(parseInt(log.response_size))}</td></tr>
                        <tr><td>Complexity:</td><td>${log.query_complexity}</td></tr>
                        <tr><td>Depth:</td><td>${log.query_depth}</td></tr>
                    </table>
                </div>
                
                <div class="detail-section">
                    <h4>Request Info</h4>
                    <table class="detail-table">
                        <tr><td>Operation Name:</td><td>${log.operation_name || "Anonymous"}</td></tr>
                        <tr><td>Timestamp:</td><td>${new Date(log.timestamp).toLocaleString()}</td></tr>
                        <tr><td>User ID:</td><td>${log.user_id || "Guest"}</td></tr>
                        <tr><td>IP Address:</td><td>${log.ip_address}</td></tr>
                    </table>
                </div>
                
                ${errorsHtml}
                ${variablesHtml}
                
                <div class="detail-section">
                    <h4>GraphQL Query</h4>
                    <pre class="query-text">${log.query_text}</pre>
                </div>
            </div>
        `;

    $("#query-detail-content").html(content);
    $("#query-detail-modal").show();
  }

  function loadCharts() {
    // Performance Chart
    const performanceCtx = $("#performance-chart")[0].getContext("2d");
    performanceChart = new Chart(performanceCtx, {
      type: "line",
      data: {
        labels: [],
        datasets: [
          {
            label: "Execution Time (ms)",
            data: [],
            borderColor: "rgb(75, 192, 192)",
            tension: 0.1,
          },
        ],
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      },
    });

    // Complexity Chart
    const complexityCtx = $("#complexity-chart")[0].getContext("2d");
    complexityChart = new Chart(complexityCtx, {
      type: "doughnut",
      data: {
        labels: ["Low (0-50)", "Medium (51-100)", "High (101-200)", "Very High (201+)"],
        datasets: [
          {
            data: [0, 0, 0, 0],
            backgroundColor: ["rgb(75, 192, 192)", "rgb(255, 205, 86)", "rgb(255, 99, 132)", "rgb(201, 203, 207)"],
          },
        ],
      },
      options: {
        responsive: true,
      },
    });

    // Memory Chart
    const memoryCtx = $("#memory-chart")[0].getContext("2d");
    memoryChart = new Chart(memoryCtx, {
      type: "bar",
      data: {
        labels: [],
        datasets: [
          {
            label: "Memory Usage (MB)",
            data: [],
            backgroundColor: "rgba(54, 162, 235, 0.2)",
            borderColor: "rgba(54, 162, 235, 1)",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      },
    });

    // Response Size Chart
    const responseSizeCtx = $("#response-size-chart")[0].getContext("2d");
    responseSizeChart = new Chart(responseSizeCtx, {
      type: "scatter",
      data: {
        datasets: [
          {
            label: "Response Size vs Execution Time",
            data: [],
            backgroundColor: "rgba(255, 99, 132, 0.2)",
            borderColor: "rgba(255, 99, 132, 1)",
          },
        ],
      },
      options: {
        responsive: true,
        scales: {
          x: {
            title: {
              display: true,
              text: "Response Size (KB)",
            },
          },
          y: {
            title: {
              display: true,
              text: "Execution Time (ms)",
            },
          },
        },
      },
    });
  }

  function updateCharts(logs) {
    if (!logs || logs.length === 0) return;

    // Update Performance Chart
    const performanceData = logs.slice(0, 20).reverse();
    performanceChart.data.labels = performanceData.map((log) => new Date(log.timestamp).toLocaleTimeString());
    performanceChart.data.datasets[0].data = performanceData.map((log) =>
      (parseFloat(log.execution_time) * 1000).toFixed(2)
    );
    performanceChart.update();

    // Update Complexity Chart
    const complexityBuckets = [0, 0, 0, 0];
    logs.forEach((log) => {
      const complexity = parseInt(log.query_complexity);
      if (complexity <= 50) complexityBuckets[0]++;
      else if (complexity <= 100) complexityBuckets[1]++;
      else if (complexity <= 200) complexityBuckets[2]++;
      else complexityBuckets[3]++;
    });
    complexityChart.data.datasets[0].data = complexityBuckets;
    complexityChart.update();

    // Update Memory Chart
    const memoryData = logs.slice(0, 10).reverse();
    memoryChart.data.labels = memoryData.map((log) => log.operation_name || "Anonymous");
    memoryChart.data.datasets[0].data = memoryData.map((log) => (parseInt(log.memory_usage) / 1024 / 1024).toFixed(2));
    memoryChart.update();

    // Update Response Size Chart
    const scatterData = logs.map((log) => ({
      x: (parseInt(log.response_size) / 1024).toFixed(2),
      y: (parseFloat(log.execution_time) * 1000).toFixed(2),
    }));
    responseSizeChart.data.datasets[0].data = scatterData;
    responseSizeChart.update();
  }

  function clearAllLogs() {
    if (!confirm("Are you sure you want to clear all query logs? This action cannot be undone.")) {
      return;
    }

    $.ajax({
      url: wpgraphqlMonitor.ajax_url,
      type: "POST",
      data: {
        action: "clear_logs",
        nonce: wpgraphqlMonitor.nonce,
      },
      success: function (response) {
        if (response.success) {
          showNotice("All logs cleared successfully", "success");
          loadQueryLogs();
          loadStats();
          // Clear all charts
          clearCharts();
        } else {
          showNotice("Error clearing logs", "error");
        }
      },
      error: function () {
        showNotice("Error clearing logs", "error");
      },
    });
  }

  function clearCharts() {
    // Clear all chart data
    if (performanceChart) {
      performanceChart.data.labels = [];
      performanceChart.data.datasets[0].data = [];
      performanceChart.update();
    }

    if (complexityChart) {
      complexityChart.data.datasets[0].data = [0, 0, 0, 0];
      complexityChart.update();
    }

    if (memoryChart) {
      memoryChart.data.labels = [];
      memoryChart.data.datasets[0].data = [];
      memoryChart.update();
    }

    if (responseSizeChart) {
      responseSizeChart.data.datasets[0].data = [];
      responseSizeChart.update();
    }
  }

  function formatBytes(bytes) {
    if (bytes >= 1073741824) {
      return (bytes / 1073741824).toFixed(2) + " GB";
    } else if (bytes >= 1048576) {
      return (bytes / 1048576).toFixed(2) + " MB";
    } else if (bytes >= 1024) {
      return (bytes / 1024).toFixed(2) + " KB";
    } else {
      return bytes + " bytes";
    }
  }

  function showNotice(message, type) {
    const noticeClass = type === "success" ? "notice-success" : "notice-error";
    const notice = $(`
            <div class="notice ${noticeClass} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `);

    $(".wrap h1").after(notice);

    // Auto-dismiss after 5 seconds
    setTimeout(function () {
      notice.fadeOut(function () {
        $(this).remove();
      });
    }, 5000);

    // Manual dismiss
    notice.find(".notice-dismiss").on("click", function () {
      notice.fadeOut(function () {
        $(this).remove();
      });
    });
  }

  // Real-time updates
  function startRealTimeUpdates() {
    // Check if page is visible
    if (document.hidden) {
      return;
    }

    // Update every 30 seconds
    setInterval(function () {
      if (!document.hidden) {
        loadStats();
        loadQueryLogs();
      }
    }, 30000);
  }

  // Handle page visibility changes
  $(document).on("visibilitychange", function () {
    if (!document.hidden) {
      // Page became visible, refresh data
      loadStats();
      loadQueryLogs();
    }
  });

  // Search and filter functionality
  function initializeFilters() {
    // Add search input
    const searchInput = $("<input>", {
      type: "text",
      placeholder: "Search queries...",
      class: "search-input",
      style: "margin-left: 10px; padding: 5px;",
    });

    $("#refresh-logs").after(searchInput);

    // Add filter dropdown
    const filterSelect = $("<select>", {
      class: "filter-select",
      style: "margin-left: 10px; padding: 5px;",
    });

    filterSelect.append('<option value="">All Queries</option>');
    filterSelect.append('<option value="errors">With Errors</option>');
    filterSelect.append('<option value="slow">Slow Queries (>1s)</option>');
    filterSelect.append('<option value="complex">Complex Queries</option>');

    searchInput.after(filterSelect);

    // Add event listeners
    let searchTimeout;
    searchInput.on("input", function () {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(function () {
        applyFilters();
      }, 300);
    });

    filterSelect.on("change", applyFilters);
  }

  function applyFilters() {
    const searchTerm = $(".search-input").val().toLowerCase();
    const filterType = $(".filter-select").val();

    $("#query-logs-table tbody tr").each(function () {
      const row = $(this);
      const operationName = row.find("td:nth-child(2)").text().toLowerCase();
      const executionTime = parseFloat(row.find("td:nth-child(3)").text());
      const hasErrors = row.find(".error-indicator").length > 0;
      const complexity = parseInt(row.find("td:nth-child(5)").text());

      let showRow = true;

      // Apply search filter
      if (searchTerm && !operationName.includes(searchTerm)) {
        showRow = false;
      }

      // Apply type filter
      if (filterType) {
        switch (filterType) {
          case "errors":
            showRow = showRow && hasErrors;
            break;
          case "slow":
            showRow = showRow && executionTime > 1000;
            break;
          case "complex":
            showRow = showRow && complexity > 100;
            break;
        }
      }

      row.toggle(showRow);
    });
  }

  // Initialize dashboard
  function initializeDashboard() {
    initDashboard();
    initializeFilters();
    startRealTimeUpdates();
  }

  // Update the document ready function
  $(document).ready(function () {
    initializeDashboard();
  });
})(jQuery);
