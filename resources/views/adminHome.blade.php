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
                            'การจัดการสนาม', 
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
                            'การจัดการอุปกรณ์', 
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

                   
                    <div class="container my-5">
                        
                        <h2 class="text-center mb-4">สรุปรายงาน</h2>
                        
                        <div class="row g-4">
                            <!-- การจองสนามรายเดือน -->
                            <div class="col-lg-4 col-md-6">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title text-center">จำนวนการจองสนามรายเดือน</h5>
                                        <canvas id="stadiumBookingChart" width="400" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                    
                            <!-- ราคารวมรายวันของการจองและยืม -->
                            <div class="col-lg-4 col-md-6">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title text-center">ราคารวมรายวันของรายการจองและรายการยืม</h5>
                                        <canvas id="dailyRevenueChart" width="400" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                    
                            <!-- จำนวนผู้ใช้ที่หมดอายุการชำระเงินและถูกปฏิเสธการชำระเงิน -->
                            <div class="col-lg-4 col-md-6">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title text-center">จำนวนผู้ใช้ที่หมดอายุการชำระเงินและถูกปฏิเสธการชำระเงินรายเดือน</h5>
                                        <canvas id="expiredPaymentChart" width="400" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                    
                            <!-- จำนวนอุปกรณ์ที่ซ่อมรายวัน -->
                            <div class="col-lg-4 col-md-6">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title text-center">จำนวนอุปกรณ์ที่ซ่อมรายวัน</h5>
                                        <canvas id="repairChart" width="400" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                    
                            <!-- อุปกรณ์ที่ซ่อมไม่ได้แล้ว -->
                            <div class="col-lg-4 col-md-6">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title text-center">อุปกรณ์ที่ซ่อมไม่ได้แล้ว</h5>
                                        <canvas id="unrepairableChart" width="400" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                            
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
        // กราฟการจองสนามรายเดือน
        const ctx1 = document.getElementById('stadiumBookingChart').getContext('2d');
        const monthlyBookings = @json($monthlyBookings);

        const stadiumData = {};
        monthlyBookings.forEach(item => {
            const month = item.month;
            const stadiumName = item.stadium_name;
            const count = item.total_bookings;

            if (!stadiumData[stadiumName]) {
                stadiumData[stadiumName] = Array(12).fill(0);
            }
            stadiumData[stadiumName][month - 1] = count;
        });

        const datasets = Object.keys(stadiumData).map((stadiumName, index) => {
            return {
                label: stadiumName,
                data: stadiumData[stadiumName],
                backgroundColor: `hsl(${index * 60}, 70%, 50%)`,
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
                            stepSize: 20,
                            callback: function(value) {
                                return value % 20 === 0 ? value : '';
                            }
                        },
                        max: 100
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

        // กราฟแสดงราคารวมรายวัน
        const ctx2 = document.getElementById('dailyRevenueChart').getContext('2d');
        const dailyRevenueBorrow = @json($dailyRevenueBorrow);
        const dailyRevenueBooking = @json($dailyRevenueBooking);

        const borrowDates = dailyRevenueBorrow.map(item => item.date);
        const borrowRevenue = dailyRevenueBorrow.map(item => item.total_revenue);
        
        const bookingDates = dailyRevenueBooking.map(item => item.date);
        const bookingRevenue = dailyRevenueBooking.map(item => item.total_revenue);

        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: borrowDates.length > bookingDates.length ? borrowDates : bookingDates,
                datasets: [
                    {
                        label: 'รายได้จากการยืม',
                        data: borrowDates.map(date => {
                            const index = borrowDates.indexOf(date);
                            return borrowRevenue[index] || 0;
                        }),
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: true,
                    },
                    {
                        label: 'รายได้จากการจอง',
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

        // แปลงเดือนเป็นชื่อเดือนภาษาไทย
    const monthNamesThai = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

// กราฟแสดงจำนวนผู้ใช้ที่หมดอายุการชำระเงินรายเดือน
const ctx3 = document.getElementById('expiredPaymentChart').getContext('2d');

// ข้อมูลผู้ใช้ที่หมดอายุการชำระเงิน
const expiredPaymentMonths = @json($expiredPaymentMonths);
const expiredPaymentUsers = @json($expiredPaymentUsers);

// ข้อมูลผู้ใช้ที่ถูกปฏิเสธการชำระเงิน
const deniedPaymentMonths = @json($deniedPaymentMonths); // เดือนที่มีการถูกปฏิเสธการชำระเงิน
const deniedPaymentUsers = @json($deniedPaymentUsers); // จำนวนผู้ใช้ที่ถูกปฏิเสธการชำระเงิน

// แสดงข้อมูลเดือนทั้งหมด (ม.ค. ถึง ธ.ค.)
const formattedExpiredPaymentMonths = monthNamesThai; // ใช้ชื่อเดือนภาษาไทยทั้งหมด

// เติมข้อมูลที่ไม่มีในเดือนใดๆ ให้เป็น 0 สำหรับผู้ใช้ที่หมดอายุการชำระเงิน
const formattedExpiredPaymentUsers = monthNamesThai.map((month, index) => {
    const monthIndex = expiredPaymentMonths.indexOf(index + 1);
    return monthIndex !== -1 ? expiredPaymentUsers[monthIndex] : 0;
});

// เติมข้อมูลที่ไม่มีในเดือนใดๆ ให้เป็น 0 สำหรับผู้ใช้ที่ถูกปฏิเสธการชำระเงิน
const formattedDeniedPaymentUsers = monthNamesThai.map((month, index) => {
    const monthIndex = deniedPaymentMonths.indexOf(index + 1);
    return monthIndex !== -1 ? deniedPaymentUsers[monthIndex] : 0;
});

// สร้างกราฟ
new Chart(ctx3, {
    type: 'bar',
    data: {
        labels: formattedExpiredPaymentMonths, // ใช้ชื่อเดือนภาษาไทยทั้งหมด
        datasets: [
            {
                label: 'จำนวนผู้ใช้ที่หมดอายุการชำระเงิน',
                data: formattedExpiredPaymentUsers, // จำนวนผู้ใช้ที่หมดอายุการชำระเงิน
                borderColor: 'rgb(255, 159, 64)',
                backgroundColor: 'rgba(255, 159, 64, 0.2)',
                fill: true,
            },
            {
                label: 'จำนวนผู้ใช้ที่ถูกปฏิเสธการชำระเงิน',
                data: formattedDeniedPaymentUsers, // จำนวนผู้ใช้ที่ถูกปฏิเสธการชำระเงิน
                borderColor: 'rgb(255, 99, 155)', // สีของกราฟเส้นที่สอง
                backgroundColor: 'rgba(255, 99, 132, 0.2)', // สีพื้นหลังของกราฟเส้นที่สอง
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
                    text: 'จำนวนผู้ใช้'
                },
                beginAtZero: true,
            }
        }
    }
});
});

// เตรียมข้อมูลสำหรับกราฟการซ่อมรายวัน
// ดึงข้อมูลวันที่และอุปกรณ์ที่ซ่อมแยกตามประเภท
const repairDataByDateAndItem = @json($repairDataByDateAndItem);

// เตรียมข้อมูลวันที่
const repairDates = Object.keys(repairDataByDateAndItem);

// หาชื่ออุปกรณ์ทั้งหมด
const itemNames = [...new Set(repairDates.flatMap(date => Object.keys(repairDataByDateAndItem[date])))];

// สร้าง dataset สำหรับกราฟ แยกตามชื่อของอุปกรณ์
const datasets = itemNames.map((itemName, index) => {
    const data = repairDates.map(date => repairDataByDateAndItem[date][itemName] || 0);
    return {
        label: itemName,
        data: data,
        backgroundColor: `rgba(${54 + index * 20}, ${162 - index * 10}, ${235 - index * 10}, 0.2)`, // สีพื้นหลังที่แตกต่างกัน
        borderColor: `rgb(${54 + index * 20}, ${162 - index * 10}, ${235 - index * 10})`, // สีขอบที่แตกต่างกัน
        borderWidth: 1
    };
});

// สร้างกราฟด้วยข้อมูลที่จัดเตรียม
const ctx4 = document.getElementById('repairChart').getContext('2d');
new Chart(ctx4, {
    type: 'bar',
    data: {
        labels: repairDates, // ใช้วันที่เป็น label
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
                title: {
                    display: true,
                    text: 'จำนวนอุปกรณ์ที่ซ่อม'
                },
                beginAtZero: true,
            },
            x: {
                title: {
                    display: true,
                    text: 'วันที่'
                }
            }
        }
    }
});

