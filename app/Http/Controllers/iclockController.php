<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class iclockController extends Controller
{
    public function __invoke(Request $request) {}

    // Handshake: receives device handshake, logs it, updates device status, and forwards handshake to cPanel.
    public function handshake(Request $request)
    {
       

        $data = [
            'url'    => json_encode($request->all()),
            'data'   => $request->getContent(),
            'sn'     => $request->input('SN'),
            'option' => $request->input('option'),
        ];
        DB::table('device_log')->insert($data);

        // Update device status in AWS DB
        DB::table('devices')->updateOrInsert(
            ['no_sn' => $request->input('SN')],
            ['online' => now()]
        );

        // Forward handshake data to cPanel (AWS sends without prefix)
        $cpanelUrl = "https://hrt.bluedreamgroup.com/api/receive-handshake"; // Replace with your actual cPanel URL
        $response = Http::get($cpanelUrl, [
            'sn'     => $request->input('SN'),
            'option' => $request->input('option'),
        ]);

        if ($response->successful()) {
            Log::info('Handshake successfully sent to cPanel');
        } else {
            Log::info('Failed to send handshake to cPanel', ['response' => $response->body()]);
        }

        $r = "GET OPTION FROM: {$request->input('SN')}\r\n".
             "Stamp=9999\r\n".
             'OpStamp=' . time() . "\r\n".
             "ErrorDelay=60\r\n".
             "Delay=30\r\n".
             "ResLogDay=18250\r\n".
             "ResLogDelCount=10000\r\n".
             "ResLogCount=50000\r\n".
             "TransTimes=00:00;14:05\r\n".
             "TransInterval=1\r\n".
             "TransFlag=1111000000\r\n".
             "Realtime=1\r\n".
             "Encrypt=0";

        return $r;
    }

    // receiveRecords: processes attendance or log data from the device, stores it in AWS DB, and forwards inserted records to cPanel.
    public function receiveRecords(Request $request)
    {
       

        // If the table is ATTLOG, log extra details.
        // if ($request->input('table') === 'ATTLOG') {
        //     $arr = preg_split('/\\r\\n|\\r|,|\\n/', $request->getContent());
        //     foreach ($arr as $line) {
        //         if (empty($line)) continue;
        //         $data = explode("\t", $line);
        //         $employee_id = $data[0] ?? 'Unknown';
        //         $timestamp   = $data[1] ?? 'Unknown';
        //         Log::info('New Device scan event', [
        //             'employee_id' => $employee_id,
        //             'timestamp'   => $timestamp,
        //         ]);
        //     }
        // }

        // Store raw request in finger_log.
        $content = [
            'url'  => json_encode($request->all()),
            'data' => $request->getContent(),
        ];
        DB::table('finger_log')->insert($content);

        try {
            $arr = preg_split('/\\r\\n|\\r|,|\\n/', $request->getContent());
            $tot = 0;

            // If the table is OPERLOG, process each non-empty line.
            if ($request->input('table') == 'OPERLOG') {
                $operlogRecords = [];
                foreach ($arr as $line) {
                    if (!empty($line)) {
                        $operlogRecords[] = [
                            'sn'         => $request->input('SN'),
                            'table'      => 'OPERLOG',
                            'line'       => $line,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $tot++;
                    }
                }
                if (count($operlogRecords) > 0) {
                    $cpanelUrl = "https://hrt.bluedreamgroup.com/api/receive-data"; // cPanel endpoint for records
                    $responseOper = Http::post($cpanelUrl, [
                        'table'   => 'OPERLOG',
                        'records' => $operlogRecords,
                    ]);
                    if ($responseOper->successful()) {
                        Log::info('OPERLOG records forwarded to cPanel');
                    } else {
                        Log::info('Failed to forward OPERLOG records to cPanel', ['response' => $responseOper->body()]);
                    }
                }
                return 'OK: ' . $tot;
            }

            // Process attendance (or similar) data.
            $attendanceRecords = [];
            foreach ($arr as $line) {
                if (empty($line)) continue;
                $data = explode("\t", $line);
                $record = [
                    'sn'          => $request->input('SN'),
                    'table'       => $request->input('table'),
                    'stamp'       => $request->input('Stamp'),
                    'employee_id' => $data[0],
                    'timestamp'   => $data[1],
                    'status1'     => $this->validateAndFormatInteger($data[2] ?? null),
                    'status2'     => $this->validateAndFormatInteger($data[3] ?? null),
                    'status3'     => $this->validateAndFormatInteger($data[4] ?? null),
                    'status4'     => $this->validateAndFormatInteger($data[5] ?? null),
                    'status5'     => $this->validateAndFormatInteger($data[6] ?? null),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
                DB::table('in_out_records')->insert($record);
                $attendanceRecords[] = $record;
                $tot++;
            }
            if (count($attendanceRecords) > 0) {
                $cpanelUrl = "https://hrt.bluedreamgroup.com/api/receive-data";
                $responseAttendance = Http::post($cpanelUrl, [
                    'table'   => 'in_out_records',
                    'records' => $attendanceRecords,
                ]);
                if ($responseAttendance->successful()) {
                    Log::info('Attendance records forwarded to cPanel');
                } else {
                    Log::info('Failed to forward attendance records to cPanel', ['response' => $responseAttendance->body()]);
                }
            }
            return 'OK: ' . $tot;
        } catch (\Throwable $e) {
            $errorData = ['error' => $e->getMessage()];
            DB::table('error_log')->insert($errorData);
            report($e);
            return 'ERROR: ' . $e->getMessage();
        }
    }

    public function test(Request $request)
    {
        $log = ['data' => $request->getContent()];
        DB::table('finger_log')->insert($log);
        $cpanelUrl = "https://hrt.bluedreamgroup.com/api/receive-data";
        $responseTest = Http::post($cpanelUrl, [
            'table'   => 'finger_log',
            'records' => [$log],
        ]);
        if ($responseTest->successful()) {
            Log::info('Test record forwarded to cPanel');
        } else {
            Log::info('Failed to forward test record to CPanel', ['response' => $responseTest->body()]);
        }
    }

    public function getrequest(Request $request)
    {
        return 'OK';
    }

    private function validateAndFormatInteger($value)
    {
        return isset($value) && $value !== '' ? (int)$value : null;
    }
}
