@extends('layouts.app') {{-- Asumsikan Anda memiliki layout utama --}}

@section('content')
    <div class="container">
        <h2 class="mb-4">Attendance</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered data-table">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Date</th>
                        <th>Shift Start</th>
                        <th>Entry Time</th>
                        <th>Late Entry</th>
                        <th>Shift End</th>
                        <th>Exit Time</th>
                        <th>Late Out Time</th>
                        <th>Is Late</th>
                        <th>Is Early</th>
                        <th>Manual Entry</th>
                        <th>Manual Entry By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->employee_id }}</td>
                            <td>{{ $attendance->date }}</td>
                            <td>{{ $attendance->shift_start_time ?? 'N/A' }}</td>
                            <td>{{ $attendance->entry_time }}</td>
                            <td>{{ $attendance->late_entry }}</td>
                            <td>{{ $attendance->shift_end_time ?? 'N/A' }}</td>
                            <td>{{ $attendance->exit_time ?? 'N/A' }}</td>
                            <td>{{ $attendance->late_out_time }}</td>
                            <td>{{ $attendance->is_late ? 'Yes' : 'No' }}</td>
                            <td>{{ $attendance->is_early ? 'Yes' : 'No' }}</td>
                            <td>{{ $attendance->manual_entry ? 'Yes' : 'No' }}</td>
                            <td>{{ $attendance->manual_entry_by ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- source: https://stackoverflow.com/a/70119390 -->
        <div class="d-felx justify-content-center">
            {{ $attendances->links() }} {{-- Tampilkan pagination jika ada --}}
        </div>


    </div>
@endsection
