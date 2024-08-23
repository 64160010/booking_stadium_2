<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
        }
        form {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        select, button {
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 16px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }
        button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .button-group form {
            display: flex;
            align-items: center;
        }
        .button-group button {
            padding: 10px 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            table-layout: fixed;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
            height: 80px;
            overflow: hidden;
        }
        th {
            background-color: #f8f9fa;
            color: #495057;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:nth-child(odd) {
            background-color: #fff;
        }
        td {
            vertical-align: top;
        }
    </style>
    
</head>
<body>
    
    <div class="container">
        @php
            // แปลงปี ค.ศ. เป็น พ.ศ.
            $yearBE = $year + 543;
            
            // ชื่อเดือนเป็นภาษาไทย
            $months = [
                1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน', 
                5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม', 
                9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
            ];

            // คำนวณเดือนก่อนหน้าและเดือนถัดไป
            $prevMonth = $month - 1;
            $nextMonth = $month + 1;

            if ($prevMonth < 1) {
                $prevMonth = 12;
                $prevYear = $year - 1;
            } else {
                $prevYear = $year;
            }

            if ($nextMonth > 12) {
                $nextMonth = 1;
                $nextYear = $year + 1;
            } else {
                $nextYear = $year;
            }
        @endphp
        <h2>{{ $months[$month] }} {{ $yearBE }}</h2>
        
        
        
        

        <!-- ฟอร์มสำหรับเลือกเดือนและปี -->
        <form method="POST" action="{{ route('calendar') }}">
            @csrf
            <select name="month" onchange="this.form.submit()">
                @foreach ($months as $m => $monthName)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                        {{ $monthName }}
                    </option>
                @endforeach
            </select>
            <select name="year" onchange="this.form.submit()">
                @for ($y = 2024; $y <= 2026; $y++)
                    @php
                        $yBE = $y + 543;
                    @endphp
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>
                        {{ $yBE }}
                    </option>
                @endfor
            </select>
        </form>
        
        <!-- กลุ่มปุ่มเดือนก่อนหน้าและเดือนถัดไป -->
        <div class="button-group">
            <form method="POST" action="{{ route('calendar') }}">
                @csrf
                <input type="hidden" name="month" value="{{ $prevMonth }}">
                <input type="hidden" name="year" value="{{ $prevYear }}">
                <button type="submit" style="background-color: #6c757d;">เดือนก่อนหน้า</button>
            </form>
            
            <form method="POST" action="{{ route('calendar') }}">
                @csrf
                <input type="hidden" name="month" value="{{ $nextMonth }}">
                <input type="hidden" name="year" value="{{ $nextYear }}">
                <button type="submit" style="background-color: #28a745;">เดือนถัดไป</button>
            </form>
        </div>

        <!-- ตารางปฏิทิน -->
        <table>
            <tr>
                <th>อาทิตย์</th>
                <th>จันทร์</th>
                <th>อังคาร</th>
                <th>พุธ</th>
                <th>พฤหัสบดี</th>
                <th>ศุกร์</th>
                <th>เสาร์</th>
            </tr>
            <tr>
                @for ($i = 0; $i < $firstDayOfWeek; $i++)
                    <td></td>
                @endfor

                @for ($day = 1; $day <= $daysInMonth; $day++, $i++)
                    <td>{{ $day }}</td>
                    @if ($i % 7 == 6)
                        </tr><tr>
                    @endif
                @endfor

                @for (; $i % 7 != 0; $i++)
                    <td></td>
                @endfor
            </tr>
        </table>
    </div>
    <div class="container mt-5">
        <a href="/" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M5.854 4.146a.5.5 0 0 1 0 .708L2.707 8l3.147 3.146a.5.5 0 0 1-.708.708l-3.5-3.5a.5.5 0 0 1 0-.708l3.5-3.5a.5.5 0 0 1 .708 0z"/>
                <path fill-rule="evenodd" d="M13.5 8a.5.5 0 0 1-.5.5H2.5a.5.5 0 0 1 0-1h10.5a.5.5 0 0 1 .5.5z"/>
            </svg>
            ย้อนกลับ
        </a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
