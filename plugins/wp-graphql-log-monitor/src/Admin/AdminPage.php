<?php
namespace WPGraphQL\LogMonitor\Admin;

class AdminPage {
    
    public function register_hooks() {
        add_action('admin_menu', [$this, 'add_admin_menu'], 999);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'graphiql-ide',
            'GraphQL Log Monitor',
            'Log Monitor',
            'manage_options',
            'graphql-log-monitor',
            [$this, 'render_admin_page']
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'graphql_page_graphql-log-monitor') {
            return;
        }
        
        wp_enqueue_script('chart-js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js', [], '3.9.1', true);
        wp_enqueue_script('graphql-log-monitor-admin', WPGRAPHQL_MONITOR_PLUGIN_URL . 'assets/admin.js', ['jquery', 'chart-js'], WPGRAPHQL_MONITOR_VERSION, true);
        wp_enqueue_style('graphql-log-monitor-admin', WPGRAPHQL_MONITOR_PLUGIN_URL . 'assets/admin.css', [], WPGRAPHQL_MONITOR_VERSION);
        
        wp_localize_script('graphql-log-monitor-admin', 'wpgraphqlMonitor', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpgraphql_monitor_nonce')
        ]);
    }
    
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>WPGraphQL Log Monitor</h1>
            
            <div class="graphql-log-monitor-dashboard">
                <div class="stats-row">
                    <div class="stat-card">
                        <h3>Total Queries (24h)</h3>
                        <div class="stat-value" id="total-queries">-</div>
                    </div>
                    <div class="stat-card">
                        <h3>Avg Response Time</h3>
                        <div class="stat-value" id="avg-response-time">-</div>
                    </div>
                    <div class="stat-card">
                        <h3>Error Rate</h3>
                        <div class="stat-value" id="error-rate">-</div>
                    </div>
                    <div class="stat-card">
                        <h3>Slowest Query</h3>
                        <div class="stat-value" id="slowest-query">-</div>
                    </div>
                </div>
                
                <div class="charts-row">
                    <div class="chart-container">
                        <h3>Query Performance Over Time</h3>
                        <canvas id="performance-chart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h3>Query Complexity Distribution</h3>
                        <canvas id="complexity-chart"></canvas>
                    </div>
                </div>
                
                <div class="charts-row">
                    <div class="chart-container">
                        <h3>Memory Usage</h3>
                        <canvas id="memory-chart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h3>Response Size Distribution</h3>
                        <canvas id="response-size-chart"></canvas>
                    </div>
                </div>
                
                <div class="table-container">
                    <div class="table-header">
                        <h3>Recent Queries</h3>
                        <div class="table-actions">
                            <button id="refresh-logs" class="button">Refresh</button>
                            <button id="clear-logs" class="button button-secondary">Clear All Logs</button>
                        </div>
                    </div>
                    <table id="query-logs-table" class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Operation</th>
                                <th>Execution Time</th>
                                <th>Memory Usage</th>
                                <th>Complexity</th>
                                <th>Errors</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Query Detail Modal -->
            <div id="query-detail-modal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h3>Query Details</h3>
                    <div id="query-detail-content"></div>
                </div>
            </div>
        </div>
        <?php
    }
}