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
                        <th>ID</th>
                        <th>Employee Name</th>
                        <th>Date</th>
                        <th>Shift Start</th>
                        <th>Entry Time</th>
                        <th>Is Late</th>
                        <th>Shift End</th>
                        <th>Exit Time</th>
                        <th>Is Early</th>
                        <th>Manual Entry</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->employee_id }}</td>
                            <td>{{ $attendance->employee->name }}</td>
                            <td>
                                {{ $attendance->day }}-{{ str_pad($attendance->month, 2, '0', STR_PAD_LEFT) }}-{{ $attendance->year }}
                            </td>
                            <td>{{ $attendance->formatted_shift_start_at }}</td>
                            <td>{{ $attendance->formatted_user_entry_time }}</td>
                            <td>{{ $attendance->is_late ? 'Yes' : 'No' }}</td>
                            <td>{{ $attendance->formatted_shift_end_at }}</td>
                            <td>{{ $attendance->formatted_user_exit_time }}</td>
                            <td>{{ $attendance->is_early ? 'Yes' : 'No' }}</td>
                            <td>{{ $attendance->manual_entry ? 'Yes' : 'No' }}</td>
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
