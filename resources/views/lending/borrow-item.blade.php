@extends('layouts.app')

@section('title', 'ยืมอุปกรณ์')

@section('content')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center font-weight-bold" style="font-size: 1.5rem; ">
                        {{ __('ยืมอุปกรณ์') }}
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('borrow-item.store') }}">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $item->id }}">

                            <!-- ข้อมูลอุปกรณ์ -->
                            <div class="form-row d-flex justify-content-between" style="gap: 5px;">

                                <div class="form-group col-md-6">
                                    <label for="item_name">{{ __('ชื่ออุปกรณ์') }}</label>
                                    <input type="text" id="item_name" name="item_name" class="form-control"
                                        style="background-color:#e2e2e2" value="{{ $item->item_name }}" readonly>
                                </div>


                                <div class="form-group col-md-6">
                                    <label for="item_code">{{ __('รหัสอุปกรณ์') }}</label>
                                    <input type="text" id="item_code" name="item_code" class="form-control"
                                        style="background-color:#e2e2e2" value="{{ $item->item_code }}" readonly>
                                </div>
                            </div>



                            <div class="form-row d-flex justify-content-between" style="gap: 5px;">
                                <div class="form-group col-md-6">
                                    <label for="item_type">{{ __('ประเภท') }}</label>
                                    <input type="text" id="item_type" name="item_type" class="form-control"
                                        style="background-color:#e2e2e2" value="{{ $item->itemType->type_name }}" readonly>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="price">{{ __('ราคา') }}</label>
                                    <input type="text" id="price" name="price" class="form-control"
                                        style="background-color:#e2e2e2" value="{{ $item->price }} บาท" readonly>
                                </div>
                            </div>

                            <!-- ฟอร์มยืมอุปกรณ์ -->
                            <div class="form-row d-flex justify-content-between" style="gap: 5px;">
                                <div class="form-group col-md-6">
                                    <label for="borrow_date">{{ __('วันที่ยืม') }}</label>
                                    <input type="date" id="borrow_date" name="borrow_date" class="form-control" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="borrow_quantity">{{ __('จำนวน') }}</label>
                                    <input type="number" id="borrow_quantity" name="borrow_quantity" class="form-control"
                                        min="1" max="{{ $item->item_quantity }}" value="0" required>
                                </div>
                            </div>

                            <div class="form-row d-flex justify-content-between" style="gap: 5px;">
                                <div class="form-group col-md-6">
                                    <label for="borrow_start_hour">{{ __('ชั่วโมงเริ่มต้น') }}</label>
                                    <select id="borrow_start_hour" name="borrow_start_hour" class="form-control" required>
                                        @for ($i = 0; $i < 24; $i++)
                                            <option value="{{ $i }}">{{ sprintf('%02d:00', $i) }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="form-group col-md-6 ">
                                    <label for="borrow_end_hour">{{ __('ชั่วโมงสิ้นสุด') }}</label>
                                    <select id="borrow_end_hour" name="borrow_end_hour" class="form-control" required>
                                        @for ($i = 0; $i < 24; $i++)
                                            <option value="{{ $i }}">{{ sprintf('%02d:00', $i) }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="form-row d-flex justify-content-between" style="gap: 5px;">
                                <div class="form-group col-md-6 ">
                                    <label for="stadium_id">{{ __('เลือกสนามที่คุณใช้') }}</label>
                                    <select id="stadium_id" name="stadium_id" class="form-control" required>
                                        @foreach ($stadiums as $stadium)
                                            <option value="{{ $stadium->id }}">{{ $stadium->stadium_name }}</option>
                                        @endforeach
                                    </select>
                                </div>


                            

                        <!-- แสดงราคารวม -->
                        <div class="mt-3 text-center">
                            <strong>{{ __('ราคารวม: ') }}</strong><span id="total_price_display">0.00</span> {{ __('บาท') }}
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block mt-3">{{ __('ยืม') }}</button>
                    </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const borrowDateInput = document.getElementById('borrow_date');
            const today = new Date().toISOString().split('T')[0];
            const maxDate = new Date();
            maxDate.setDate(maxDate.getDate() + 7);
            const maxDateStr = maxDate.toISOString().split('T')[0];
    
            borrowDateInput.setAttribute('min', today);
            borrowDateInput.setAttribute('max', maxDateStr);
    
            // Format the date to Thai B.E.
            function formatDateToThai(dateString) {
                const monthNames = [
                    'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                    'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
                ];
    
                const date = new Date(dateString);
                const day = date.getDate();
                const month = monthNames[date.getMonth()];
                const year = date.getFullYear() + 543; // Convert to Thai year
    
                return `${day} ${month} ${year}`;
            }
    
            borrowDateInput.addEventListener('change', function() {
                const selectedDate = borrowDateInput.value;
                if (selectedDate) {
                    const formattedDate = formatDateToThai(selectedDate);
                    console.log('วันที่เลือก:', formattedDate);
                }
            });
    
            // Calculate total price when quantity changes
            const priceInput = document.getElementById('price');
            const quantityInput = document.getElementById('borrow_quantity');
            const totalPriceDisplay = document.getElementById('total_price_display');
    
            function updateTotalPrice() {
                const pricePerUnit = parseFloat(priceInput.value.replace(' บาท', '').replace(',', '.'));
                const quantity = parseInt(quantityInput.value);
                const totalPrice = pricePerUnit * quantity;
                totalPriceDisplay.textContent = `${totalPrice.toFixed(2)}`;
            }
    
            quantityInput.addEventListener('input', updateTotalPrice);
    
            // Initial calculation
            updateTotalPrice();
        });
    </script>
    @endpush
    
    @endsection