// ดึงข้อมูลวันที่และอุปกรณ์ที่ซ่อมไม่ได้แยกตามประเภท
const unrepairableDataByDateAndItem = @json($unrepairableDataByDateAndItem);

// เตรียมข้อมูลวันที่
const unrepairableDates = Object.keys(unrepairableDataByDateAndItem);

// หาชื่ออุปกรณ์ทั้งหมดที่ซ่อมไม่ได้
const unrepairableItemNames = [...new Set(unrepairableDates.flatMap(date => Object.keys(unrepairableDataByDateAndItem[date])))];

// สร้าง dataset สำหรับกราฟ แยกตามชื่อของอุปกรณ์ที่ซ่อมไม่ได้
const unrepairableDatasets = unrepairableItemNames.map((itemName, index) => {
    const data = unrepairableDates.map(date => unrepairableDataByDateAndItem[date][itemName] || 0);
    return {
        label: itemName,
        data: data,
        backgroundColor: `rgba(${255 - index * 20}, ${99 + index * 10}, ${132 - index * 5}, 0.2)`, // สีพื้นหลังที่แตกต่างกัน
        borderColor: `rgb(${255 - index * 20}, ${99 + index * 10}, ${132 - index * 5})`, // สีขอบที่แตกต่างกัน
        borderWidth: 1
    };
});

// สร้างกราฟสำหรับแสดงอุปกรณ์ที่ซ่อมไม่ได้รายวัน
const ctx5 = document.getElementById('unrepairableChart').getContext('2d');
new Chart(ctx5, {
    type: 'bar',
    data: {
        labels: unrepairableDates, // ใช้วันที่เป็น label
        datasets: unrepairableDatasets
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
                    text: 'จำนวนอุปกรณ์ที่ซ่อมไม่ได้'
                },
                beginAtZero: true,
            },
            x: {
                title: {
                    display: true,
                    text: 'วันที่'
                }
            }
        }
    }
});


</script>

@endsection