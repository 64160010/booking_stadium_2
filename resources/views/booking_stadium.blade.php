{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Book a Football Field</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('booking.submit') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="field" class="form-label">Field</label>
            <select class="form-select" id="field" name="field" required>
                <option value="">Select a field</option>
                <option value="Field 1">Field 1 (7 people)</option>
                <option value="Field 2">Field 2 (7 people)</option>
                <option value="Field 3">Field 3 (7 people)</option>
                <option value="Field 4">Field 4 (7 people)</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" required>
        </div>

        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control" id="date" name="date" required>
        </div>

        <div class="mb-3">
            <label for="start_time" class="form-label">Start Time</label>
            <input type="time" class="form-control" id="start_time" name="start_time" required>
        </div>

        <div class="mb-3">
            <label for="end_time" class="form-label">End Time</label>
            <input type="time" class="form-control" id="end_time" name="end_time" required>
        </div>

        <button type="submit" class="btn btn-primary">Book Field</button>
    </form>
</div>
</body>
</html> --}}
ก็งงทำไมไม่ใช้หน้านี้ เอาไว้ถามเพ้ด มันไปใช้ใน หน้า from.blade.php