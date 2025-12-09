HTML
<x-app-layout>
    <div style="padding: 30px;">
        <h2><i class="bi bi-bar-chart-fill"></i> System Reports</h2>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-top: 20px;">
            <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <h4 style="color: #28a745;">Financial Report</h4>
                <div style="font-size: 24px; font-weight: bold; margin: 10px 0;">
                    Total Income: {{ number_format($totalIncome) }} BDT
                </div>
                <div style="color: #666;">Last 30 Days: {{ number_format($monthlyIncome) }} BDT</div>
            </div>

            <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <h4 style="color: #007bff;">User Growth</h4>
                <div style="font-size: 24px; font-weight: bold; margin: 10px 0;">
                    New Users (7 Days): +{{ $newUsers }}
                </div>
                <div style="color: #666;">Active Subscriptions: {{ $activeSubs }}</div>
            </div>
        </div>

        <button onclick="window.print()" style="margin-top: 30px; padding: 10px 20px; background: #333; color: white; border: none; border-radius: 5px;">
            <i class="bi bi-printer"></i> Print Report
        </button>
    </div></x-app-layout>