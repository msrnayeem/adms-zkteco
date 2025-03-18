@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Attendance</h2>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Filter Form -->
        <form method="GET" action="{{ route('attendance.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label for="employee_id">Employee</label>
                    <select name="employee_id" class="form-control">
                        <option value="">All</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}"
                                {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="day">Day</label>
                    <input type="number" name="day" class="form-control" value="{{ request('day') }}"
                        placeholder="1-31">
                </div>

                <div class="col-md-2">
                    <label for="month">Month</label>
                    <input type="number" name="month" class="form-control" value="{{ request('month') }}"
                        placeholder="1-12">
                </div>

                <div class="col-md-2">
                    <label for="year">Year</label>
                    <input type="number" name="year" class="form-control" value="{{ request('year') }}"
                        placeholder="YYYY">
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mx-2">Filter</button>
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>

        <!-- Attendance Table -->
        <div class="table-responsive">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing {{ $attendances->firstItem() }} to {{ $attendances->lastItem() }} of
                    {{ $attendances->total() }}
                    entries
                </div>
                <div>
                    {{ $attendances->links() }}
                </div>
            </div>

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
                    @forelse ($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->employee_id }}</td>
                            <td>{{ $attendance->employee->name }}</td>
                            <td>{{ $attendance->day }}-{{ str_pad($attendance->month, 2, '0', STR_PAD_LEFT) }}-{{ $attendance->year }}
                            </td>
                            <td>{{ $attendance->formatted_shift_start_at }}</td>
                            <td>{{ $attendance->formatted_user_entry_time }}</td>
                            <td>{{ $attendance->is_late ? 'Yes' : 'No' }}</td>
                            <td>{{ $attendance->formatted_shift_end_at }}</td>
                            <td>{{ $attendance->formatted_user_exit_time }}</td>
                            <td>{{ $attendance->is_early ? 'Yes' : 'No' }}</td>
                            <td>{{ $attendance->manual_entry ? 'Yes' : 'No' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">No attendance records found.</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>



    </div>
@endsection
