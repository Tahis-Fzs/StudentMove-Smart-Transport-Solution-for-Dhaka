<x-app-layout>
    <style>
        .reports-page {
            width: 100% !important;
            max-width: 100% !important;
            padding: 30px;
            box-sizing: border-box;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .reports-page h2 {
            color: #1a202c !important;
            margin-bottom: 30px;
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .reports-page h2 i {
            color: #667eea;
            margin-right: 15px;
        }
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        .report-card {
            background: white;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12), 0 4px 15px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .report-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, transparent, currentColor, transparent);
            opacity: 0.3;
        }
        .report-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 50px rgba(0,0,0,0.15), 0 6px 20px rgba(0,0,0,0.1);
        }
        .report-card-financial {
            border-top: 5px solid #10b981;
        }
        .report-card-financial::before {
            background: linear-gradient(90deg, transparent, #10b981, transparent);
        }
        .report-card-financial .card-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .report-card-users {
            border-top: 5px solid #3b82f6;
        }
        .report-card-users::before {
            background: linear-gradient(90deg, transparent, #3b82f6, transparent);
        }
        .report-card-users .card-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }
        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a202c;
            margin: 0;
        }
        .card-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1a202c;
            margin: 15px 0;
            line-height: 1.2;
        }
        .card-label {
            font-size: 1rem;
            color: #6b7280;
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-subvalue {
            font-size: 1.3rem;
            font-weight: 600;
            color: #4b5563;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px dashed #e5e7eb;
        }
        .print-btn {
            margin-top: 40px;
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        @media (max-width: 768px) {
            .reports-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <div class="reports-page">
        <h2><i class="bi bi-bar-chart-fill"></i> System Reports</h2>

        <div class="reports-grid">
            <div class="report-card report-card-financial">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <h3 class="card-title">Financial Report</h3>
                </div>
                <div class="card-value">
                    {{ number_format($totalIncome) }} BDT
                </div>
                <div class="card-label">
                    <i class="bi bi-info-circle"></i> Total Income
                </div>
                <div class="card-subvalue">
                    <i class="bi bi-calendar-month"></i> Last 30 Days: <strong>{{ number_format($monthlyIncome) }} BDT</strong>
                </div>
            </div>

            <div class="report-card report-card-users">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h3 class="card-title">User Growth</h3>
                </div>
                <div class="card-value" style="color: #3b82f6;">
                    +{{ $newUsers }}
                </div>
                <div class="card-label">
                    <i class="bi bi-clock-history"></i> New Users (Last 7 Days)
                </div>
                <div class="card-subvalue">
                    <i class="bi bi-check-circle-fill"></i> Active Subscriptions: <strong>{{ $activeSubs }}</strong>
                </div>
            </div>
        </div>

        <button onclick="window.print()" class="print-btn">
            <i class="bi bi-printer-fill"></i> Print Report
        </button>
    </div>
</x-app-layout>