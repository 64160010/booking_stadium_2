@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Profile</h1>
    <form action="{{ route('profile.update') }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Add your profile fields here -->
        <div class="mb-3">
            <label for="fname" class="form-label">First Name</label>
            <input type="text" class="form-control" id="fname" name="fname" value="{{ old('fname', Auth::user()->fname) }}">
        </div>

        <div class="mb-3">
            <label for="lname" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="lname" name="lname" value="{{ old('lname', Auth::user()->lname) }}">
        </div>
        

        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>
@endsection
