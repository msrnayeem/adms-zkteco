<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Models\Shift;

class iclockController extends Controller
{

    public function __invoke(Request $request) {}

    // handshake
    public function handshake(Request $request)
    {

        $data = [
            'url' => json_encode($request->all()),
            'data' => $request->getContent(),
            'sn' => $request->input('SN'),
            'option' => $request->input('option'),
        ];
        DB::table('device_log')->insert($data);

        // update status device
        DB::table('devices')->updateOrInsert(
            ['no_sn' => $request->input('SN')],
            ['online' => Carbon::now('Asia/Dhaka')]
        );


        $response = Http::post("https://hrt.bluedreamgroup.com/api/receive-handshake", [
            'SN'     => $request->input('SN'),
            'option' => Carbon::now('Asia/Dhaka'),
        ]);

        // Log::info("Handshake Response from cPanel", [
        //     'http_code' => $response->status(),
        //     'response'  => $response->body()
        // ]);


        $r = "GET OPTION FROM: {$request->input('SN')}\r\n" .
            "Stamp=9999\r\n" .
            "OpStamp=" . time() . "\r\n" .
            "ErrorDelay=60\r\n" .
            "Delay=30\r\n" .
            "ResLogDay=18250\r\n" .
            "ResLogDelCount=10000\r\n" .
            "ResLogCount=50000\r\n" .
            "TransTimes=00:00;14:05\r\n" .
            "TransInterval=1\r\n" .
            "TransFlag=1111000000\r\n" .
            //  "TimeZone=7\r\n" .
            "Realtime=1\r\n" .
            "Encrypt=0";

        return $r;
    }
    //$r = "GET OPTION FROM:%s{$request->SN}\nStamp=".strtotime('now')."\nOpStamp=1565089939\nErrorDelay=30\nDelay=10\nTransTimes=00:00;14:05\nTransInterval=1\nTransFlag=1111000000\nTimeZone=7\nRealtime=1\nEncrypt=0\n";
    // implementasi https://docs.nufaza.com/docs/devices/zkteco_attendance/push_protocol/
    // setting timezone
    // request absensi

    public function receiveRecords(Request $request)
    {

        //DB::connection()->enableQueryLog();
        $content['url'] = json_encode($request->all());
        $content['data'] = $request->getContent();;
        DB::table('finger_log')->insert($content);
        try {
            // $post_content = $request->getContent();
            //$arr = explode("\n", $post_content);
            $arr = preg_split('/\\r\\n|\\r|,|\\n/', $request->getContent());
            //$tot = count($arr);
            $tot = 0;
            //operation log
            if ($request->input('table') == "OPERLOG") {
                // $tot = count($arr) - 1;
                foreach ($arr as $rey) {
                    if (isset($rey)) {
                        $tot++;
                    }
                }
                return "OK: " . $tot;
            }
            $attendanceRecords = [];
            //attendance
            foreach ($arr as $rey) {

                // $data = preg_split('/\s+/', trim($rey));
                $data = explode("\t", $rey);

                // Check if the expected index exists to avoid Undefined array key error
                $employee_id = $data[0] ?? 'Unknown';
                $timestamp = $data[1] ?? 'Unknown';
                $method = $data[3] ?? null;

                // Log the values to make sure we see what's being processed
                Log::info('Processed Attendance Record', [
                    'employee_id' => $employee_id,
                    'timestamp' => $timestamp,
                    'method' => $method,
                ]);

                if ($employee_id == 'Unknown' || $timestamp == 'Unknown' || $method == null) {
                    continue;
                } else {
                    $q['sn'] = $request->input('SN');
                    $q['table'] = $request->input('table');
                    $q['stamp'] = $request->input('Stamp');
                    $q['employee_id'] = $employee_id;
                    $q['timestamp'] = $timestamp;
                    $q['status2'] = $method;
                    $q['created_at'] = now();
                    $q['updated_at'] = now();
                    DB::table('in_out_records')->insert($q);
                    $attendanceRecords[] = $q;
                    $tot++;
                }
            }

            if (count($attendanceRecords) > 0) {
                $response = Http::post("https://hrt.bluedreamgroup.com/api/receive-data", [
                    'table'   => 'new_in_out_records',
                    'records' => $attendanceRecords,
                ]);

                if ($response->status() == 200) {
                    Log::info("success store from cPanel", [
                        'http_code' => $response->status(),
                        'response'  => $response->body(),
                    ]);
                } else {
                    Log::info("failed to store in cpanel", [
                        'http_code' => $response->status(),
                        'response'  => $response->body(),
                    ]);
                }
            }

            return "OK: " . $tot;
        } catch (Throwable $e) {

            $data['error'] = $e;
            // DB::table('error_log')->insert($data);
            report($e);
            return "ERROR: " . $tot . "\n";
        }
    }
    public function test(Request $request)
    {
        $log['data'] = $request->getContent();
        DB::table('finger_log')->insert($log);
    }
    public function getrequest(Request $request)
    {
        // $r = "GET OPTION FROM: ".$request->SN."\nStamp=".strtotime('now')."\nOpStamp=".strtotime('now')."\nErrorDelay=60\nDelay=30\nResLogDay=18250\nResLogDelCount=10000\nResLogCount=50000\nTransTimes=00:00;14:05\nTransInterval=1\nTransFlag=1111000000\nRealtime=1\nEncrypt=0";

        return "OK";
    }

