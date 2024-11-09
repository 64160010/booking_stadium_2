@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- การ์ดหลักของหน้า Dashboard -->
            <div class="card shadow-lg border-0">
                <!-- ส่วนหัวของการ์ด: แสดงชื่อ Dashboard -->
                <div class="card-header bg-danger text-white text-center">
                    <h4 class="mb-0">การจัดการ</h4>
                </div>
                <div class="card-body p-3">
                    <!-- แสดงข้อความสถานะการทำงานสำเร็จ -->
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- เริ่มต้นการแสดงการ์ดข้อมูลต่างๆ -->
                    <div class="row g-4">
                        @foreach ([ 
                            // การ์ดที่ 1: การจัดการสมาชิก
                            ['info', 
                            'การจัดการสมาชิก', 
                            'เพิ่ม ลบ และแก้ไขข้อมูลสมาชิก', 
                            route('users.index'), 
                            'จัดการสมาชิก', 
                            "มีผู้ใช้ทั้งหมด: $userCount ท่าน"],

                            // การ์ดที่ 2: การจองสนาม
                            ['success', 
                            'การจองสนาม', 
                            'ดูและจัดการการจองสนามทั้งหมด', 
                            route('stadiums.index'), 
                            'จัดการการจอง',
                            "มีสนามทั้งหมด: $stadiumCount สนาม"],

                            // การ์ดที่ 3: สถานะการชำระเงิน
                            ['warning', 
                            'สถานะการชำระเงิน', 
                            'ตรวจสอบและอัพเดตสถานะการชำระเงิน', 
                            route('history.booking'),
                            'จัดการสถานะ'], 

                            // การ์ดที่ 4: การยืมอุปกรณ์
                            ['danger', 
                            'การยืมอุปกรณ์', 
                            'ตรวจสอบและจัดการการยืมอุปกรณ์', 
                            route('lending.index'), 
                            'จัดการการยืม'],
                            
                            // การ์ดที่ 5: ยืม-คืน-ซ่อม
                            ['primary', 
                            'ยืม-คืน-ซ่อม', 
                            'จัดการและตรวจสอบการยืม คืน และซ่อมแซมอุปกรณ์', 
                            route('admin.borrow'), 
                            'จัดการยืม-คืน-ซ่อม']
                            
                        ] as $card) 
                        <div class="col-md-3">
                            <a href="{{ $card[3] }}" class="text-decoration-none">
                                <div class="card text-white bg-{{ $card[0] }} shadow-sm h-100 card-hover">
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <h5 class="card-title">
                                            <i class="bi bi-box-arrow-up-right"></i> {{ $card[1] }}
                                        </h5>
                                        <p class="card-text">{{ $card[2] }}</p>
                                        @if(isset($card[5]))
                                            <p class="mb-0"><small>{{ $card[5] }}</small></p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>

                   
                     <!-- กราฟการจองสนามรายเดือน และ ราคารวมรายวัน -->
                     <div class="row mt-5">
                        <div class="col-md-6">
                            <h5 class="text-center">จำนวนการจองสนามรายเดือน</h5>
                            <!-- กำหนดขนาดให้กับ canvas -->
                            <canvas id="stadiumBookingChart" width="400" height="300"></canvas> <!-- ปรับขนาดที่นี่ -->
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-center">ราคารวมรายวันของรายการจองและรายการยืม</h5>
                            <!-- กำหนดขนาดให้กับ canvas -->
                            <canvas id="dailyRevenueChart" width="400" height="300"></canvas> <!-- ปรับขนาดที่นี่ -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript สำหรับเพิ่มเอฟเฟกต์ hover บนการ์ด -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx1 = document.getElementById('stadiumBookingChart').getContext('2d');
        const monthlyBookings = @json($monthlyBookings);

        // กราฟการจองสนามรายเดือน
        const stadiumData = {};
        monthlyBookings.forEach(item => {
            const month = item.month;
            const stadiumName = item.stadium_name; 
            const count = item.total_bookings;

            if (!stadiumData[stadiumName]) {
                stadiumData[stadiumName] = Array(12).fill(0); // เตรียมข้อมูลเป็น 12 เดือน
            }
            stadiumData[stadiumName][month - 1] = count; // อัปเดตจำนวนการจอง
        });

        const datasets = Object.keys(stadiumData).map((stadiumName, index) => {
            return {
                label: stadiumName, // เปลี่ยนชื่อสนามเป็นชื่อในกราฟ
                data: stadiumData[stadiumName],
                backgroundColor: `hsl(${index * 60}, 70%, 50%)`, // ใช้สีสำหรับกราฟแท่ง
            };
        });

        new Chart(ctx1, {
            type: 'bar', 
            data: {
                labels: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'],
                datasets: datasets
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'จำนวนการจอง'
                        },
                        ticks: {
                            stepSize: 20, // ระยะห่างระหว่างค่าที่แสดง
                            callback: function(value) {
                                return value % 20 === 0 ? value : ''; // แสดงเฉพาะค่าที่เป็นทวีคูณของ 10
                            }
                        },
                        max: 100 // กำหนดค่าสูงสุดของแกน Y
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'เดือน'
                        }
                    }
                }
            }
        });

        const ctx2 = document.getElementById('dailyRevenueChart').getContext('2d');
const dailyRevenueBorrow = @json($dailyRevenueBorrow); // ข้อมูลรายวันจากการยืม
const dailyRevenueBooking = @json($dailyRevenueBooking); // ข้อมูลรายวันจากการจอง


        // กราฟแสดงราคารวมรายวัน
        const borrowDates = dailyRevenueBorrow.map(item => item.date);
        const borrowRevenue = dailyRevenueBorrow.map(item => item.total_revenue);
        
        const bookingDates = dailyRevenueBooking.map(item => item.date);
        const bookingRevenue = dailyRevenueBooking.map(item => item.total_revenue);

        new Chart(ctx2, {
            type: 'line', 
            data: {
                labels: borrowDates.length > bookingDates.length ? borrowDates : bookingDates, // วันที่ที่มีมากที่สุด
                datasets: [
                    {
                        label: 'รายได้จากการยืม', // เส้นกราฟการยืม
                        data: borrowDates.map(date => {
                            const index = borrowDates.indexOf(date);
                            return borrowRevenue[index] || 0;
                        }),
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: true,
                    },
                    {
                        label: 'รายได้จากการจอง', // เส้นกราฟการจอง
                        data: bookingDates.map(date => {
                            const index = bookingDates.indexOf(date);
                            return bookingRevenue[index] || 0;
                        }),
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        title: {
                            display: true,
                            text: 'รายได้ (บาท)'
                        },
                        beginAtZero: true,
                    }
                }
            }
        });
    });
</script>
@endsection