    public function inOutTrigger(Request $request)
    {
        if ($request->input('table') === 'ATTLOG') {
            // Extract employee ID from request content and log it
            $arr = preg_split('/\\r\\n|\\r|,|\\n/', $request->getContent());

            foreach ($arr as $rey) {
                if (empty($rey)) {
                    continue;
                }
                // Split the data from the attendance record
                $data = explode("\t", $rey);

                // Assuming employee ID is the first field in the data
                $employee_id = $data[0] ?? 'Unknown';

                // Assuming timestamp is the second field in the data
                $timestamp = $data[1] ?? 'Unknown';
                $method = $data[3] ?? null;

                if ($employee_id !== 'Unknown' || $timestamp !== 'Unknown' || $method !== null) {
                    $q['sn'] = $request->input('SN');
                    $q['table'] = $request->input('table');
                    $q['stamp'] = $request->input('Stamp');
                    $q['employee_id'] = $employee_id;
                    $q['timestamp'] = $timestamp;
                    $q['status2'] = $method;
                    $q['created_at'] = now();
                    $q['updated_at'] = now();
                    DB::table('in_out_records')->insert($q);
                }
            }
        }
    }

    public function updateAttendance(Request $request)
    {
        // Extract the provided employee_id (zk_device_id) and punch timestamp
        $zkEmployeeId = $request->input('employee_id');
        $timestamp = Carbon::parse($request->input('timestamp'));  // This will handle the timestamp
        $date = $timestamp->toDateString();      // e.g. "2025-03-17"
        $timeOnly = $timestamp->toTimeString();  // e.g. "17:30:00" (for exit punch)

        // Get user details including shift
        $user = User::where('zk_device_id', $zkEmployeeId)
            ->select('id', 'shift_id')
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Get shift details
        $shift = Shift::find($user->shift_id);

        if (!$shift) {
            return response()->json(['message' => 'Shift not found'], 404);
        }

        // Convert shift times to Carbon instances
        $shiftStartTime = Carbon::parse($shift->entry_time)->format('H:i:s');  // Shift start time as time string (e.g. "10:00:00")
        $shiftEndTime = Carbon::parse($shift->out_time)->format('H:i:s');      // Shift end time as time string (e.g. "18:00:00")
        $lateEntryLimit = Carbon::parse($shift->entry_time)->addMinutes($shift->late_entry)->format('H:i:s');  // e.g. "10:15:00"
        $earlyOutLimit = Carbon::parse($shift->out_time)->subMinutes($shift->early_out_time)->format('H:i:s'); // e.g. "17:45:00"

        // Check if attendance entry already exists for the day
        $attendance = DB::table('attendances')
            ->where('employee_id', $user->id)
            ->where('date', $date)
            ->first();

        if (!$attendance) {
            // First punch of the day
            $isLate = $timestamp->format('H:i:s') > $lateEntryLimit; // Compare the punch time with late entry limit

            // Insert the first attendance entry
            DB::table('attendances')->insert([
                'employee_id'    => $user->id,
                'date'           => $date,
                'shift_start_at' => $shiftStartTime,
                'user_entry_time' => $timeOnly, // Store the time in time format
                'is_late'        => $isLate,
                'shift_end_at'   => $shiftEndTime,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            return response()->json(['message' => 'Attendance entry created'], 201);
        } else {
            // 2nd punch of the day (Exit)
            $isEarly = $timestamp->format('H:i:s') < $earlyOutLimit; // Compare the exit time with early out limit

            // Update exit time and early out related fields
            DB::table('attendances')
                ->where('id', $attendance->id)
                ->update([
                    'user_exit_time' => $timeOnly, // Store the exit time as a time
                    'is_early'       => $isEarly,
                    'updated_at'     => now(),
                ]);

            return response()->json(['message' => 'Attendance exit updated'], 200);
        }
    }
}
